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
    
    // المخازن الرئيسية والفرعية (اللي بتظهر في الجدول)
    $warehouses = \App\Models\Warehouse::whereIn('type', ['main', 'sub'])
        ->orderByRaw("FIELD(type, 'main', 'sub')")
        ->get();

    $inventoryMap = [];
    $samplesMap = []; 
    $productTotals = [];

    // 1. جلب الأرصدة الأساسية من inventories
    $allInventory = \App\Models\Inventory::with('warehouse')->get();
    foreach ($allInventory as $item) {
        $warehouse = $item->warehouse;
        if (!$warehouse) continue;

        // تحديد المخزن الأب لو دي نقطة توزيع
        $ownerId = ($warehouse->type == 'dispatch_point') ? $warehouse->parent_id : $warehouse->id;

        if ($ownerId && in_array($ownerId, $warehouses->pluck('id')->toArray())) {
            if (!isset($inventoryMap[$ownerId][$item->product_id])) {
                $inventoryMap[$ownerId][$item->product_id] = 0;
            }
            $inventoryMap[$ownerId][$item->product_id] += $item->quantity;
        }
        
        if (!isset($productTotals[$item->product_id])) {
            $productTotals[$item->product_id] = 0;
        }
        $productTotals[$item->product_id] += $item->quantity;
    }

    // 2. جلب العينات المتاحة من جدول samples (المصدر الحقيقي)
    $allSamples = \App\Models\Sample::with('warehouse')
        ->whereIn('status', ['pending', 'tested', 'approved'])
        ->get();

    foreach ($allSamples as $sample) {
        $warehouse = $sample->warehouse;
        if (!$warehouse) continue;

        $availableSamples = max(0, $sample->received_quantity - $sample->consumed_quantity);
        
        if ($availableSamples > 0) {
            // تحديد المخزن الأب
            $ownerId = ($warehouse->type == 'dispatch_point') ? $warehouse->parent_id : $warehouse->id;
            
            if ($ownerId && in_array($ownerId, $warehouses->pluck('id')->toArray())) {
                if (!isset($samplesMap[$ownerId][$sample->product_id])) {
                    $samplesMap[$ownerId][$sample->product_id] = 0;
                }
                $samplesMap[$ownerId][$sample->product_id] += $availableSamples;
            }
        }
    }

    return view('backend.inventories.index', compact('products', 'warehouses', 'inventoryMap', 'productTotals', 'samplesMap'));
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
    
    $warehouses = \App\Models\Warehouse::orderByRaw("FIELD(type, 'main', 'sub', 'dispatch_point')")
        ->orderBy('name', 'asc')
        ->get();

    $allInventory = \App\Models\Inventory::with('warehouse')->get();
    
    $inventoryMap = [];
    $productTotals = [];

    foreach ($allInventory as $item) {
        $warehouse = $item->warehouse;
        
        if (!$warehouse) continue;

        // ✅ نخزّن الرصيد باسم المخزن الأصلي بدون أي دمج
        if (!isset($inventoryMap[$warehouse->id][$item->product_id])) {
            $inventoryMap[$warehouse->id][$item->product_id] = 0;
        }
        $inventoryMap[$warehouse->id][$item->product_id] += $item->quantity;
        
        // ✅ إجمالي كل منتج في النظام كله
        if (!isset($productTotals[$item->product_id])) {
            $productTotals[$item->product_id] = 0;
        }
        $productTotals[$item->product_id] += $item->quantity;
    }

    // ✅ تأكد إن كل منتج عندها قيمة (حتى 0)
    foreach ($products as $product) {
        if (!isset($productTotals[$product->id])) {
            $productTotals[$product->id] = 0;
        }
    }

    return view('backend.inventories.all_transactions', compact(
        'products', 
        'warehouses', 
        'inventoryMap', 
        'productTotals'
    ));
}
}
