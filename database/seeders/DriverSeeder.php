<?php
// database/seeders/DriverSeeder.php

namespace Database\Seeders;

use App\Models\Driver;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    public function run()
    {
        $drivers = [
            [
                'name' => 'أحمد محمد علي',
                'line_number' => 'LINE-001',
                'national_id' => '12345678901234',
                'health_certificate_status' => 'valid',
                'phone' => '0501234567',
                'training_status' => 'completed',
                'training_date' => '2025-01-15',
                'notes' => 'مندوب متميز',
                'status' => 'active',
            ],
            [
                'name' => 'محمد إبراهيم حسن',
                'line_number' => 'LINE-002',
                'national_id' => '22345678901234',
                'health_certificate_status' => 'pending',
                'phone' => '0501234568',
                'training_status' => 'pending',
                'training_date' => null,
                'notes' => 'جديد',
                'status' => 'active',
            ],
            [
                'name' => 'سعيد عبد الله عمر',
                'line_number' => 'LINE-003',
                'national_id' => '32345678901234',
                'health_certificate_status' => 'expired',
                'phone' => '0501234569',
                'training_status' => 'failed',
                'training_date' => '2025-02-20',
                'notes' => 'يحتاج إعادة تدريب',
                'status' => 'inactive',
            ],
            [
                'name' => 'خالد سامر محمود',
                'line_number' => 'LINE-004',
                'national_id' => '42345678901234',
                'health_certificate_status' => 'valid',
                'phone' => '0501234570',
                'training_status' => 'completed',
                'training_date' => '2025-03-10',
                'notes' => '',
                'status' => 'active',
            ],
            [
                'name' => 'ياسر فتحي رشاد',
                'line_number' => 'LINE-005',
                'national_id' => '52345678901234',
                'health_certificate_status' => 'not_required',
                'phone' => '0501234571',
                'training_status' => 'completed',
                'training_date' => '2025-01-30',
                'notes' => 'خبرة عالية',
                'status' => 'active',
            ],
        ];

        foreach ($drivers as $driver) {
            Driver::create($driver);
        }
    }
}