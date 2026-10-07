<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Livewire\Projects\Form;
use App\Livewire\Projects\Index;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');


Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/data', [ProjectController::class, 'data'])->name('projects.data');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    // export
    Route::get('projects/export', [ProjectController::class, 'export'])->name('projects.export');

    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/data', [UserController::class, 'data'])->name('users.data');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');

    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');


    Route::prefix('products')
    ->name('products.')
    ->controller(ProductController::class)
    ->group(function () {

        Route::get('/', 'index')->name('index');
        Route::get('/data', 'data')->name('data');

        Route::post('/', 'store')->name('store');
        Route::get('/{product}', 'show')->name('show');
        Route::put('/{product}', 'update')->name('update');
        Route::delete('/{product}', 'destroy')->name('destroy');
    });

    Route::prefix('suppliers')
    ->name('suppliers.')
    ->controller(SupplierController::class)
    ->group(function () {

        Route::get('/', 'index')->name('index');
        Route::get('/data', 'data')->name('data');

        Route::post('/', 'store')->name('store');
        Route::get('/{supplier}', 'show')->name('show');
        Route::put('/{supplier}', 'update')->name('update');
        Route::delete('/{supplier}', 'destroy')->name('destroy');
    });

    Route::prefix('customers')
    ->name('customers.')
    ->controller(CustomerController::class)
    ->group(function () {

        Route::get('/', 'index')->name('index');
        Route::get('/data', 'data')->name('data');

        Route::post('/', 'store')->name('store');
        Route::get('/{customer}', 'show')->name('show');
        Route::put('/{customer}', 'update')->name('update');
        Route::delete('/{customer}', 'destroy')->name('destroy');
    });

    Route::prefix('purchase-requests')
    ->name('purchase_requests.')
    ->controller(PurchaseRequestController::class)
    ->group(function () {

        Route::get('/', 'index')->name('index');
        Route::get('/data', 'data')->name('data');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');

        Route::get('/{pr}/edit', 'edit')->name('edit');
        Route::put('/{pr}', 'update')->name('update');

        Route::delete('/{pr}', 'destroy')->name('destroy');

        Route::get('/{pr}', 'show')->name('show');

        // PRINT PDF
        Route::get('/{pr}/print', 'print')->name('print');
    });

    Route::prefix('purchase-orders')
    ->name('purchase_orders.')
    ->controller(PurchaseOrderController::class)
    ->group(function () {

        Route::get('/', 'index')->name('index');
        Route::get('/data', 'data')->name('data');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        // export
        Route::get('/export-po', 'export')->name('export');

        Route::get('/{po}/edit', 'edit')->name('edit');
        Route::get('/{po}/edit', 'edit')->name('edit');
        Route::put('/{po}', 'update')->name('update');

        Route::delete('/{po}', 'destroy')->name('destroy');

        Route::get('/{po}', 'show')->name('show');
        Route::get('/po-invoice/{po}', 'createFromPo')->name('po-invoice');

        // PRINT PDF
        Route::get('/{po}/print', 'print')->name('print');
    });

    Route::prefix('invoices')
    ->name('invoices.')
    ->controller(InvoiceController::class)
    ->group(function () {

        Route::get('/', 'index')->name('index');
        Route::get('/data', 'data')->name('data');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::post('/updateItems', 'updateItems')->name('updateItems');
        Route::post('/piutang', 'piutangStore')->name('piutangStore');

        Route::get('/{inv}/edit', 'edit')->name('edit');
        Route::put('/{inv}', 'update')->name('update');

        Route::post('po', 'poPembayaran')->name('poPembayaran');

        Route::delete('/{inv}', 'destroy')->name('destroy');

        Route::get('/{inv}', 'show')->name('show');

        // PRINT PDF
        Route::get('/{inv}/print', 'print')->name('print');
        Route::get('/{inv}/piutang', 'piutangSo')->name('so-piutang');

    });

    Route::prefix('sales-orders')
    ->name('sales_orders.')
    ->controller(SalesOrderController::class)
    ->group(function () {

        Route::get('/', 'index')->name('index');
        Route::get('/data', 'data')->name('data');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        // export
        Route::get('/export-so', 'export')->name('export');

        Route::get('/{so}/edit', 'edit')->name('edit');
        Route::put('/{so}', 'update')->name('update');
        Route::put('/margin/{so}', 'marginUpdate')->name('margin-update');

        Route::delete('/{so}', 'destroy')->name('destroy');

        Route::get('/{so}', 'show')->name('show');
        Route::get('/so-invoice/{so}', 'createFromSo')->name('so-invoice');
        Route::get('/so-margin/{so}', 'marginSo')->name('so-margin');
        Route::get('/so-excel/{so}', 'excelSo')->name('so-excel');
        Route::get('/so-margin/export/{so}', 'marginExportSo')->name('so-margin-export');


        // PRINT PDF
        Route::get('/{so}/print', 'print')->name('print');


    });

    Route::get('/report', [ReportController::class, 'index'])
    ->name('report.index');
    Route::post('/report/export-pdf', [ReportController::class, 'exportPdf'])
    ->name('report.export-pdf');

    Route::get('/ajax/suppliers', [PurchaseOrderController::class, 'ajaxSuppliers']);
    Route::get('/ajax/customers', [PurchaseOrderController::class, 'ajaxCustomers']);
    Route::get('/ajax/products', [PurchaseOrderController::class, 'ajaxProducts']);
    Route::get('/ajax/projects', [PurchaseOrderController::class, 'ajaxProjects']);
    Route::get('/ajax/prs', [PurchaseOrderController::class, 'ajaxPrs']);
    Route::get('/ajax/prs/{pr}/items', [PurchaseOrderController::class, 'prItems']);
    Route::get('/ajax/so/{so}/items', [SalesOrderController::class, 'soItems']);

    Route::get('/profile', function () {
        return view('profile.index');
    })->name('profile.index');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');


    Volt::route('settings/profile', 'settings.profile')->name('profile.edit');
    Volt::route('settings/password', 'settings.password')->name('user-password.edit');
    Volt::route('settings/appearance', 'settings.appearance')->name('appearance.edit');

    Volt::route('settings/two-factor', 'settings.two-factor')
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});
