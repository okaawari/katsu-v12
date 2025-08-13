<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Sessions;
use App\Livewire\Profile\Show;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Public profile routes
Route::get('profile', Show::class)->name('profile.show');
Route::get('profile/{userId}', Show::class)->name('profile.show.user');

// Authentication routes
require __DIR__.'/auth.php';

// Dashboard routes (all authenticated routes)
Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard.')->group(function () {
    // Main dashboard index
    Route::get('/', function () {
        return view('dashboard');
    })->name('index');
    
    // Settings routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::redirect('/', 'profile');
        Route::get('profile', Profile::class)->name('profile');
        Route::get('password', Password::class)->name('password');
        Route::get('appearance', Appearance::class)->name('appearance');
        Route::get('sessions', Sessions::class)->name('sessions');
    });
    
    // Admin routes (for future admin functionality)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', function () {
            return view('dashboard.admin.index');
        })->name('index');
        
        // Add more admin routes here as needed
        // Route::get('users', AdminUsers::class)->name('users');
        // Route::get('analytics', AdminAnalytics::class)->name('analytics');
    });
    
    // User profile routes
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', Show::class)->name('show');
    });
    
    // Analytics routes (for future analytics functionality)
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', function () {
            return view('dashboard.analytics.index');
        })->name('index');
        
        // Add more analytics routes here as needed
        // Route::get('reports', AnalyticsReports::class)->name('reports');
        // Route::get('charts', AnalyticsCharts::class)->name('charts');
    });
    
    // Reports routes (for future reports functionality)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', function () {
            return view('dashboard.reports.index');
        })->name('index');
        
        // Add more reports routes here as needed
        // Route::get('monthly', ReportsMonthly::class)->name('monthly');
        // Route::get('yearly', ReportsYearly::class)->name('yearly');
    });
});
