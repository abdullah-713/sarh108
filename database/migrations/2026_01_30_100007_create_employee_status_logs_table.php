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
        Schema::create('employee_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date');
            $table->enum('status', ['present', 'late', 'absent', 'on_leave', 'holiday'])->default('absent');
            $table->time('checkin_time')->nullable();
            $table->time('checkout_time')->nullable();
            $table->integer('late_minutes')->default(0);
            $table->integer('early_leave_minutes')->default(0);
            $table->integer('worked_minutes')->default(0);
            $table->integer('deduction_points')->default(0);
            $table->boolean('is_perfect_day')->default(false)->comment('يوم مثالي بدون تأخير');
            $table->timestamps();
            
            $table->unique(['employee_id', 'date']);
            $table->index(['date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_status_logs');
    }
};
