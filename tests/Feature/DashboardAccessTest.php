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

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Driver account created successfully. You can now log in.');

        $user = User::where('email', 'new-driver@example.com')->firstOrFail();

        $this->assertTrue($user->fresh()->hasRole('driver'));
        $this->assertNotNull($user->driver);
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Driver account created successfully. You can now log in.');
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

    public function test_superadmin_created_driver_account_can_sign_in_without_approval(): void
    {
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        $superAdmin = User::factory()->create(['status' => 'approved']);
        $superAdmin->assignRole('super-admin');

        $response = $this->actingAs($superAdmin)->post(route('superadmin.drivers.store'), [
            'name' => 'New Driver',
            'email' => 'new-driver-admin-created@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'contact_number' => '09123456793',
        ]);

        $response->assertRedirect(route('superadmin.drivers'));

        $user = User::where('email', 'new-driver-admin-created@example.com')->firstOrFail();
        $this->assertSame('approved', $user->status);
        $this->assertNull($user->driver->license_number);
        $this->assertNull($user->driver->license_expiry);

        $this->post('/logout');

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('driver.dashboard'));

        $this->get(route('driver.dashboard'))->assertOk();
    }

    public function test_driver_registration_does_not_require_license_details(): void
    {
        $response = $this->post(route('driver.register.store'), [
            'name' => 'No License Driver',
            'email' => 'no-license-driver@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'contact_number' => '09123456794',
        ]);

        $response->assertSessionHasNoErrors();
        $user = User::where('email', 'no-license-driver@example.com')->firstOrFail();

        $this->assertSame('approved', $user->status);
        $this->assertNull($user->driver->license_number);
        $this->assertNull($user->driver->license_expiry);
    }

    public function test_new_admin_can_sign_in_without_account_approval(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->post(route('admin.register.store'), [
            'name' => 'New Admin',
            'email' => 'new-public-admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Admin account created successfully. You can now log in.');

        $user = User::where('email', 'new-public-admin@example.com')->firstOrFail();
        $this->assertSame('approved', $user->status);
        $this->assertTrue($user->hasRole('admin'));

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_super_admin_can_sign_in_without_account_approval(): void
    {
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        $superAdmin = User::factory()->create([
            'email' => 'superadmin-login@example.com',
            'password' => bcrypt('password123'),
            'status' => 'pending',
        ]);
        $superAdmin->assignRole('super-admin');

        $this->post('/login', [
            'email' => $superAdmin->email,
            'password' => 'password123',
        ])->assertRedirect(route('superadmin.dashboard'));

        $this->get(route('superadmin.dashboard'))->assertOk();
    }

    public function test_superadmin_registration_controller_creates_admin_account_without_approval(): void
    {
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $superAdmin = User::factory()->create(['status' => 'approved']);
        $superAdmin->assignRole('super-admin');

        $request = new \Illuminate\Http\Request([
            'name' => 'New Admin',
            'email' => 'new-admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $controller = new \App\Http\Controllers\SuperAdmin\AdminRegistrationController();
        $response = $controller->store($request);

        $user = User::where('email', 'new-admin@example.com')->firstOrFail();
        $this->assertSame('approved', $user->status);
        $this->assertTrue($user->hasRole('admin'));
        $this->assertSame('Administrator account created successfully.', $response->getSession()->get('success'));
    }
}
