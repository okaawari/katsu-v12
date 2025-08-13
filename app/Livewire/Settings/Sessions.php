<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\Attributes\On;

class Sessions extends Component
{
    public array $sessions = [];
    public string $currentSessionId = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->currentSessionId = Session::getId();
        $this->loadSessions();
    }

    /**
     * Load all active sessions for the current user with deduplication.
     */
    public function loadSessions(): void
    {
        $user = Auth::user();
        
        // Get all sessions for the user
        $rawSessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderBy('last_activity', 'desc')
            ->get();

        // Group sessions by device fingerprint (IP + User Agent)
        $groupedSessions = [];
        
        foreach ($rawSessions as $session) {
            $deviceFingerprint = $this->createDeviceFingerprint($session->ip_address, $session->user_agent);
            
            if (!isset($groupedSessions[$deviceFingerprint])) {
                $groupedSessions[$deviceFingerprint] = [
                    'sessions' => [],
                    'latest_activity' => 0,
                    'latest_session_id' => null
                ];
            }
            
            $groupedSessions[$deviceFingerprint]['sessions'][] = $session;
            
            // Keep track of the most recent session for this device
            if ($session->last_activity > $groupedSessions[$deviceFingerprint]['latest_activity']) {
                $groupedSessions[$deviceFingerprint]['latest_activity'] = $session->last_activity;
                $groupedSessions[$deviceFingerprint]['latest_session_id'] = $session->id;
            }
        }

        // Convert grouped sessions to display format
        $this->sessions = [];
        foreach ($groupedSessions as $deviceFingerprint => $deviceData) {
            $latestSession = collect($deviceData['sessions'])->first(function ($session) use ($deviceData) {
                return $session->id === $deviceData['latest_session_id'];
            });
            
            if ($latestSession) {
                $this->sessions[] = [
                    'id' => $latestSession->id,
                    'ip_address' => $latestSession->ip_address ?? 'Unknown',
                    'user_agent' => $latestSession->user_agent ?? 'Unknown',
                    'last_activity' => $latestSession->last_activity,
                    'is_current' => $latestSession->id === $this->currentSessionId,
                    'device_info' => $this->parseUserAgent($latestSession->user_agent ?? ''),
                    'session_count' => count($deviceData['sessions']),
                    'device_fingerprint' => $deviceFingerprint,
                ];
            }
        }

        // Sort by last activity (most recent first)
        usort($this->sessions, function ($a, $b) {
            return $b['last_activity'] <=> $a['last_activity'];
        });
    }

    /**
     * Create a device fingerprint based on IP and user agent.
     */
    private function createDeviceFingerprint(?string $ipAddress, ?string $userAgent): string
    {
        $ip = $ipAddress ?? 'unknown';
        $ua = $userAgent ?? 'unknown';
        
        // Create a hash that represents this device/browser combination
        return md5($ip . '|' . $ua);
    }

    /**
     * Parse user agent to get device information.
     */
    private function parseUserAgent(string $userAgent): array
    {
        $device = 'Unknown';
        $browser = 'Unknown';
        $os = 'Unknown';

        // Simple parsing - in production you might want to use a proper library
        if (strpos($userAgent, 'Mobile') !== false || strpos($userAgent, 'Android') !== false || strpos($userAgent, 'iPhone') !== false) {
            $device = 'Mobile';
        } elseif (strpos($userAgent, 'Tablet') !== false || strpos($userAgent, 'iPad') !== false) {
            $device = 'Tablet';
        } else {
            $device = 'Desktop';
        }

        if (strpos($userAgent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            $browser = 'Edge';
        } elseif (strpos($userAgent, 'Opera') !== false) {
            $browser = 'Opera';
        }

        if (strpos($userAgent, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (strpos($userAgent, 'Mac') !== false) {
            $os = 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $os = 'Android';
        } elseif (strpos($userAgent, 'iOS') !== false || strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
            $os = 'iOS';
        }

        return [
            'device' => $device,
            'browser' => $browser,
            'os' => $os,
        ];
    }

    /**
     * Terminate a specific session and all related sessions from the same device.
     */
    public function terminateSession(string $sessionId): void
    {
        if ($sessionId === $this->currentSessionId) {
            return; // Don't allow terminating current session
        }

        // Find the session to get its device fingerprint
        $sessionToTerminate = null;
        foreach ($this->sessions as $session) {
            if ($session['id'] === $sessionId) {
                $sessionToTerminate = $session;
                break;
            }
        }

        if ($sessionToTerminate) {
            // Delete all sessions with the same device fingerprint
            $deviceFingerprint = $sessionToTerminate['device_fingerprint'];
            
            // Get all sessions for this user and delete those matching the fingerprint
            $userSessions = DB::table('sessions')
                ->where('user_id', Auth::id())
                ->get();

            foreach ($userSessions as $session) {
                $fingerprint = $this->createDeviceFingerprint($session->ip_address, $session->user_agent);
                if ($fingerprint === $deviceFingerprint) {
                    DB::table('sessions')->where('id', $session->id)->delete();
                }
            }
        }

        $this->loadSessions();
        session()->flash('message', 'Session terminated successfully.');
    }

    /**
     * Terminate all other sessions except the current one.
     */
    public function terminateAllOtherSessions(): void
    {
        // Get current session's device fingerprint
        $currentSession = null;
        foreach ($this->sessions as $session) {
            if ($session['is_current']) {
                $currentSession = $session;
                break;
            }
        }

        if ($currentSession) {
            $currentDeviceFingerprint = $currentSession['device_fingerprint'];
            
            // Delete all sessions except those matching current device fingerprint
            $userSessions = DB::table('sessions')
                ->where('user_id', Auth::id())
                ->get();

            foreach ($userSessions as $session) {
                $fingerprint = $this->createDeviceFingerprint($session->ip_address, $session->user_agent);
                if ($fingerprint !== $currentDeviceFingerprint) {
                    DB::table('sessions')->where('id', $session->id)->delete();
                }
            }
        }

        $this->loadSessions();
        session()->flash('message', 'All other sessions terminated successfully.');
    }

    /**
     * Refresh the sessions list.
     */
    public function refreshSessions(): void
    {
        $this->loadSessions();
    }

    /**
     * Clean up duplicate sessions for the current user.
     */
    public function cleanupDuplicateSessions(): void
    {
        $user = Auth::user();
        $userSessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->get();

        $deviceGroups = [];
        $sessionsToDelete = [];

        // Group sessions by device fingerprint
        foreach ($userSessions as $session) {
            $fingerprint = $this->createDeviceFingerprint($session->ip_address, $session->user_agent);
            
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
            DB::table('sessions')->whereIn('id', $sessionsToDelete)->delete();
        }

        $this->loadSessions();
        session()->flash('message', 'Duplicate sessions cleaned up successfully.');
    }

    /**
     * Listen for session updates.
     */
    #[On('session-updated')]
    public function handleSessionUpdate(): void
    {
        $this->loadSessions();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.settings.sessions');
    }
}
