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
        Schema::table('users', function (Blueprint $table) {
            // Profile Information
            $table->text('about')->nullable()->after('email_verified_at');
            $table->string('avatar')->nullable()->after('about');
            $table->string('cover_image')->nullable()->after('avatar');
            $table->string('location')->nullable()->after('cover_image');
            $table->string('website')->nullable()->after('location');
            $table->date('birth_date')->nullable()->after('website');
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable()->after('birth_date');
            
            // Subscription & Premium
            $table->timestamp('subscription_date')->nullable()->after('gender');
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_date');
            $table->enum('subscription_type', ['free', 'premium', 'vip'])->default('free')->after('subscription_expires_at');
            $table->boolean('is_premium')->default(false)->after('subscription_type');
            
            // Statistics
            $table->integer('total_watch_time')->default(0)->after('is_premium'); // in minutes
            $table->integer('anime_watched')->default(0)->after('total_watch_time');
            $table->integer('episodes_watched')->default(0)->after('anime_watched');
            $table->decimal('average_rating_given', 3, 2)->default(0.00)->after('episodes_watched');
            $table->integer('reviews_count')->default(0)->after('average_rating_given');
            $table->integer('comments_count')->default(0)->after('reviews_count');
            
            // Preferences
            $table->json('preferences')->nullable()->after('comments_count'); // Theme, language, etc.
            $table->string('timezone', 50)->default('UTC')->after('preferences');
            $table->string('language', 5)->default('en')->after('timezone');
            
            // Activity & Status
            $table->timestamp('last_active_at')->nullable()->after('language');
            $table->enum('status', ['active', 'inactive', 'suspended', 'banned'])->default('active')->after('last_active_at');
            $table->text('status_reason')->nullable()->after('status');
            
            // Privacy Settings
            $table->boolean('profile_public')->default(true)->after('status_reason');
            $table->boolean('show_watch_history')->default(true)->after('profile_public');
            $table->boolean('show_favorites')->default(true)->after('show_watch_history');
            $table->boolean('allow_friend_requests')->default(true)->after('show_favorites');
            
            // Verification & Trust
            $table->boolean('is_verified')->default(false)->after('allow_friend_requests');
            $table->integer('trust_score')->default(0)->after('is_verified');
            
            // Add indexes
            $table->index(['status', 'is_premium']);
            $table->index('last_active_at');
            $table->index(['subscription_type', 'subscription_expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'about', 'avatar', 'cover_image', 'location', 'website', 'birth_date', 'gender',
                'subscription_date', 'subscription_expires_at', 'subscription_type', 'is_premium',
                'total_watch_time', 'anime_watched', 'episodes_watched', 'average_rating_given', 
                'reviews_count', 'comments_count', 'preferences', 'timezone', 'language',
                'last_active_at', 'status', 'status_reason', 'profile_public', 'show_watch_history',
                'show_favorites', 'allow_friend_requests', 'is_verified', 'trust_score'
            ]);
        });
    }
};