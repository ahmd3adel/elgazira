<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use App\Http\Requests\StoreInventoryRequest;
use App\Http\Requests\UpdateInventoryRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Product;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
/**
 * عرض قائمة أرصدة المخازن الحالية
 */
/**
 * عرض قائمة أرصدة المخازن الحالية
 */
public function index(Request $request)
{
    $products = Product::orderBy('name')->get();
    
    $warehouses = \App\Models\Warehouse::whereIn('type', ['main', 'sub'])
        ->orderByRaw("FIELD(type, 'main', 'sub')")
        ->get();

    $allInventory = \App\Models\Inventory::with('warehouse')->get();
    
    $inventoryMap = [];
    $productTotals = []; // مصفوفة المجاميع الكلية

    foreach ($allInventory as $item) {
        $warehouse = $item->warehouse;
        
        if (!$warehouse) continue;

        $ownerId = ($warehouse->type == 'dispatch_point') 
            ? $warehouse->parent_id 
            : $warehouse->id;

        if ($ownerId && in_array($ownerId, $warehouses->pluck('id')->toArray())) {
            if (!isset($inventoryMap[$ownerId][$item->product_id])) {
                $inventoryMap[$ownerId][$item->product_id] = 0;
            }
            $inventoryMap[$ownerId][$item->product_id] += $item->quantity;
        }
        
        // حساب المجموع الكلي من جميع المستودعات (بما فيها نقاط التوزيع)
        if (!isset($productTotals[$item->product_id])) {
            $productTotals[$item->product_id] = 0;
        }
        $productTotals[$item->product_id] += $item->quantity;
    }

    return view('backend.inventories.index', compact('products', 'warehouses', 'inventoryMap', 'productTotals'));
}
   /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInventoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Inventory $inventory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Inventory $inventory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInventoryRequest $request, Inventory $inventory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Inventory $inventory)
    {
        //
    }

public function all_warehouses(Request $request)
{
    $products = Product::orderBy('name')->get();
    
    // جلب الكل: رئيسي، فرعي، ونقاط توزيع
    $warehouses = \App\Models\Warehouse::orderByRaw("FIELD(type, 'main', 'sub', 'dispatch_point')")
        ->orderBy('name', 'asc')
        ->get();

    // خريطة المخزون كما هي في قاعدة البيانات
    $inventoryMap = \App\Models\Inventory::all()
        ->groupBy('warehouse_id')
        ->map(function ($items) {
            return $items->keyBy('product_id')->map->quantity;
        });

    return view('backend.inventories.all_transactions', compact('products', 'warehouses', 'inventoryMap'));
}
}
