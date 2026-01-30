<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_ticker', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content')->nullable();
            
            // النوع والأهمية
            $table->enum('type', [
                'announcement',  // إعلان
                'achievement',   // إنجاز
                'reminder',      // تذكير
                'warning',       // تحذير
                'celebration',   // احتفال
                'mvp',          // موظف مثالي
                'badge',        // شارة جديدة
                'streak',       // رقم قياسي
                'custom'        // مخصص
            ])->default('announcement');
            
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // التصميم
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->string('background_color')->nullable();
            
            // الاستهداف
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->json('target_departments')->nullable();
            $table->boolean('is_global')->default(true); // للجميع
            
            // الجدولة
            $table->datetime('starts_at')->nullable();
            $table->datetime('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            // الإحصائيات
            $table->integer('views_count')->default(0);
            $table->integer('clicks_count')->default(0);
            
            // الروابط
            $table->string('action_url')->nullable();
            $table->string('action_text')->nullable();
            
            $table->integer('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['is_active', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_ticker');
    }
};
