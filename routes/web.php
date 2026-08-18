<?php

use App\Http\Controllers\GeocodingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Admin\AdminRegistrationController;
use App\Http\Controllers\Admin\GpsMonitoringController;
use App\Http\Controllers\Admin\DispatchController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IncidentReportController as AdminIncidentReportController;
use App\Http\Controllers\Admin\VehicleMaintenanceController;
use App\Http\Controllers\Admin\VehicleUtilizationController;
use App\Http\Controllers\Admin\DriverPerformanceController;
use App\Http\Controllers\Admin\ResponseTimeController;
use App\Http\Controllers\Admin\PdfReportController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\ReportsCenterController;
use App\Http\Controllers\Admin\NearestVehicleController;
use App\Http\Controllers\Admin\AutoDispatchController;
use App\Http\Controllers\Admin\IncidentController as AdminIncidentController;
use App\Http\Controllers\Admin\OperationsCenterController;
use App\Http\Controllers\Admin\ResponseTimeAnalyticsController;
use App\Http\Controllers\Admin\HijackAlertController;
use App\Http\Controllers\Admin\PanicAlertController as AdminPanicAlertController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\IncidentHistoryController;
use App\Http\Controllers\Admin\AmbulanceController as adminAmbulanceController;

use App\Http\Controllers\Driver\DashboardController as DriverDashboardController;
use App\Http\Controllers\Driver\DriverRegistrationController;
use App\Http\Controllers\Driver\GpsController;
use App\Http\Controllers\Driver\MyAssignmentController;
use App\Http\Controllers\Driver\PanicController;
use App\Http\Controllers\Driver\IncidentReportController;
use App\Http\Controllers\Driver\HijackController;
use App\Http\Controllers\Driver\DriverAssignmentController;
use App\Http\Controllers\Driver\NavigationController;
use App\Http\Controllers\Driver\DriverHistoryController;
use App\Http\Controllers\Driver\DriverSettingsController;

use App\Http\Controllers\SuperAdmin\UserApprovalController;
use App\Http\Controllers\SuperAdmin\BackupController;
use App\Http\Controllers\SuperAdmin\AmbulanceController;
use App\Http\Controllers\SuperAdmin\AssignmentController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\DriverController;

use App\Http\Controllers\SuperAdmin\SystemSettingsController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Driver Registration
|--------------------------------------------------------------------------
*/

Route::get('/driver/register', [DriverRegistrationController::class, 'create'])
    ->name('driver.register');

Route::post('/driver/register', [DriverRegistrationController::class, 'store'])
    ->name('driver.register.store');

Route::post('/driver/hijack', [HijackController::class, 'trigger'])
    ->name('driver.hijack.trigger');

/*
|--------------------------------------------------------------------------
| Admin Registration
|--------------------------------------------------------------------------
*/

Route::get('/admin/register', [AdminRegistrationController::class, 'create'])
    ->name('admin.register');

Route::post('/admin/register', [AdminRegistrationController::class, 'store'])
    ->name('admin.register.store');

