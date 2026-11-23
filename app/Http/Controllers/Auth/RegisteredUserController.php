<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register', ['staffRoles' => []]);
    }

    public function createStaff(): View
    {
        return view('auth.register', ['staffRoles' => User::STAFF_ROLES]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $details = $request->validate([
            'phone' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',
            'rfc' => ['nullable', 'string', 'max:13', Rule::unique('customers', 'rfc')->where(fn ($query) => $query->where('email', '!=', $request->input('email')))],
            'tax_regime' => ['nullable', Rule::in(['601', '612', '626'])],
            'address' => 'nullable|string|max:1000',
            'delivery_address' => 'nullable|string|max:1000',
        ]);

        return $this->register($request, User::CUSTOMER_ROLE, $details);
    }

    public function storeStaff(Request $request): RedirectResponse
    {
        $request->validate([
            'staff_code' => ['required', 'string', 'regex:/\A[0-9]{4}\z/'],
            'role_id' => ['required', Rule::in(array_keys(User::STAFF_ROLES))],
        ], [
            'staff_code.required' => 'Ingresa el código de personal.',
            'staff_code.regex' => 'El código debe contener exactamente cuatro dígitos.',
            'role_id.required' => 'Selecciona el rol del personal.',
            'role_id.in' => 'Selecciona un rol de personal válido.',
        ]);

        $code = (string) config('staff.registration_code');
        if (strlen($code) !== 4 || !hash_equals($code, $request->string('staff_code')->toString())) {
            throw ValidationException::withMessages(['staff_code' => 'El código de personal es incorrecto.']);
        }

        return $this->register($request, $request->integer('role_id'));
    }

    private function register(Request $request, int $role, ?array $details = null): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role,
            'customer_registration' => $details,
        ]);

        Auth::login($user);

        try {
            event(new Registered($user));
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return redirect()->route('verification.notice')->with('status', 'verification-delivery-failed');
        }

        return redirect()->route('verification.notice');
    }
}
