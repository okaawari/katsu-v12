<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register the cleanup sessions command
Artisan::command('sessions:cleanup {--days=7}', function () {
    $days = $this->option('days');
    $cutoffTime = time() - ($days * 24 * 60 * 60);
    
    $this->info("Cleaning up sessions older than {$days} days...");
    
    // Delete old sessions
    $oldSessionsDeleted = DB::table('sessions')
        ->where('last_activity', '<', $cutoffTime)
        ->delete();
        
    $this->info("Deleted {$oldSessionsDeleted} old sessions.");
    
    // Clean up duplicate sessions
    $duplicatesDeleted = cleanupDuplicateSessions();
    
    $this->info("Deleted {$duplicatesDeleted} duplicate sessions.");
    $this->info('Session cleanup completed successfully.');
})->purpose('Clean up old and duplicate sessions');

// Helper function for deduplication
function cleanupDuplicateSessions() {
    $users = DB::table('sessions')
        ->whereNotNull('user_id')
        ->distinct()
        ->pluck('user_id');
        
    $totalDuplicatesDeleted = 0;
    
    foreach ($users as $userId) {
        $userSessions = DB::table('sessions')
            ->where('user_id', $userId)
            ->get();
            
        $deviceGroups = [];
        $sessionsToDelete = [];
        
        // Group sessions by device fingerprint
        foreach ($userSessions as $session) {
            $fingerprint = createDeviceFingerprint($session->ip_address, $session->user_agent);
            
            if (!isset($deviceGroups[$fingerprint])) {
                $deviceGroups[$fingerprint] = [];
            }
            
            $deviceGroups[$fingerprint][] = $session;
        }
        
        // For each device group, keep only the most recent session
        foreach ($deviceGroups as $fingerprint => $sessions) {
            if (count($sessions) > 1) {
                // Sort by last_activity descending
                usort($sessions, function ($a, $b) {
                    return $b->last_activity <=> $a->last_activity;
                });
                
                // Keep the first (most recent) session, mark others for deletion
                for ($i = 1; $i < count($sessions); $i++) {
                    $sessionsToDelete[] = $sessions[$i]->id;
                }
            }
        }
        
        // Delete duplicate sessions
        if (!empty($sessionsToDelete)) {
            $deleted = DB::table('sessions')->whereIn('id', $sessionsToDelete)->delete();
            $totalDuplicatesDeleted += $deleted;
        }
    }
    
    return $totalDuplicatesDeleted;
}

// Helper function to create device fingerprint
function createDeviceFingerprint($ipAddress, $userAgent) {
    $ip = $ipAddress ?? 'unknown';
    $ua = $userAgent ?? 'unknown';
    
    return md5($ip . '|' . $ua);
}
