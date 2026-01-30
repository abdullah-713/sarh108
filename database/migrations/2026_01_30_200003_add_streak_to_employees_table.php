<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->integer('current_streak')->default(0)->after('status'); // الأيام المتتالية الحالية
            $table->integer('longest_streak')->default(0)->after('current_streak'); // أطول سلسلة
            $table->date('streak_start_date')->nullable()->after('longest_streak');
            $table->date('last_attendance_date')->nullable()->after('streak_start_date');
            
            // نقاط MVP
            $table->integer('mvp_points')->default(0)->after('last_attendance_date');
            $table->integer('total_badges')->default(0)->after('mvp_points');
            
            // إحصائيات الشهر
            $table->integer('monthly_on_time_days')->default(0)->after('total_badges');
            $table->integer('monthly_early_days')->default(0)->after('monthly_on_time_days');
            $table->integer('monthly_late_days')->default(0)->after('monthly_early_days');
            $table->integer('monthly_absent_days')->default(0)->after('monthly_late_days');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'current_streak',
                'longest_streak',
                'streak_start_date',
                'last_attendance_date',
                'mvp_points',
                'total_badges',
                'monthly_on_time_days',
                'monthly_early_days',
                'monthly_late_days',
                'monthly_absent_days',
            ]);
        });
    }
};
