<?php

namespace Tests\Feature;

use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DriverPerformanceAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_performance_page_renders_analytics_dashboard(): void
    {
        Role::create(['name' => 'admin']);

        $admin = User::factory()->create([
            'status' => 'approved',
        ]);
        $admin->assignRole('admin');

        $user = User::factory()->create();
        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'DRV-100',
            'contact_number' => '123456',
            'license_number' => 'LIC-1',
            'license_expiry' => now()->addYear()->toDateString(),
            'status' => 'available',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-100',
            'reporter_name' => 'Test Reporter',
            'contact_number' => '123456',
            'incident_type' => 'Medical',
            'location' => 'Test Location',
            'description' => 'Test',
            'status' => 'completed',
        ]);

        Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => null,
            'status' => Dispatch::STATUS_COMPLETED,
            'assigned_at' => now()->subMinutes(5),
            'accepted_at' => now()->subMinutes(3),
            'arrived_at' => now()->subMinutes(2),
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports/driver-performance');

        $response->assertOk();
        $response->assertSee('Completed Dispatches');
        $response->assertSee('Average Response Time');
        $response->assertSee('Completion Rate');
    }
}
