<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('orders.index') : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('orders.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/impresoras', [PrinterController::class, 'index'])->name('printers.index');
    Route::post('/impresoras/{printer}/lecturas', [PrinterController::class, 'store'])->name('printers.readings.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Módulos del Sistema CRM
    Route::post('/customers/campanas', [CustomerController::class, 'storeCampaign'])->name('customers.campaigns.store');
    Route::get('/customers/campanas/{campaign}/editar', [CustomerController::class, 'editCampaign'])->name('customers.campaigns.edit');
    Route::put('/customers/campanas/{campaign}', [CustomerController::class, 'updateCampaign'])->name('customers.campaigns.update');
    Route::patch('/customers/campanas/{campaign}/terminar', [CustomerController::class, 'endCampaign'])->name('customers.campaigns.end');
    Route::delete('/customers/campanas/{campaign}', [CustomerController::class, 'destroyCampaign'])->name('customers.campaigns.destroy');
    Route::resource('customers', CustomerController::class);
    Route::resource('products', ProductController::class);
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');

    // Módulo de Punto de Venta (POS) / Carrito
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::post('/pos', [PosController::class, 'store'])->name('pos.store');


    //Modulo Pantalla de venta
    Route::get('/orders/{order}/pos', [PosController::class, 'index'])->name('orders.pos');
    Route::post('/orders/{order}/pos', [PosController::class, 'update'])->name('orders.pos.update');

    // Buscador Global
    Route::get('/search', [SearchController::class, 'globalSearch'])->name('search.global');
});

require __DIR__.'/auth.php';
