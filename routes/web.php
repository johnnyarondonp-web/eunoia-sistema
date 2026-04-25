<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

// Redirección inicial (cuando alguien entra a tu dominio)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Rutas protegidas por autenticación
Route::middleware(['auth', 'verified'])->group(function () {
    
    // El Dashboard ahora llama al ProductController para mostrar tus productos
    Route::get('/dashboard', [ProductController::class, 'index'])->name('dashboard');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Productos
    Route::get('/productos/crear', [ProductController::class, 'create'])->name('products.create');
    Route::post('/productos', [ProductController::class, 'store'])->name('products.store');
    Route::get('/productos/{product}/editar', [ProductController::class, 'edit'])->name('products.edit');
    Route::patch('/productos/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/productos/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Ventas
    Route::prefix('ventas')->group(function () {
        Route::get('/nueva', [SaleController::class, 'create'])->name('sales.create');
        Route::post('/', [SaleController::class, 'store'])->name('sales.store');
        Route::get('/historial', [SaleController::class, 'index'])->name('sales.index');
    });

    // Balance
    Route::prefix('balance')->group(function () {
        Route::get('/', [ExpenseController::class, 'balance'])->name('expenses.balance');
        Route::post('/guardar', [ExpenseController::class, 'store'])->name('expenses.store');
    });
    Route::patch('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])
     ->name('products.toggle-status');
});

require __DIR__.'/auth.php';