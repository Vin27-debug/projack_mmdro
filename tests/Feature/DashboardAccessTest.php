<?php

namespace Tests\Feature;

use App\Models\Ambulance;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\User;
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
        $response->assertSee('Pending Incidents');
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
}
