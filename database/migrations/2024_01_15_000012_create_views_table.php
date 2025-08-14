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
        Schema::create('views', function (Blueprint $table) {
            $table->id();
            $table->morphs('viewable'); // viewable_type, viewable_id
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            // Visitor Information
            $table->string('visitor_id')->nullable(); // For anonymous users
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('referer')->nullable();
            
            // Geographic Information
            $table->string('country', 2)->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            
            // Device & Platform
            $table->string('device_type')->nullable(); // mobile, desktop, tablet
            $table->string('browser')->nullable();
            $table->string('platform')->nullable(); // windows, macos, linux, android, ios
            
            // View Metadata
            $table->string('collection')->nullable(); // Group related views
            $table->json('metadata')->nullable(); // Additional view data
            $table->integer('duration_seconds')->nullable(); // How long they viewed
            
            // Timestamps
            $table->timestamp('viewed_at');
            $table->timestamps();
            
            // Indexes
            $table->index(['viewable_type', 'viewable_id']);
            $table->index(['user_id', 'viewed_at']);
            $table->index(['visitor_id', 'viewed_at']);
            $table->index('viewed_at');
            $table->index(['country', 'viewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('views');
    }
};