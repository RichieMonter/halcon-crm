<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::latest()->paginate(10);
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateCustomer($request);

        if (!empty($validated['rfc'])) {
            $validated['rfc'] = strtoupper($validated['rfc']);
        }

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Cliente creado correctamente.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $this->validateCustomer($request, $customer->customer_number);

        if (!empty($validated['rfc'])) {
            $validated['rfc'] = strtoupper($validated['rfc']);
        }

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Cliente eliminado correctamente.');
    }

    private function validateCustomer(Request $request, $customerId = null)
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^(?=.*[a-zA-ZáéíóúÁÉÍÓÚñÑ])[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.\,\-]+$/'
            ],
            'email' => [
                'required',
                'string',
                'max:255',
                // Una sola regla estricta: bloquea símbolos raros tanto en la cuenta como en el dominio
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'phone' => [
                'nullable',
                'regex:/^\+?[0-9\s\-]{7,15}$/'
            ],
            'company_name' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^(?=.*[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ])[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.\,\&\-]+$/'
            ],
            'address' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^(?=.*[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ])[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.\,\#\-\/]+$/'
            ],
            'delivery_address' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^(?=.*[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ])[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.\,\#\-\/]+$/'
            ],
            'rfc' => [
                'nullable',
                'string',
                'regex:/^([A-ZÑ&]{3,4})([0-9]{2})(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])([A-Z0-9]{3})$/i',
                'unique:customers,rfc,' . $customerId . ',customer_number',
            ],
            'tax_regime' => 'required|string',
        ], [
            'name.regex' => 'El nombre de contacto debe contener letras reales y no solo símbolos.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.regex' => 'El correo electrónico introducido no tiene un formato válido (ej. usuario@dominio.com).',
            'company_name.regex' => 'La Razón Social debe incluir letras o números válidos.',
            'address.regex' => 'La dirección debe ser un domicilio válido.',
            'delivery_address.regex' => 'La dirección de entrega debe ser un domicilio válido.',
            'phone.regex' => 'El teléfono solo permite números, espacios, guiones y el símbolo +.',
            'rfc.regex' => 'El RFC debe tener exactamente 12 caracteres (Moral) o 13 caracteres (Física) con formato SAT válido.',
            'rfc.unique' => 'Ya existe un cliente registrado con este RFC.',
            'tax_regime.required' => 'Debes seleccionar un régimen fiscal.',
        ]);
    }
}