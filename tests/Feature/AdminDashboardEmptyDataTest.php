<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\Ambulance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardEmptyDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_with_empty_data(): void
    {
        Role::create(['name' => 'admin']);

        $admin = User::factory()->create([
            'status' => 'approved',
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Municipal Emergency Operations Center');
        $response->assertSee('No active panic alerts at this time.');
    }

    public function test_admin_dashboard_excludes_active_dispatches_for_archived_incidents(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole('admin');

        $driverUser = User::factory()->create(['status' => 'approved']);
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'ARCHIVED-DISPATCH-' . $driverUser->id,
            'contact_number' => '09123456789',
        ]);
        $vehicle = Ambulance::create([
            'plate_number' => 'ARCHIVED-DISPATCH-' . $driver->id,
            'vehicle_name' => 'Archived Incident Unit',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_AVAILABLE,
        ]);
        $incident = Incident::create([
            'incident_number' => 'INC-ARCHIVED-DISPATCH-' . $driver->id,
            'reporter_name' => 'Archived Incident',
            'incident_type' => 'Medical Emergency',
            'location' => 'Test Street',
            'status' => Incident::STATUS_CLOSED,
            'archived_at' => now(),
        ]);
        Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('data-counter="activeDispatches">0', false);
        $response->assertDontSee($incident->incident_number);
        $response->assertDontSee('Archived Incident Unit');
        $response->assertDontSee($driver->badge_id);
    }
}
