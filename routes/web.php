<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('accounts', AccountController::class)->except(['update']);
    Route::patch('accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');

    Route::resource('categories', CategoryController::class)->except(['update']);
    Route::patch('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');

    Route::resource('transactions', TransactionController::class)->except(['update']);
    Route::patch('transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
});

require __DIR__.'/auth.php';
