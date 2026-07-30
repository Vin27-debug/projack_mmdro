<?php

namespace Tests\Feature;

use App\Http\Controllers\SuperAdmin\UserApprovalController;
use App\Models\Driver;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DispatchAndApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_incident_coordinates_are_stored_from_request(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create([
            'status' => 'approved',
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $response = $this->post(route('admin.incidents.store'), [
            'reporter_name' => 'Test Reporter',
            'contact_number' => '09170001111',
            'incident_type' => 'Medical',
            'location' => 'Test Location',
            'description' => 'Test description',
            'latitude' => '14.1234567',
            'longitude' => '121.1234567',
        ]);

        $response->assertRedirect(route('admin.incidents.index'));

        $incident = Incident::latest()->first();

        $this->assertNotNull($incident);
        $this->assertSame('14.1234567', (string) $incident->latitude);
        $this->assertSame('121.1234567', (string) $incident->longitude);
    }

    public function test_user_approval_marks_user_as_approved_and_assigns_driver_role(): void
    {
        Role::create(['name' => 'driver', 'guard_name' => 'web']);
        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);

        $superAdmin = User::factory()->create([
            'status' => 'approved',
        ]);
        $superAdmin->assignRole('super-admin');

        $driverUser = User::factory()->create([
            'status' => 'pending',
        ]);

        $driver = Driver::create([
            'user_id' => $driverUser->id,
            'badge_id' => 'PENDING',
            'contact_number' => '09170001111',
            'license_number' => 'LIC-001',
            'license_expiry' => now()->addYear()->toDateString(),
            'status' => 'available',
        ]);

        $driverUser->driver()->save($driver);

        $this->actingAs($superAdmin);

        $controller = new UserApprovalController();
        $response = $controller->approve($driverUser);

        $driverUser->refresh();

        $this->assertSame('approved', $driverUser->status);
        $this->assertNotNull($driverUser->approved_at);
        $this->assertTrue($driverUser->fresh()->hasRole('driver'));
        $this->assertEquals('AMB-001', $driver->fresh()->badge_id);
        $this->assertTrue($response->isRedirect());
    }
}