/*
|--------------------------------------------------------------------------
| Driver Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'approved',
    'role:driver'
])->group(function () {


    Route::post('/driver/panic', [PanicController::class, 'trigger'])
        ->name('driver.panic.trigger');

    Route::post('/driver/dispatches/{dispatch}/accept', [DriverDashboardController::class, 'acceptDispatch'])
        ->name('driver.dispatch.accept');

    Route::post('/driver/dispatches/{dispatch}/decline', [DriverDashboardController::class, 'declineDispatch'])
        ->name('driver.dispatch.decline');

    Route::get('/driver/dashboard', [DriverDashboardController::class, 'index'])
        ->name('driver.dashboard');

    Route::get('/driver/my-assignment', [MyAssignmentController::class, 'index'])
        ->name('driver.assignment');

    Route::get('/driver/incidents/report/{incident?}', [IncidentReportController::class, 'create'])
        ->name('driver.report.create');

    Route::post('/driver/incidents/report/{incident?}', [IncidentReportController::class, 'store'])
        ->name('driver.report.store');

    Route::post('/driver/gps/update', [GpsController::class, 'update'])
        ->name('driver.gps.update');

    Route::post('/driver/incidents/{incident}/en-route', [DriverDashboardController::class, 'markEnRoute'])
        ->name('driver.incidents.en-route');

    Route::post('/driver/incidents/{incident}/arrived', [DriverDashboardController::class, 'markArrived'])
        ->name('driver.incidents.arrived');

    Route::post('/driver/incidents/{incident}/completed', [DriverDashboardController::class, 'markCompleted'])
        ->name('driver.incidents.completed');

    Route::get('/driver/navigation', [NavigationController::class, 'show'])
        ->name('driver.navigation');

    Route::get('/driver/history', [DriverHistoryController::class, 'index'])
        ->name('driver.history');

    Route::get('/driver/settings', [DriverSettingsController::class, 'index'])
        ->name('driver.settings');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'approved',
    'role:admin|super-admin'
])->group(function () {

    //geoloc

    Route::get('/geocode', [GeocodingController::class, 'search'])
        ->name('geocode.search');

    Route::resource('ambulances', AmbulanceController::class)
        ->except(['show'])
        ->names('admin.ambulances');

    Route::get('/admin/reports/pdf', [PdfReportController::class, 'downloadReport'])
        ->name('admin.reports.pdf');
    Route::get('/admin/reports/pdf/view', [PdfReportController::class, 'viewReport'])
        ->name('admin.reports.pdf.view');

    Route::get('/admin/reports/driver-performance', [DriverPerformanceController::class, 'index'])
        ->name('admin.reports.driver-performance');

    Route::get('/admin/reports/driver-performance/pdf', [DriverPerformanceController::class, 'exportPdf'])
        ->name('admin.reports.driver-performance.pdf');

    Route::get('/admin/reports/driver-performance/excel', [DriverPerformanceController::class, 'exportExcel'])
        ->name('admin.reports.driver-performance.excel');

    Route::get('/dispatch-center', [DispatchController::class, 'index'])
        ->name('dispatch.center');

    Route::post('/dispatches/{incident}/assign', [DispatchController::class, 'assign'])
        ->name('admin.dispatches.assign');

    Route::get('/panic-alerts', [AdminPanicAlertController::class, 'index'])
        ->name('admin.panic.index');

    Route::get('/hijack-alerts', [HijackAlertController::class, 'index'])
        ->name('admin.hijack.index');

    Route::get('/admin/reports/response-time', [ResponseTimeController::class, 'index'])
        ->name('admin.reports.response-time');

    Route::get('/admin/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard');

    Route::get('/admin/dashboard/counters', [DashboardController::class, 'counters'])
        ->name('admin.dashboard.counters');

    Route::get('/admin/dashboard/gps-locations', [DashboardController::class, 'gpsLocations'])
        ->name('admin.dashboard.gps-locations');

    Route::get('/admin/dashboard/live-command-map', [DashboardController::class, 'liveCommandMapData'])
        ->name('admin.dashboard.live-command-map');

    Route::get('/admin/dashboard/response-load-analytics', [DashboardController::class, 'responseLoadAnalytics'])
        ->name('admin.dashboard.response-load-analytics');

    Route::get('/admin/dashboard/situation-overview', [DashboardController::class, 'situationOverview'])
        ->name('admin.dashboard.situation-overview');

    Route::get('/admin/dashboard/fleet-readiness', [DashboardController::class, 'fleetReadiness'])
        ->name('admin.dashboard.fleet-readiness');

    Route::get('/admin/gps-monitoring', [GpsMonitoringController::class, 'index'])
        ->name('admin.gps.monitoring');

    Route::get('/admin/gps-history', [GpsMonitoringController::class, 'history'])
        ->name('admin.gps.history');

    Route::get('/admin/operations-center', [OperationsCenterController::class, 'index'])
        ->name('admin.operations.center');

    Route::get('/admin/nearest-vehicle/{incident}', [NearestVehicleController::class, 'show'])
        ->name('admin.nearest.vehicle');

    Route::get('/admin/notifications', [NotificationController::class, 'index'])
        ->name('admin.notifications.index');

    Route::get('/admin/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->name('admin.notifications.unread-count');

    Route::post('/admin/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('admin.notifications.read-all');

    Route::post('/admin/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('admin.notifications.read');

    Route::get('/admin/incidents', [AdminIncidentController::class, 'index'])
        ->name('admin.incidents.index');
    Route::get('/admin/incidents/create', [AdminIncidentController::class, 'create'])
        ->name('admin.incidents.create');
    Route::post('/admin/incidents', [AdminIncidentController::class, 'store'])
        ->name('admin.incidents.store');
    Route::get('/admin/incidents/{incident}/dispatch', [AdminIncidentController::class, 'dispatchForm'])
        ->name('admin.incidents.dispatch.form');
    Route::post('/admin/incidents/{incident}/dispatch', [AdminIncidentController::class, 'dispatch'])
        ->name('admin.incidents.dispatch');

    Route::get('/admin/incident-reports', [AdminIncidentReportController::class, 'index'])
        ->name('admin.reports.index');
    Route::get('/admin/incident-reports/create', [AdminIncidentReportController::class, 'create'])
        ->name('admin.incident-reports.create');
    Route::post('/admin/incident-reports', [AdminIncidentReportController::class, 'store'])
        ->name('admin.incident-reports.store');

    Route::get('/admin/reports-center', [ReportsCenterController::class, 'index'])
        ->name('admin.reports.center');
    Route::get('/admin/reports-center/export/pdf', [ReportsCenterController::class, 'exportPdf'])
        ->name('admin.reports.center.export.pdf');
    Route::get('/admin/reports-center/export/excel', [ReportsCenterController::class, 'exportExcel'])
        ->name('admin.reports.center.export.excel');

    Route::resource('dispatches', DispatchController::class);

    Route::get('/admin/vehicle-utilization', [VehicleUtilizationController::class, 'index'])
        ->name('admin.reports.vehicle-utilization');
    Route::get('/admin/vehicle-utilization/create', [VehicleUtilizationController::class, 'create'])
        ->name('admin.vehicle-utilization.create');
    Route::post('/admin/vehicle-utilization', [VehicleUtilizationController::class, 'store'])
        ->name('admin.vehicle-utilization.store');

    Route::get('/admin/vehicle-maintenance', [VehicleMaintenanceController::class, 'index'])
        ->name('admin.maintenance.index');
    Route::get('/admin/vehicle-maintenance/create', [VehicleMaintenanceController::class, 'create'])
        ->name('admin.maintenance.create');
    Route::post('/admin/vehicle-maintenance', [VehicleMaintenanceController::class, 'store'])
        ->name('admin.maintenance.store');
    Route::get('/admin/vehicle-maintenance/{vehicle_maintenance}/edit', [VehicleMaintenanceController::class, 'edit'])
        ->name('admin.maintenance.edit');
    Route::put('/admin/vehicle-maintenance/{vehicle_maintenance}', [VehicleMaintenanceController::class, 'update'])
        ->name('admin.maintenance.update');
    Route::delete('/admin/vehicle-maintenance/{vehicle_maintenance}', [VehicleMaintenanceController::class, 'destroy'])
        ->name('admin.maintenance.destroy');
    Route::post('/admin/vehicle-maintenance/{vehicle_maintenance}/complete', [VehicleMaintenanceController::class, 'complete'])
        ->name('admin.maintenance.complete');

    Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])
        ->name('admin.audit-logs.index');
    Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])
        ->name('admin.audit.logs');

    Route::get('/admin/backups', [BackupController::class, 'index'])
        ->name('admin.backups.index');

    Route::post('/admin/backups', [BackupController::class, 'create'])
        ->name('admin.backups.create');

    Route::post('/admin/backups/{backup}/restore', [BackupController::class, 'restore'])
        ->name('admin.backups.restore');

    Route::resource('nearest-vehicle', NearestVehicleController::class);

    Route::post('/admin/incidents/{incident}/auto-dispatch', [AutoDispatchController::class, 'dispatch'])
        ->name('admin.incidents.auto-dispatch');
});

/*
|--------------------------------------------------------------------------
| Admin GPS Monitoring & Dispatch (Admin + Super Admin)
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'approved',
    'role:admin|super-admin'
])->group(function () {
    Route::get('/admin/gps-locations', [GpsMonitoringController::class, 'locations'])
        ->name('admin.gps.locations');

    Route::get('/admin/dispatches', [DispatchController::class, 'index'])
        ->name('admin.dispatches.index');

    Route::get('/admin/dispatch-center', [DispatchController::class, 'index'])
        ->name('admin.dispatch.center');

    Route::post('/admin/reports/{report}/approve', [AdminIncidentReportController::class, 'approve'])
        ->name('admin.reports.approve');
});

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'approved',
    'role:super-admin'
])->group(function () {

    Route::get(
        '/superadmin/drivers/{driver}/assign',
        [UserApprovalController::class, 'assignForm']
    )->name('superadmin.drivers.assign');

    Route::post(
        '/superadmin/drivers/{driver}/assign',
        [UserApprovalController::class, 'assignVehicle']
    )->name('superadmin.drivers.assign.store');

    Route::get('/superadmin/drivers/create', [DriverController::class, 'create'])
        ->name('superadmin.drivers.create');

    Route::post('/superadmin/drivers', [DriverController::class, 'store'])
        ->name('superadmin.drivers.store');

    Route::post('/superadmin/drivers', [DriverController::class, 'store'])
        ->name('superadmin.drivers.store');
    Route::get('/backups', [BackupController::class, 'index'])
        ->name('backups.index');

    Route::post('/backups/create', [BackupController::class, 'create'])
        ->name('backups.create');

    Route::get('/backups/download/{file}', [BackupController::class, 'download'])
        ->name('backups.download');

    Route::post('/backups/restore', [BackupController::class, 'restore'])
        ->name('backups.restore');

    Route::get('/superadmin/dashboard', [SuperAdminDashboardController::class, 'index'])
        ->name('superadmin.dashboard');

    Route::get('/superadmin/drivers', [UserApprovalController::class, 'drivers'])
        ->name('superadmin.drivers');

    Route::get('/superadmin/users/pending', [UserApprovalController::class, 'index'])
        ->name('superadmin.users.pending');

    Route::post('/superadmin/users/{user}/approve', [UserApprovalController::class, 'approve'])
        ->name('superadmin.users.approve');

    Route::post('/superadmin/users/{user}/reject', [UserApprovalController::class, 'reject'])
        ->name('superadmin.users.reject');

    Route::resource('superadmin/ambulances', AmbulanceController::class);

    Route::get('/superadmin/assignments', [AssignmentController::class, 'index'])
        ->name('assignments.index');

    Route::get('/superadmin/assignments/create', [AssignmentController::class, 'create'])
        ->name('assignments.create');

    Route::post('/superadmin/assignments', [AssignmentController::class, 'store'])
        ->name('assignments.store');
});

Route::middleware([
    'auth',
    'approved',
    'role:super-admin'
])->prefix('superadmin')->group(function () {
    Route::get('/settings', [SystemSettingsController::class, 'index'])
        ->name('superadmin.settings');

    Route::post('/settings', [SystemSettingsController::class, 'update'])
        ->name('superadmin.settings.update');
});

Route::get(
    '/admin/incidents/history',
    [IncidentHistoryController::class, 'index']
)->name('admin.incidents.history');

require __DIR__ . '/auth.php';
