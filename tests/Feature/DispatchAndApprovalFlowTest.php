<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSee('Black — Critical');
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
