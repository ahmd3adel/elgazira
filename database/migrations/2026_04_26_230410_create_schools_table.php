<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('schools', function (Blueprint $table) {
    $table->id();
    $table->string('silocode')->nullable();
    $table->string('name');
    $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
    $table->string('address')->nullable();
    $table->enum('type', ['ابتدائي', 'اعدادي', 'رياض اطفال', 'تعليم مجتمعي'])->nullable(); 
    $table->boolean('status')->default(true);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
