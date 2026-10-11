<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminModuleRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_admin_modules_render(): void
    {
        Role::create(['name' => 'admin']);

        $admin = User::factory()->create([
            'status' => 'approved',
        ]);
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $dashboard = $this->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('<summary aria-label="Reports" title="Reports">', false)
            ->assertSee('<span>Reports</span>', false)
            ->assertSee('bi bi-bar-chart-line', false)
            ->assertSee('class="admin-nav-group admin-reports-nav"', false)
            ->assertDontSee('class="admin-nav-group admin-reports-nav" open', false)
            ->assertSee('Reports Center')
            ->assertSee('Incident Reports')
            ->assertSee('Driver Performance')
            ->assertSee('Response Time Analytics')
            ->assertSee('Vehicle Utilization')
            ->assertSee('PDF Reports')
            ->assertSee(route('admin.reports.center'), false)
            ->assertSee(route('admin.reports.index'), false)
            ->assertSee(route('admin.reports.driver-performance'), false)
            ->assertSee(route('admin.reports.response-time'), false)
            ->assertSee(route('admin.reports.vehicle-utilization'), false)
            ->assertSee(route('admin.reports.pdf.view'), false);

        $dashboardHtml = $dashboard->getContent();
        preg_match_all(
            '/<details\b(?=[^>]*\badmin-reports-nav\b)[^>]*>(.*?)<\/details>/si',
            $dashboardHtml,
            $reportsGroups
        );

        $this->assertCount(1, $reportsGroups[0], 'The rendered sidebar should contain one Reports group.');

        foreach ([
            'admin.reports.center',
            'admin.reports.index',
            'admin.reports.driver-performance',
            'admin.reports.response-time',
            'admin.reports.vehicle-utilization',
            'admin.reports.pdf.view',
        ] as $routeName) {
            $this->assertStringContainsString(
                'href="' . route($routeName) . '"',
                $reportsGroups[1][0],
                "The Reports group should contain the {$routeName} link."
            );
        }

        $this->get('/admin/operations-center')->assertOk();
        $this->get('/admin/audit-logs')->assertOk();
        $this->get('/admin/incident-reports')
            ->assertOk()
            ->assertSee('class="admin-nav-group admin-reports-nav" open', false)
            ->assertSee('href="' . route('admin.reports.index') . '"', false)
            ->assertSee('class="nav-link active"', false);
        $this->get('/admin/vehicle-maintenance')->assertOk();
        $reportsCenter = $this->get('/admin/reports-center')
            ->assertOk()
            ->assertSee('class="admin-nav-group admin-reports-nav" open', false)
            ->assertSee('href="' . route('admin.reports.center') . '"', false)
            ->assertSee('class="nav-link active"', false)
            ->assertSee('Reports Center')
            ->assertSee('View Overview')
            ->assertSee('View Response Time')
            ->assertSee('View Incidents')
            ->assertSee('View Fleet')
            ->assertSee(route('admin.reports.center.export.pdf'), false)
            ->assertSee(route('admin.reports.center.export.excel'), false);

        $reportsCenter->assertSee('href="' . route('admin.reports.center') . '"', false)
            ->assertSee('class="nav-link active"', false)
            ->assertSee(route('admin.reports.index'), false)
            ->assertSee(route('admin.reports.driver-performance'), false)
            ->assertSee(route('admin.reports.response-time'), false)
            ->assertSee(route('admin.reports.vehicle-utilization'), false)
            ->assertSee(route('admin.reports.pdf.view'), false);

        $this->get('/admin/reports/driver-performance')
            ->assertOk()
            ->assertSee('class="admin-nav-group admin-reports-nav" open', false)
            ->assertSee('href="' . route('admin.reports.driver-performance') . '"', false)
            ->assertSee('class="nav-link active"', false);
        $this->get('/admin/reports/response-time')
            ->assertOk()
            ->assertSee('class="admin-nav-group admin-reports-nav" open', false)
            ->assertSee('href="' . route('admin.reports.response-time') . '"', false)
            ->assertSee('class="nav-link active"', false);
        $this->get('/admin/vehicle-utilization')
            ->assertOk()
            ->assertSee('class="admin-nav-group admin-reports-nav" open', false)
            ->assertSee('href="' . route('admin.reports.vehicle-utilization') . '"', false)
            ->assertSee('class="nav-link active"', false);
        $this->get('/admin/reports/pdf/view')
            ->assertOk()
            ->assertSee('class="admin-nav-group admin-reports-nav" open', false)
            ->assertSee('href="' . route('admin.reports.pdf.view') . '"', false)
            ->assertSee('class="nav-link active"', false)
            ->assertSee('Incident report PDF preview', false)
            ->assertSee('href="' . route('admin.reports.pdf') . '"', false);
        $this->get(route('admin.reports.pdf'))->assertOk();
        $this->get(route('admin.reports.center.export.pdf'))->assertOk();
        $this->get(route('admin.reports.center.export.excel'))->assertOk();
        $this->get(route('admin.reports.driver-performance.pdf'))->assertOk();
        $this->get(route('admin.reports.driver-performance.excel'))->assertOk();
    }
}
