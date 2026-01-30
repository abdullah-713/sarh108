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
        Schema::create('overtime', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('attendance_record_id')->nullable()->constrained('attendance_records')->onDelete('set null');
            $table->date('overtime_date');
            $table->decimal('hours', 5, 2);
            $table->decimal('rate_per_hour', 8, 2);
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->enum('overtime_type', ['daily', 'weekly', 'monthly', 'holiday'])->default('daily');
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_paid')->default(false);
            $table->string('approval_status')->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->enum('payment_status', ['pending', 'processed', 'paid', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            $table->index('employee_id');
            $table->index('overtime_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime');
    }
};
