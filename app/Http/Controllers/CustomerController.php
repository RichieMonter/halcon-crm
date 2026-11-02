<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

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

        return view('customers.index', compact('customers'));
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
