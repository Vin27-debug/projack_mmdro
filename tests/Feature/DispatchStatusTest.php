<?php

namespace Tests\Feature;

use App\Models\Ambulance;
use App\Models\AuditLog;
use App\Models\Dispatch;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\IncidentReport;
use App\Models\GpsLocation;
use App\Models\User;
use App\Services\DispatchRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DispatchStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_incident_dispatch_form(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['status' => 'pending']);
        $admin->assignRole($adminRole);

        $incident = Incident::create([
            'incident_number' => 'INC-DISPATCH-FORM',
            'reporter_name' => 'Dispatch Form Test',
            'incident_type' => 'Medical',
            'location' => 'Test Street',
            'description' => 'Dispatch form route test',
            'status' => Incident::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.incidents.dispatch.form', $incident))
            ->assertOk()
            ->assertSee('Dispatch Incident')
            ->assertSee(route('admin.incidents.dispatch', $incident));
    }

    public function test_fresh_driver_and_available_vehicle_can_be_dispatched_from_incident_page(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $driverRole = Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'web']);
        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole($adminRole);
        $driverUser = User::factory()->create(['status' => 'approved']);
        $driverUser->assignRole($driverRole);

        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'DISPATCH-FRESH',
            'contact_number' => '09123456789',
            'status' => Driver::STATUS_AVAILABLE,
            'management_status' => Driver::MANAGEMENT_STATUS_ACTIVE,
        ]);
        GpsLocation::create([
            'driver_id' => $driver->id,
            'latitude' => 14.5995,
            'longitude' => 120.9842,
            'recorded_at' => now()->subMinute(),
        ]);

        $vehicle = Ambulance::create([
            'plate_number' => 'DISPATCH-AVAILABLE',
            'vehicle_name' => 'Available Rescue Vehicle',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_AVAILABLE,
        ]);
        $incident = Incident::create([
            'incident_number' => 'INC-DISPATCH-ELIGIBLE',
            'reporter_name' => 'Dispatch Eligibility Test',
            'incident_type' => 'Medical Emergency',
            'location' => 'Test Street',
            'description' => 'Eligible resources should dispatch',
            'status' => Incident::STATUS_PENDING,
            'latitude' => 14.6000,
            'longitude' => 120.9845,
        ]);

        $page = $this->actingAs($admin)
            ->get(route('admin.incidents.dispatch.form', $incident))
            ->assertOk()
            ->assertSee('DISPATCH-FRESH')
            ->assertSee('Available Rescue Vehicle')
            ->assertSee('Distance unavailable');

        $this->assertMatchesRegularExpression(
            '/<button class="btn btn-primary"\\s*>\\s*Dispatch Incident\\s*<\\/button>/',
            $page->getContent()
        );

        $this->post(route('admin.incidents.dispatch', $incident), [
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
        ])->assertRedirect(route('admin.incidents.index'));

        $this->assertDatabaseHas('dispatches', [
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'status' => Dispatch::STATUS_ASSIGNED,
        ]);
    }

    public function test_stale_and_missing_gps_drivers_are_not_dispatch_eligible(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $driverRole = Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'web']);
        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole($adminRole);

        foreach (['STALE', 'NO-GPS'] as $badge) {
            $driverUser = User::factory()->create(['status' => 'approved']);
            $driverUser->assignRole($driverRole);
            $driver = Driver::create([
                'user_id' => $driverUser->id,
                'badge_id' => 'DISPATCH-' . $badge,
                'contact_number' => '09123456789',
                'status' => Driver::STATUS_AVAILABLE,
            ]);
            if ($badge === 'STALE') {
                GpsLocation::create([
                    'driver_id' => $driver->id,
                    'latitude' => 14.5995,
                    'longitude' => 120.9842,
                    'recorded_at' => now()->subMinutes(6),
                ]);
            }
        }

        $vehicle = Ambulance::create([
            'plate_number' => 'DISPATCH-GPS-TEST',
            'vehicle_name' => 'GPS Test Vehicle',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_AVAILABLE,
        ]);
        $incident = Incident::create([
            'incident_number' => 'INC-DISPATCH-GPS',
            'reporter_name' => 'GPS Eligibility Test',
            'incident_type' => 'Medical Emergency',
            'location' => 'Test Street',
            'status' => Incident::STATUS_PENDING,
            'latitude' => 14.6000,
            'longitude' => 120.9845,
        ]);

        $page = $this->actingAs($admin)
            ->get(route('admin.incidents.dispatch.form', $incident))
            ->assertOk()
            ->assertSee('No eligible drivers are currently within a fresh GPS window.')
            ->assertDontSee('DISPATCH-STALE')
            ->assertDontSee('DISPATCH-NO-GPS');

        $this->assertStringContainsString('disabled', $page->getContent());
    }

    public function test_unavailable_or_already_dispatched_vehicle_is_not_eligible(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $driverRole = Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'web']);
        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole($adminRole);
        $driverUser = User::factory()->create(['status' => 'approved']);
        $driverUser->assignRole($driverRole);
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'DISPATCH-VEHICLE-TEST',
            'contact_number' => '09123456789',
            'status' => Driver::STATUS_AVAILABLE,
        ]);
        GpsLocation::create([
            'driver_id' => $driver->id,
            'latitude' => 14.5995,
            'longitude' => 120.9842,
            'recorded_at' => now(),
        ]);

        $occupiedVehicle = Ambulance::create([
            'plate_number' => 'DISPATCH-OCCUPIED',
            'vehicle_name' => 'Already Dispatched Vehicle',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_AVAILABLE,
        ]);
        $occupiedDriver = Driver::create([
            'user_id' => User::factory()->create(['status' => 'approved'])->id,
            'badge_id' => 'OTHER-ACTIVE-DRIVER',
            'contact_number' => '09123456788',
        ]);
        $occupiedIncident = Incident::create([
            'incident_number' => 'INC-OTHER-ACTIVE',
            'reporter_name' => 'Other Dispatch',
            'incident_type' => 'Medical Emergency',
            'location' => 'Other Street',
            'status' => Incident::STATUS_DISPATCHED,
        ]);
        Dispatch::create([
            'incident_id' => $occupiedIncident->id,
            'driver_id' => $occupiedDriver->id,
            'vehicle_id' => $occupiedVehicle->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);

        $maintenanceVehicle = Ambulance::create([
            'plate_number' => 'DISPATCH-MAINTENANCE',
            'vehicle_name' => 'Maintenance Vehicle',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_MAINTENANCE,
        ]);
        $incident = Incident::create([
            'incident_number' => 'INC-VEHICLE-ELIGIBILITY',
            'reporter_name' => 'Vehicle Eligibility Test',
            'incident_type' => 'Medical Emergency',
            'location' => 'Test Street',
            'status' => Incident::STATUS_PENDING,
            'latitude' => 14.6000,
            'longitude' => 120.9845,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.incidents.dispatch.form', $incident))
            ->assertOk()
            ->assertSee('No eligible vehicles are available for dispatch.')
            ->assertDontSee('Already Dispatched Vehicle')
            ->assertDontSee('Maintenance Vehicle')
            ->assertSee('No available vehicles; driver can choose one later');

        $page = $this->get(route('admin.incidents.dispatch.form', $incident));
        $this->assertMatchesRegularExpression(
            '/<button class="btn btn-primary"\\s*>\\s*Dispatch Incident\\s*<\\/button>/',
            $page->getContent()
        );

        $this->from(route('admin.incidents.dispatch.form', $incident))
            ->post(route('admin.incidents.dispatch', $incident), [
                'driver_id' => $driver->id,
                'vehicle_id' => $occupiedVehicle->id,
            ])
            ->assertSessionHas('error', 'This vehicle is not active or currently available.');
    }

    public function test_dispatch_without_incident_coordinates_is_safe_and_keeps_eligible_resources(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $driverRole = Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'web']);
        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole($adminRole);
        $driverUser = User::factory()->create(['status' => 'approved']);
        $driverUser->assignRole($driverRole);
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'DISPATCH-NO-INCIDENT-GPS',
            'contact_number' => '09123456789',
            'status' => Driver::STATUS_AVAILABLE,
        ]);
        GpsLocation::create([
            'driver_id' => $driver->id,
            'latitude' => 14.5995,
            'longitude' => 120.9842,
            'recorded_at' => now(),
        ]);
        $vehicle = Ambulance::create([
            'plate_number' => 'DISPATCH-NO-INCIDENT-COORDS',
            'vehicle_name' => 'No Incident Coordinates Vehicle',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_AVAILABLE,
        ]);
        $incident = Incident::create([
            'incident_number' => 'INC-NO-COORDINATES',
            'reporter_name' => 'No Coordinates Test',
            'incident_type' => 'Medical Emergency',
            'location' => 'Ungeocoded Location',
            'status' => Incident::STATUS_PENDING,
        ]);

        $page = $this->actingAs($admin)
            ->get(route('admin.incidents.dispatch.form', $incident))
            ->assertOk()
            ->assertSee('Incident coordinates are unavailable')
            ->assertSee('DISPATCH-NO-INCIDENT-GPS')
            ->assertSee('No Incident Coordinates Vehicle');
        $this->assertMatchesRegularExpression(
            '/<button class="btn btn-primary"\\s*>\\s*Dispatch Incident\\s*<\\/button>/',
            $page->getContent()
        );

        $this->post(route('admin.incidents.dispatch', $incident), [
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
        ])->assertRedirect(route('admin.incidents.index'));
    }

    public function test_dispatch_routes_remain_restricted_to_admin_roles(): void
    {
        $user = User::factory()->create(['status' => 'approved']);
        $incident = Incident::create([
            'incident_number' => 'INC-UNAUTHORIZED-DISPATCH',
            'reporter_name' => 'Unauthorized Test',
            'incident_type' => 'Medical Emergency',
            'location' => 'Test Street',
            'status' => Incident::STATUS_PENDING,
        ]);

        $this->actingAs($user)
            ->get(route('admin.incidents.dispatch.form', $incident))
            ->assertForbidden();
    }

    public function test_driver_can_select_vehicle_reserved_by_their_assigned_dispatch(): void
    {
        $driverRole = Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'web']);
        $driverUser = User::factory()->create(['status' => 'approved']);
        $driverUser->assignRole($driverRole);
        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'DRIVER-RESERVED-VEHICLE',
            'contact_number' => '09123456789',
            'status' => Driver::STATUS_AVAILABLE,
        ]);
        $vehicle = Ambulance::create([
            'plate_number' => 'RESERVED-VEHICLE',
            'vehicle_name' => 'Reserved for Assigned Driver',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_ON_DUTY,
        ]);
        $incident = Incident::create([
            'incident_number' => 'INC-RESERVED-VEHICLE',
            'reporter_name' => 'Reserved Vehicle Test',
            'incident_type' => 'Medical Emergency',
            'location' => 'Test Street',
            'status' => Incident::STATUS_DISPATCHED,
            'driver_id' => $driver->id,
            'ambulance_id' => $vehicle->id,
        ]);
        Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);

        $this->actingAs($driverUser)
            ->get(route('driver.dashboard'))
            ->assertOk()
            ->assertSee('Reserved for Assigned Driver')
            ->assertDontSee('No available vehicle at this time.');
    }

    public function test_admin_can_assign_a_dispatch_with_a_supported_status(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-100',
            'contact_number' => '09123456789',
            'license_number' => 'LIC-100',
            'license_expiry' => '2030-01-01',
        ]);
        GpsLocation::create([
            'driver_id' => $driver->id,
            'latitude' => 14.5995,
            'longitude' => 120.9842,
            'recorded_at' => now(),
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'ABC-100',
            'vehicle_name' => 'Ambulance One',
            'vehicle_type' => 'ambulance',
            'status' => 'available',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-0100',
            'reporter_name' => 'Jane Doe',
            'contact_number' => '09120000000',
            'incident_type' => 'Medical',
            'location' => 'Test Street',
            'description' => 'Sample incident',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->post(route('admin.dispatches.assign', $incident), [
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('dispatches', [
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
            'status' => Dispatch::STATUS_ASSIGNED,
        ]);

        $this->assertDatabaseHas('incidents', [
            'id' => $incident->id,
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
            'status' => 'dispatched',
        ]);

        $dispatch = Dispatch::where('incident_id', $incident->id)->firstOrFail();
        $this->assertDispatchAudit($dispatch, $user, 'dispatch_assigned');

        $this->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertSee('Dispatch')
            ->assertSee('dispatch_assigned');

        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin = User::factory()->create(['status' => 'approved']);
        $superAdmin->assignRole($superAdminRole);
        $this->actingAs($superAdmin)
            ->get(route('admin.audit-logs.index'))
            ->assertOk()
            ->assertSee('Dispatch')
            ->assertSee('dispatch_assigned');
    }

    public function test_dispatch_recommendation_filters_out_stale_or_missing_gps_and_ranks_fresh_resources(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        $incident = Incident::create([
            'incident_number' => 'INC-NEW-RANK',
            'reporter_name' => 'Rank Test',
            'contact_number' => '09120000010',
            'incident_type' => 'Medical',
            'location' => 'Rank Avenue',
            'description' => 'Ranking test',
            'status' => 'pending',
            'latitude' => 14.5995,
            'longitude' => 120.9842,
        ]);

        $freshDriver = Driver::create([
            'user_id' => User::factory()->create(['status' => 'approved'])->id,
            'badge_id' => 'AMB-210',
            'contact_number' => '09123456781',
            'license_number' => 'LIC-210',
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_AVAILABLE,
        ]);
        GpsLocation::create([
            'driver_id' => $freshDriver->id,
            'latitude' => 14.5998,
            'longitude' => 120.9845,
            'recorded_at' => now()->subMinute(),
        ]);

        $staleDriver = Driver::create([
            'user_id' => User::factory()->create(['status' => 'approved'])->id,
            'badge_id' => 'AMB-211',
            'contact_number' => '09123456782',
            'license_number' => 'LIC-211',
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_AVAILABLE,
        ]);
        GpsLocation::create([
            'driver_id' => $staleDriver->id,
            'latitude' => 14.6000,
            'longitude' => 120.9849,
            'recorded_at' => now()->subMinutes(15),
        ]);

        $noGpsDriver = Driver::create([
            'user_id' => User::factory()->create(['status' => 'approved'])->id,
            'badge_id' => 'AMB-212',
            'contact_number' => '09123456783',
            'license_number' => 'LIC-212',
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_AVAILABLE,
        ]);

        $recommendedVehicle = Ambulance::create([
            'plate_number' => 'ABC-210',
            'vehicle_name' => 'Priority One',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_AVAILABLE,
            'latitude' => 14.6030,
            'longitude' => 120.9850,
        ]);

        $secondVehicle = Ambulance::create([
            'plate_number' => 'ABC-211',
            'vehicle_name' => 'Priority Two',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_AVAILABLE,
            'latitude' => 14.6100,
            'longitude' => 120.9900,
        ]);

        $recommendation = app(DispatchRecommendationService::class)->recommend(
            $incident,
            [$freshDriver, $staleDriver, $noGpsDriver],
            [$recommendedVehicle, $secondVehicle]
        );

        $this->assertSame($freshDriver->id, $recommendation['nearestDriver']->id);
        $this->assertSame(1, count($recommendation['eligibleDrivers']));
        $this->assertSame($recommendedVehicle->id, $recommendation['nearestAmbulance']->id);
        $this->assertSame(2, count($recommendation['eligibleVehicles']));
        $this->assertSame([$freshDriver->id], array_map(fn($driver) => $driver->id, $recommendation['rankedDrivers']));
        $this->assertSame([$recommendedVehicle->id, $secondVehicle->id], array_map(fn($vehicle) => $vehicle->id, $recommendation['rankedVehicles']));
    }

    public function test_driver_dashboard_shows_accept_and_decline_actions_for_assigned_dispatches(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        $role = Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-104',
            'contact_number' => '09123456793',
            'license_number' => 'LIC-104',
            'license_expiry' => '2030-01-01',
            'status' => 'available',
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'ABC-104',
            'vehicle_name' => 'Ambulance Five',
            'vehicle_type' => 'ambulance',
            'status' => 'on_duty',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-0104',
            'reporter_name' => 'Carol Doe',
            'contact_number' => '09120000004',
            'incident_type' => 'Medical',
            'location' => 'Test Plaza',
            'description' => 'Sample incident',
            'status' => 'dispatched',
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
        ]);

        Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('driver.dashboard'));

        $response->assertOk();
        $response->assertSee('Accept Dispatch');
        $response->assertSee('Decline Dispatch');
        $response->assertDontSee('Mark En Route');
    }

    public function test_driver_dashboard_shows_next_emergency_action_in_sequence(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        $role = Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-105',
            'contact_number' => '09123456794',
            'license_number' => 'LIC-105',
            'license_expiry' => '2030-01-01',
            'status' => 'en_route',
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'ABC-105',
            'vehicle_name' => 'Ambulance Six',
            'vehicle_type' => 'ambulance',
            'status' => 'on_duty',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-0105',
            'reporter_name' => 'Dana Doe',
            'contact_number' => '09120000005',
            'incident_type' => 'Medical',
            'location' => 'Test Avenue',
            'description' => 'Sample incident',
            'status' => Incident::STATUS_DISPATCHED,
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
            'call_received_at' => now()->subMinutes(20),
            'response_at' => now()->subMinutes(14),
            'at_scene_at' => now()->subMinutes(7),
        ]);

        Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
            'status' => Dispatch::STATUS_EN_ROUTE,
            'accepted_at' => now()->subMinutes(15),
            'en_route_at' => now()->subMinutes(14),
        ]);

        $response = $this->actingAs($user)->get(route('driver.dashboard'));

        $response->assertOk();
        $response->assertSee('Mark At Patient');
        $response->assertDontSee('Mark At Scene');
    }

    public function test_driver_return_to_base_cycle_keeps_vehicle_busy_until_ready_for_next_mission(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        $role = Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-107',
            'contact_number' => '09123456796',
            'license_number' => 'LIC-107',
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_ON_SCENE,
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'ABC-107',
            'vehicle_name' => 'Return Base Vehicle',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_ON_DUTY,
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-0107',
            'reporter_name' => 'Eva Doe',
            'contact_number' => '09120000007',
            'incident_type' => 'Medical',
            'location' => 'Test Highway',
            'description' => 'Return to base workflow',
            'status' => Incident::STATUS_RESPONDING,
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
            'at_hospital_at' => now()->subMinutes(5),
        ]);

        $dispatch = Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
            'status' => Dispatch::STATUS_ARRIVED,
            'assigned_at' => now()->subMinutes(30),
            'arrived_at' => now()->subMinutes(10),
        ]);

        $firstResponse = $this->actingAs($user)->post(route('driver.incidents.completed', $incident));
        $firstResponse->assertSessionHas('success');
        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'status' => Driver::STATUS_RETURNING,
        ]);
        $this->assertDatabaseHas('ambulances', [
            'id' => $ambulance->id,
            'status' => Ambulance::STATUS_ON_DUTY,
        ]);

        $secondResponse = $this->actingAs($user)->post(route('driver.incidents.ready', $incident));
        $secondResponse->assertSessionHas('success');
        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'status' => Driver::STATUS_AVAILABLE,
        ]);
        $this->assertDatabaseHas('ambulances', [
            'id' => $ambulance->id,
            'status' => Ambulance::STATUS_AVAILABLE,
        ]);
    }

    public function test_driver_gps_update_syncs_ambulance_coordinates_via_active_dispatch(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        $role = Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-102',
            'contact_number' => '09123456791',
            'license_number' => 'LIC-102',
            'license_expiry' => '2030-01-01',
            'status' => 'available',
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'ABC-102',
            'vehicle_name' => 'Ambulance Three',
            'vehicle_type' => 'ambulance',
            'status' => 'on_duty',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-0102',
            'reporter_name' => 'Alice Doe',
            'contact_number' => '09120000002',
            'incident_type' => 'Medical',
            'location' => 'Test Road',
            'description' => 'Sample incident',
            'status' => 'dispatched',
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
        ]);

        Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('driver.gps.update'), [
            'latitude' => '15.421484',
            'longitude' => '120.842789',
        ]);

        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('ambulances', [
            'id' => $ambulance->id,
            'latitude' => '15.421484',
            'longitude' => '120.842789',
        ]);
    }

    public function test_driver_decline_dispatch_returns_the_incident_to_pending(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        $role = Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-103',
            'contact_number' => '09123456792',
            'license_number' => 'LIC-103',
            'license_expiry' => '2030-01-01',
            'status' => 'available',
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'ABC-103',
            'vehicle_name' => 'Ambulance Four',
            'vehicle_type' => 'ambulance',
            'status' => 'on_duty',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-0103',
            'reporter_name' => 'Bob Doe',
            'contact_number' => '09120000003',
            'incident_type' => 'Medical',
            'location' => 'Test Street',
            'description' => 'Sample incident',
            'status' => 'dispatched',
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
        ]);

        $dispatch = Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('driver.dispatch.decline', $dispatch));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('dispatches', [
            'id' => $dispatch->id,
            'status' => Dispatch::STATUS_CANCELLED,
        ]);
        $this->assertDatabaseHas('incidents', [
            'id' => $incident->id,
            'status' => Incident::STATUS_PENDING,
        ]);
        $this->assertDispatchAudit($dispatch->fresh(), $user, 'dispatch_declined', Dispatch::STATUS_ASSIGNED, Dispatch::STATUS_CANCELLED);
    }

    public function test_driver_can_switch_to_an_available_vehicle_when_accepting(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole('driver');
        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-106',
            'contact_number' => '09123456795',
            'license_number' => 'LIC-106',
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_ASSIGNED,
        ]);

        $oldVehicle = Ambulance::create([
            'plate_number' => 'ABC-106',
            'vehicle_name' => 'Old Vehicle',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_ON_DUTY,
        ]);
        $newVehicle = Ambulance::create([
            'plate_number' => 'ABC-107',
            'vehicle_name' => 'Available Vehicle',
            'vehicle_type' => 'rescue_van',
            'status' => Ambulance::STATUS_AVAILABLE,
        ]);
        $incident = Incident::create([
            'incident_number' => 'INC-0106',
            'reporter_name' => 'Driver Test',
            'contact_number' => '09120000006',
            'incident_type' => 'Medical',
            'location' => 'Test Location',
            'description' => 'Switch vehicle',
            'status' => Incident::STATUS_DISPATCHED,
            'driver_id' => $driver->id,
            'ambulance_id' => $oldVehicle->id,
        ]);
        $dispatch = Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $oldVehicle->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('driver.dispatch.accept', $dispatch), [
            'vehicle_id' => $newVehicle->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('dispatches', [
            'id' => $dispatch->id,
            'vehicle_id' => $newVehicle->id,
            'status' => Dispatch::STATUS_EN_ROUTE,
        ]);
        $this->assertNotNull($dispatch->fresh()->en_route_at);
        $this->assertDatabaseHas('ambulances', [
            'id' => $newVehicle->id,
            'status' => Ambulance::STATUS_ON_DUTY,
        ]);
        $this->assertDatabaseHas('ambulances', [
            'id' => $oldVehicle->id,
            'status' => Ambulance::STATUS_AVAILABLE,
        ]);
        $this->assertDispatchAudit($dispatch->fresh(), $user, 'dispatch_accepted', Dispatch::STATUS_ASSIGNED, Dispatch::STATUS_EN_ROUTE);
    }

    public function test_driver_cannot_accept_with_a_vehicle_that_became_busy(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole('driver');
        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-108',
            'contact_number' => '09123456796',
            'license_number' => 'LIC-108',
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_ASSIGNED,
        ]);
        $vehicle = Ambulance::create([
            'plate_number' => 'ABC-108',
            'vehicle_name' => 'Busy Vehicle',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_ON_DUTY,
        ]);
        $otherDriver = Driver::create([
            'user_id' => User::factory()->create(['status' => 'approved'])->id,
            'badge_id' => 'AMB-109',
            'contact_number' => '09123456797',
            'license_number' => 'LIC-109',
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_ASSIGNED,
        ]);
        $incident = Incident::create([
            'incident_number' => 'INC-0108',
            'reporter_name' => 'Driver Test',
            'contact_number' => '09120000008',
            'incident_type' => 'Medical',
            'location' => 'Test Location',
            'description' => 'Busy vehicle',
            'status' => Incident::STATUS_DISPATCHED,
            'driver_id' => $driver->id,
        ]);
        $otherIncident = Incident::create([
            'incident_number' => 'INC-0109',
            'reporter_name' => 'Other Driver',
            'contact_number' => '09120000009',
            'incident_type' => 'Medical',
            'location' => 'Test Location',
            'description' => 'Busy vehicle owner',
            'status' => Incident::STATUS_DISPATCHED,
            'driver_id' => $otherDriver->id,
        ]);
        $dispatch = Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);
        Dispatch::create([
            'incident_id' => $otherIncident->id,
            'driver_id' => $otherDriver->id,
            'vehicle_id' => $vehicle->id,
            'status' => Dispatch::STATUS_ACCEPTED,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('driver.dispatch.accept', $dispatch), [
            'vehicle_id' => $vehicle->id,
        ]);

        $response->assertSessionHas('error', 'This vehicle is no longer available. Please select another vehicle.');
        $this->assertDatabaseHas('dispatches', [
            'id' => $dispatch->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'vehicle_id' => null,
        ]);
    }

    public function test_admin_can_dispatch_a_driver_without_forcing_a_vehicle_choice(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-110',
            'contact_number' => '09123456798',
            'license_number' => 'LIC-110',
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_AVAILABLE,
        ]);
        GpsLocation::create([
            'driver_id' => $driver->id,
            'latitude' => 14.5995,
            'longitude' => 120.9842,
            'recorded_at' => now(),
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-0110',
            'reporter_name' => 'Vehicle Delay Test',
            'contact_number' => '09120000010',
            'incident_type' => 'Medical',
            'location' => 'Test Location',
            'description' => 'Driver chooses vehicle later',
            'status' => Incident::STATUS_PENDING,
        ]);

        $response = $this->actingAs($user)->post(route('admin.incidents.dispatch', $incident), [
            'driver_id' => $driver->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('dispatches', [
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => null,
            'status' => Dispatch::STATUS_ASSIGNED,
        ]);
    }

    public function test_driver_accept_dispatch_creates_notification_with_selected_vehicle_details(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole('driver');

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-111',
            'contact_number' => '09123456799',
            'license_number' => 'LIC-111',
            'license_expiry' => '2030-01-01',
            'status' => Driver::STATUS_AVAILABLE,
        ]);

        $vehicle = Ambulance::create([
            'plate_number' => 'ABC-111',
            'vehicle_name' => 'Selected Rescue',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_AVAILABLE,
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-0111',
            'reporter_name' => 'Notification Driver',
            'contact_number' => '09120000011',
            'incident_type' => 'Medical',
            'location' => 'Test Road',
            'description' => 'Vehicle selected by driver',
            'status' => Incident::STATUS_DISPATCHED,
            'driver_id' => $driver->id,
        ]);

        $dispatch = Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('driver.dispatch.accept', $dispatch), [
            'vehicle_id' => $vehicle->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('notifications', [
            'type' => 'dispatch',
            'is_read' => false,
        ]);

        $notification = \App\Models\Notification::query()->latest()->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString('INC-0111', $notification->message);
        $this->assertStringContainsString('Selected Rescue', $notification->message);
    }

    public function test_gps_inside_incident_radius_automatically_marks_at_scene(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        [$user, $driver, $incident, $dispatch] = $this->createGeofencedDispatch([
            'latitude' => 15.0000000,
            'longitude' => 120.0000000,
        ]);

        $response = $this->actingAs($user)->json('POST', route('driver.gps.update'), [
            'latitude' => 15.0005000,
            'longitude' => 120.0000000,
            'accuracy' => 5,
        ]);

        $response->assertOk();
        $this->assertNotNull($incident->fresh()->at_scene_at);
        $this->assertDatabaseHas('dispatches', [
            'id' => $dispatch->id,
            'status' => Dispatch::STATUS_ARRIVED,
        ]);
        $this->assertSame(Driver::STATUS_ON_SCENE, $driver->fresh()->status);
    }

    public function test_gps_outside_incident_radius_does_not_mark_at_scene(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        [$user,, $incident, $dispatch] = $this->createGeofencedDispatch([
            'latitude' => 15.0000000,
            'longitude' => 120.0000000,
        ]);

        $response = $this->actingAs($user)->json('POST', route('driver.gps.update'), [
            'latitude' => 15.0030000,
            'longitude' => 120.0000000,
            'accuracy' => 5,
        ]);

        $response->assertOk();
        $this->assertNull($incident->fresh()->at_scene_at);
        $this->assertSame(Dispatch::STATUS_EN_ROUTE, $dispatch->fresh()->status);
    }

    public function test_gps_does_not_mark_at_scene_without_incident_coordinates(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        [$user,, $incident, $dispatch] = $this->createGeofencedDispatch([
            'latitude' => null,
            'longitude' => null,
        ]);

        $response = $this->actingAs($user)->json('POST', route('driver.gps.update'), [
            'latitude' => 15.0000000,
            'longitude' => 120.0000000,
            'accuracy' => 5,
        ]);

        $response->assertOk();
        $this->assertNull($incident->fresh()->at_scene_at);
        $this->assertSame(Dispatch::STATUS_EN_ROUTE, $dispatch->fresh()->status);
    }

    public function test_poor_or_stale_gps_does_not_trigger_geofence_transitions(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        [$user,, $incident, $dispatch] = $this->createGeofencedDispatch([
            'latitude' => 15.0000000,
            'longitude' => 120.0000000,
        ]);

        $poorAccuracy = $this->actingAs($user)->json('POST', route('driver.gps.update'), [
            'latitude' => 15.0005000,
            'longitude' => 120.0000000,
            'accuracy' => 100,
        ]);
        $poorAccuracy->assertOk();

        $stale = $this->actingAs($user)->json('POST', route('driver.gps.update'), [
            'latitude' => 15.0005000,
            'longitude' => 120.0000000,
            'accuracy' => 5,
            'recorded_at' => now()->subMinutes(10)->toISOString(),
        ]);
        $stale->assertOk();

        $this->assertNull($incident->fresh()->at_scene_at);
        $this->assertSame(Dispatch::STATUS_EN_ROUTE, $dispatch->fresh()->status);
    }

    public function test_repeated_gps_does_not_overwrite_at_scene_timestamp(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        [$user,, $incident] = $this->createGeofencedDispatch([
            'latitude' => 15.0000000,
            'longitude' => 120.0000000,
        ]);

        $payload = [
            'latitude' => 15.0005000,
            'longitude' => 120.0000000,
            'accuracy' => 5,
        ];

        $this->actingAs($user)->json('POST', route('driver.gps.update'), $payload)->assertOk();
        $firstTimestamp = $incident->fresh()->at_scene_at;
        $this->actingAs($user)->json('POST', route('driver.gps.update'), $payload)->assertOk();

        $this->assertTrue($firstTimestamp->equalTo($incident->fresh()->at_scene_at));
    }

    public function test_automatic_departure_requires_at_patient_and_uses_larger_radius(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        [$user,, $incident, $dispatch] = $this->createGeofencedDispatch([
            'latitude' => 15.0000000,
            'longitude' => 120.0000000,
        ], Dispatch::STATUS_ARRIVED);

        $betweenRadii = [
            'latitude' => 15.0020000,
            'longitude' => 120.0000000,
            'accuracy' => 5,
        ];

        $this->actingAs($user)->json('POST', route('driver.gps.update'), $betweenRadii)
            ->assertOk();
        $this->assertNull($incident->fresh()->depart_scene_at);

        $incident->update(['at_patient_at' => now()]);

        $outsideBothRadii = [
            'latitude' => 15.0040000,
            'longitude' => 120.0000000,
            'accuracy' => 5,
        ];

        $this->actingAs($user)->json('POST', route('driver.gps.update'), $outsideBothRadii)
            ->assertOk();
        $this->assertNotNull($incident->fresh()->depart_scene_at);
        $this->assertSame(Dispatch::STATUS_ARRIVED, $dispatch->fresh()->status);
    }

    public function test_manual_at_patient_action_remains_available(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);
        [$user,, $incident] = $this->createGeofencedDispatch([
            'latitude' => 15.0000000,
            'longitude' => 120.0000000,
        ], Dispatch::STATUS_ARRIVED);

        $response = $this->actingAs($user)->post(route('driver.incidents.at-patient', $incident));

        $response->assertSessionHas('success');
        $this->assertNotNull($incident->fresh()->at_patient_at);
    }

    private function createGeofencedDispatch(array $coordinates, string $status = Dispatch::STATUS_EN_ROUTE): array
    {
        Role::firstOrCreate(['name' => 'driver']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole('driver');

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'GEO-' . random_int(100, 999),
            'contact_number' => '09123450000',
            'license_number' => 'LIC-GEO',
            'license_expiry' => '2030-01-01',
            'status' => $status === Dispatch::STATUS_ARRIVED ? Driver::STATUS_ON_SCENE : Driver::STATUS_EN_ROUTE,
        ]);

        $vehicle = Ambulance::create([
            'plate_number' => 'GEO-' . random_int(100, 999),
            'vehicle_name' => 'Geofence Ambulance',
            'vehicle_type' => 'ambulance',
            'status' => Ambulance::STATUS_ON_DUTY,
        ]);

        $incident = Incident::create([
            'incident_number' => 'GEO-' . random_int(1000, 9999),
            'reporter_name' => 'Geofence Test',
            'contact_number' => '09123450000',
            'incident_type' => 'Medical',
            'location' => 'Geofence Road',
            'description' => 'Geofence test incident',
            'status' => Incident::STATUS_RESPONDING,
            'driver_id' => $driver->id,
            'ambulance_id' => $vehicle->id,
            'latitude' => $coordinates['latitude'],
            'longitude' => $coordinates['longitude'],
            'at_scene_at' => $status === Dispatch::STATUS_ARRIVED ? now()->subMinute() : null,
        ]);

        $dispatch = Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'status' => $status,
            'assigned_at' => now(),
            'accepted_at' => now(),
            'en_route_at' => $status === Dispatch::STATUS_EN_ROUTE ? now() : now()->subMinute(),
            'arrived_at' => $status === Dispatch::STATUS_ARRIVED ? now() : null,
        ]);

        return [$user, $driver, $incident, $dispatch];
    }

    public function test_admin_report_approval_closes_the_incident_and_completes_the_dispatch(): void
    {
        $this->withoutMiddleware(PreventRequestForgery::class);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $driver = Driver::create([
            'user_id' => $user->id,
            'badge_id' => 'AMB-101',
            'contact_number' => '09123456790',
            'license_number' => 'LIC-101',
            'license_expiry' => '2030-01-01',
            'status' => 'available',
        ]);

        $ambulance = Ambulance::create([
            'plate_number' => 'ABC-101',
            'vehicle_name' => 'Ambulance Two',
            'vehicle_type' => 'ambulance',
            'status' => 'on_duty',
        ]);

        $incident = Incident::create([
            'incident_number' => 'INC-0101',
            'reporter_name' => 'John Doe',
            'contact_number' => '09120000001',
            'incident_type' => 'Traffic',
            'location' => 'Test Avenue',
            'description' => 'Sample incident',
            'status' => 'completed',
            'driver_id' => $driver->id,
            'ambulance_id' => $ambulance->id,
        ]);

        $dispatch = Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $ambulance->id,
            'status' => Dispatch::STATUS_ARRIVED,
            'assigned_at' => now(),
            'arrived_at' => now(),
        ]);

        $report = IncidentReport::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'summary' => 'Patient stabilized',
            'actions_taken' => 'Transported to hospital',
            'casualties' => 'None',
            'remarks' => 'Completed',
            'submitted_at' => now(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->post(route('admin.reports.approve', $report));

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('incident_reports', [
            'id' => $report->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('incidents', [
            'id' => $incident->id,
            'status' => 'closed',
        ]);
        $this->assertDatabaseHas('dispatches', [
            'id' => $dispatch->id,
            'status' => Dispatch::STATUS_COMPLETED,
        ]);
    }

    private function assertDispatchAudit(
        Dispatch $dispatch,
        User $actor,
        string $action,
        ?string $oldStatus = null,
        ?string $newStatus = null
    ): void {
        $log = AuditLog::where('module', 'Dispatch')
            ->where('action', $action)
            ->where('user_id', $actor->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertStringContainsString('Dispatch #' . $dispatch->id, $log->description);
        $this->assertStringContainsString('Incident #' . $dispatch->incident_id, $log->description);
        $this->assertStringContainsString($dispatch->vehicle->vehicle_name, $log->description);
        $this->assertStringContainsString($dispatch->vehicle->plate_number, $log->description);
        $this->assertStringContainsString($dispatch->driver->user->name, $log->description);
        $this->assertStringContainsString($actor->name, $log->description);

        if ($oldStatus !== null && $newStatus !== null) {
            $this->assertStringContainsString(str_replace('_', ' ', $oldStatus), $log->description);
            $this->assertStringContainsString(str_replace('_', ' ', $newStatus), $log->description);
        }
    }
}
