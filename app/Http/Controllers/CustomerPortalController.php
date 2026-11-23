<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MarketingCampaign;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerPortalController extends Controller
{
    public function index(Request $request)
    {
        $customer = $this->customer($request->user());
        $orders = $customer->orders()->latest()->paginate(10);
        $campaigns = MarketingCampaign::visibleInPortal($customer->id)->orderBy('starts_on')->get();

        $offers = [
            'impresiones' => ['title' => 'Impresiones', 'description' => 'Blanco y negro o color para documentos, tareas y trabajos escolares.'],
            'engargolado' => ['title' => 'Engargolados', 'description' => 'Encuadernación para cuadernos, proyectos y presentaciones.'],
            'enmicado' => ['title' => 'Enmicados', 'description' => 'Protección para documentos, tarjetas y material escolar.'],
            'papeleria' => ['title' => 'Productos de papelería', 'description' => 'Material para la escuela, la oficina y tus proyectos.'],
        ];
        $products = $this->products()->orderBy('name')->get();
        foreach ($offers as $key => &$offer) {
            $offer['products'] = $products->filter(function (Product $product) use ($key) {
                if ($key === 'papeleria') {
                    return $product->type === 'PRODUCTO';
                }
                $description = Str::lower(Str::ascii($product->name.' '.$product->category));
                $term = $key === 'impresiones' ? 'impresion' : $key;

                return $product->type === 'SERVICIO' && str_contains($description, $term);
            });
        }

        return view('portal.home', compact('orders', 'campaigns', 'offers'));
    }

    public function catalog(Request $request)
    {
        $filters = $request->validate([
            'buscar' => 'nullable|string|max:100',
            'categoria' => 'nullable|string|max:100',
            'tipo' => 'nullable|in:PRODUCTO,SERVICIO',
            'orden' => 'nullable|in:precio-asc,precio-desc',
        ]);
        $categories = $this->products()->whereNotNull('category')->where('category', '!=', '')
            ->distinct()->orderBy('category')->pluck('category');
        $query = $this->products()
            ->when($filters['categoria'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['tipo'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['orden'] ?? null, fn ($query, $sort) => $query->orderBy('price', $sort === 'precio-asc' ? 'asc' : 'desc'));

        foreach (preg_split('/\s+/', trim($filters['buscar'] ?? ''), -1, PREG_SPLIT_NO_EMPTY) as $term) {
            $query->where(fn ($match) => $match->whereLike('name', '%'.$term.'%')
                ->orWhereLike('code', '%'.$term.'%')->orWhereLike('category', '%'.$term.'%'));
        }
        $products = $query->orderBy('name')->orderBy('id')->paginate(24)->appends($filters);

        return view('portal.catalog', compact('products', 'categories', 'filters'));
    }

    public function create(Request $request)
    {
        $id = $request->validate(['product_id' => 'nullable|integer'])['product_id'] ?? null;
        $product = $id ? $this->products()->findOrFail($id) : null;

        $draft = new Order(['status' => 'pendiente']);
        $draft->setRelation('items', collect($product ? [
            new OrderItem(['product_id' => $product->id, 'quantity' => 1, 'unit_price' => $product->price]),
        ] : []));

        return $this->cartView($draft, $this->customer($request->user()));
    }

    public function store(Request $request)
    {
        [$quantities, $payment] = $this->validateCart($request);
        if (!$quantities) {
            return $this->cartResponse($request, 'El carrito está vacío.');
        }

        $customer = $this->customer($request->user());
        DB::transaction(function () use ($customer, $request, $quantities, $payment) {
            $folio = 'WEB-'.Str::ulid();
            $order = $customer->orders()->create([
                'created_by_user_id' => $request->user()->id,
                'order_number' => $folio,
                'invoice_number' => $folio,
                'order_type' => 'Cotización web',
                'status' => 'pendiente',
            ]);
            $this->saveCart($order->refresh(), $quantities, $payment);
        });

        return $this->cartResponse($request, 'Cotización guardada.');
    }

    public function cart(Request $request, Order $order)
    {
        $this->authorizeOrder($request->user(), $order);
        $order->load('items.product');

        return $this->cartView($order, $this->customer($request->user()));
    }

    public function update(Request $request, Order $order)
    {
        $customerId = $this->authorizeOrder($request->user(), $order);
        [$quantities, $payment] = $this->validateCart($request);

        DB::transaction(function () use ($order, $quantities, $customerId, $payment, $request) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            abort_unless((int) $order->customer_id === $customerId, 404);
            abort_unless($order->status === 'pendiente', 403);
            if (!$quantities) {
                abort_unless((int) $order->created_by_user_id === (int) $request->user()->id, 403);
                $order->delete();
            } else {
                $this->saveCart($order, $quantities, $payment);
            }
        });

        return $this->cartResponse($request, $quantities ? 'Cotización guardada.' : 'Se eliminó la cotización vacía.');
    }

    private function validateCart(Request $request): array
    {
        $data = $request->validate([
            'quantities' => 'present|array',
            'quantities.*' => 'nullable|integer|min:0|max:9999',
        ]);
        $quantities = array_filter($data['quantities']);
        $payment = $quantities ? $request->validate([
            'payment_method' => ['required', Rule::in(array_keys(Order::PAYMENT_METHODS))],
        ], [
            'payment_method.required' => 'Selecciona el método de pago para guardar la cotización.',
            'payment_method.in' => 'Selecciona un método de pago válido.',
        ])['payment_method'] : null;

        return [$quantities, $payment];
    }

    private function saveCart(Order $order, array $quantities, string $payment): void
    {
        $products = $this->products()->whereIn('id', array_keys($quantities))->get()->keyBy('id');
        if ($products->count() !== count($quantities)) {
            throw ValidationException::withMessages(['quantities' => 'Hay productos o servicios que ya no están disponibles.']);
        }

        $existing = $order->items()->get();
        $order->items()->delete();
        $subtotal = 0;
        foreach ($quantities as $id => $quantity) {
            $product = $products[$id];
            if ($product->type === 'PRODUCTO' && $quantity > $product->stock) {
                throw ValidationException::withMessages(['quantities' => 'La cantidad supera las existencias de '.$product->name.'.']);
            }
            $price = $product->price;
            $lineTotal = round($price * $quantity, 2);
            $subtotal += $lineTotal;
            $unchanged = $existing->where('product_id', $id);
            $lines = $unchanged->sum('quantity') == $quantity && $unchanged->every(fn ($line) => $line->unit_price == $price)
                ? $unchanged->map(fn ($line) => $line->only(['product_id', 'quantity', 'unit_price', 'subtotal', 'printer', 'print_mode']))->all()
                : [['product_id' => $id, 'quantity' => $quantity, 'unit_price' => $price, 'subtotal' => $lineTotal]];
            $order->items()->createMany($lines);
        }
        $discount = $order->discount_percent === null
            ? min($subtotal, $order->discount)
            : round($subtotal * $order->discount_percent / 100, 2);
        $order->update([
            'subtotal' => $subtotal, 'discount' => $discount, 'total' => round($subtotal - $discount, 2),
            'payment_method' => $payment,
        ]);
    }

    private function cartView(Order $order, Customer $customer)
    {
        $isReadOnly = $order->status !== 'pendiente';
        $products = $this->products()->orderBy('name')->get(['id', 'code', 'name', 'price', 'stock', 'type', 'category']);
        $categories = $products->pluck('category')->filter()->unique()->sort();
        $cartOrder = $order->only(['id', 'status', 'subtotal', 'total', 'discount', 'discount_percent', 'payment_method']);
        $cartOrder['items'] = $order->items->filter(fn ($item) => $products->contains('id', $item->product_id))
            ->map(fn ($item) => $item->only(['product_id', 'quantity', 'unit_price']))->values();

        return view('pos.index', [
            'order' => $order, 'cartOrder' => $cartOrder, 'products' => $products, 'categories' => $categories,
            'customers' => collect([$customer]), 'isReadOnly' => $isReadOnly,
            'printers' => [], 'recommendations' => [], 'paymentMethods' => Order::PAYMENT_METHODS, 'isPortal' => true,
        ]);
    }

    private function cartResponse(Request $request, string $message)
    {
        $redirect = route('portal.home').'#mis-pedidos';
        $request->session()->flash('success', $message);

        return $request->expectsJson()
            ? response()->json(['success' => true, 'redirect' => $redirect, 'message' => $message])
            : redirect()->to($redirect);
    }

    private function products(): Builder
    {
        return Product::whereIn('type', ['PRODUCTO', 'SERVICIO']);
    }

    private function authorizeOrder(User $user, Order $order): int
    {
        $customerId = $this->customer($user)->id;
        abort_unless((int) $order->customer_id === $customerId, 404);

        return $customerId;
    }

    private function customer(User $user): Customer
    {
        if ($user->customer_id) {
            return Customer::findOrFail($user->customer_id);
        }

        return DB::transaction(function () use ($user) {
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($user->customer_id) {
                return Customer::findOrFail($user->customer_id);
            }
            $details = array_filter($user->customer_registration ?? [], fn ($value) => $value !== null && $value !== '');
            $email = Str::lower($user->email);
            $customer = Customer::whereRaw('LOWER(email) = ?', [$email])->first();
            if (isset($details['rfc']) && Customer::where('rfc', $details['rfc'])
                ->when($customer, fn ($query) => $query->whereKeyNot($customer->id))->exists()) {
                unset($details['rfc']);
            }
            if (!$customer) {
                $customer = Customer::firstOrCreate(['email' => $email], ['name' => $user->name] + $details);
            }
            abort_if(User::where('customer_id', $customer->id)->exists(), 409, 'Este perfil ya está vinculado a otra cuenta. Contacta con la papelería.');
            $customer->fill(array_filter($details, fn ($field) => blank($customer->getAttribute($field)), ARRAY_FILTER_USE_KEY))->save();
            $user->forceFill(['customer_id' => $customer->id, 'customer_registration' => null])->save();

            return $customer;
        });
    }
}
