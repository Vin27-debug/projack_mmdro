<?php

namespace Tests\Feature;

use App\Models\Ambulance;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ResponseTimeAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_response_time_page_renders_metrics_and_chart(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-100',
            'contact_number' => '09123450000',
            'license_number' => 'LIC-100',
            'license_expiry' => '2030-01-01',
            'status' => 'available',
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'ABC-9999',
            'vehicle_name' => 'Rapid Response Unit',
            'vehicle_type' => 'ambulance',
            'status' => 'available',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-9001',
            'reporter_name' => 'Test Reporter',
            'contact_number' => '09120000001',
            'incident_type' => 'Medical',
            'location' => 'Test Location',
            'description' => 'Response test case',
            'status' => 'completed',
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
        ]);

        Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
            'status' => Dispatch::STATUS_COMPLETED,
            'assigned_at' => now()->subMinutes(18),
            'arrived_at' => now(),
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/admin/reports/response-time');

        $response->assertOk();
        $response->assertSee('Average Response Time');
        $response->assertSee('Fastest Response');
        $response->assertSee('Slowest Response');
        $response->assertSee('Total Completed Responses');
        $response->assertSee('Response Time Trend');
    }
}
