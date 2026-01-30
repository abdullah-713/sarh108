<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            
            // التصميم
            $table->string('icon')->default('star'); // اسم الأيقونة
            $table->string('color')->default('#ff8531'); // اللون
            $table->string('background_color')->default('#fff7ed');
            $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum', 'diamond'])->default('bronze');
            
            // الشروط
            $table->enum('type', [
                'punctuality',      // الالتزام بالوقت
                'attendance',       // الحضور المنتظم
                'early_bird',       // الوصول المبكر
                'streak',           // الاستمرارية
                'perfect_month',    // شهر مثالي
                'mvp',              // موظف مثالي
                'team_player',      // لاعب فريق
                'custom'            // مخصص
            ])->default('custom');
            
            $table->integer('required_days')->nullable(); // عدد الأيام المطلوبة
            $table->integer('required_streak')->nullable(); // عدد الأيام المتتالية
            $table->decimal('required_rate', 5, 2)->nullable(); // النسبة المطلوبة
            $table->integer('points')->default(0); // النقاط الممنوحة
            
            $table->boolean('is_active')->default(true);
            $table->boolean('is_auto_award')->default(true); // منح تلقائي
            $table->integer('sort_order')->default(0);
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('employee_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_id')->constrained()->onDelete('cascade');
            
            $table->date('awarded_date');
            $table->string('period')->nullable(); // الفترة (يناير 2026، الربع الأول، إلخ)
            $table->text('reason')->nullable();
            
            $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_displayed')->default(true); // عرض في البروفايل
            
            $table->timestamps();
            
            $table->index(['employee_id', 'badge_id']);
            $table->index(['awarded_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_badges');
        Schema::dropIfExists('badges');
    }
};
