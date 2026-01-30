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
        Schema::create('work_hours_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            $table->decimal('daily_working_hours', 5, 2);
            $table->time('shift_start_time');
            $table->time('shift_end_time');
            $table->decimal('late_arrival_grace', 5, 2)->default(15);
            $table->decimal('early_departure_grace', 5, 2)->default(15);
            $table->decimal('break_duration', 5, 2)->default(60);
            $table->integer('max_breaks_per_day')->default(2);
            $table->decimal('max_break_duration', 5, 2)->default(60);
            $table->boolean('allow_flexible_timing')->default(false);
            $table->decimal('overtime_rate_per_hour', 8, 2)->default(150.00);
            $table->decimal('overtime_rate_holiday', 8, 2)->default(300.00);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_hours_settings');
    }
};
