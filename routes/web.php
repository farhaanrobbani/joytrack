<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FuelRecordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceRecordController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('/offline', 'offline')->name('offline');

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

    Route::resource('vehicles', VehicleController::class)->except(['update']);
    Route::patch('vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');

    Route::resource('fuel-records', FuelRecordController::class);

    Route::resource('service-records', ServiceRecordController::class);

    Route::resource('documents', DocumentController::class)->except(['show']);

    Route::resource('subscriptions', SubscriptionController::class)->except(['show']);
    Route::post('subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])->name('subscriptions.renew');

    Route::get('reminders', [ReminderController::class, 'index'])->name('reminders.index');

    Route::get('reports/finance', [ReportController::class, 'finance'])->name('reports.finance');
    Route::get('reports/vehicle', [ReportController::class, 'vehicle'])->name('reports.vehicle');
    Route::get('reports/fuel', [ReportController::class, 'fuel'])->name('reports.fuel');
    Route::get('reports/service', [ReportController::class, 'service'])->name('reports.service');

    Route::get('export/transactions', [ExportController::class, 'transactions'])->name('export.transactions');
    Route::get('export/finance', [ExportController::class, 'finance'])->name('export.finance');
    Route::get('export/vehicle', [ExportController::class, 'vehicle'])->name('export.vehicle');
    Route::get('export/fuel', [ExportController::class, 'fuel'])->name('export.fuel');
    Route::get('export/service', [ExportController::class, 'service'])->name('export.service');
    Route::get('export/transactions/excel', [ExportController::class, 'transactionsExcel'])->name('export.transactions.excel');
    Route::get('export/finance/excel', [ExportController::class, 'financeExcel'])->name('export.finance.excel');
    Route::get('export/vehicle/excel', [ExportController::class, 'vehicleExcel'])->name('export.vehicle.excel');
    Route::get('export/fuel/excel', [ExportController::class, 'fuelExcel'])->name('export.fuel.excel');
    Route::get('export/service/excel', [ExportController::class, 'serviceExcel'])->name('export.service.excel');
    Route::get('export/finance/pdf', [ExportController::class, 'financePdf'])->name('export.finance.pdf');
    Route::get('export/vehicle/pdf', [ExportController::class, 'vehiclePdf'])->name('export.vehicle.pdf');
    Route::get('export/transactions/pdf', [ExportController::class, 'transactionsPdf'])->name('export.transactions.pdf');
    Route::get('export/fuel/pdf', [ExportController::class, 'fuelPdf'])->name('export.fuel.pdf');
    Route::get('export/service/pdf', [ExportController::class, 'servicePdf'])->name('export.service.pdf');

    Route::post('attachments', [AttachmentController::class, 'store'])->name('attachments.store');
    Route::get('attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/toggle-role', [UserController::class, 'toggleRole'])->name('users.toggle');
        Route::get('/settings', [SiteSettingController::class, 'edit'])->name('settings.edit');
        Route::patch('/settings', [SiteSettingController::class, 'update'])->name('settings.update');
    });
});

require __DIR__.'/auth.php';
