<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Dispatch;
use App\Models\Incident;
use App\Models\IncidentAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DispatchAndApprovalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_incident_list_history_and_detail_pages(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['status' => 'pending']);
        $admin->assignRole('admin');

        $incident = Incident::create([
            'incident_number' => 'INC-PAGES-001',
            'reporter_name' => 'Incident Page Test',
            'incident_type' => 'Medical Emergency',
            'location' => 'Test Street',
            'description' => 'Incident page route test',
            'status' => Incident::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.incidents.index'))
            ->assertOk()
            ->assertSee($incident->incident_number);

        $this->get('/admin/incidents/history')->assertOk();

        $this->get(route('admin.incidents.show', $incident))
            ->assertOk()
            ->assertSee($incident->incident_number);
    }

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
            'priority' => 'Medium',
            'latitude' => '14.1234567',
            'longitude' => '121.1234567',
        ]);

        $response->assertRedirect(route('admin.incidents.index'));
        $incident = Incident::latest()->firstOrFail();

        $this->assertSame('14.1234567', (string) $incident->latitude);
        $this->assertSame('121.1234567', (string) $incident->longitude);
    }

    public function test_incident_stores_house_number_and_allows_optional_contact_and_location(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('admin.incidents.store'), [
            'reporter_name' => 'Address Reporter',
            'contact_number' => '',
            'incident_type' => 'Medical Emergency',
            'location' => '',
            'house_number' => '123',
            'street' => 'Main Street',
            'barangay' => 'Barangay One',
            'city' => 'Test City',
            'province' => 'Test Province',
            'description' => 'Address persistence test',
            'priority' => 'Critical',
        ]);

        $incident = Incident::latest()->firstOrFail();

        $response->assertRedirect(route('admin.incidents.index'));
        $this->assertSame('123', $incident->house_number);
        $this->assertSame('Critical', $incident->priority);
        $this->assertNull($incident->contact_number);
        $this->assertNull($incident->location);

        $this->get(route('admin.incidents.show', $incident))
            ->assertOk()
            ->assertSee('123, Main Street, Barangay One, Test City, Test Province')
            ->assertSee('Black — Disease-Related Incident');
    }

    public function test_admin_incident_creation_redirects_to_existing_list_and_can_continue_to_dispatch(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->post(route('admin.incidents.store'), [
            'reporter_name' => 'Redirect Test Reporter',
            'contact_number' => '09170002222',
            'incident_type' => 'Medical Emergency',
            'location' => 'Test Dispatch Street',
            'description' => 'Verify incident create redirect and dispatch workflow',
            'priority' => 'High',
        ]);

        $incident = Incident::where('reporter_name', 'Redirect Test Reporter')->firstOrFail();
        $response->assertRedirect(route('admin.incidents.index'));
        $this->assertDatabaseHas('incidents', [
            'id' => $incident->id,
            'reporter_name' => 'Redirect Test Reporter',
            'status' => Incident::STATUS_PENDING,
        ]);

        $this->get(route('admin.incidents.index'))
            ->assertOk()
            ->assertSee($incident->incident_number)
            ->assertSee(route('admin.incidents.dispatch.form', $incident));

        $this->get(route('admin.incidents.dispatch.form', $incident))
            ->assertOk()
            ->assertSee('Dispatch Incident');
    }

    public function test_super_admin_can_view_incidents_and_download_attachments_but_cannot_manage_them(): void
    {
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create(['status' => 'approved']);
        $superAdmin->assignRole('super-admin');
        $incident = Incident::create([
            'incident_number' => 'INC-SA-READ-001',
            'reporter_name' => 'Read Only Test',
            'incident_type' => 'Medical Emergency',
            'location' => 'Read Only Street',
            'description' => 'Super Admin should only view this incident.',
            'status' => Incident::STATUS_PENDING,
        ]);

        Storage::fake('local');
        $attachmentPath = 'incident-attachments/' . $incident->id . '/evidence.jpg';
        Storage::disk('local')->put($attachmentPath, 'existing evidence');
        $attachment = IncidentAttachment::create([
            'incident_id' => $incident->id,
            'uploaded_by' => $superAdmin->id,
            'disk' => 'local',
            'path' => $attachmentPath,
            'original_name' => 'evidence.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 17,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('superadmin.incidents.index'))
            ->assertOk()
            ->assertSee('INC-SA-READ-001');

        $this->get(route('superadmin.incidents.show', $incident))
            ->assertOk()
            ->assertSee('<title>MuniResQ Super Admin</title>', false)
            ->assertSee('INC-SA-READ-001');
        $this->get(route('admin.dispatches.index'))->assertOk();

        $this->get(route('admin.incidents.attachments.download', [$incident, $attachment]))
            ->assertDownload('evidence.jpg');

        $this->assertIncidentWriteActionsDenied($superAdmin, $incident);
    }

    public function test_user_with_admin_and_super_admin_roles_still_cannot_manage_incidents(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create(['status' => 'approved']);
        $superAdmin->assignRole('admin', 'super-admin');
        $this->assertTrue($superAdmin->hasRole('admin'));
        $this->assertTrue($superAdmin->hasRole('super-admin'));

        $incident = Incident::create([
            'incident_number' => 'INC-SA-DUAL-001',
            'reporter_name' => 'Dual Role Read Only Test',
            'incident_type' => 'Medical Emergency',
            'location' => 'Dual Role Street',
            'description' => 'An Admin and Super Admin role combination remains read-only.',
            'status' => Incident::STATUS_PENDING,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('superadmin.incidents.index'))
            ->assertOk()
            ->assertSee($incident->incident_number);

        $this->get(route('superadmin.incidents.show', $incident))->assertOk();
        Storage::fake('local');
        $this->assertIncidentWriteActionsDenied($superAdmin, $incident);
    }

    public function test_driver_cannot_open_or_submit_admin_incident_creation_routes(): void
    {
        Role::firstOrCreate(['name' => 'driver', 'guard_name' => 'web']);
        $driver = User::factory()->create(['status' => 'approved']);
        $driver->assignRole('driver');

        $this->actingAs($driver)
            ->get(route('admin.incidents.create'))
            ->assertForbidden();

        $this->post(route('admin.incidents.store'), [
            'reporter_name' => 'Unauthorized Reporter',
            'incident_type' => 'Medical Emergency',
            'priority' => 'High',
        ])->assertForbidden();

        $this->assertDatabaseMissing('incidents', [
            'reporter_name' => 'Unauthorized Reporter',
        ]);
    }

    private function assertIncidentWriteActionsDenied(User $user, Incident $incident): void
    {
        $driverAccount = User::factory()->create(['status' => 'approved']);
        $driver = Driver::create([
            'user_id' => $driverAccount->id,
            'badge_id' => 'READONLY-' . $user->id,
            'contact_number' => '09170005555',
            'license_number' => 'READONLY-LICENSE-' . $user->id,
            'license_expiry' => now()->addYear()->toDateString(),
        ]);
        $dispatch = Dispatch::create([
            'incident_id' => $incident->id,
            'driver_id' => $driver->id,
            'status' => Dispatch::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);

        $incidentCount = Incident::count();
        $dispatchCount = Dispatch::count();
        $attachmentCount = IncidentAttachment::count();
        $incidentSnapshot = $incident->only(['reporter_name', 'status', 'archived_at', 'description', 'call_received_at']);
        $dispatchSnapshot = [
            'incident_id' => $dispatch->incident_id,
            'driver_id' => $dispatch->driver_id,
            'vehicle_id' => $dispatch->vehicle_id,
            'status' => $dispatch->status,
            'assigned_at' => $dispatch->assigned_at?->toISOString(),
        ];
        $storedFiles = Storage::disk('local')->allFiles('incident-attachments/' . $incident->id);

        $this->actingAs($user)->get(route('admin.incidents.create'))->assertForbidden();
        $this->post(route('admin.incidents.store'), [
            'reporter_name' => 'Unauthorized Super Admin Incident',
            'contact_number' => '09170003333',
            'incident_type' => 'Medical Emergency',
            'location' => 'Super Admin Test Street',
            'description' => 'Must not be persisted.',
            'priority' => 'High',
        ])->assertForbidden();
        $this->get(route('admin.incidents.edit', $incident))->assertForbidden();
        $this->put(route('admin.incidents.update', $incident), [
            'reporter_name' => 'Changed by Super Admin',
            'contact_number' => '09170004444',
            'incident_type' => 'Fire',
            'location' => 'Changed Location',
            'description' => 'Must not be updated.',
            'priority' => 'Critical',
            'attachments' => [UploadedFile::fake()->create('dual-role-evidence.pdf', 10, 'application/pdf')],
        ])->assertForbidden();
        $this->post(route('admin.incidents.archive', $incident))->assertForbidden();
        $this->post(route('admin.incidents.restore', $incident))->assertForbidden();
        $this->post(route('admin.incidents.timestamp.update', [$incident, 'call-received']), [
            'timestamp' => now()->format('Y-m-d\\TH:i'),
        ])->assertForbidden();
        $this->get(route('admin.incidents.dispatch.form', $incident))->assertForbidden();
        $this->get(route('dispatches.create'))->assertForbidden();
        $this->get(route('dispatches.edit', $dispatch))->assertForbidden();
        $this->post(route('admin.incidents.dispatch', $incident), [
            'driver_id' => 1,
            'vehicle_id' => 1,
        ])->assertForbidden();
        $this->post(route('admin.incidents.auto-dispatch', $incident))->assertForbidden();
        $this->post(route('admin.dispatches.assign', $incident), [
            'driver_id' => 1,
            'ambulance_id' => 1,
        ])->assertForbidden();
        $this->post(route('dispatches.store'), [])->assertForbidden();
        $this->put(route('dispatches.update', $dispatch), [])->assertForbidden();
        $this->delete(route('dispatches.destroy', $dispatch))->assertForbidden();

        $this->assertSame($incidentCount, Incident::count());
        $this->assertSame($dispatchCount, Dispatch::count());
        $this->assertSame($attachmentCount, IncidentAttachment::count());
        $this->assertSame($incidentSnapshot, $incident->fresh()->only(['reporter_name', 'status', 'archived_at', 'description', 'call_received_at']));
        $freshDispatch = $dispatch->fresh();
        $this->assertSame($dispatchSnapshot, [
            'incident_id' => $freshDispatch->incident_id,
            'driver_id' => $freshDispatch->driver_id,
            'vehicle_id' => $freshDispatch->vehicle_id,
            'status' => $freshDispatch->status,
            'assigned_at' => $freshDispatch->assigned_at?->toISOString(),
        ]);
        $this->assertSame($storedFiles, Storage::disk('local')->allFiles('incident-attachments/' . $incident->id));
        $this->assertDatabaseMissing('incidents', ['reporter_name' => 'Unauthorized Super Admin Incident']);
    }

    public function test_incident_edit_and_update_preserve_house_number_and_priority(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole('admin');
        $incident = Incident::create([
            'incident_number' => 'INC-EDIT-001',
            'reporter_name' => 'Original Reporter',
            'contact_number' => '09123456789',
            'incident_type' => 'Medical Emergency',
            'location' => 'Original Location',
            'house_number' => '10',
            'street' => 'Old Street',
            'barangay' => 'Old Barangay',
            'city' => 'Test City',
            'province' => 'Test Province',
            'priority' => 'Low',
            'status' => Incident::STATUS_PENDING,
        ]);

        $this->actingAs($admin)->get(route('admin.incidents.edit', $incident))->assertOk();

        $this->put(route('admin.incidents.update', $incident), [
            'reporter_name' => 'Updated Reporter',
            'contact_number' => '09111111111',
            'incident_type' => 'Fire',
            'location' => 'Updated Location',
            'house_number' => '20',
            'street' => 'New Street',
            'barangay' => 'New Barangay',
            'city' => 'New City',
            'province' => 'New Province',
            'description' => 'Updated incident',
            'priority' => 'High',
        ])->assertRedirect(route('admin.incidents.show', $incident));

        $incident->refresh();
        $this->assertSame('20', $incident->house_number);
        $this->assertSame('High', $incident->priority);
        $this->assertSame('New Street', $incident->street);
    }

}
