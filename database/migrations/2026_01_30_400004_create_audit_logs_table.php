<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Audit Logs - Comprehensive system auditing
     */
    public function up(): void
    {
        Schema::create('attendance_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Who made the change
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null'); // Affected employee
            $table->string('auditable_type'); // Model class
            $table->unsignedBigInteger('auditable_id'); // Model ID
            $table->enum('action', ['create', 'update', 'delete', 'restore', 'login', 'logout', 'checkin', 'checkout', 'approve', 'reject', 'override', 'export', 'import', 'bulk_action', 'settings_change', 'permission_change']);
            $table->string('action_label')->nullable(); // Human readable action
            $table->string('action_label_ar')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('changed_fields')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_type')->nullable(); // mobile, desktop, tablet
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name')->nullable();
            $table->string('session_id')->nullable();
            $table->boolean('is_suspicious')->default(false);
            $table->string('suspicious_reason')->nullable();
            $table->enum('severity', ['info', 'low', 'medium', 'high', 'critical'])->default('info');
            $table->boolean('requires_review')->default(false);
            $table->boolean('reviewed')->default(false);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['action', 'created_at']);
            $table->index('is_suspicious');
            $table->index('requires_review');
        });
        
        // Audit summary for quick stats
        Schema::create('audit_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->date('summary_date');
            $table->integer('total_actions')->default(0);
            $table->integer('create_actions')->default(0);
            $table->integer('update_actions')->default(0);
            $table->integer('delete_actions')->default(0);
            $table->integer('login_actions')->default(0);
            $table->integer('checkin_actions')->default(0);
            $table->integer('checkout_actions')->default(0);
            $table->integer('approve_actions')->default(0);
            $table->integer('reject_actions')->default(0);
            $table->integer('suspicious_actions')->default(0);
            $table->integer('unique_users')->default(0);
            $table->integer('unique_devices')->default(0);
            $table->json('top_users')->nullable();
            $table->json('action_breakdown')->nullable();
            $table->timestamps();
            
            $table->unique(['company_id', 'summary_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_summaries');
        Schema::dropIfExists('attendance_audit_logs');
    }
};
