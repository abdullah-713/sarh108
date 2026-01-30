<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_performance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->date('date');
            
            // مؤشرات الأداء
            $table->integer('total_employees')->default(0);
            $table->integer('present_count')->default(0);
            $table->integer('late_count')->default(0);
            $table->integer('absent_count')->default(0);
            $table->integer('on_leave_count')->default(0);
            
            // النسب المئوية
            $table->decimal('attendance_rate', 5, 2)->default(0); // نسبة الحضور
            $table->decimal('punctuality_rate', 5, 2)->default(0); // نسبة الالتزام بالوقت
            $table->decimal('early_arrival_rate', 5, 2)->default(0); // نسبة الوصول المبكر
            
            // التأخير
            $table->integer('total_late_minutes')->default(0);
            $table->decimal('avg_late_minutes', 8, 2)->default(0);
            
            // النقاط والترتيب
            $table->integer('performance_score')->default(0); // من 100
            $table->integer('rank')->nullable();
            $table->integer('rank_change')->default(0); // التغير عن اليوم السابق
            
            // إحصائيات إضافية
            $table->integer('perfect_days_count')->default(0); // أيام بدون تأخير
            $table->integer('streak_days')->default(0); // أيام متتالية بأداء ممتاز
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->unique(['branch_id', 'date']);
            $table->index(['date', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_performance');
    }
};
