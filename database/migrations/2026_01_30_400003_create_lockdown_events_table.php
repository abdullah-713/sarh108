<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Lockdown Mode - Emergency attendance control
     */
    public function up(): void
    {
        Schema::create('lockdown_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade'); // null = all branches
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->text('description')->nullable();
            $table->enum('lockdown_type', ['full', 'partial', 'checkin_only', 'checkout_only', 'emergency'])->default('full');
            $table->enum('status', ['scheduled', 'active', 'ended', 'cancelled'])->default('scheduled');
            $table->timestamp('start_time');
            $table->timestamp('end_time')->nullable(); // null = indefinite
            $table->timestamp('actual_end_time')->nullable();
            $table->foreignId('initiated_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('ended_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('allow_emergency_checkin')->default(false);
            $table->boolean('allow_emergency_checkout')->default(true); // Allow checkout during lockdown
            $table->json('exempt_employees')->nullable(); // Employees who can still check in/out
            $table->json('exempt_departments')->nullable();
            $table->json('exempt_designations')->nullable();
            $table->text('notification_message')->nullable();
            $table->text('notification_message_ar')->nullable();
            $table->boolean('notify_employees')->default(true);
            $table->boolean('notify_managers')->default(true);
            $table->text('end_reason')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index(['start_time', 'end_time']);
        });
        
        // Lockdown affected attendance records
        Schema::create('lockdown_attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lockdown_event_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->enum('action_type', ['blocked_checkin', 'blocked_checkout', 'emergency_checkin', 'emergency_checkout', 'exempt_access']);
            $table->timestamp('attempted_at');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('device_id')->nullable();
            $table->boolean('was_allowed')->default(false);
            $table->string('override_reason')->nullable();
            $table->foreignId('overridden_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['lockdown_event_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lockdown_attendance_logs');
        Schema::dropIfExists('lockdown_events');
    }
};
