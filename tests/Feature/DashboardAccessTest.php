<?php

namespace Tests\Feature;

use App\Models\Ambulance;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\User;
use App\Models\VehicleDriverAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_dashboard_shows_summary_cards(): void
    {
        $role = Role::firstOrCreate(['name' => 'super-admin']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-001',
            'contact_number' => '09123456789',
            'license_number' => 'LIC-001',
            'license_expiry' => '2030-01-01',
        ]);

        Ambulance::create([
            'plate_number' => 'ABC-123',
            'vehicle_name' => 'Ambulance One',
            'vehicle_type' => 'ambulance',
            'status' => 'available',
        ]);

        Incident::create([
            'incident_number' => 'INC-0001',
            'reporter_name' => 'Jane Doe',
            'contact_number' => '09120000001',
            'incident_type' => 'Medical',
            'location' => 'Main Street',
            'description' => 'Sample incident',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/superadmin/dashboard');

        $response->assertOk();
        $response->assertSee('Total Incidents');
        $response->assertSee('Awaiting response');
        $response->assertSee('Recent Incidents');
    }

    public function test_driver_registration_assigns_the_driver_role(): void
    {
        Role::query()->delete();

        $response = $this->post(route('driver.register.store'), [
            'name' => 'New Driver',
            'email' => 'new-driver@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'contact_number' => '09123456789',
            'license_number' => 'LIC-999',
            'license_expiry' => '2030-01-01',
        ]);

        $response->assertSessionHas('success');

        $user = User::where('email', 'new-driver@example.com')->firstOrFail();

        $this->assertTrue($user->fresh()->hasRole('driver'));
        $this->assertNotNull($user->driver);
    }

    public function test_driver_dashboard_shows_assigned_incidents(): void
    {
        $role = Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-002',
            'contact_number' => '09123456790',
            'license_number' => 'LIC-002',
            'license_expiry' => '2030-01-01',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-0002',
            'reporter_name' => 'John Doe',
            'contact_number' => '09120000002',
            'incident_type' => 'Fire',
            'location' => 'Central Avenue',
            'description' => 'Sample incident',
            'status' => 'dispatched',
            'driver_id' => $driver->id,
        ]);

        $response = $this->actingAs($user)->get('/driver/dashboard');

        $response->assertOk();
        $response->assertSee('Assigned Incidents');
        $response->assertSee($incident->incident_number);
    }

    public function test_driver_dashboard_uses_active_vehicle_assignment_when_no_dispatch_exists(): void
    {
        $role = Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-003',
            'contact_number' => '09123456791',
            'license_number' => 'LIC-003',
            'license_expiry' => '2030-01-01',
            'status' => 'available',
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'FIRE-001',
            'vehicle_name' => 'Firetruck 01',
            'vehicle_type' => 'fire_truck',
            'status' => 'available',
        ]);

        VehicleDriverAssignment::create([
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
            'status' => 'active',
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/driver/dashboard');

        $response->assertOk();
        $response->assertSee('Firetruck 01');
        $response->assertSee('FIRE-001');
        $response->assertDontSee('Not Assigned');
    }

    public function test_driver_gps_update_requires_valid_coordinates(): void
    {
        $role = Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-004',
            'contact_number' => '09123456792',
            'license_number' => 'LIC-004',
            'license_expiry' => '2030-01-01',
            'status' => 'available',
        ]);

        $response = $this->actingAs($user)
            ->json('POST', '/driver/gps/update', [
                'latitude' => 'latitude',
                'longitude' => 'longitude',
            ]);

        $response->assertStatus(422);
    }
}
