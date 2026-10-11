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
            ->assertSee('Reports Center');

        preg_match('/<aside\b[^>]*id="adminSidebar"[^>]*>.*?<\/aside>/si', $dashboard->getContent(), $sidebarMatch);
        $this->assertNotEmpty($sidebarMatch, 'The rendered dashboard should include the Admin sidebar.');
        $sidebar = $sidebarMatch[0];
        $this->assertSame(1, substr_count($sidebar, 'Reports Center'));
        $this->assertStringContainsString('href="' . route('admin.reports.center') . '"', $sidebar);

        foreach (['Incident Reports', 'Driver Performance', 'Response Time Analytics', 'Vehicle Utilization', 'PDF Reports'] as $separateReportLink) {
            $this->assertStringNotContainsString($separateReportLink, $sidebar);
        }

        foreach ([
            'admin.reports.index',
            'admin.reports.driver-performance',
            'admin.reports.response-time',
            'admin.reports.vehicle-utilization',
            'admin.reports.pdf.view',
        ] as $separateReportRoute) {
            $this->assertStringNotContainsString('href="' . route($separateReportRoute) . '"', $sidebar);
        }

        $this->get('/admin/operations-center')->assertOk();
        $this->get('/admin/audit-logs')->assertOk();
        $incidentReports = $this->get('/admin/incident-reports')
            ->assertOk()
            ->assertSee('href="' . route('admin.reports.center') . '"', false)
            ->assertSee('Incident Reports');
        $this->assertReportsCenterSidebarActive($incidentReports, false);
        $this->get('/admin/vehicle-maintenance')->assertOk();
        $reportsCenter = $this->get('/admin/reports-center')
            ->assertOk()
            ->assertSee('href="' . route('admin.reports.center') . '"', false)
            ->assertSee('Reports Center')
            ->assertSee('data-report-tab="overview"', false)
            ->assertSee('Incident Reports')
            ->assertSee('Driver Performance')
            ->assertSee('Response Time Analytics')
            ->assertSee('Vehicle Utilization')
            ->assertSee('PDF Reports')
            ->assertSee('data-report-tab="incidents"', false)
            ->assertSee('data-report-tab="driver-performance"', false)
            ->assertSee('data-report-tab="response-time"', false)
            ->assertSee('data-report-tab="fleet"', false)
            ->assertSee('data-report-tab="pdf"', false)
            ->assertSee('data-report-section="driver-performance"', false)
            ->assertSee('data-report-section="pdf"', false)
            ->assertSee('href="' . route('admin.reports.pdf.view') . '"', false)
            ->assertSee('href="' . route('admin.reports.driver-performance.pdf') . '"', false)
            ->assertSee('href="' . route('admin.reports.driver-performance.excel') . '"', false)
            ->assertSee(route('admin.reports.center.export.pdf'), false)
            ->assertSee(route('admin.reports.center.export.excel'), false);

        $this->assertReportsCenterSidebarActive($reportsCenter, true);

        foreach (['overview', 'incidents', 'driver-performance', 'response-time', 'fleet', 'pdf'] as $section) {
            $sectionResponse = $this->get(route('admin.reports.center', ['section' => $section]))
                ->assertOk()
                ->assertSee('class="report-section active" data-report-section="' . $section . '"', false);
            $this->assertReportsCenterSidebarActive($sectionResponse, true);
        }

        $driverPerformance = $this->get('/admin/reports/driver-performance')->assertOk();
        $this->assertReportsCenterSidebarActive($driverPerformance, false);
        $responseTime = $this->get('/admin/reports/response-time')->assertOk();
        $this->assertReportsCenterSidebarActive($responseTime, false);
        $vehicleUtilization = $this->get('/admin/vehicle-utilization')->assertOk();
        $this->assertReportsCenterSidebarActive($vehicleUtilization, false);
        $pdfPreview = $this->get('/admin/reports/pdf/view')
            ->assertOk()
            ->assertSee('Incident report PDF preview', false)
            ->assertSee('href="' . route('admin.reports.pdf') . '"', false);
        $this->assertReportsCenterSidebarActive($pdfPreview, false);
        $this->get(route('admin.reports.pdf'))->assertOk();
        $this->get(route('admin.reports.center.export.pdf'))->assertOk();
        $this->get(route('admin.reports.center.export.excel'))->assertOk();
        $this->get(route('admin.reports.driver-performance.pdf'))->assertOk();
        $this->get(route('admin.reports.driver-performance.excel'))->assertOk();
    }

    private function assertReportsCenterSidebarActive($response, bool $active): void
    {
        $href = preg_quote('href="' . route('admin.reports.center') . '"', '/');
        preg_match('/<a\b(?=[^>]*' . $href . ')[^>]*>/si', $response->getContent(), $linkMatch);

        $this->assertNotEmpty($linkMatch, 'The Reports Center sidebar link should be rendered.');
        $hasActiveClass = preg_match('/\bclass="[^"]*\bactive\b[^"]*"/i', $linkMatch[0]) === 1;

        $this->assertSame($active, $hasActiveClass);
    }
}
