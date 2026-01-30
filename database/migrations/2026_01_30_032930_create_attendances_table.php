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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            $table->date('attendance_date');
            $table->timestamp('check_in_time')->nullable();
            $table->timestamp('check_out_time')->nullable();
            $table->string('check_in_location')->nullable();
            $table->string('check_out_location')->nullable();
            $table->decimal('latitude_in', 10, 8)->nullable();
            $table->decimal('longitude_in', 11, 8)->nullable();
            $table->decimal('latitude_out', 10, 8)->nullable();
            $table->decimal('longitude_out', 11, 8)->nullable();
            $table->string('qr_code_uuid')->nullable()->unique();
            $table->decimal('total_hours', 5, 2)->nullable();
            $table->decimal('break_hours', 5, 2)->default(0);
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->decimal('work_hours', 5, 2)->nullable();
            $table->boolean('is_late')->default(false);
            $table->boolean('is_early_departure')->default(false);
            $table->boolean('is_absent')->default(false);
            $table->boolean('is_present')->default(false);
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_weekend')->default(false);
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'leave', 'holiday', 'weekend'])->default('present');
            $table->text('notes')->nullable();
            $table->text('justification')->nullable();
            $table->string('approval_status')->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('employee_id');
            $table->index('attendance_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
