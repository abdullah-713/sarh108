<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * PWA Support - Progressive Web App configuration
     */
    public function up(): void
    {
        // PWA configurations per company
        Schema::create('pwa_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->onDelete('cascade');
            $table->boolean('pwa_enabled')->default(true);
            $table->string('app_name')->default('صرح الإتقان');
            $table->string('app_short_name')->default('صرح');
            $table->text('app_description')->nullable();
            $table->string('theme_color', 7)->default('#ff8531');
            $table->string('background_color', 7)->default('#ffffff');
            $table->enum('display_mode', ['standalone', 'fullscreen', 'minimal-ui', 'browser'])->default('standalone');
            $table->enum('orientation', ['any', 'portrait', 'landscape'])->default('portrait');
            $table->string('start_url')->default('/dashboard');
            $table->string('scope')->default('/');
            $table->json('icons')->nullable();
            $table->json('screenshots')->nullable();
            $table->json('shortcuts')->nullable();
            $table->boolean('enable_push_notifications')->default(true);
            $table->boolean('enable_offline_mode')->default(true);
            $table->json('offline_pages')->nullable(); // Pages available offline
            $table->json('cache_strategy')->nullable();
            $table->timestamps();
        });
        
        // Push notification subscriptions
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('endpoint', 500)->unique();
            $table->string('public_key')->nullable();
            $table->string('auth_token')->nullable();
            $table->string('content_encoding')->default('aesgcm');
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
        });
        
        // Notification queue
        Schema::create('notification_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('channel', ['push', 'email', 'sms', 'in_app', 'all'])->default('push');
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->text('body');
            $table->text('body_ar')->nullable();
            $table->string('icon')->nullable();
            $table->string('action_url')->nullable();
            $table->json('data')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->enum('status', ['pending', 'sending', 'sent', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'scheduled_at']);
            $table->index(['user_id', 'status']);
        });
        
        // Offline sync queue
        Schema::create('offline_sync_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('device_id');
            $table->enum('action_type', ['checkin', 'checkout', 'location_update', 'form_submit', 'data_sync']);
            $table->json('payload');
            $table->timestamp('client_timestamp');
            $table->enum('sync_status', ['pending', 'processing', 'synced', 'failed', 'conflict'])->default('pending');
            $table->timestamp('synced_at')->nullable();
            $table->text('conflict_resolution')->nullable();
            $table->integer('sync_attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'sync_status']);
            $table->index(['device_id', 'sync_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_sync_queue');
        Schema::dropIfExists('notification_queue');
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('pwa_configurations');
    }
};
