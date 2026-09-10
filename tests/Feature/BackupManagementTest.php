<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackupManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_backup_creation_is_rejected_when_database_is_not_mysql(): void
    {
        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);

        $superAdmin = User::factory()->create(['status' => 'approved']);
        $superAdmin->assignRole('super-admin');

        $response = $this->actingAs($superAdmin)
            ->withSession(['_token' => 'test'])
            ->from(route('backups.index'))
            ->post(route('backups.create'), ['_token' => 'test']);

        $response->assertRedirect(route('backups.index'));
        $response->assertSessionHas('error', 'Database backups are only supported for MySQL databases.');
    }
}
