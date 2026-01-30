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
        Schema::table('branches', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('address')->comment('خط العرض');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude')->comment('خط الطول');
            $table->integer('geofence_radius')->default(100)->after('longitude')->comment('نطاق الموقع بالمتر');
            $table->boolean('require_wifi')->default(false)->after('geofence_radius')->comment('يتطلب Wi-Fi للحضور');
            $table->boolean('require_gps')->default(true)->after('require_wifi')->comment('يتطلب GPS للحضور');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'geofence_radius', 'require_wifi', 'require_gps']);
        });
    }
};
