<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReceivingOrderSeeder extends Seeder
{
    public function run(): void
    {
        // تعطيل فحص المفاتيح الأجنبية مؤقتاً
        Schema::disableForeignKeyConstraints();
        DB::table('receiving_orders')->truncate();
        Schema::enableForeignKeyConstraints();
        
        // جلب البيانات الموجودة
        $suppliers = Supplier::pluck('id')->toArray();
        $warehouses = Warehouse::where('status', 1)->pluck('id')->toArray();
        $products = Product::pluck('id')->toArray();
        
        if (empty($suppliers)) {
            $this->command->error('لا يوجد موردين! قم بتشغيل SupplierSeeder أولاً');
            $this->command->info('php artisan db:seed --class=SupplierSeeder');
            return;
        }
        
        if (empty($warehouses)) {
            $this->command->error('لا يوجد مخازن! قم بتشغيل WarehouseSeeder أولاً');
            $this->command->info('php artisan db:seed --class=WarehouseSeeder');
            return;
        }
        
        if (empty($products)) {
            $this->command->error('لا يوجد منتجات! قم بتشغيل ProductSeeder أولاً');
            $this->command->info('php artisan db:seed --class=ProductSeeder');
            return;
        }
        
        $orders = [];
        $startDate = strtotime('-6 months');
        $endDate = time();
        
        for ($i = 1; $i <= 50; $i++) {
            $quantity = rand(100, 5000);
            $samples = rand(0, 1) ? rand(1, 50) : 0;
            $arrivalTime = date('Y-m-d H:i:s', rand($startDate, $endDate));
            $departureTime = rand(0, 1) ? date('Y-m-d H:i:s', rand(strtotime($arrivalTime), $endDate)) : null;
            
            $orders[] = [
                'document_number' => 'PO-' . date('Y') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'batch_number' => rand(0, 1) ? 'BATCH-' . rand(100, 999) . '-' . chr(rand(65, 90)) . chr(rand(65, 90)) : null,
                'supplier_id' => $suppliers[array_rand($suppliers)],
                'warehouse_id' => $warehouses[array_rand($warehouses)],
                'product_id' => $products[array_rand($products)],
                'quantity' => $quantity,
                'samples_quantity' => $samples,
                'arrival_time' => $arrivalTime,
                'departure_time' => $departureTime,
                'notes' => rand(0, 1) ? 'ملاحظة: شحنة رقم ' . $i : null,
                'user_id' => 1,
                'created_at' => $arrivalTime,
                'updated_at' => $departureTime ?? $arrivalTime,
            ];
        }
        
        // إدخال البيانات دفعات
        $chunks = array_chunk($orders, 20);
        foreach ($chunks as $chunk) {
            DB::table('receiving_orders')->insert($chunk);
        }
        
        $this->command->newLine();
        $this->command->info('✅ تم إدخال ' . count($orders) . ' شحنة بنجاح');
        $this->command->newLine();
        $this->command->info('📊 إحصائيات الشحنات:');
        $this->command->info('   - إجمالي الكميات: ' . number_format(DB::table('receiving_orders')->sum('quantity')) . ' كرتونة');
        $this->command->info('   - إجمالي العينات: ' . number_format(DB::table('receiving_orders')->sum('samples_quantity')) . ' عينة');
        $this->command->info('   - عدد الموردين: ' . DB::table('receiving_orders')->distinct('supplier_id')->count('supplier_id'));
        $this->command->info('   - عدد المخازن: ' . DB::table('receiving_orders')->distinct('warehouse_id')->count('warehouse_id'));
        $this->command->info('   - عدد المنتجات: ' . DB::table('receiving_orders')->distinct('product_id')->count('product_id'));
    }
}