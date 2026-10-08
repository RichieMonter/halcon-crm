<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if ($user && Hash::check($credentials['password'], $user->password_hash)) {
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->intended('/customers');
        }

        return back()->withErrors([
            'username' => 'Las credenciales proporcionadas no son válidas.',
        ])->onlyInput('username');
    }

    public function showRegister()
    {
        $roles = Role::all();
        return view('auth.register', compact('roles'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                'unique:users,username',
                // Exige al menos letras o números reales y permite guiones/puntos/guion bajo
                'regex:/^(?=.*[a-zA-Z0-9])[a-zA-Z0-9._\-]+$/'
            ],
            'password' => 'required|string|min:6|confirmed',
            'role_id'  => 'required|exists:roles,id',
        ], [
            'username.regex' => 'El nombre de usuario solo puede contener letras, números, puntos, guiones y debe incluir al menos un carácter alfanumérico.',
            'username.unique' => 'El nombre de usuario ya se encuentra registrado.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'role_id.required' => 'Debes seleccionar un rol para el usuario.',
        ]);

        $user = User::create([
            'username'      => $request->username,
            'password_hash' => Hash::make($request->password),
            'role_id'       => $request->role_id,
        ]);

        Auth::login($user);

        return redirect()->route('customers.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}