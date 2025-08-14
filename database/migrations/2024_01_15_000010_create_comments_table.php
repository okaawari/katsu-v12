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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
            
            // Commentable polymorphic relationship
            $table->morphs('commentable'); // commentable_type, commentable_id
            
            // Comment Content
            $table->text('content');
            $table->text('content_html')->nullable(); // Parsed markdown/HTML
            
            // Moderation
            $table->enum('status', ['published', 'pending', 'hidden', 'spam'])->default('published');
            $table->text('moderation_reason')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('moderated_at')->nullable();
            
            // Interaction Stats
            $table->integer('likes_count')->default(0);
            $table->integer('dislikes_count')->default(0);
            $table->integer('replies_count')->default(0);
            
            // Metadata
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            
            // Timestamps
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['commentable_type', 'commentable_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['parent_id', 'status']);
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};