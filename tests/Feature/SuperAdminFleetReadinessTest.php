<?php

namespace Tests\Feature;

use App\Models\Ambulance;
use App\Models\AuditLog;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\Notification;
use App\Models\PanicAlert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperAdminFleetReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_fleet_counts_use_real_active_vehicle_statuses_without_double_counting(): void
    {
        $superAdmin = $this->createUserWithRole('super-admin');

        $this->createVehicle('FLEET-AVAILABLE', Ambulance::STATUS_AVAILABLE);
        $this->createVehicle('FLEET-ON-DUTY', Ambulance::STATUS_ON_DUTY);
        $this->createVehicle('FLEET-MAINTENANCE', Ambulance::STATUS_MAINTENANCE);
        $dispatchedAvailableVehicle = $this->createVehicle('FLEET-DISPATCHED', Ambulance::STATUS_AVAILABLE);
        $this->createVehicle('FLEET-ARCHIVED', Ambulance::STATUS_AVAILABLE, now());

        $driverUser = User::factory()->create(['status' => 'approved']);
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'FLEET-DRIVER',
            'contact_number' => '09123456789',
            'status' => Driver::STATUS_AVAILABLE,
        ]);
        $incident = Incident::create([
            'incident_number' => 'INC-FLEET-READINESS',
            'reporter_name' => 'Fleet readiness test',
            'incident_type' => 'Medical Emergency',
            'location' => 'Test location',
            'status' => Incident::STATUS_DISPATCHED,
        ]);
        Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $dispatchedAvailableVehicle->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($superAdmin)->get(route('superadmin.dashboard'));

        $response->assertOk()
            ->assertSee('data-fleet-total="4"', false)
            ->assertSee('data-fleet-available="1"', false)
            ->assertSee('data-fleet-responding="2"', false)
            ->assertSee('data-fleet-maintenance="1"', false);

        preg_match('/<a href="([^"]+)" class="btn btn-sm btn-outline-light">View fleet/s', $response->getContent(), $viewFleetLink);
        $this->assertSame(route('superadmin.ambulances.index'), html_entity_decode($viewFleetLink[1] ?? ''));

        $this->get(route('superadmin.ambulances.index'))
            ->assertOk()
            ->assertSee('Vehicle Fleet')
            ->assertSee('<title>MuniResQ Super Admin</title>', false);
    }

    public function test_empty_fleet_shows_a_clear_state_and_superadmin_fleet_link(): void
    {
        $superAdmin = $this->createUserWithRole('super-admin');

        $response = $this->actingAs($superAdmin)->get(route('superadmin.dashboard'));

        $response->assertOk()
            ->assertSee('No active vehicles are registered.')
            ->assertDontSee('0 / 0');

        preg_match('/<a href="([^"]+)" class="btn btn-sm btn-outline-light">View fleet/s', $response->getContent(), $viewFleetLink);
        $this->assertSame(route('superadmin.ambulances.index'), html_entity_decode($viewFleetLink[1] ?? ''));
    }

    public function test_archived_incident_dispatch_does_not_make_a_vehicle_responding(): void
    {
        $superAdmin = $this->createUserWithRole('super-admin');
        $vehicle = $this->createVehicle('FLEET-ARCHIVED-DISPATCH', Ambulance::STATUS_AVAILABLE);

        $driverUser = User::factory()->create(['status' => 'approved']);
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'FLEET-ARCHIVED-DISPATCH-DRIVER',
            'contact_number' => '09123456789',
            'status' => Driver::STATUS_AVAILABLE,
        ]);
        $incident = Incident::create([
            'incident_number' => 'INC-FLEET-ARCHIVED-DISPATCH',
            'reporter_name' => 'Archived fleet dispatch test',
            'incident_type' => 'Medical Emergency',
            'location' => 'Archived test location',
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

        $response = $this->actingAs($superAdmin)->get(route('superadmin.dashboard'));

        $response->assertOk()
            ->assertSee('data-fleet-total="1"', false)
            ->assertSee('data-fleet-available="0"', false)
            ->assertSee('data-fleet-responding="0"', false)
            ->assertSee('data-fleet-maintenance="0"', false);
    }

    public function test_superadmin_dashboard_renders_inherited_incident_alert_activity_and_notification_data(): void
    {
        $superAdmin = $this->createUserWithRole('super-admin');
        $incident = Incident::create([
            'incident_number' => 'INC-SA-DASHBOARD-001',
            'reporter_name' => 'Dashboard overview test',
            'incident_type' => 'Medical Emergency',
            'location' => 'Dashboard test location',
            'priority' => Incident::PRIORITY_HIGH,
            'status' => Incident::STATUS_PENDING,
        ]);
        $driverUser = User::factory()->create(['status' => 'approved']);
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'SA-DASH-DRIVER',
            'contact_number' => '09123456789',
            'license_number' => 'SA-DASH-LICENSE',
            'license_expiry' => '2030-01-01',
        ]);
        PanicAlert::create([
            'driver_id' => $driver->id,
            'latitude' => 15.49,
            'longitude' => 120.97,
            'triggered_at' => now(),
            'resolved' => false,
        ]);
        AuditLog::create([
            'user_id' => $superAdmin->id,
            'action' => 'Dashboard review event',
            'module' => 'Security Review',
            'description' => 'Recent administrative activity is available.',
        ]);
        Notification::create([
            'user_id' => $superAdmin->id,
            'title' => 'Dashboard notification',
            'message' => 'A current notification is visible to this Super Admin.',
            'type' => 'system',
            'is_read' => false,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('superadmin.dashboard'));

        $response->assertOk()
            ->assertSee($incident->incident_number)
            ->assertSee('Dashboard review event')
            ->assertSee('Recent Activity')
            ->assertSee('Recent Notifications')
            ->assertSee('Dashboard notification')
            ->assertSee('Active Emergency Alerts')
            ->assertSee($driverUser->name);
        $response->assertViewHas('stats', fn (array $stats): bool =>
            $stats['total_incidents'] === 1
            && $stats['pending_incidents'] === 1
        );
    }

    public function test_admin_keeps_its_existing_fleet_listing_and_driver_is_denied(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();
        preg_match('/<a href="([^"]+)" class="btn btn-sm btn-outline-light">View fleet/s', $response->getContent(), $viewFleetLink);
        $this->assertSame(route('admin.ambulances.index'), html_entity_decode($viewFleetLink[1] ?? ''));
        $this->get(route('admin.ambulances.index'))->assertOk();

        $driver = $this->createUserWithRole('driver');
        $this->actingAs($driver)->get(route('superadmin.dashboard'))->assertForbidden();
        $this->actingAs($driver)->get(route('superadmin.ambulances.index'))->assertForbidden();
        $this->actingAs($driver)->get(route('admin.ambulances.index'))->assertForbidden();
    }

    public function test_admin_and_superadmin_ambulance_forms_offer_only_supported_vehicle_types(): void
    {
        $supportedTypes = Ambulance::VALID_TYPES;
        $admin = $this->createUserWithRole('admin');
        $adminForm = $this->actingAs($admin)->get(route('admin.ambulances.create'))->assertOk();
        foreach ($supportedTypes as $type) {
            $adminForm->assertSee('value="' . $type . '"', false);
        }

        $superAdmin = $this->createUserWithRole('super-admin');
        $superAdminForm = $this->actingAs($superAdmin)->get(route('superadmin.ambulances.create'))->assertOk();
        foreach ($supportedTypes as $type) {
            $superAdminForm->assertSee('value="' . $type . '"', false);
        }

        $superAdminForm->assertDontSee('value="other"', false);
    }

    private function createUserWithRole(string $role): User
    {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        return $user;
    }

    private function createVehicle(string $plate, string $status, mixed $archivedAt = null): Ambulance
    {
        return Ambulance::create([
            'plate_number' => $plate,
            'vehicle_name' => $plate,
            'vehicle_type' => 'ambulance',
            'status' => $status,
            'archived_at' => $archivedAt,
        ]);
    }
}
