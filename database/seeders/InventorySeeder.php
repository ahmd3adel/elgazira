<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
public function run(): void
    {
        $rows = [
            ['id' => 70,  'product_id' => 2, 'warehouse_id' => 1,  'quantity' => 19,  'samples_quantity' => 0, 'last_movement_at' => '2026-10-22 06:21:43', 'created_at' => '2026-09-18 13:11:43', 'updated_at' => '2026-10-26 08:24:45'],
            ['id' => 71,  'product_id' => 1, 'warehouse_id' => 1,  'quantity' => 149, 'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 07:17:20', 'created_at' => '2026-09-18 13:26:39', 'updated_at' => '2026-10-26 08:24:45'],
            ['id' => 72,  'product_id' => 3, 'warehouse_id' => 1,  'quantity' => 137, 'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 07:17:00', 'created_at' => '2026-09-18 15:38:30', 'updated_at' => '2026-10-26 08:24:45'],
            ['id' => 73,  'product_id' => 3, 'warehouse_id' => 3,  'quantity' => 1,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-19 09:40:41', 'created_at' => '2026-09-19 05:40:30', 'updated_at' => '2026-10-19 09:40:41'],
            ['id' => 74,  'product_id' => 2, 'warehouse_id' => 3,  'quantity' => 600, 'samples_quantity' => 0, 'last_movement_at' => '2026-10-26 09:03:35', 'created_at' => '2026-09-19 05:45:32', 'updated_at' => '2026-10-26 09:03:35'],
            ['id' => 75,  'product_id' => 1, 'warehouse_id' => 3,  'quantity' => 601, 'samples_quantity' => 0, 'last_movement_at' => '2026-10-26 09:03:10', 'created_at' => '2026-09-19 05:48:51', 'updated_at' => '2026-10-26 09:03:10'],
            ['id' => 76,  'product_id' => 2, 'warehouse_id' => 5,  'quantity' => 2,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-19 09:38:18', 'created_at' => '2026-09-19 06:15:19', 'updated_at' => '2026-10-19 09:38:18'],
            ['id' => 77,  'product_id' => 3, 'warehouse_id' => 5,  'quantity' => 223, 'samples_quantity' => 0, 'last_movement_at' => '2026-10-26 09:00:49', 'created_at' => '2026-09-19 06:17:10', 'updated_at' => '2026-10-26 09:00:49'],
            ['id' => 78,  'product_id' => 1, 'warehouse_id' => 5,  'quantity' => 225, 'samples_quantity' => 0, 'last_movement_at' => '2026-10-26 09:00:49', 'created_at' => '2026-09-19 06:19:16', 'updated_at' => '2026-10-26 09:00:49'],
            ['id' => 79,  'product_id' => 3, 'warehouse_id' => 4,  'quantity' => 408, 'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:08:50', 'created_at' => '2026-09-19 06:21:27', 'updated_at' => '2026-10-25 18:08:50'],
            ['id' => 80,  'product_id' => 1, 'warehouse_id' => 4,  'quantity' => 408, 'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:08:50', 'created_at' => '2026-09-19 06:23:34', 'updated_at' => '2026-10-25 18:08:50'],
            ['id' => 81,  'product_id' => 1, 'warehouse_id' => 16, 'quantity' => 1,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:14:02', 'created_at' => '2026-09-26 09:43:06', 'updated_at' => '2026-10-25 18:14:02'],
            ['id' => 82,  'product_id' => 3, 'warehouse_id' => 16, 'quantity' => 1,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:14:02', 'created_at' => '2026-09-26 09:43:06', 'updated_at' => '2026-10-25 18:14:02'],
            ['id' => 83,  'product_id' => 1, 'warehouse_id' => 7,  'quantity' => 2,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:09:04', 'created_at' => '2026-09-27 02:53:02', 'updated_at' => '2026-10-25 18:09:04'],
            ['id' => 84,  'product_id' => 3, 'warehouse_id' => 7,  'quantity' => 2,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:09:04', 'created_at' => '2026-09-27 02:53:02', 'updated_at' => '2026-10-25 18:09:04'],
            ['id' => 85,  'product_id' => 1, 'warehouse_id' => 11, 'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:32:16', 'created_at' => '2026-09-27 05:32:22', 'updated_at' => '2026-10-25 18:32:16'],
            ['id' => 86,  'product_id' => 2, 'warehouse_id' => 11, 'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:32:16', 'created_at' => '2026-09-27 05:32:22', 'updated_at' => '2026-10-25 18:32:16'],
            ['id' => 87,  'product_id' => 1, 'warehouse_id' => 9,  'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:15:27', 'created_at' => '2026-09-27 07:27:47', 'updated_at' => '2026-10-25 18:15:27'],
            ['id' => 88,  'product_id' => 3, 'warehouse_id' => 9,  'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:15:27', 'created_at' => '2026-09-27 07:27:47', 'updated_at' => '2026-10-25 18:15:27'],
            ['id' => 89,  'product_id' => 1, 'warehouse_id' => 8,  'quantity' => 2,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:14:44', 'created_at' => '2026-09-27 07:31:24', 'updated_at' => '2026-10-25 18:15:07'],
            ['id' => 90,  'product_id' => 3, 'warehouse_id' => 8,  'quantity' => 2,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:14:44', 'created_at' => '2026-09-27 07:31:24', 'updated_at' => '2026-10-25 18:15:07'],
            ['id' => 91,  'product_id' => 1, 'warehouse_id' => 17, 'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-26 08:53:20', 'created_at' => '2026-09-27 08:00:37', 'updated_at' => '2026-10-26 08:53:20'],
            ['id' => 92,  'product_id' => 2, 'warehouse_id' => 17, 'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-20 08:54:16', 'created_at' => '2026-09-27 08:00:37', 'updated_at' => '2026-10-20 08:54:16'],
            ['id' => 93,  'product_id' => 1, 'warehouse_id' => 13, 'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:31:27', 'created_at' => '2026-09-27 08:02:02', 'updated_at' => '2026-10-25 18:31:27'],
            ['id' => 94,  'product_id' => 2, 'warehouse_id' => 13, 'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:31:27', 'created_at' => '2026-09-27 08:02:02', 'updated_at' => '2026-10-25 18:31:27'],
            ['id' => 95,  'product_id' => 1, 'warehouse_id' => 10, 'quantity' => 1,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-26 08:53:47', 'created_at' => '2026-09-27 11:29:24', 'updated_at' => '2026-10-26 08:53:47'],
            ['id' => 96,  'product_id' => 2, 'warehouse_id' => 10, 'quantity' => 1,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-26 08:53:47', 'created_at' => '2026-09-27 11:29:24', 'updated_at' => '2026-10-26 08:53:47'],
            ['id' => 97,  'product_id' => 1, 'warehouse_id' => 6,  'quantity' => 13,  'samples_quantity' => 0, 'last_movement_at' => '2026-10-26 09:01:37', 'created_at' => '2026-09-27 11:30:59', 'updated_at' => '2026-10-26 09:01:37'],
            ['id' => 98,  'product_id' => 3, 'warehouse_id' => 6,  'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-26 09:01:37', 'created_at' => '2026-09-27 11:30:59', 'updated_at' => '2026-10-26 09:01:37'],
            ['id' => 99,  'product_id' => 2, 'warehouse_id' => 16, 'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:14:02', 'created_at' => '2026-09-28 04:32:50', 'updated_at' => '2026-10-25 18:14:02'],
            ['id' => 100, 'product_id' => 2, 'warehouse_id' => 6,  'quantity' => 13,  'samples_quantity' => 0, 'last_movement_at' => '2026-10-22 05:48:19', 'created_at' => '2026-09-28 05:42:43', 'updated_at' => '2026-10-22 05:48:19'],
            ['id' => 101, 'product_id' => 2, 'warehouse_id' => 7,  'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-19 09:39:57', 'created_at' => '2026-09-28 05:47:40', 'updated_at' => '2026-10-19 09:39:57'],
            ['id' => 102, 'product_id' => 1, 'warehouse_id' => 12, 'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-26 08:52:40', 'created_at' => '2026-09-28 06:13:47', 'updated_at' => '2026-10-26 08:52:40'],
            ['id' => 103, 'product_id' => 2, 'warehouse_id' => 12, 'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-26 08:52:40', 'created_at' => '2026-09-28 06:13:47', 'updated_at' => '2026-10-26 08:52:40'],
            ['id' => 104, 'product_id' => 2, 'warehouse_id' => 8,  'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-19 09:39:37', 'created_at' => '2026-09-28 06:30:37', 'updated_at' => '2026-10-19 10:06:16'],
            ['id' => 105, 'product_id' => 2, 'warehouse_id' => 9,  'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-19 09:39:17', 'created_at' => '2026-09-28 06:31:03', 'updated_at' => '2026-10-19 09:39:17'],
            ['id' => 106, 'product_id' => 1, 'warehouse_id' => 15, 'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:31:10', 'created_at' => '2026-09-28 10:28:46', 'updated_at' => '2026-10-25 18:31:10'],
            ['id' => 107, 'product_id' => 2, 'warehouse_id' => 15, 'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:31:10', 'created_at' => '2026-09-28 10:28:46', 'updated_at' => '2026-10-25 18:31:10'],
            ['id' => 108, 'product_id' => 1, 'warehouse_id' => 14, 'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:32:31', 'created_at' => '2026-09-28 10:30:37', 'updated_at' => '2026-10-25 18:32:31'],
            ['id' => 109, 'product_id' => 2, 'warehouse_id' => 14, 'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-25 18:32:31', 'created_at' => '2026-09-28 10:30:37', 'updated_at' => '2026-10-25 18:32:31'],
            ['id' => 110, 'product_id' => 1, 'warehouse_id' => 18, 'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => null, 'created_at' => '2026-09-30 06:25:26', 'updated_at' => '2026-10-04 04:50:40'],
            ['id' => 111, 'product_id' => 3, 'warehouse_id' => 18, 'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => null, 'created_at' => '2026-09-30 06:25:26', 'updated_at' => '2026-10-04 04:50:40'],
            ['id' => 112, 'product_id' => 2, 'warehouse_id' => 4,  'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-20 08:44:28', 'created_at' => '2026-10-04 02:24:13', 'updated_at' => '2026-10-20 08:44:28'],
            ['id' => 113, 'product_id' => 5, 'warehouse_id' => 1,  'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-05 08:18:01', 'created_at' => '2026-10-05 08:18:01', 'updated_at' => '2026-10-12 07:01:01'],
            ['id' => 114, 'product_id' => 5, 'warehouse_id' => 9,  'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-06 08:38:37', 'created_at' => '2026-10-06 08:34:24', 'updated_at' => '2026-10-06 08:38:37'],
            ['id' => 115, 'product_id' => 5, 'warehouse_id' => 8,  'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-12 04:34:32', 'created_at' => '2026-10-06 08:35:19', 'updated_at' => '2026-10-12 04:34:32'],
            ['id' => 116, 'product_id' => 5, 'warehouse_id' => 7,  'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-12 05:01:35', 'created_at' => '2026-10-06 08:36:02', 'updated_at' => '2026-10-12 05:01:35'],
            ['id' => 117, 'product_id' => 5, 'warehouse_id' => 6,  'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-12 07:01:26', 'created_at' => '2026-10-11 08:01:52', 'updated_at' => '2026-10-12 07:01:26'],
            ['id' => 118, 'product_id' => 3, 'warehouse_id' => 17, 'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-26 08:53:20', 'created_at' => '2026-10-26 08:23:50', 'updated_at' => '2026-10-26 08:53:20'],
            ['id' => 119, 'product_id' => 3, 'warehouse_id' => 10, 'quantity' => 0,   'samples_quantity' => 0, 'last_movement_at' => '2026-10-26 08:53:47', 'created_at' => '2026-10-26 08:24:45', 'updated_at' => '2026-10-26 08:53:47'],
        ];
                DB::table('inventories')->insert($rows);

}
}
