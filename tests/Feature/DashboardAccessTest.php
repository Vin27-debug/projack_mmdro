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
        $response->assertSee('Command Center');
        $response->assertSee('System Overview');
        $response->assertDontSee('Priority Incident Queue');
        $response->assertSee(route('superadmin.incidents.index'), false);
        $response->assertSee('<title>MuniResQ Super Admin</title>', false);
        $response->assertDontSee(route('admin.incidents.create'), false);
        $response->assertDontSee('Create Incident');
        $response->assertSee('View Incidents');

        preg_match('/<a href="(' . preg_quote(route('superadmin.incidents.index'), '/') . ')"[^>]*>[\s\S]*?View all/s', $response->getContent(), $viewAllLink);
        $this->assertSame(route('superadmin.incidents.index'), html_entity_decode($viewAllLink[1] ?? ''));

        $incident = Incident::where('incident_number', 'INC-0001')->firstOrFail();
        $response->assertSee('#' . $incident->id);
        $response->assertSee(route('superadmin.incidents.show', $incident), false);
        $response->assertSee('View details');
        $response->assertSee('Main Street');
        $response->assertSee('Medical');
        $response->assertSee('PENDING');
        $response->assertSee('Elapsed');
        preg_match('/<div class="sa-section-heading sa-recent-heading">[\s\S]*?<\/table>/', $response->getContent(), $recentSection);
        $this->assertNotSame('', $recentSection[0] ?? '');
        $this->assertDoesNotMatchRegularExpression('/Create Incident|Edit|Delete|Archive|Restore|Dispatch Response/', $recentSection[0]);

        $this->get(route('superadmin.incidents.index'))
            ->assertOk()
            ->assertSee('Incident Command')
            ->assertSee('<title>MuniResQ Super Admin</title>', false)
            ->assertSee('INC-0001')
            ->assertSee(route('superadmin.incidents.show', $incident), false)
            ->assertDontSee('Create Incident')
            ->assertDontSee(route('admin.incidents.archive', $incident), false);

        $superAdminDetail = $this->get(route('superadmin.incidents.show', $incident));
        $superAdminDetail
            ->assertOk()
            ->assertSee('Incident Details')
            ->assertSee('VIEW ONLY')
            ->assertSee('Back to Incidents')
            ->assertSee(route('superadmin.incidents.index'), false)
            ->assertDontSee('Create Incident')
            ->assertDontSee(route('admin.incidents.archive', $incident), false)
            ->assertDontSee(route('admin.incidents.create'), false)
            ->assertDontSee('Edit Time Record')
            ->assertDontSee('Dispatch Response')
            ->assertSee('<title>MuniResQ Super Admin</title>', false)
            ->assertSee('INC-0001');
        $this->assertDoesNotMatchRegularExpression('/<button[^>]*>[\s\S]*?\bArchive\b[\s\S]*?<\/button>/i', $superAdminDetail->getContent());
    }

    public function test_admin_can_access_the_admin_incidents_listing(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.incidents.index'))
            ->assertOk()
            ->assertSee('Incident Command')
            ->assertSee('Create Incident')
            ->assertSee('<title>MuniResQ Admin Panel</title>', false);

        $incident = Incident::create([
            'incident_number' => 'INC-ADMIN-DETAIL',
            'reporter_name' => 'Admin Test',
            'contact_number' => '09120000000',
            'incident_type' => 'Medical',
            'location' => 'Admin test location',
            'description' => 'Existing incident record',
            'status' => 'pending',
            'priority' => 'High',
        ]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.incidents.index'), false)
            ->assertSee(route('admin.incidents.show', $incident), false)
            ->assertSee('View details');
        $this->get(route('admin.incidents.show', $incident))
            ->assertOk()
            ->assertSee('<title>MuniResQ Admin Panel</title>', false)
            ->assertSee('Back to Incidents')
            ->assertSee(route('admin.incidents.index'), false)
            ->assertSee('Edit Time Record')
            ->assertSee('Archive')
            ->assertSee(route('admin.incidents.archive', $incident), false)
            ->assertSee('INC-ADMIN-DETAIL');
        $this->get(route('admin.incidents.create'))
            ->assertOk()
            ->assertSee('Create Incident');
        $this->get(route('superadmin.dashboard'))->assertForbidden();
    }

    public function test_driver_cannot_access_admin_or_superadmin_incidents_listings(): void
    {
        Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'web']);
        $driver = User::factory()->create(['status' => 'approved']);
        $driver->assignRole('driver');

        $this->actingAs($driver)->get(route('admin.incidents.index'))->assertForbidden();
        $this->actingAs($driver)->get(route('superadmin.incidents.index'))->assertForbidden();
        $incident = Incident::create([
            'incident_number' => 'INC-DRIVER-DENIED',
            'reporter_name' => 'Driver Test',
            'incident_type' => 'Medical',
            'location' => 'Test location',
            'description' => 'Driver should not view this record',
            'status' => 'pending',
        ]);
        $this->actingAs($driver)->get(route('superadmin.incidents.show', $incident))->assertForbidden();
        $this->actingAs($driver)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($driver)->get(route('superadmin.dashboard'))->assertForbidden();
    }

    public function test_dashboard_active_and_priority_counts_follow_incident_status_rules(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole('admin');

        foreach ([
            ['INC-ACTIVE', 'pending', 'Critical', null],
            ['INC-COMPLETE', 'completed', 'High', null],
            ['INC-CLOSED', 'closed', 'High', null],
            ['INC-CANCELLED', 'cancelled', 'Critical', null],
            ['INC-ARCHIVED', 'pending', 'High', now()],
        ] as [$number, $status, $priority, $archivedAt]) {
            Incident::create([
                'incident_number' => $number,
                'reporter_name' => 'Dashboard Test',
                'contact_number' => '09120000000',
                'incident_type' => 'Medical',
                'location' => 'Test location',
                'description' => 'Dashboard count test',
                'status' => $status,
                'priority' => $priority,
                'archived_at' => $archivedAt,
            ]);
        }

        $response = $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('data-counter="activeIncidents">1', false)
            ->assertSee('data-counter="priorityIncidents">1', false)
            ->assertSee('High / critical incidents')
            ->assertSee('Open priority cases')
            ->assertSee('data-status-count="pending"', false)
            ->assertSee('data-status-count="completed"', false)
            ->assertSee('data-status-count="closed"', false)
            ->assertSee('data-status-count="cancelled"', false)
            ->assertSee('Elapsed')
            ->assertSee('INC-ACTIVE')
            ->assertDontSee('INC-ARCHIVED');
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
