<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * فحص الحيوية - Liveness Checks
     */
    public function up(): void
    {
        Schema::create('liveness_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->onDelete('set null');
            
            // نوع الفحص
            $table->enum('check_type', [
                'face',      // التعرف على الوجه
                'blink',     // رمش العين
                'smile',     // ابتسامة
                'turn_head', // تدوير الرأس
                'voice',     // صوت
                'gesture',   // إيماءة
                'random'     // عشوائي
            ])->default('face');
            
            // النتيجة
            $table->boolean('passed')->default(false);
            $table->decimal('confidence_score', 5, 2)->default(0); // نسبة الثقة
            $table->decimal('similarity_score', 5, 2)->nullable(); // تشابه الوجه
            
            // بيانات الفحص
            $table->string('image_path')->nullable(); // صورة الفحص
            $table->string('reference_image_path')->nullable(); // صورة المرجع
            $table->json('face_landmarks')->nullable(); // معالم الوجه
            $table->json('detection_data')->nullable(); // بيانات الكشف
            
            // معلومات محاولة التزوير
            $table->boolean('is_spoofing_attempt')->default(false);
            $table->enum('spoofing_type', [
                'none',
                'photo',       // صورة مطبوعة
                'screen',      // شاشة
                'mask',        // قناع
                'video',       // فيديو
                'deepfake',    // تزييف عميق
                'other'
            ])->nullable();
            $table->decimal('spoofing_confidence', 5, 2)->nullable();
            
            // معلومات الجهاز
            $table->string('device_type')->nullable();
            $table->string('device_id')->nullable();
            $table->string('browser')->nullable();
            $table->string('ip_address')->nullable();
            $table->json('device_fingerprint')->nullable();
            
            // الوقت
            $table->integer('processing_time_ms')->nullable(); // وقت المعالجة
            $table->integer('attempt_number')->default(1); // رقم المحاولة
            
            $table->timestamps();
            
            $table->index(['employee_id', 'passed']);
            $table->index(['is_spoofing_attempt', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liveness_checks');
    }
};
