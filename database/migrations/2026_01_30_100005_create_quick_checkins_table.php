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
        Schema::create('quick_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->enum('type', ['checkin', 'checkout'])->default('checkin');
            $table->timestamp('checked_at');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('wifi_ssid')->nullable();
            $table->string('wifi_bssid')->nullable();
            $table->enum('verification_method', ['wifi', 'gps', 'both', 'manual'])->default('gps');
            $table->boolean('is_verified')->default(false);
            $table->integer('late_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->string('device_info')->nullable();
            $table->string('ip_address')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['employee_id', 'checked_at']);
            $table->index(['branch_id', 'checked_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quick_checkins');
    }
};
