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
        Schema::create('time_windows', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم النافذة الزمنية');
            $table->enum('type', ['checkin', 'checkout'])->default('checkin');
            $table->time('start_time')->comment('وقت البداية');
            $table->time('end_time')->comment('وقت النهاية');
            $table->integer('grace_period')->default(15)->comment('فترة السماح بالدقائق');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_windows');
    }
};
