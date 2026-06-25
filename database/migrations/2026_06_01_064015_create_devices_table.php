<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('devices')) {
            Schema::create('devices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('department_id')->constrained()->onDelete('cascade');
                $table->string('line_number')->unique();
                $table->string('pos_username')->unique();
                $table->string('pos_password')->nullable();
                $table->string('serial_number')->unique();
                $table->enum('technical_status', ['working', 'maintenance', 'broken', 'retired'])->default('working');
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->text('notes')->nullable();
                $table->text('specifications')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->foreignId('updated_by')->nullable()->constrained('users');
                $table->timestamps();
                
                // إضافة indexes
                $table->index('technical_status');
                $table->index('status');
                $table->index('line_number');
                $table->index('department_id');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('devices');
    }
};