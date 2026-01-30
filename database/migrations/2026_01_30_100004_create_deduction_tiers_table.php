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
        Schema::create('deduction_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم المستوى');
            $table->integer('min_minutes')->comment('الحد الأدنى بالدقائق');
            $table->integer('max_minutes')->comment('الحد الأقصى بالدقائق');
            $table->integer('deduction_points')->comment('نقاط الخصم');
            $table->decimal('deduction_percentage', 5, 2)->default(0)->comment('نسبة الخصم من الراتب');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deduction_tiers');
    }
};
