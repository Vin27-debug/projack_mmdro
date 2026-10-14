<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BackupManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_creation_is_rejected_for_an_in_memory_sqlite_database(): void
    {
        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);

        $superAdmin = User::factory()->create(['status' => 'approved']);
        $superAdmin->assignRole('super-admin');

        $response = $this->actingAs($superAdmin)
            ->withSession(['_token' => 'test'])
            ->from(route('backups.index'))
            ->post(route('backups.create'), ['_token' => 'test']);

        $response->assertRedirect(route('backups.index'));
        $response->assertSessionHas('error', 'Database backups require MySQL, MariaDB, or a file-based SQLite database.');
    }

    public function test_admin_and_driver_cannot_access_backup_operations(): void
    {
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        Role::create(['name' => 'driver', 'guard_name' => 'web']);

        $admin = User::factory()->create(['status' => 'approved']);
        $admin->assignRole('admin');
        $driver = User::factory()->create(['status' => 'approved']);
        $driver->assignRole('driver');

        $this->actingAs($admin)->get(route('backups.index'))->assertForbidden();
        $this->actingAs($admin)->post(route('backups.create'))->assertForbidden();
        $this->actingAs($driver)->get(route('backups.index'))->assertForbidden();
    }

    public function test_super_admin_can_list_and_download_a_private_backup(): void
    {
        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create(['status' => 'approved']);
        $superAdmin->assignRole('super-admin');
        $directory = storage_path('app/private/backups');
        File::ensureDirectoryExists($directory);
        $filename = 'backup_download_test.sqlite';
        File::put($directory.DIRECTORY_SEPARATOR.$filename, "SQLite format 3\000test fixture");

        try {
            $this->actingAs($superAdmin)->get(route('backups.index'))
                ->assertOk()
                ->assertSee($filename)
                ->assertSee('Download');
            $this->actingAs($superAdmin)->get(route('backups.download', $filename))
                ->assertOk()
                ->assertDownload($filename);
        } finally {
            File::delete($directory.DIRECTORY_SEPARATOR.$filename);
        }
    }

    public function test_restore_requires_explicit_confirmation_and_rejects_invalid_backup(): void
    {
        Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create(['status' => 'approved']);
        $superAdmin->assignRole('super-admin');

        $directory = storage_path('app/private/backups');
        File::ensureDirectoryExists($directory);
        $filename = 'backup_invalid_test.sqlite';
        File::put($directory.DIRECTORY_SEPARATOR.$filename, 'not a sqlite backup');

        try {
            $this->actingAs($superAdmin)
                ->post(route('backups.restore'), ['backup_file' => $filename])
                ->assertSessionHasErrors('confirm_restore');

            config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
            $this->actingAs($superAdmin)
                ->from(route('backups.index'))
                ->post(route('backups.restore'), ['backup_file' => $filename, 'confirm_restore' => '1'])
                ->assertSessionHas('error', 'The selected backup is corrupted, empty, or unsupported.');
        } finally {
            File::delete($directory.DIRECTORY_SEPARATOR.$filename);
        }
    }
}
