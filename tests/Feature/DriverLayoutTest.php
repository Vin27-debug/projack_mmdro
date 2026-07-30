<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class DriverLayoutTest extends TestCase
{
    public function test_profile_page_uses_driver_layout(): void
    {
        $user = new User([
            'name' => 'Test Driver',
            'email' => 'driver@example.com',
        ]);

        $view = view('profile.edit', [
            'user' => $user,
            'errors' => new ViewErrorBag(),
        ])->render();

        $this->assertStringContainsString('Dashboard', $view);
        $this->assertStringContainsString('My Assignment', $view);
        $this->assertStringContainsString('Navigation', $view);
        $this->assertStringContainsString('Reports', $view);
        $this->assertStringContainsString('Profile', $view);
    }

    public function test_driver_dashboard_renders_without_blade_errors(): void
    {
        $driver = new \stdClass();
        $driver->user = new \stdClass();
        $driver->user->name = 'Test Driver';
        $driver->status = 'available';

        $view = view('driver.dashboard', [
            'driver' => $driver,
            'currentDispatch' => null,
            'incidents' => new Collection(),
        ])->render();

        $this->assertStringContainsString('Driver Operations Center', $view);
    }
}
