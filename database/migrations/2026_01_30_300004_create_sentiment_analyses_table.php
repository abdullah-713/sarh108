<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تحليل المشاعر - Sentiment Analysis
     */
    public function up(): void
    {
        Schema::create('sentiment_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            
            // مصدر التحليل
            $table->enum('source_type', [
                'attendance_pattern',  // نمط الحضور
                'performance_review',  // تقييم الأداء
                'survey_response',     // استجابة استبيان
                'feedback',            // ملاحظات
                'complaint',           // شكوى
                'manual_entry',        // إدخال يدوي
                'ai_analysis'          // تحليل ذكي
            ]);
            $table->unsignedBigInteger('source_id')->nullable();
            
            // نتائج التحليل
            $table->enum('sentiment', [
                'very_positive',
                'positive',
                'neutral',
                'negative',
                'very_negative'
            ])->default('neutral');
            
            $table->decimal('sentiment_score', 5, 2)->default(0); // -100 to 100
            $table->decimal('confidence_score', 5, 2)->default(0);
            
            // المشاعر المحددة
            $table->json('emotions')->nullable(); // مثال: {happiness: 0.8, stress: 0.2}
            $table->string('primary_emotion')->nullable();
            $table->decimal('engagement_level', 5, 2)->nullable(); // مستوى التفاعل
            $table->decimal('satisfaction_score', 5, 2)->nullable(); // رضا الموظف
            
            // مؤشرات الخطر
            $table->boolean('is_concerning')->default(false);
            $table->json('risk_indicators')->nullable();
            $table->text('concerns_summary')->nullable();
            
            // التوصيات
            $table->json('recommendations')->nullable();
            $table->text('action_items')->nullable();
            
            // متابعة
            $table->boolean('requires_followup')->default(false);
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('followup_date')->nullable();
            $table->enum('followup_status', [
                'pending',
                'in_progress',
                'completed',
                'cancelled'
            ])->nullable();
            $table->text('followup_notes')->nullable();
            
            // الفترة الزمنية
            $table->date('analysis_date');
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'quarterly'])->default('weekly');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['employee_id', 'analysis_date']);
            $table->index(['sentiment', 'is_concerning']);
            $table->index(['branch_id', 'analysis_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sentiment_analyses');
    }
};
