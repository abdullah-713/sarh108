<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bulk_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['checkin', 'checkout'])->default('checkin');
            $table->integer('employee_count')->default(0);
            $table->json('employee_ids')->nullable();
            $table->timestamp('checked_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['branch_id', 'checked_at']);
            $table->index('supervisor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_checkins');
    }
};
