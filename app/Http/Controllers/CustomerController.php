<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MarketingCampaign;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $customers = Customer::when($search, function($query, $search) {
            return $query->where('name', 'LIKE', "%{$search}%")
                         ->orWhere('company_name', 'LIKE', "%{$search}%")
                         ->orWhere('rfc', 'LIKE', "%{$search}%")
                         ->orWhere('email', 'LIKE', "%{$search}%");
        })
        ->orderBy('name')
        ->get();

        $topCustomers = Customer::rankedByPurchases()->limit(5)->get();
        $campaigns = MarketingCampaign::orderByDesc('starts_on')->orderByDesc('id')->paginate(8, ['*'], 'campanas')->withQueryString();
        $audiences = MarketingCampaign::AUDIENCES;

        return view('customers.index', compact('customers', 'topCustomers', 'campaigns', 'audiences'));
    }

    public function storeCampaign(Request $request)
    {
        MarketingCampaign::create($this->validateCampaign($request));

        return redirect()->route('customers.index')->with('success', 'Campaña de temporada guardada.');
    }

    public function editCampaign(MarketingCampaign $campaign)
    {
        return view('customers.campaign-edit', ['campaign' => $campaign, 'audiences' => MarketingCampaign::AUDIENCES]);
    }

    public function updateCampaign(Request $request, MarketingCampaign $campaign)
    {
        $campaign->update($this->validateCampaign($request));

        return redirect()->route('customers.index')->with('success', 'Campaña actualizada.');
    }

    public function endCampaign(MarketingCampaign $campaign)
    {
        $ended = MarketingCampaign::active()->whereKey($campaign->id)->update(['ended_at' => now()]);

        return $ended
            ? redirect()->route('customers.index')->with('success', 'Campaña terminada. Ya no se sugiere en el carrito.')
            : redirect()->route('customers.index')->with('error', 'Solo puedes terminar antes una campaña activa.');
    }

    public function destroyCampaign(MarketingCampaign $campaign)
    {
        $campaign->delete();

        return redirect()->route('customers.index')->with('success', 'Campaña eliminada.');
    }

    private function validateCampaign(Request $request): array
    {
        return $request->validateWithBag('campaign', [
            'name' => 'required|string|max:120',
            'starts_on' => 'required|date_format:Y-m-d',
            'ends_on' => 'required|date_format:Y-m-d|after_or_equal:starts_on',
            'audience' => ['required', Rule::in(array_keys(MarketingCampaign::AUDIENCES))],
            'discount_percent' => 'required|numeric|between:1,100|decimal:0,2',
        ], [
            'starts_on.date_format' => 'Ingresa una fecha de inicio válida.',
            'ends_on.date_format' => 'Ingresa una fecha de fin válida.',
            'ends_on.after_or_equal' => 'La fecha de fin debe ser igual o posterior al inicio.',
            'discount_percent.between' => 'El descuento debe estar entre 1 y 100 %.',
            'discount_percent.decimal' => 'El descuento admite hasta dos decimales.',
        ], ['starts_on' => 'fecha de inicio', 'ends_on' => 'fecha de fin', 'audience' => 'público', 'discount_percent' => 'descuento']);

    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:customers,email',
            'phone' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',
            'rfc' => 'nullable|string|max:13|unique:customers,rfc',
            'tax_regime' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'delivery_address' => 'nullable|string',
            'preferred_discount' => 'nullable|numeric|between:0,100|decimal:0,2',
        ]);

        if (empty($validated['company_name'])) {
            $validated['company_name'] = 'NA';
        }

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', '¡Cliente registrado correctamente!');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',
            'rfc' => 'nullable|string|max:13|unique:customers,rfc,' . $customer->id,
            'tax_regime' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'delivery_address' => 'nullable|string',
            'preferred_discount' => 'nullable|numeric|between:0,100|decimal:0,2',
        ]);

        if (empty($validated['company_name'])) {
            $validated['company_name'] = 'NA';
        }

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', '¡Datos actualizados con éxito!');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Cliente eliminado.');
    }
}
