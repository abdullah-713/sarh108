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
        Schema::create('wifi_networks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم الشبكة التعريفي');
            $table->string('ssid')->comment('SSID الشبكة');
            $table->string('bssid')->nullable()->comment('BSSID الشبكة');
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false)->comment('الشبكة الأساسية');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['branch_id', 'is_active']);
            $table->index('ssid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wifi_networks');
    }
};
