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
        Schema::create('scheduled_publications', function (Blueprint $table) {
            $table->id();
            $table->morphs('publishable'); // publishable_type, publishable_id
            $table->foreignId('scheduled_by')->constrained('users')->onDelete('cascade');
            
            // Scheduling Information
            $table->timestamp('scheduled_for');
            $table->enum('status', ['pending', 'published', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('published_at')->nullable();
            
            // Publication Settings
            $table->enum('visibility', ['public', 'private', 'unlisted', 'members_only'])->default('public');
            $table->boolean('notify_subscribers')->default(true);
            $table->boolean('send_notifications')->default(true);
            
            // Failure Handling
            $table->text('failure_reason')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            
            // Metadata
            $table->json('publication_settings')->nullable(); // Additional settings
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'scheduled_for']);
            $table->index(['publishable_type', 'publishable_id']);
            $table->index(['scheduled_for', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_publications');
    }
};