<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Check if columns already exist before adding them
            if (!Schema::hasColumn('employees', 'current_streak')) {
                $table->integer('current_streak')->default(0); // الأيام المتتالية الحالية
            }
            if (!Schema::hasColumn('employees', 'longest_streak')) {
                $table->integer('longest_streak')->default(0); // أطول سلسلة
            }
            if (!Schema::hasColumn('employees', 'streak_start_date')) {
                $table->date('streak_start_date')->nullable();
            }
            if (!Schema::hasColumn('employees', 'last_attendance_date')) {
                $table->date('last_attendance_date')->nullable();
            }
            
            // نقاط MVP
            if (!Schema::hasColumn('employees', 'mvp_points')) {
                $table->integer('mvp_points')->default(0);
            }
            if (!Schema::hasColumn('employees', 'total_badges')) {
                $table->integer('total_badges')->default(0);
            }
            
            // إحصائيات الشهر
            if (!Schema::hasColumn('employees', 'monthly_on_time_days')) {
                $table->integer('monthly_on_time_days')->default(0);
            }
            if (!Schema::hasColumn('employees', 'monthly_early_days')) {
                $table->integer('monthly_early_days')->default(0);
            }
            if (!Schema::hasColumn('employees', 'monthly_late_days')) {
                $table->integer('monthly_late_days')->default(0);
            }
            if (!Schema::hasColumn('employees', 'monthly_absent_days')) {
                $table->integer('monthly_absent_days')->default(0);
            }
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
