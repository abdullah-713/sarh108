<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * نظام توقع المخاطر - Risk Predictions
     */
    public function up(): void
    {
        Schema::create('risk_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            
            // نوع المخاطرة
            $table->enum('risk_type', [
                'absence',      // غياب متوقع
                'late',         // تأخير متوقع
                'resignation',  // استقالة محتملة
                'burnout',      // إرهاق
                'pattern_break' // كسر النمط
            ]);
            
            // مستوى الخطورة
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('low');
            $table->decimal('confidence_score', 5, 2)->default(0); // نسبة الثقة 0-100
            $table->decimal('risk_score', 5, 2)->default(0); // درجة المخاطرة 0-100
            
            // تفاصيل التوقع
            $table->json('factors')->nullable(); // العوامل المؤثرة
            $table->json('historical_data')->nullable(); // البيانات التاريخية
            $table->date('predicted_date')->nullable(); // التاريخ المتوقع
            $table->text('prediction_reason')->nullable(); // سبب التوقع
            $table->text('recommended_action')->nullable(); // الإجراء الموصى به
            
            // حالة المتابعة
            $table->enum('status', [
                'pending',    // بانتظار المراجعة
                'reviewed',   // تمت المراجعة
                'acted',      // تم اتخاذ إجراء
                'resolved',   // تم الحل
                'dismissed',  // تم التجاهل
                'occurred',   // حدث فعلاً
                'false_alarm' // إنذار كاذب
            ])->default('pending');
            
            // المتابعة
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->text('action_taken')->nullable();
            
            // نتيجة التوقع
            $table->boolean('was_accurate')->nullable(); // هل كان التوقع دقيقاً
            $table->timestamp('outcome_date')->nullable();
            $table->text('outcome_notes')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['employee_id', 'risk_type']);
            $table->index(['status', 'severity']);
            $table->index(['predicted_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_predictions');
    }
};
