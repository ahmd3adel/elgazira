<?php

use App\Http\Controllers\DistributionOrderController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DepartmentAllocationController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\Frontend\DashboardController;
use App\Http\Controllers\GovernorateController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceivingOrderController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\WarehouseController;
use App\Models\DepartmentAllocation;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------
// 1. روابط الواجهة الأمامية (Frontend / Public)
// ---------------------------------------------------------
// Route::get('/', [DashboardController::class, 'index'])->name('home');


// ---------------------------------------------------------
// 2. روابط المستخدم المسجل (User Dashboard / Auth)
// ---------------------------------------------------------
// Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    // Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
// });


// ---------------------------------------------------------
// 3. روابط لوحة التحكم (Admin Panel / Backend)
// ---------------------------------------------------------
Route::prefix('admin')->name('admin.')->group(function() {
    
 

    Route::resource('categories', CategoryController::class);
    Route::resource('governorates', GovernorateController::class);
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

});