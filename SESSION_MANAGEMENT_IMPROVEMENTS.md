# Session Management Improvements

## Issues Fixed

### 1. Multiple Session Entries for Same Device
**Problem**: The system was creating multiple session entries for the same device/browser combination, even when the IP address and user agent were identical.

**Solution**: 
- Implemented device fingerprinting using IP address + user agent hash
- Sessions are now grouped by device fingerprint and only the most recent session per device is displayed
- Added session count indicator to show when multiple sessions exist for the same device

### 2. Current Session Not Showing Without Refresh
**Problem**: The current session wasn't automatically displayed in the Active Sessions list without manually pressing the Refresh button.

**Solution**:
- Added automatic polling every 30 seconds to the sessions component (`wire:poll.30s`)
- Implemented JavaScript-based activity detection to trigger session updates
- Added middleware to automatically update session activity on each request
- Sessions component now automatically refreshes when user is active

## New Features

### 1. Session Deduplication
- **Cleanup Duplicates Button**: Manually remove duplicate sessions from the same device
- **Automatic Deduplication**: Sessions with the same IP and user agent are grouped together
- **Session Count Display**: Shows how many sessions exist for each device

### 2. Improved Session Management
- **Device Fingerprinting**: Uses MD5 hash of IP + User Agent to identify unique devices
- **Smart Termination**: Terminating a session now removes all sessions from the same device
- **Current Session Highlighting**: Current session is clearly marked and protected from termination

### 3. Console Command
- **`php artisan sessions:cleanup`**: Command to clean up old and duplicate sessions
- **Configurable Retention**: `--days=7` option to specify how long to keep sessions
- **Automatic Deduplication**: Removes duplicate sessions while keeping the most recent one

## Technical Implementation

### Device Fingerprinting
```php
private function createDeviceFingerprint(?string $ipAddress, ?string $userAgent): string
{
    $ip = $ipAddress ?? 'unknown';
    $ua = $userAgent ?? 'unknown';
    
    return md5($ip . '|' . $ua);
}
```
 
### Session Grouping
Sessions are grouped by device fingerprint and only the most recent session per device is displayed. The system tracks:
- Session count per device
- Most recent activity timestamp
- Current session identification

### Automatic Updates
- **Livewire Polling**: Component refreshes every 30 seconds
- **Activity Detection**: JavaScript monitors user activity and triggers updates
- **Middleware**: Updates session activity on each authenticated request

## Usage

### Manual Cleanup
1. Go to Settings → Sessions
2. Click "Cleanup Duplicates" button
3. Confirm the action

### Console Cleanup
```bash
# Clean up sessions older than 7 days (default)
php artisan sessions:cleanup

# Clean up sessions older than 30 days
php artisan sessions:cleanup --days=30
```

### Scheduled Cleanup
Add to your server's cron job:
```bash
# Run daily at 2 AM
0 2 * * * cd /path/to/your/app && php artisan sessions:cleanup
```

## Benefits

1. **Cleaner Session List**: No more duplicate entries for the same device
2. **Real-time Updates**: Current session always visible without manual refresh
3. **Better Security**: Easier to identify and terminate suspicious sessions
4. **Reduced Database Load**: Fewer session entries to manage
5. **Improved UX**: Clear indication of current session and device counts

## Files Modified

- `app/Livewire/Settings/Sessions.php` - Enhanced session management logic
- `resources/views/livewire/settings/sessions.blade.php` - Updated UI with deduplication features
- `app/Http/Middleware/UpdateSessionActivity.php` - New middleware for session updates
- `app/Providers/AppServiceProvider.php` - Registered middleware
- `resources/views/components/layouts/app/sidebar.blade.php` - Added JavaScript for activity detection
- `routes/console.php` - Added cleanup command
- `app/Console/Commands/CleanupOldSessions.php` - Console command implementation
- `tests/Feature/SessionManagementTest.php` - Tests for deduplication functionality
