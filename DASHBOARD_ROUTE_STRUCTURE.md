# Dashboard Route Structure

## Overview

All authenticated routes are now organized under the `/dashboard` prefix, providing a clean and organized URL structure for your application.

## Route Structure

### Main Dashboard
- **URL**: `/dashboard`
- **Route Name**: `dashboard.index`
- **Description**: Main dashboard page with overview and statistics

### Settings
- **URL**: `/dashboard/settings`
- **Route Name**: `dashboard.settings.*`
- **Pages**:
  - `/dashboard/settings/profile` - `dashboard.settings.profile`
  - `/dashboard/settings/password` - `dashboard.settings.password`
  - `/dashboard/settings/appearance` - `dashboard.settings.appearance`
  - `/dashboard/settings/sessions` - `dashboard.settings.sessions`

### User Profile
- **URL**: `/dashboard/profile`
- **Route Name**: `dashboard.profile.show`
- **Description**: User's own profile page

### Analytics
- **URL**: `/dashboard/analytics`
- **Route Name**: `dashboard.analytics.index`
- **Description**: Analytics and insights dashboard

### Reports
- **URL**: `/dashboard/reports`
- **Route Name**: `dashboard.reports.index`
- **Description**: Reports generation and viewing

### Admin Panel
- **URL**: `/dashboard/admin`
- **Route Name**: `dashboard.admin.index`
- **Description**: Administrative dashboard (admin users only)

## Navigation Structure

### Main Navigation
- **Dashboard** - Main overview
- **Analytics** - Data insights and metrics
- **Reports** - Report generation and viewing

### User Navigation
- **Profile** - User profile management
- **Settings** - Application settings

### Admin Navigation (Conditional)
- **Admin Panel** - Administrative functions (shown only for admin users)

## URL Examples

```
# Main dashboard
https://yourwebsite.com/dashboard

# Settings pages
https://yourwebsite.com/dashboard/settings/profile
https://yourwebsite.com/dashboard/settings/password
https://yourwebsite.com/dashboard/settings/appearance
https://yourwebsite.com/dashboard/settings/sessions

# User profile
https://yourwebsite.com/dashboard/profile

# Analytics
https://yourwebsite.com/dashboard/analytics

# Reports
https://yourwebsite.com/dashboard/reports

# Admin panel
https://yourwebsite.com/dashboard/admin
```

## Benefits

1. **Organized Structure**: All authenticated pages are under `/dashboard`
2. **Clear Hierarchy**: Logical grouping of related functionality
3. **Scalable**: Easy to add new sections under the dashboard prefix
4. **SEO Friendly**: Clean, descriptive URLs
5. **Security**: Clear separation between public and authenticated areas

## Future Extensions

The structure is designed to easily accommodate new features:

```php
// Example: Adding new dashboard sections
Route::prefix('dashboard')->name('dashboard.')->group(function () {
    // Existing routes...
    
    // New sections can be added here
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', UsersIndex::class)->name('index');
        Route::get('/create', UsersCreate::class)->name('create');
        Route::get('/{user}', UsersShow::class)->name('show');
    });
    
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', ProductsIndex::class)->name('index');
        Route::get('/create', ProductsCreate::class)->name('create');
    });
});
```

## Authentication Flow

After login, users are automatically redirected to `/dashboard` (dashboard.index route), providing a seamless experience.

## Public Routes

Public routes remain outside the dashboard structure:
- `/` - Welcome page
- `/login` - Login page
- `/register` - Registration page
- `/profile/{userId}` - Public profile pages
