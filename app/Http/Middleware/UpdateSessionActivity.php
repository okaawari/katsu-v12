<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;
use Symfony\Component\HttpFoundation\Response;

class UpdateSessionActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only update session activity for authenticated users
        if (Auth::check()) {
            $this->updateSessionActivity($request);
        }

        return $response;
    }

    /**
     * Update the current session's last activity timestamp.
     */
    private function updateSessionActivity(Request $request): void
    {
        $sessionId = Session::getId();
        $userId = Auth::id();
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();
        $currentTime = time();

        // Update the current session's last activity
        DB::table('sessions')
            ->where('id', $sessionId)
            ->update([
                'last_activity' => $currentTime,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

        // If this is a Livewire request, dispatch an event to update the sessions component
        if ($request->hasHeader('X-Livewire')) {
            // Dispatch a browser event to update the sessions component
            $this->dispatchSessionUpdateEvent();
        }
    }

    /**
     * Dispatch a browser event to update the sessions component.
     */
    private function dispatchSessionUpdateEvent(): void
    {
        // This will be handled by JavaScript to trigger a Livewire update
        $script = "
            <script>
                if (typeof Livewire !== 'undefined') {
                    Livewire.dispatch('session-updated');
                }
            </script>
        ";
        
        // We'll need to inject this into the response
        // For now, we'll use a simpler approach with a custom header
        header('X-Session-Updated: ' . time());
    }
}
