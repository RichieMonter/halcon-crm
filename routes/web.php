<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Redirección inicial al login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas de Autenticación (Huéspedes - Sin iniciar sesión)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Rutas Protegidas (Usuarios Autenticados)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Módulos del Sistema
    Route::resource('customers', CustomerController::class);
    Route::resource('products', ProductController::class);
    
    // Rutas protegidas para el seguimiento de ventas / cotizaciones
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
});