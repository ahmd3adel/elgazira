<?php

use App\Http\Controllers\DistributionOrderController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DepartmentAllocationController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\Frontend\DashboardController;
use App\Http\Controllers\GovernorateController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceivingOrderController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\DriverController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------
// 1. روابط الواجهة الأمامية (Frontend / Public)
// ---------------------------------------------------------
// Route::get('/', [DashboardController::class, 'index'])->name('home');

// ---------------------------------------------------------
// 2. روابط المستخدم المسجل (User Dashboard / Auth)
// ---------------------------------------------------------
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
});

// ---------------------------------------------------------
// 3. روابط لوحة التحكم (Admin Panel / Backend)
// ---------------------------------------------------------
Route::prefix('admin')->name('admin.')->group(function() {
    
    Route::resource('categories', CategoryController::class);
    Route::resource('governorates', GovernorateController::class);
    
    // ✅ مهم: ضع routes المحذوفات BEFORE resource warehouses
    Route::get('warehouses/trashed', [WarehouseController::class, 'trashed'])->name('warehouses.trashed');
    Route::post('warehouses/restore', [WarehouseController::class, 'restore'])->name('warehouses.restore');
    Route::delete('warehouses/force-delete', [WarehouseController::class, 'forceDelete'])->name('warehouses.force-delete');
    
    // ✅ ثم ضع resource warehouses بعدها
    Route::resource('warehouses', WarehouseController::class);
    
    Route::resource('suppliers', SupplierController::class);
    Route::resource('products', ProductController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('inventories', InventoryController::class);
    Route::resource('receiving_orders', ReceivingOrderController::class);
    Route::resource('department_allocations', DepartmentAllocationController::class);
    
    // المدارس
    Route::resource('schools', SchoolController::class);
    Route::get('schools/by-department', [SchoolController::class, 'getByDepartment'])->name('schools.by_department');

    // المنصرف اليومي للمدارس
    Route::resource('distribution_orders', DistributionOrderController::class);
    Route::get('all_distribution_orders', [InventoryController::class, 'all_warehouses'])->name('all_warehouses');

    Route::get('/get-schools-by-department', [DistributionOrderController::class, 'getSchoolsByDepartment'])
        ->name('getSchoolsByDepartment');

    Route::resource('transfers', StockTransferController::class);

    // الأجهزة
    Route::resource('devices', DeviceController::class);
    Route::patch('devices/{id}/update-technical-status', [DeviceController::class, 'updateTechnicalStatus'])
        ->name('devices.update-technical-status');
    Route::get('devices/{id}/maintenance', [DeviceController::class, 'getMaintenanceDetails'])
        ->name('devices.maintenance-details');
    
    // المناديب
    Route::resource('drivers', DriverController::class);
});