<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SessionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_deduplication_works()
    {
        $user = User::factory()->create();
        
        // Create multiple sessions with the same IP and user agent
        $ipAddress = '192.168.1.1';
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
        
        // Insert multiple sessions manually
        for ($i = 0; $i < 3; $i++) {
            DB::table('sessions')->insert([
                'id' => 'session_' . $i,
                'user_id' => $user->id,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'payload' => 'test',
                'last_activity' => time() - $i * 60, // Different timestamps
            ]);
        }
        
        // Verify we have 3 sessions
        $this->assertEquals(3, DB::table('sessions')->where('user_id', $user->id)->count());
        
        // Run the cleanup command
        $this->artisan('sessions:cleanup');
        
        // Should only have 1 session left (the most recent one)
        $this->assertEquals(1, DB::table('sessions')->where('user_id', $user->id)->count());
        
        // The remaining session should be the most recent one
        $remainingSession = DB::table('sessions')->where('user_id', $user->id)->first();
        $this->assertEquals('session_0', $remainingSession->id);
    }

    public function test_sessions_with_different_devices_are_not_deduplicated()
    {
        $user = User::factory()->create();
        
        // Create sessions with different user agents
        $sessions = [
            [
                'id' => 'session_1',
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ],
            [
                'id' => 'session_2',
                'ip_address' => '192.168.1.1',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
            ],
        ];
        
        foreach ($sessions as $session) {
            DB::table('sessions')->insert([
                'id' => $session['id'],
                'user_id' => $user->id,
                'ip_address' => $session['ip_address'],
                'user_agent' => $session['user_agent'],
                'payload' => 'test',
                'last_activity' => time(),
            ]);
        }
        
        // Run the cleanup command
        $this->artisan('sessions:cleanup');
        
        // Should still have 2 sessions (different devices)
        $this->assertEquals(2, DB::table('sessions')->where('user_id', $user->id)->count());
    }
}
