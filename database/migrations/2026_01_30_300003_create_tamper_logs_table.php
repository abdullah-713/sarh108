<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * سجل محاولات التلاعب - Tamper Logs
     */
    public function up(): void
    {
        Schema::create('tamper_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            
            // نوع التلاعب
            $table->enum('tamper_type', [
                'gps_spoof',          // تزوير الموقع
                'photo_spoof',        // تزوير الصورة
                'time_manipulation',  // التلاعب بالوقت
                'device_clone',       // استنساخ الجهاز
                'proxy_vpn',          // استخدام VPN
                'multiple_accounts',  // حسابات متعددة
                'rooted_device',      // جهاز مكسور الحماية
                'emulator',           // محاكي
                'automation',         // أتمتة
                'other'
            ]);
            
            // خطورة المحاولة
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->decimal('confidence_score', 5, 2)->default(0);
            
            // تفاصيل الكشف
            $table->json('detection_details')->nullable();
            $table->text('description')->nullable();
            
            // معلومات الجهاز
            $table->string('device_id')->nullable();
            $table->string('device_model')->nullable();
            $table->string('os_version')->nullable();
            $table->boolean('is_rooted')->default(false);
            $table->boolean('is_emulator')->default(false);
            
            // معلومات الموقع
            $table->decimal('reported_latitude', 10, 8)->nullable();
            $table->decimal('reported_longitude', 11, 8)->nullable();
            $table->decimal('actual_latitude', 10, 8)->nullable();
            $table->decimal('actual_longitude', 11, 8)->nullable();
            $table->decimal('location_discrepancy_meters', 10, 2)->nullable();
            
            // معلومات الشبكة
            $table->string('ip_address')->nullable();
            $table->string('ip_country')->nullable();
            $table->string('ip_city')->nullable();
            $table->boolean('is_vpn')->default(false);
            $table->boolean('is_proxy')->default(false);
            $table->boolean('is_tor')->default(false);
            
            // الإجراء المتخذ
            $table->enum('action_taken', [
                'none',
                'logged',
                'blocked',
                'alerted',
                'suspended',
                'reported'
            ])->default('logged');
            
            // حالة المراجعة
            $table->enum('review_status', [
                'pending',
                'confirmed',
                'false_positive',
                'dismissed'
            ])->default('pending');
            
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['employee_id', 'tamper_type']);
            $table->index(['severity', 'created_at']);
            $table->index(['review_status', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tamper_logs');
    }
};
