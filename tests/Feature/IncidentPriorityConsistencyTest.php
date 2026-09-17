<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncidentPriorityConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_valid_priority_can_be_stored(): void
    {
        foreach (Incident::VALID_PRIORITIES as $priority) {
            $incident = Incident::create([
                'incident_number' => 'INC-' . strtoupper(substr(md5($priority), 0, 6)),
                'reporter_name' => 'Reporter',
                'contact_number' => '09123456789',
                'incident_type' => 'Medical Emergency',
                'location' => 'Test Location',
                'priority' => $priority,
                'status' => Incident::STATUS_PENDING,
            ]);

            $this->assertSame($priority, $incident->fresh()->priority);
        }
    }

    public function test_invalid_priority_is_rejected_by_validation(): void
    {
        $validator = Validator::make(
            ['priority' => 'Severe'],
            ['priority' => ['required', 'in:' . implode(',', Incident::VALID_PRIORITIES)]]
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('priority', $validator->errors()->toArray());
    }

    public function test_priority_display_mapping_is_consistent(): void
    {
        $this->assertSame('Green — Low', Incident::priorityDisplayLabel(Incident::PRIORITY_LOW));
        $this->assertSame('Yellow — Medium', Incident::priorityDisplayLabel(Incident::PRIORITY_MEDIUM));
        $this->assertSame('Red — High', Incident::priorityDisplayLabel(Incident::PRIORITY_HIGH));
        $this->assertSame('Black — Critical', Incident::priorityDisplayLabel(Incident::PRIORITY_CRITICAL));
    }

    public function test_incident_creation_form_uses_consistent_priority_labels(): void
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        $user = User::factory()->create(['status' => 'approved']);
        $user->assignRole($role);

        $response = $this->actingAs($user)->get(route('admin.incidents.create'));

        $response->assertOk();
        $response->assertSee('Green — Low');
        $response->assertSee('Yellow — Medium');
        $response->assertSee('Red — High');
        $response->assertSee('Black — Critical');
        $response->assertDontSee('Dead On Spot');
        $response->assertDontSee('Dead on Spot');
    }
}
