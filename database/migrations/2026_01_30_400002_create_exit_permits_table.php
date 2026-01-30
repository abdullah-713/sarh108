<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Exit Permits - Permission to leave during work hours
     */
    public function up(): void
    {
        Schema::create('exit_permits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('permit_type', ['personal', 'official', 'medical', 'emergency', 'other'])->default('personal');
            $table->date('permit_date');
            $table->time('exit_time');
            $table->time('expected_return_time');
            $table->time('actual_return_time')->nullable();
            $table->text('reason');
            $table->string('destination')->nullable();
            $table->boolean('requires_vehicle')->default(false);
            $table->string('vehicle_number')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'used', 'expired'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_note')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_extended')->default(false);
            $table->time('extended_return_time')->nullable();
            $table->text('extension_reason')->nullable();
            $table->foreignId('extension_approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('total_minutes_out')->nullable();
            $table->boolean('affects_attendance')->default(true); // Whether to deduct from work hours
            $table->string('qr_code')->unique()->nullable(); // For security verification
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'employee_id']);
            $table->index(['permit_date', 'status']);
            $table->index('qr_code');
        });
        
        // Exit permit settings per company
        Schema::create('exit_permit_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->onDelete('cascade');
            $table->boolean('require_approval')->default(true);
            $table->integer('max_permits_per_day')->default(1);
            $table->integer('max_permits_per_month')->default(5);
            $table->integer('max_duration_minutes')->default(120);
            $table->integer('min_advance_hours')->default(1); // Minimum hours before to request
            $table->boolean('allow_same_day_request')->default(true);
            $table->boolean('notify_manager')->default(true);
            $table->boolean('notify_hr')->default(false);
            $table->boolean('auto_approve_official')->default(false); // Auto-approve official permits
            $table->json('exempt_employees')->nullable(); // Employees exempt from limits
            $table->json('exempt_designations')->nullable(); // Designations exempt from limits
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exit_permit_settings');
        Schema::dropIfExists('exit_permits');
    }
};
