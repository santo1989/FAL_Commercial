<?php

use App\Http\Controllers\BuyerController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesContractController;
use App\Http\Controllers\SalesExportController;
use App\Http\Controllers\SalesImportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Models\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');

// });

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/s', function () {
    return view('search');
});

// Route::get('/search',  [DivisionController::class, 'search'])->name('search');
Route::get('/user-of-supervisor', function () {
    return view('backend.users.superindex');
})->name('superindex');

//New registration ajax route

Route::get('/get-company-designation/{divisionId}', [CompanyController::class, 'getCompanyDesignations'])->name('get_company_designation');


Route::get('/get-department/{company_id}', [CompanyController::class, 'getdepartments'])->name('get_departments');

Route::post('/buyers/update-list', [BuyerController::class, 'updateBuyersList'])
    ->name('buyers_list_update');

Route::middleware('auth')->group(function () {
    // Route::get('/check', function () {
    //     return "Hello world";
    // });

    Route::get('/home', function () {
        return view('backend.home');
    })->name('home');


    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');


    //user

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get(
        '/users/{user}/edit',
        [UserController::class, 'edit']
    )->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/online-user', [UserController::class, 'onlineuserlist'])->name('online_user');

    Route::post('/users/{user}/users_active', [UserController::class, 'user_active'])->name('users.active');

    Route::post('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');

    //divisions

    Route::get('/divisions', [DivisionController::class, 'index'])->name('divisions.index');
    Route::get('/divisions/create', [DivisionController::class, 'create'])->name('divisions.create');
    Route::post('/divisions', [DivisionController::class, 'store'])->name('divisions.store');
    Route::get('/divisions/{division}', [DivisionController::class, 'show'])->name('divisions.show');
    Route::get('/divisions/{division}/edit', [DivisionController::class, 'edit'])->name('divisions.edit');
    Route::put('/divisions/{division}', [DivisionController::class, 'update'])->name('divisions.update');
    Route::delete('/divisions/{division}', [DivisionController::class, 'destroy'])->name('divisions.destroy');

    // companies
    Route::resource('companies', CompanyController::class);

    //departments
    Route::resource('departments', DepartmentController::class);

    // designations
    Route::resource('designations', DesignationController::class);

    ///buyers
    Route::get('/buyers', [BuyerController::class, 'index'])->name('buyers.index');
    Route::get('/buyers/create', [BuyerController::class, 'create'])->name('buyers.create');
    Route::post('/buyers', [BuyerController::class, 'store'])->name('buyers.store');
    Route::get('/buyers/{buyer}', [BuyerController::class, 'show'])->name('buyers.show');
    Route::get('/buyers/{buyer}/edit', [BuyerController::class, 'edit'])->name('buyers.edit');
    Route::put('/buyers/{buyer}', [BuyerController::class, 'update'])->name('buyers.update');
    Route::delete('/buyers/{buyer}', [BuyerController::class, 'destroy'])->name('buyers.destroy');
    Route::post('/buyers/{buyer}/buyers_active', [BuyerController::class, 'buyer_active'])->name('buyers.active');
    Route::get('/get_buyer', [BuyerController::class, 'get_buyer'])->name('get_buyer');

    ///suppliers
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    Route::post('/suppliers/{supplier}/suppliers_active', [SupplierController::class, 'supplier_active'])->name('suppliers.active');
    Route::get('/get_supplier', [SupplierController::class, 'get_supplier'])->name('get_supplier');

    //sales info
    Route::resource('sales-contracts', SalesContractController::class);
    Route::resource('sales-imports', SalesImportController::class);
    Route::resource('sales-exports', SalesExportController::class);
    // Add these routes
    Route::post('/sales-contracts/{contract}/ud', [SalesContractController::class, 'storeUD'])->name('sales-contracts.ud.store');
    Route::post('/sales-contracts/{contract}/revised', [SalesContractController::class, 'storeRevised'])->name('sales-contracts.revised.store');

    // // Excel Import/Export Routes
    // Route::prefix('excel')->group(function () {
    //     // Imports
    //     Route::get('import-template', [SalesExportController::class, 'downloadImportTemplate']);
    //     Route::post('import-upload', [SalesExportController::class, 'processImportUpload'])->name('import.upload');
    //     Route::post('import-confirm', [SalesExportController::class, 'confirmImport'])->name('import.confirm');

    //     // Exports
    //     Route::get('export-template', [SalesExportController::class, 'downloadExportTemplate']);
    //     Route::post('export-upload', [SalesExportController::class, 'processExportUpload'])->name('export.upload');
    //     Route::post('export-confirm', [SalesExportController::class, 'confirmExport'])->name('export.confirm');
    // });

    Route::prefix('excel')->group(function () {
        // Imports
        Route::get('import-template', [SalesImportController::class, 'downloadImportTemplate'])->name('excel.import-template');
        Route::post('import-upload/{contract}', [SalesImportController::class, 'processImportUpload'])->name('import.upload');

        // Import routes
        Route::post('/import/confirm', [SalesImportController::class, 'confirmImport'])->name('import.confirm');
        Route::get('/import/confirmation', [SalesImportController::class, 'showImportConfirmation'])->name('import.confirmation');



        // Exports
        Route::get('export-template', [SalesExportController::class, 'downloadExportTemplate'])->name('excel.export-template');
        Route::post('export-upload/{contract}', [SalesExportController::class, 'processExportUpload'])->name('export.upload');
        Route::post('export-confirm', [SalesExportController::class, 'confirmExport'])->name('export.confirm');
    });


    // routes/web.php

    Route::get('/export/confirmation', [SalesExportController::class, 'showExportConfirmation'])
        ->name('export.confirmation');

});



























Route::get('/read/{notification}', [NotificationController::class, 'read'])->name('notification.read');


require __DIR__ . '/auth.php';

//php artisan command

Route::get('/foo', function () {
    Artisan::call('storage:link');
});

Route::get('/cleareverything', function () {
    $clearcache = Artisan::call('cache:clear');
    echo "Cache cleared<br>";

    $clearview = Artisan::call('view:clear');
    echo "View cleared<br>";

    $clearconfig = Artisan::call('config:cache');
    echo "Config cleared<br>";
});

Route::get('/key =', function () {
    $key =  Artisan::call('key:generate');
    echo "key:generate<br>";
});

Route::get('/migrate', function () {
    $migrate = Artisan::call('migrate');
    echo "migration create<br>";
});

Route::get('/migrate-fresh', function () {
    $fresh = Artisan::call('migrate:fresh --seed');
    echo "migrate:fresh --seed create<br>";
});

Route::get('/optimize', function () {
    $optimize = Artisan::call('optimize:clear');
    echo "optimize cleared<br>";
});
Route::get('/route-clear', function () {
    $route_clear = Artisan::call('route:clear');
    echo "route cleared<br>";
});

Route::get('/route-cache', function () {
    $route_cache = Artisan::call('route:cache');
    echo "route cache<br>";
});

Route::get('/updateapp', function () {
    $dump_autoload = Artisan::call('dump-autoload');
    echo 'dump-autoload complete';
});
