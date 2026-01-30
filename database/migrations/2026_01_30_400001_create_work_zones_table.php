<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Work Zones - Defining specific work areas within branches
     */
    public function up(): void
    {
        Schema::create('work_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->enum('zone_type', ['indoor', 'outdoor', 'parking', 'gate', 'cafeteria', 'meeting', 'restricted', 'custom'])->default('indoor');
            $table->decimal('center_latitude', 10, 8)->nullable();
            $table->decimal('center_longitude', 11, 8)->nullable();
            $table->integer('radius_meters')->default(50); // Zone radius
            $table->json('polygon_coordinates')->nullable(); // For complex shapes
            $table->integer('floor_number')->nullable();
            $table->string('building')->nullable();
            $table->boolean('requires_authentication')->default(false);
            $table->boolean('track_time_in_zone')->default(true);
            $table->integer('min_time_minutes')->nullable(); // Minimum time required in zone
            $table->integer('max_time_minutes')->nullable(); // Maximum time allowed
            $table->json('allowed_employees')->nullable(); // Specific employee IDs
            $table->json('allowed_departments')->nullable(); // Department IDs
            $table->json('allowed_designations')->nullable(); // Designation IDs
            $table->boolean('is_active')->default(true);
            $table->string('color', 7)->default('#ff8531'); // Zone color for map display
            $table->integer('display_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['company_id', 'branch_id']);
            $table->index('is_active');
        });
        
        // Zone access logs
        Schema::create('zone_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_zone_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->enum('access_type', ['entry', 'exit']);
            $table->timestamp('access_time');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('accuracy_meters')->nullable();
            $table->string('device_id')->nullable();
            $table->integer('duration_minutes')->nullable(); // Calculated on exit
            $table->boolean('was_authorized')->default(true);
            $table->string('denial_reason')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'access_time']);
            $table->index(['work_zone_id', 'access_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zone_access_logs');
        Schema::dropIfExists('work_zones');
    }
};
