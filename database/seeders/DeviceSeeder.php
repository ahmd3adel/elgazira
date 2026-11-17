<?php

namespace Database\Seeders;

use App\Models\Device;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    public function run()
    {
        // الحصول على أول إدارة موجودة
        $departmentId = \App\Models\Department::first()?->id;
        
        if(!$departmentId) {
            $this->command->error('لا توجد إدارات. قم بإضافة إدارة أولاً.');
            return;
        }
        
        Device::create([
            'department_id' => $departmentId,
            'line_number' => 'LINE-001',
            'pos_username' => 'user001',
            'pos_password' => 'pass123',
            'serial_number' => 'SN-123456789',
            'technical_status' => 'working',
            'status' => 'active',
            'notes' => 'جهاز تجريبي 1',
            'specifications' => 'Intel Core i5, 8GB RAM, 256GB SSD'
        ]);
        
        Device::create([
            'department_id' => $departmentId,
            'line_number' => 'LINE-002',
            'pos_username' => 'user002',
            'pos_password' => 'pass456',
            'serial_number' => 'SN-987654321',
            'technical_status' => 'maintenance',
            'status' => 'active',
            'notes' => 'جهاز تجريبي 2',
            'specifications' => 'Intel Core i7, 16GB RAM, 512GB SSD'
        ]);
        
        Device::create([
            'department_id' => $departmentId,
            'line_number' => 'LINE-003',
            'pos_username' => 'user003',
            'pos_password' => 'pass789',
            'serial_number' => 'SN-555555555',
            'technical_status' => 'working',
            'status' => 'active',
            'notes' => 'جهاز تجريبي 3',
            'specifications' => 'Intel Core i3, 4GB RAM, 128GB SSD'
        ]);
        
        $this->command->info('تم إضافة 3 أجهزة تجريبية بنجاح');
    }
}