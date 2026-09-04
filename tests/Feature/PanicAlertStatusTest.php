<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\PanicAlert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanicAlertStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_panic_alerts_use_the_resolved_column(): void
    {
        $user = User::factory()->create();
        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'PANIC-TEST-001',
            'contact_number' => '09123456789',
            'license_number' => 'PANIC-LICENSE-001',
            'license_expiry' => '2030-01-01',
        ]);

        PanicAlert::create([
            'driver_id' => $driver->id,
            'latitude' => 14.5995,
            'longitude' => 120.9842,
            'triggered_at' => now(),
            'resolved' => false,
        ]);

        PanicAlert::create([
            'driver_id' => $driver->id,
            'latitude' => 14.5995,
            'longitude' => 120.9842,
            'triggered_at' => now(),
            'resolved' => true,
        ]);

        $activeAlerts = PanicAlert::where('resolved', false)->get();

        $this->assertCount(1, $activeAlerts);
        $this->assertFalse($activeAlerts->first()->resolved);
    }
}
