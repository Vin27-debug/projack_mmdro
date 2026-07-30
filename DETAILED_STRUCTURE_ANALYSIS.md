# MuniResQ Laravel Project - Detailed Structure Analysis
**Generated:** 2026-07-24

---

## EXECUTIVE SUMMARY

**Total Controllers:** 48  
**Total Models:** 16  
**Total Routes:** 101+  
**Middleware:** 1 (critically insufficient)  
**Policies:** 1 (incomplete implementation)  
**Form Requests:** 2 (inadequate coverage)  
**Key Status:** FUNCTIONAL but with SIGNIFICANT GAPS in production readiness

---

## 1. CONTROLLERS ANALYSIS

### 1.1 Controller Directory Structure
```
app/Http/Controllers/
├── Admin/ (21 files)
├── Auth/ (9 files)
├── Driver/ (11 files)
├── SuperAdmin/ (6 files)
├── Controller.php
└── ProfileController.php
```

### 1.2 Admin Controllers (21 files)
**Location:** [app/Http/Controllers/Admin/](app/Http/Controllers/Admin/)

| Controller | Methods | Key Findings |
|-----------|---------|---|
| AdminRegistrationController | create, store | ✓ Basic registration |
| AuditLogController | index | ✓ Simple list view |
| AutoDispatchController | dispatch | ⚠️ Limited implementation |
| DashboardController | index, counters, gpsLocations, liveCommandMapData, responseLoadAnalytics, situationOverview, fleetReadiness | ✓ Comprehensive but complex |
| DispatchController | index, assign | ❌ Missing: show, update, destroy, create |
| DriverPerformanceController | index, exportPdf, exportExcel | ✓ Report generation |
| GpsMonitoringController | index, locations, history | ✓ Real-time tracking |
| HijackAlertController | index | ✓ Alert listing |
| IncidentController | index, create, store, dispatchForm, dispatch | ⚠️ Incomplete CRUD |
| IncidentReportController | index, create, store, approve | ✓ Report workflow |
| NearestVehicleController | Resource controller | ⚠️ Using resource routing |
| NotificationController | index, markAllRead, markAsRead, unreadCount | ✓ Notification management |
| OperationsCenterController | index | ✓ Central command view |
| PanicAlertController | index | ✓ Alert listing |
| PdfReportController | downloadReport, viewReport | ✓ PDF generation |
| ReportsCenterController | index, exportPdf, exportExcel | ✓ Report hub |
| ReportsController | (minimal) | ❌ Incomplete |
| ResponseTimeAnalyticsController | (minimal) | ❌ Incomplete |
| ResponseTimeController | index | ⚠️ Limited analytics |
| VehicleMaintenanceController | index, create, store, edit, update, destroy, complete | ✓ Full CRUD |
| VehicleUtilizationController | index, create, store | ⚠️ Missing update/destroy |

**Key Issues - Admin Controllers:**
- ❌ No authorization checks beyond middleware role verification
- ❌ Inconsistent method implementation (some have full CRUD, others partial)
- ❌ Heavy database queries without pagination in many endpoints (e.g., DashboardController)
- ❌ Missing `show()` and `edit()` methods in several controllers
- ❌ No batch operations or bulk updates
- ⚠️ SQL injection risks in queries without proper parameter binding
- ⚠️ N+1 query problems (e.g., DashboardController loading full collections)

**Example Issue - DashboardController:**
[Line 20-100] Multiple query operations without pagination, causing memory issues with large datasets:
```php
// Loads ALL incidents without limit
$incidents = Incident::latest()->get();
```

### 1.3 Driver Controllers (11 files)
**Location:** [app/Http/Controllers/Driver/](app/Http/Controllers/Driver/)

| Controller | Methods | Status |
|-----------|---------|--------|
| DashboardController | index, acceptDispatch, declineDispatch, markEnRoute, markArrived, markCompleted | ✓ Complete |
| DriverAssignmentController | (needs review) | ? |
| DriverHistoryController | index | ✓ Minimal but functional |
| DriverRegistrationController | create, store | ✓ Registration workflow |
| DriverSettingsController | index | ✓ Settings view |
| GpsController | update | ✓ Location tracking |
| HijackController | trigger | ✓ Alert mechanism |
| IncidentReportController | create, store | ✓ Report submission |
| MyAssignmentController | index | ✓ View current assignment |
| NavigationController | show | ⚠️ Minimal implementation |
| PanicController | trigger | ✓ Emergency alert |

**Key Issues - Driver Controllers:**
- ⚠️ GpsController [Line 30] uses instanceof check instead of proper type hints
- ⚠️ IncidentReportController [Line 48-50] validates manually instead of using FormRequest
- ✓ Good authorization pattern with `$this->authorizeIncident()`
- ❌ Missing error responses for failed actions
- ❌ No rate limiting on critical actions (panic, hijack triggers)

### 1.4 SuperAdmin Controllers (6 files)
**Location:** [app/Http/Controllers/SuperAdmin/](app/Http/Controllers/SuperAdmin/)

| Controller | Methods | Status |
|-----------|---------|--------|
| AmbulanceController | (needs review) | ? |
| AssignmentController | (needs review) | ? |
| BackupController | index, create, restore | ⚠️ No delete method |
| DashboardController | index | ✓ Statistics view |
| SystemSettingsController | (needs review) | ? |
| UserApprovalController | index, approve, reject, ensureVehicleAssignment | ✓ User workflow |

**Key Issues - SuperAdmin Controllers:**
- ⚠️ UserApprovalController [Line 45-65] has complex logic without proper abstraction
- ✓ Good transaction handling for approvals
- ❌ No audit trail for approvals/rejections

### 1.5 Auth Controllers (9 files)
**Location:** [app/Http/Controllers/Auth/](app/Http/Controllers/Auth/)

**Standard Laravel Authentication (✓ Properly implemented):**
- AuthenticatedSessionController
- ConfirmablePasswordController
- EmailVerificationNotificationController
- EmailVerificationPromptController
- NewPasswordController
- PasswordController
- PasswordResetLinkController
- RegisteredUserController
- VerifyEmailController

**Key Implementation Details:**
- [AuthenticatedSessionController, Line 28-33] Proper role-based redirection after login
- ✓ Rate limiting on sensitive operations
- ✓ Proper session management

### 1.6 ProfileController
**Location:** [app/Http/Controllers/ProfileController.php](app/Http/Controllers/ProfileController.php)

- ✓ Uses FormRequest validation (ProfileUpdateRequest)
- ✓ Proper dirty checking for email changes
- ✓ Account deletion with password confirmation

---

## 2. MODELS ANALYSIS

### 2.1 Model Directory Structure & Count
**Location:** [app/Models/](app/Models/)

**Total Models:** 16
- Ambulance.php
- AmbulanceLocation.php
- AuditLog.php
- BackupLog.php
- Dispatch.php
- Driver.php
- GpsLocation.php
- HijackAlert.php
- Incident.php
- IncidentReport.php
- Notification.php
- PanicAlert.php
- SystemSetting.php ⚠️
- User.php
- VehicleDriverAssignment.php
- VehicleMaintenance.php

### 2.2 Detailed Model Analysis

#### User Model
**File:** [app/Models/User.php](app/Models/User.php)

**Properties:**
```php
✓ Fillable: name, email, password, badge_id, status, created_by, approved_by, approved_at
✓ Hidden: password, remember_token
✓ Casts: email_verified_at (datetime), password (hashed)
✓ Uses HasFactory, Notifiable, HasRoles (Spatie Permission)
```

**Relationships:**
- `driver()` → hasOne(Driver) - [Line 42]
- `notifications()` → hasMany(Notification) - [Line 46]

**Issues:**
- ❌ Missing timestamps property (should be `protected $timestamps = true` explicitly if needed)
- ❌ No SoftDeletes trait
- ⚠️ No scopes for common queries (pending users, approved users, etc.)
- ⚠️ `created_by`, `approved_by` fields but no relationships defined
- ❌ No mutators/accessors
- ❌ Missing `status` enum values documentation

#### Driver Model
**File:** [app/Models/Driver.php](app/Models/Driver.php)

**Properties:**
```php
✓ Fillable: user_id, badge_id, contact_number, license_number, license_expiry, status
```

**Relationships:**
- `user()` → belongsTo(User)
- `gpsLocations()` → hasMany(GpsLocation)
- `report()` → hasOne(IncidentReport) ⚠️ Ambiguous
- `reports()` → hasMany(IncidentReport)
- `incidentReports()` → hasMany(IncidentReport) ⚠️ Duplicate
- `dispatches()` → hasMany(Dispatch)
- `vehicleAssignments()` → hasMany(VehicleDriverAssignment)
- `activeVehicleAssignment()` → [incomplete in file]

**Issues:**
- ❌ Duplicate relationship definitions (`report()` and `reports()`, `reports()` and `incidentReports()`)
- ⚠️ `report()` singular might cause confusion
- ❌ Missing `activeVehicleAssignment()` implementation (partial read)
- ❌ No SoftDeletes
- ❌ No scopes for status filtering
- ⚠️ No license expiry validation

#### Ambulance Model
**File:** [app/Models/Ambulance.php](app/Models/Ambulance.php)

**Properties:**
```php
✓ Fillable: plate_number, vehicle_name, vehicle_type, status, latitude, longitude
```

**Relationships:**
- `maintenances()` → hasMany(VehicleMaintenance)
- `dispatches()` → hasMany(Dispatch, 'vehicle_id')
- `driverAssignments()` → hasMany(VehicleDriverAssignment)

**Issues:**
- ⚠️ Status field not explicitly validated/cast to enum
- ❌ No SoftDeletes
- ❌ No scopes
- ⚠️ GPS coordinates stored directly (should be in separate AmbulanceLocation table)
- ❌ Missing factory methods for testing
- ❌ No attribute casting for coordinate precision

#### Incident Model
**File:** [app/Models/Incident.php](app/Models/Incident.php)

**Properties:**
```php
✓ Fillable: incident_number, reporter_name, contact_number, incident_type, location, 
           description, latitude, longitude, status, driver_id, ambulance_id
```

**Relationships:**
- `ambulance()` → belongsTo(Ambulance)
- `driver()` → belongsTo(Driver)
- `dispatches()` → hasMany(Dispatch)
- `report()` → hasOne(IncidentReport)

**Issues:**
- ❌ No SoftDeletes (can't recover deleted incidents)
- ⚠️ Status field not explicitly validated as enum
- ❌ No scopes for common queries (active incidents, completed, etc.)
- ⚠️ Missing relationship with PanicAlert and HijackAlert
- ❌ No timestamps explicitly defined
- ❌ No automatic incident number generation (done in controller instead)

#### Dispatch Model
**File:** [app/Models/Dispatch.php](app/Models/Dispatch.php)

**Properties:**
```php
✓ Well-structured status constants
✓ Proper fillable properties
✓ Good datetime casting
```

**Relationships:**
- `incident()` → belongsTo(Incident)
- `driver()` → belongsTo(Driver)
- `ambulance()` → belongsTo(Ambulance, 'vehicle_id')
- `vehicle()` → belongsTo(Ambulance, 'vehicle_id') ⚠️ Duplicate

**Issues:**
- ⚠️ Duplicate relationships: `ambulance()` and `vehicle()` both reference Ambulance
- ❌ No scopes for filtering by status
- ❌ No event listeners for status changes
- ❌ Missing relationship to PanicAlert/HijackAlert

#### IncidentReport Model
**File:** [app/Models/IncidentReport.php](app/Models/IncidentReport.php)

**Properties:**
```php
✓ Table explicitly defined
✓ Fillable: incident_id, driver_id, summary, actions_taken, casualties, remarks, submitted_at, status
✓ Proper casting for submitted_at
```

**Relationships:**
- `incident()` → belongsTo(Incident)
- `driver()` → belongsTo(Driver)

**Issues:**
- ⚠️ Status values not documented/validated
- ❌ No SoftDeletes
- ❌ No scopes for approval workflow
- ❌ No relationship to User (reporter vs approver)

#### Other Models
**PanicAlert, HijackAlert, VehicleDriverAssignment, VehicleMaintenance, GpsLocation, Notification, AuditLog, AmbulanceLocation, BackupLog**

**Critical Issue - SystemSetting Model:**
📍 [app/Models/SystemSetting.php](app/Models/SystemSetting.php) - **FILE IS CORRUPTED**

The file contains Schema blueprint code mixed with model class, making it invalid PHP:
```php
// ❌ INVALID - Schema code inside Model file
Schema::create('system_settings', function (Blueprint $table) {
    $table->id();
    // ... table definition
});
```

**Status Summary:**

| Model | SoftDeletes | Casts | Scopes | Timestamps | Relationships |
|-------|------------|-------|--------|-----------|---|
| User | ❌ | ✓ Partial | ❌ | ✓ | ✓ |
| Driver | ❌ | ❌ | ❌ | ? | ✓ Duplicated |
| Ambulance | ❌ | ❌ | ❌ | ? | ✓ |
| Incident | ❌ | ❌ | ❌ | ? | ✓ |
| Dispatch | ❌ | ✓ | ❌ | ? | ✓ Duplicated |
| IncidentReport | ❌ | ✓ | ❌ | ? | ✓ |
| GpsLocation | ❌ | ❌ | ❌ | ? | ✓ |
| PanicAlert | ❌ | ✓ | ❌ | ? | ✓ |
| HijackAlert | ❌ | ✓ | ❌ | ? | ✓ |
| Others | ❌ | ? | ❌ | ? | ? |

**Critical Gaps:**
- ⚠️ ZERO use of SoftDeletes across entire application
- ⚠️ No scopes for filtering
- ⚠️ No events/observers
- ⚠️ Minimal casting (mainly datetime)
- ⚠️ Duplicate and redundant relationships
- ❌ SystemSetting.php is corrupted

---

## 3. ROUTES ANALYSIS

### 3.1 Route Files
- [routes/web.php](routes/web.php) - Main application routes
- [routes/auth.php](routes/auth.php) - Authentication routes
- ❌ Missing: routes/api.php (no API endpoints exposed)

### 3.2 Route Distribution

**Total Named Routes: ~101+**

#### Public Routes (Unauthenticated)
```
GET  /                    → welcome view
GET  /register            → auth.register
POST /register            → RegisteredUserController@store
GET  /login               → auth.login
POST /login               → AuthenticatedSessionController@store
GET  /forgot-password     → password.request
POST /forgot-password     → password.email
GET  /reset-password/{token} → password.reset
POST /reset-password      → password.store
```

#### Guest Routes (Only for non-authenticated)
```
GET  /driver/register      → driver registration form
POST /driver/register      → store driver registration
POST /driver/hijack        → trigger hijack alert (⚠️ NO AUTH!)
```

**🚨 CRITICAL SECURITY ISSUE:** Driver hijack endpoint accessible without authentication on [routes/web.php line 89-91]

#### Authenticated Routes (auth middleware)
```
GET  /dashboard            → generic dashboard
GET  /profile              → edit profile
PATCH /profile             → update profile
DELETE /profile            → delete account

GET  /verify-email         → email verification
GET  /verify-email/{id}/{hash}
POST /email/verification-notification
GET  /confirm-password     → password confirmation
```

#### Role-Protected Routes (auth + approved + role middleware)

**Driver Routes (role:driver)**
```
POST /driver/panic                              → panic trigger
POST /driver/dispatches/{dispatch}/accept       → accept dispatch
POST /driver/dispatches/{dispatch}/decline      → decline dispatch
GET  /driver/dashboard                          → driver dashboard
GET  /driver/my-assignment                      → current assignment
GET  /driver/incidents/{incident?}/report       → create report form
POST /driver/incidents/{incident?}/report       → store report
POST /driver/gps/update                         → update GPS location
POST /driver/incidents/{incident}/en-route      → mark en-route
POST /driver/incidents/{incident}/arrived       → mark arrived
POST /driver/incidents/{incident}/completed     → mark completed
GET  /driver/navigation                         → navigation view
GET  /driver/history                            → incident history
GET  /driver/settings                           → driver settings
```

**Admin Routes (role:admin|super-admin)**
```
// Dashboard & Analytics
GET /admin/dashboard                            → admin dashboard
GET /admin/dashboard/counters                   → stats API
GET /admin/dashboard/gps-locations              → GPS data API
GET /admin/dashboard/live-command-map           → live map data
GET /admin/dashboard/response-load-analytics    → analytics API
GET /admin/dashboard/situation-overview         → situation data
GET /admin/dashboard/fleet-readiness            → fleet status

// Monitoring
GET /admin/gps-monitoring                       → GPS map interface
GET /admin/gps-history                          → GPS history
GET /admin/gps-locations                        → GPS locations API
GET /admin/operations-center                    → operations interface

// Dispatch Management
GET /dispatch-center                            → dispatch interface
POST /dispatches/{incident}/assign              → assign dispatch
GET /admin/dispatch-center                      → duplicate route
GET /admin/dispatches                           → list dispatches
GET /admin/dispatches/{dispatch}                → show dispatch
// + full resource routing (PUT, DELETE, PATCH)

// Incident Management
GET /admin/incidents                            → list incidents
GET /admin/incidents/create                     → create form
POST /admin/incidents                           → store incident
GET /admin/incidents/{incident}/dispatch        → dispatch form
POST /admin/incidents/{incident}/dispatch       → dispatch action
POST /admin/incidents/{incident}/auto-dispatch  → auto-dispatch

// Reports & Analytics
GET /admin/reports/pdf                          → download PDF
GET /admin/reports/pdf/view                     → view PDF
GET /admin/reports/driver-performance           → performance report
GET /admin/reports/driver-performance/pdf       → export PDF
GET /admin/reports/driver-performance/excel     → export Excel
GET /admin/reports/response-time                → response time report
GET /admin/reports-center                       → reports hub
GET /admin/reports-center/export/pdf            → hub PDF export
GET /admin/reports-center/export/excel          → hub Excel export
GET /admin/incident-reports                     → incident reports
GET /admin/incident-reports/create              → create report form
POST /admin/incident-reports                    → store report
POST /admin/reports/{report}/approve            → approve report

// Alerts
GET /admin/panic-alerts                         → panic alerts list
GET /admin/hijack-alerts                        → hijack alerts list

// Vehicle Management
GET /admin/vehicle-utilization                  → utilization report
GET /admin/vehicle-utilization/create           → create utilization
POST /admin/vehicle-utilization                 → store utilization
GET /admin/vehicle-maintenance                  → maintenance list
GET /admin/vehicle-maintenance/create           → create maintenance
POST /admin/vehicle-maintenance                 → store maintenance
GET /admin/vehicle-maintenance/{vm}/edit        → edit maintenance
PUT /admin/vehicle-maintenance/{vm}             → update maintenance
DELETE /admin/vehicle-maintenance/{vm}          → delete maintenance
POST /admin/vehicle-maintenance/{vm}/complete   → mark completed

// Other Admin
GET /admin/nearest-vehicle/{incident}           → find nearest vehicle
GET /admin/notifications                        → notifications
GET /admin/notifications/unread-count           → unread count
POST /admin/notifications/read-all              → mark all read
POST /admin/notifications/{id}/read             → mark read
GET /admin/audit-logs                           → audit log view
GET /admin/backups                              → backups list
POST /admin/backups                             → create backup
POST /admin/backups/{backup}/restore            → restore backup
```

**SuperAdmin Routes (role:super-admin)**
```
GET /backups                                    → backups list
// (continues from admin routes)
```

### 3.3 Route Issues

| Issue | Severity | Location | Details |
|-------|----------|----------|---------|
| **Duplicate routes** | ⚠️ Medium | /dispatch-center & /admin/dispatch-center | Both map to DispatchController@index |
| **Missing auth on hijack** | 🚨 CRITICAL | /driver/hijack [L89] | No middleware, can be triggered by anyone |
| **No API routes** | ⚠️ Medium | N/A | No dedicated API endpoints, all web routes |
| **No versioning** | ⚠️ Low | N/A | No API versioning structure |
| **Inconsistent naming** | ⚠️ Low | Various | Some routes use hyphens, some underscores |
| **Missing PATCH route** | ⚠️ Medium | Dispatches | Only PUT defined, not PATCH |
| **Incomplete resource route** | ⚠️ Medium | nearestVehicle | Resource routing but incomplete implementation |
| **Missing show/edit** | ⚠️ Medium | Incidents, Dispatches | Some RESTful endpoints missing |
| **No route model binding** | ⚠️ Low | All routes with {id} | Using implicit binding but not consistently typed |
| **Unprotected admin endpoints** | ⚠️ Medium | Some GPS endpoints | Missing 'approved' middleware check |

### 3.4 Route Naming Consistency

**Issue:** Inconsistent route naming conventions
```php
✓ Consistent: admin.incidents.*, admin.dispatches.*
⚠️ Inconsistent: admin.panic vs admin.panic.index
⚠️ Inconsistent: driver.gps.update vs admin.gps.monitoring
```

---

## 4. MIDDLEWARE ANALYSIS

### 4.1 Middleware Directory
**Location:** [app/Http/Middleware/](app/Http/Middleware/)

**Total Custom Middleware: 1**

### 4.2 Implemented Middleware

#### EnsureUserApproved
**File:** [app/Http/Middleware/EnsureUserApproved.php](app/Http/Middleware/EnsureUserApproved.php)

```php
✓ Checks user is authenticated
✓ Verifies status === 'approved'
✓ Logs out unauthorized users
✓ Redirects with error message
```

**Issues:**
- ⚠️ Logs out user silently without notification
- ❌ No logging of rejection attempts
- ❌ Hard-coded status string (should use constant)

### 4.3 Critical Middleware Gaps

| Middleware | Status | Impact | Priority |
|-----------|--------|--------|----------|
| **CORS Middleware** | ❌ Missing | API calls fail cross-origin | HIGH |
| **Rate Limiting** | ❌ Missing | No protection against brute force | HIGH |
| **Request Logging** | ❌ Missing | No audit trail for requests | HIGH |
| **API Authentication** | ❌ Missing | No token/API key support | HIGH |
| **Encryption Middleware** | ❌ Missing | Sensitive data unencrypted | HIGH |
| **X-Frame-Options** | ❌ Missing | Clickjacking vulnerability | MEDIUM |
| **CSRF Protection** | ⚠️ Basic | Only form-based, no API protection | MEDIUM |
| **XSS Protection** | ⚠️ Basic | Default Blade escaping only | MEDIUM |
| **Request Validation** | ❌ Missing | No centralized validation middleware | MEDIUM |
| **Cache Headers** | ❌ Missing | No cache control strategy | LOW |

### 4.4 Available Laravel Middleware (Auto-Loaded)

From `bootstrap/app.php` (likely):
- Authentication
- Email Verification
- Rate Limiting (probably)
- CSRF Protection (form)
- Encryption
- TrustHosts
- TrustProxies

**⚠️ Note:** Actual middleware stack not visible in provided files

---

## 5. POLICIES ANALYSIS

### 5.1 Policies Directory
**Location:** [app/Policies/](app/Policies/)

**Total Policies: 1**

### 5.2 IncidentPolicy
**File:** [app/Policies/IncidentPolicy.php](app/Policies/IncidentPolicy.php)

```php
✗ ALL methods return false
✗ viewAny() → false
✗ view() → false
✗ create() → false
✗ update() → false
✗ delete() → false
✗ restore() → false
✗ forceDelete() → false
```

**Status:** ❌ **STUB IMPLEMENTATION - CURRENTLY BLOCKS ALL ACTIONS**

This policy prevents any Incident operations if authorization is actually checked. Currently appears to be bypassed in controllers.

### 5.3 Missing Policies

| Model | Policy | Status |
|-------|--------|--------|
| Incident | ✗ Returns false | ❌ Broken |
| Driver | ❌ Missing | - |
| Ambulance | ❌ Missing | - |
| Dispatch | ❌ Missing | - |
| IncidentReport | ❌ Missing | - |
| User | ❌ Missing | - |
| VehicleMaintenance | ❌ Missing | - |
| PanicAlert | ❌ Missing | - |
| HijackAlert | ❌ Missing | - |
| Notification | ❌ Missing | - |
| AuditLog | ❌ Missing | - |

### 5.4 Authorization Implementation Issues

**Current State:**
- ✓ Using role-based route middleware (`role:admin|driver`)
- ❌ No model-based authorization checks in controllers
- ❌ Policies not integrated into controller logic
- ❌ No use of `$this->authorize()` or `authorize()` helper
- ⚠️ Some controllers use `authorizeIncident()` method (custom implementation)

**Example - IncidentController:**
- No policy check when accessing incidents
- Relies only on route middleware

---

## 6. VALIDATION (FORM REQUESTS) ANALYSIS

### 6.1 Form Requests Directory
**Location:** [app/Http/Requests/](app/Http/Requests/)

**Total Form Request Classes: 2**

### 6.2 Implemented Form Requests

#### ProfileUpdateRequest
**File:** [app/Http/Requests/ProfileUpdateRequest.php](app/Http/Requests/ProfileUpdateRequest.php)

```php
✓ rules(): array {
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => [
            'required', 'string', 'lowercase', 'email',
            'max:255',
            Rule::unique(User::class)->ignore($this->user()->id),
        ],
    ];
}
✓ Used in: ProfileController@update
```

**Quality:** ✓ Good

#### LoginRequest
**File:** [app/Http/Requests/Auth/LoginRequest.php](app/Http/Requests/Auth/LoginRequest.php)

```php
✓ authorize(): bool { return true; }
✓ rules(): array {
    return [
        'email' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
    ];
}
✓ authenticate() method for custom auth logic
✓ Rate limiting with ensureIsNotRateLimited()
✓ Used in: AuthenticatedSessionController@store
```

**Quality:** ✓ Excellent (includes rate limiting)

### 6.3 Missing Validation

**Critical Gaps - No FormRequest Classes For:**

| Endpoint | Current Validation | Issue | Priority |
|----------|-------------------|-------|----------|
| Driver Registration | Controller inline | No reusability | HIGH |
| Admin Registration | ? | Not reviewed | HIGH |
| Incident Creation | Controller inline | No reusability, N+1 risk | HIGH |
| Dispatch Assignment | Controller inline | No reusability | HIGH |
| Incident Report | Controller inline | No reusability | HIGH |
| GPS Update | Controller inline [GpsController L18] | No validation actually | HIGH |
| Panic Trigger | Missing | No validation | MEDIUM |
| Hijack Trigger | Missing | No validation | MEDIUM |
| Maintenance Create/Update | Controller inline | No reusability | MEDIUM |
| Vehicle Utilization | ? | Not reviewed | MEDIUM |
| Report Approval | ? | Not reviewed | MEDIUM |

### 6.4 Validation Pattern Issues

**Example - IncidentController@store:**
```php
// ❌ BAD: Validation in controller
$request->validate([
    'reporter_name' => 'required|string|max:255',
    'contact_number' => 'nullable|string|max:255',
    // ... more rules
]);

// ✓ SHOULD BE: In FormRequest class
// app/Http/Requests/StoreIncidentRequest.php
```

**Example - GpsController@update:**
```php
// ❌ NO VALIDATION AT ALL!
$gpsLocation = GpsLocation::create([
    'driver_id' => $driver->id,
    'latitude' => $request->latitude,  // Not validated!
    'longitude' => $request->longitude, // Not validated!
]);
```

**Validation Gap Summary:**
- ✓ 2/15+ major endpoints have FormRequest classes (13%)
- ❌ 13+ endpoints lack proper validation
- ⚠️ GPS endpoint has ZERO validation
- ⚠️ Inconsistent validation approaches across app

---

## 7. BLADE VIEWS ANALYSIS

### 7.1 Views Directory Structure
**Location:** [resources/views/](resources/views/)

```
resources/views/
├── admin/
│   ├── analytics/
│   ├── audit-logs/
│   ├── dashboard.blade.php
│   ├── dispatches/
│   ├── driver-performance.blade.php
│   ├── gps-history.blade.php
│   ├── gps-monitoring.blade.php
│   ├── hijack-alerts.blade.php
│   ├── incidents/
│   │   ├── create.blade.php
│   │   ├── dispatch.blade.php
│   │   └── index.blade.php
│   ├── locations.blade.php
│   ├── maintenance/
│   ├── nearest-vehicle.blade.php
│   ├── notifications/
│   ├── operations-center.blade.php
│   ├── panic-alerts.blade.php
│   ├── pdf/
│   ├── register.blade.php
│   ├── reports/
│   ├── reports-center.blade.php
│   ├── response-time.blade.php
│   └── vehicle-utilization.blade.php
├── auth/
│   ├── register.blade.php
│   ├── login.blade.php
│   ├── forgot-password.blade.php
│   ├── reset-password.blade.php
│   ├── verify-email.blade.php
│   └── ... (others)
├── components/
├── dashboard.blade.php
├── driver/
│   ├── assignment/
│   ├── dashboard.blade.php
│   ├── history.blade.php
│   ├── navigation.blade.php
│   ├── register.blade.php
│   ├── reports/
│   └── settings.blade.php
├── layouts/
│   ├── admin.blade.php
│   ├── app.blade.php
│   ├── driver.blade.php
│   ├── guest.blade.php
│   ├── navigation.blade.php
│   └── superadmin.blade.php
├── profile/
├── superadmin/
├── welcome.blade.php
└── ... (others)
```

### 7.2 Layout Structure Analysis

#### Base Layout (app.blade.php)
**File:** [resources/views/layouts/app.blade.php](resources/views/layouts/app.blade.php) [Line 1-60 reviewed]

**Features:**
- ✓ Bootstrap 5.3.3 included
- ✓ Vite for asset compilation
- ✓ Responsive viewport meta tag
- ✓ Basic sidebar navigation

**Issues:**
- ⚠️ Hardcoded Bootstrap CDN link (not using Vite/npm version)
- ❌ No CSRF token meta tag visible
- ❌ No consistent error display component
- ⚠️ Navigation partially visible (truncated in review)

#### Admin Layout (admin.blade.php)
**Observed from routes/views:**
- Government-style color scheme (navy, gold, cream)
- Command center UI (eoc- prefix)
- Real-time dashboard panels
- ✓ Responsive grid layout

**Issues:**
- ⚠️ Inline CSS variables in templates
- ❌ No separate CSS file referenced

#### Driver Layout (driver.blade.php)
**Observed:**
- Mobile-first design (expected)
- Dashboard, assignment, history views

#### SuperAdmin Layout (superadmin.blade.php)
**Observed:**
- System management interface
- Approval workflows

### 7.3 Form Implementation Analysis

#### Incident Creation Form
**File:** [resources/views/admin/incidents/create.blade.php](resources/views/admin/incidents/create.blade.php)

**Features:**
```blade
✓ Leaflet map integration for location selection
✓ Form validation display structure
✓ CSRF protection (@csrf)
✓ Multi-field form with proper labels
```

**Issues:**
- ⚠️ Map library from CDN (no npm package management)
- ⚠️ Hardcoded coordinates (15.425 latitude) [Line 58]
- ❌ No error message display
- ⚠️ Inline JavaScript likely (not visible in excerpt)
- ❌ No client-side validation feedback
- ⚠️ form-control classes but Bootstrap version unclear in some views

### 7.4 Navigation Consistency

**File:** [resources/views/layouts/navigation.blade.php](resources/views/layouts/navigation.blade.php)

**Issues:**
- ✓ Central navigation component (reduces duplication)
- ⚠️ Three separate layout files (admin, driver, superadmin) suggest possible duplication

### 7.5 Error Handling in Views

**Current Implementation:**
- ✓ Form validation errors should display via Blade helpers
- ❌ No global error display observed
- ❌ No success message display component
- ❌ No flash message component

**Example Missing:**
```blade
{{-- ❌ NO ERROR COMPONENT --}}
@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
```

### 7.6 Responsive Design Assessment

**Framework:** Bootstrap 5.3.3
**Status:**
- ✓ Grid system available (col-md-2, col-md-4, etc.)
- ✓ Flexbox utilities
- ✓ Responsive utilities (d-md-none, etc.)

**Gaps:**
- ⚠️ Mobile design not fully reviewed
- ⚠️ No Tailwind (using Bootstrap)
- ⚠️ Admin views heavy with CSS (likely not optimized for mobile)
- ❌ Operations center might not be mobile-friendly

### 7.7 View Components

**Observed:**
- [resources/views/components/] - Directory exists but not detailed
- ⚠️ Likely Blade components for reusable UI
- ❌ Details not provided in analysis

### 7.8 View Issues Summary

| Issue | Severity | Impact |
|-------|----------|--------|
| No global error component | ⚠️ Medium | Duplicate error handling code |
| CDN-based libraries | ⚠️ Medium | CORS issues, offline problems |
| Hardcoded coordinates | ❌ High | Inflexible for different regions |
| Inconsistent layout files | ⚠️ Low | Code duplication risk |
| No success message component | ⚠️ Medium | User feedback inconsistent |
| Heavy inline styles | ⚠️ Medium | Hard to maintain |
| Mobile design unclear | ⚠️ Medium | May not be responsive |

---

## 8. DATABASE ANALYSIS

### 8.1 Migrations Overview

**Total Migrations: 27**

**Location:** [database/migrations/](database/migrations/)

### 8.2 Migration Timeline & Dependencies

```
1. 0001_01_01_000000 - create_users_table
2. 0001_01_01_000001 - create_cache_table
3. 0001_01_01_000002 - create_jobs_table
4. 2026_06_30_115123 - create_permission_tables (Spatie)
5. 2026_06_30_124213 - add_status_fields_to_users_table
6. 2026_06_30_133347 - create_drivers_table (FK: users)
7. 2026_07_01_034038 - create_ambulances_table
8. 2026_07_01_040935 - create_vehicle_driver_assignments_table (FK: drivers, ambulances)
9. 2026_07_01_043129 - create_incidents_table (FK: ambulances, drivers)
10. 2026_07_02_000000 - update_incident_statuses
11. 2026_07_02_022013 - change_incident_status_to_string
12. 2026_07_02_040509 - create_gps_locations_table (FK: drivers)
13. 2026_07_02_142312 - create_dispatches_table (FK: incidents, drivers, ambulances)
14. 2026_07_03_135037 - create_panic_alerts_table (FK: drivers)
15. 2026_07_04_095314 - create_incident_reports_table (FK: incidents, drivers)
16. 2026_07_04_110443 - create_hijack_alerts_table (FK: drivers)
17. 2026_07_04_115813 - create_vehicle_maintenances_table (FK: ambulances)
18. 2026_07_05_064020 - add_coordinates_to_incidents_table
19. 2026_07_05_082321 - add_status_to_drivers_table
20. 2026_07_05_122148 - add_coordinates_to_ambulances_table
21. 2026_07_06_071042 - create_notifications_table (FK: users)
22. 2026_07_06_085929 - create_audit_logs_table (FK: users)
23. 2026_07_08_000001 - update_dispatch_status_constraint
24. 2026_07_09_105343 - create_system_settings_table
25. 2026_07_10_000000 - create_backup_logs_table
26. 2026_07_11_070504 - create_ambulance_locations_table
```

### 8.3 Schema Design Analysis

#### Drivers Table
**File:** [database/migrations/2026_06_30_133347_create_drivers_table.php](database/migrations/2026_06_30_133347_create_drivers_table.php)

```php
Schema::create('drivers', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('badge_id')->unique();
    $table->string('contact_number');
    $table->string('license_number');
    $table->date('license_expiry');
    $table->timestamps();
});
```

**Analysis:**
- ✓ Foreign key with cascade delete
- ✓ Unique badge_id
- ✓ License expiry tracking
- ❌ No indexes on frequently queried columns (status added later)
- ⚠️ Phone number stored as string (should validate format)
- ❌ No soft deletes

#### Ambulances Table
**File:** [database/migrations/2026_07_01_034038_create_ambulances_table.php](database/migrations/2026_07_01_034038_create_ambulances_table.php)

```php
Schema::create('ambulances', function (Blueprint $table) {
    $table->id();
    $table->string('plate_number')->unique();
    $table->string('vehicle_name');
    $table->enum('vehicle_type', ['ambulance', 'rescue_van', 'fire_truck'])->default('ambulance');
    $table->enum('status', ['available', 'on_duty', 'maintenance'])->default('available');
    $table->timestamps();
});
```

**Analysis:**
- ✓ Unique plate number
- ✓ Enum fields for type/status
- ❌ Missing GPS coordinates in base table (added later separately) - design inconsistency
- ⚠️ Limited status values - later modified in updates
- ✓ Timestamps included

**Later migration adds:**
- `latitude` and `longitude` columns [2026_07_05_122148]

#### Incidents Table
**File:** [database/migrations/2026_07_01_043129_create_incidents_table.php](database/migrations/2026_07_01_043129_create_incidents_table.php)

```php
Schema::create('incidents', function (Blueprint $table) {
    $table->id();
    $table->string('incident_number')->unique();
    $table->string('reporter_name');
    $table->string('contact_number');
    $table->string('incident_type');
    $table->string('location');
    $table->text('description')->nullable();
    $table->enum('status', [...5 values...])->default('pending');
    $table->foreignId('ambulance_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
    $table->timestamps();
});
```

**Analysis:**
- ✓ Unique incident number
- ✓ Status enum
- ✓ Nullable foreign keys
- ✓ Cascade behavior defined
- ⚠️ 3 status migrations later (design not finalized upfront)
- ❌ No index on `incident_number` or `status` for searches
- ⚠️ Coordinates added separately later

#### Dispatches Table
**File:** [database/migrations/2026_07_02_142312_create_dispatches_table.php](database/migrations/2026_07_02_142312_create_dispatches_table.php)

```php
Schema::create('dispatches', function (Blueprint $table) {
    $table->id();
    $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
    $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
    $table->foreignId('vehicle_id')->nullable()->constrained('ambulances')->nullOnDelete();
    $table->enum('status', [7 values...])->default('pending');
    $table->timestamp('assigned_at')->nullable();
    $table->timestamp('accepted_at')->nullable();
    $table->timestamp('arrived_at')->nullable();
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});
```

**Analysis:**
- ✓ Good event tracking timestamps
- ✓ Proper enum for status
- ✓ Cascade constraints
- ❌ No composite index on (incident_id, driver_id, status) for queries
- ⚠️ vehicle_id nullable but should be required

#### GPS Locations Table
**File:** [database/migrations/2026_07_02_040509_create_gps_locations_table.php](database/migrations/2026_07_02_040509_create_gps_locations_table.php)

```php
// Status: NOT PROVIDED - assuming from model
$table->id();
$table->foreignId('driver_id')->constrained();
$table->decimal('latitude', 10, 7);
$table->decimal('longitude', 10, 7);
$table->timestamp('recorded_at');
```

**Analysis:**
- ✓ Decimal precision for GPS (7 decimals ≈ 1cm accuracy)
- ⚠️ No index on recorded_at for time-based queries
- ⚠️ No index on driver_id for driver tracking
- ❌ No pruning strategy for old records

#### Panic Alerts Table
**File:** [database/migrations/2026_07_03_135037_create_panic_alerts_table.php](database/migrations/2026_07_03_135037_create_panic_alerts_table.php)

```php
Schema::create('panic_alerts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('driver_id');
    $table->decimal('latitude', 10, 7);
    $table->decimal('longitude', 10, 7);
    $table->timestamp('triggered_at');
    $table->boolean('resolved')->default(false);
    $table->timestamps();
});
```

**Analysis:**
- ✓ GPS coordinates stored
- ⚠️ No foreign key constraint on driver_id (missing constrained())
- ❌ `resolved` boolean not referenced in model (model has 'status' field instead)
- ⚠️ Design mismatch: migration uses 'resolved', model expects 'status'

#### Hijack Alerts Table
**File:** [database/migrations/2026_07_04_110443_create_hijack_alerts_table.php](database/migrations/2026_07_04_110443_create_hijack_alerts_table.php)

```php
Schema::create('hijack_alerts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('driver_id');
    $table->decimal('latitude', 10, 7);
    $table->decimal('longitude', 10, 7);
    $table->string('status')->default('active');
    $table->timestamp('triggered_at');
    $table->timestamps();
});
```

**Analysis:**
- ❌ No foreign key constraint on driver_id
- ✓ Status string (matches model)
- ✓ Timestamps and GPS coordinates
- ⚠️ No index on status for filtering active alerts

#### System Settings Table
**File:** [database/migrations/2026_07_09_105343_create_system_settings_table.php](database/migrations/2026_07_09_105343_create_system_settings_table.php)

```php
Schema::create('system_settings', function (Blueprint $table) {
    $table->id();
    $table->timestamps();
});
```

**Analysis:**
- ❌ **EMPTY TABLE** - No actual settings columns!
- ❌ Mismatch with corrupted SystemSetting model file
- ⚠️ Useless implementation
- ✓ Only has id and timestamps

**Note:** The model file has schema code in it, but migration is empty!

#### Ambulance Locations Table
**File:** [database/migrations/2026_07_11_070504_create_ambulance_locations_table.php](database/migrations/2026_07_11_070504_create_ambulance_locations_table.php)

```php
Schema::create('ambulance_locations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ambulance_id');
    $table->decimal('latitude', 10, 7);
    $table->decimal('longitude', 10, 7);
    $table->timestamps();
});
```

**Analysis:**
- ⚠️ Separate table for ambulance locations (duplicates with ambulances.latitude/longitude)
- ❌ No foreign key constraint (missing constrained())
- ❌ Design confusion: are locations in ambulances table or here?
- ⚠️ No composite index on (ambulance_id, created_at)

### 8.4 Indexing Analysis

| Table | Indexes Defined | Recommended Indexes | Missing |
|-------|-----------------|-------------------|---------|
| drivers | PK, UK (badge_id) | status, user_id, license_expiry | ⚠️ 3 |
| ambulances | PK, UK (plate_number) | status, vehicle_type | ⚠️ 2 |
| incidents | PK, UK (incident_number) | status, incident_type, (driver_id, status), created_at | ⚠️ 4 |
| dispatches | PK, FK (3), UK* | (incident_id, driver_id), status, created_at | ⚠️ 3 |
| drivers.status | - | Need index | ❌ |
| gps_locations | PK, FK (implied) | (driver_id, recorded_at), recorded_at | ⚠️ 2 |
| panic_alerts | PK | (driver_id, status), triggered_at | ⚠️ 2 |
| hijack_alerts | PK | (driver_id, status), triggered_at | ⚠️ 2 |
| audit_logs | PK, FK (user_id) | (user_id, created_at), action, module | ⚠️ 3 |

**Overall:** ~22 missing indexes for optimal query performance

### 8.5 Foreign Key Analysis

**Orphaned Foreign Keys:**
```
❌ panic_alerts.driver_id - NO constrained() call
❌ hijack_alerts.driver_id - NO constrained() call
❌ ambulance_locations.ambulance_id - NO constrained() call
```

**Cascade Behavior:**
```
✓ drivers → users (CASCADE DELETE)
✓ dispatches → incidents (CASCADE DELETE)
✓ dispatches → drivers (CASCADE DELETE)
✓ incident_reports → incidents (CASCADE DELETE)
✓ incident_reports → drivers (CASCADE DELETE)

⚠️ ambulances → incidents (NULL ON DELETE) - may leave orphans
⚠️ ambulances → dispatches (NULL ON DELETE) - data loss possible
```

### 8.6 Data Type Issues

| Column | Type | Issue | Impact |
|--------|------|-------|--------|
| contact_number (drivers, incidents) | string | No format validation | ⚠️ Medium |
| license_number (drivers) | string | No format validation | ⚠️ Low |
| incident_number | string | Generated in code, not DB | ⚠️ Medium |
| gps coordinates | decimal(10,7) | Good precision | ✓ |
| status fields | enum/string | Inconsistent (enum vs string) | ⚠️ Medium |

### 8.7 Design Issues Summary

| Issue | Severity | Quantity | Impact |
|-------|----------|----------|--------|
| **Missing Indexes** | ⚠️ High | 22+ | Slow queries on large datasets |
| **Orphaned FKs** | ❌ High | 3 | Data integrity risk |
| **Duplicate Columns** | ⚠️ Medium | GPS in ambulances + ambulance_locations | Data sync issues |
| **Empty Settings Table** | ❌ High | 1 | Completely non-functional |
| **Inconsistent Status** | ⚠️ Medium | enum vs string | Code confusion |
| **No Soft Deletes** | ⚠️ High | All tables | Can't recover data |
| **No Composite Indexes** | ⚠️ High | All tables | Query performance |
| **Migration Iterations** | ⚠️ Medium | 3 incident status changes | Schema churn |

---

## CRITICAL FINDINGS SUMMARY

### 🚨 CRITICAL ISSUES (Fix Immediately)

1. **Security: Unprotected Hijack Endpoint** [routes/web.php L89]
   - `/driver/hijack` accessible without authentication
   - Impact: Anyone can trigger hijack alerts

2. **Data Corruption: SystemSetting Model** [app/Models/SystemSetting.php]
   - File contains schema blueprint code
   - Invalid PHP syntax
   - Model unusable

3. **Missing Validation: GPS Endpoint** [app/Http/Controllers/Driver/GpsController.php]
   - No input validation on coordinates
   - Accepts invalid/null values
   - Can cause map rendering errors

4. **Empty Table: system_settings** [database/migrations/2026_07_09_105343_create_system_settings_table.php]
   - Only has id and timestamps
   - No actual settings implementation
   - Completely non-functional

5. **Broken Policy: IncidentPolicy** [app/Policies/IncidentPolicy.php]
   - All methods return false
   - Currently blocks all actions
   - Or being completely ignored (no enforcement)

### ⚠️ HIGH PRIORITY ISSUES

6. **Route Hijacking Risk** [routes/web.php L89-91]
   - `POST /driver/hijack` before auth routes
   - Middleware ordering issue
   - Can be exploited

7. **22+ Missing Database Indexes**
   - Query performance degradation
   - Large datasets become slow
   - No composite indexes for common queries

8. **3 Orphaned Foreign Keys**
   - panic_alerts.driver_id
   - hijack_alerts.driver_id  
   - ambulance_locations.ambulance_id
   - Data integrity risk

9. **GPS Data Duplication**
   - ambulances.latitude/longitude
   - ambulance_locations table
   - Sync issues likely

10. **No Input Validation on Critical Actions**
    - GPS updates (no validation)
    - Panic triggers (no validation)
    - Hijack triggers (no validation)

### ⚠️ MEDIUM PRIORITY ISSUES

11. **Inconsistent Relationship Names** [Models]
    - Driver: `report()` vs `reports()` vs `incidentReports()`
    - Dispatch: `ambulance()` vs `vehicle()`
    - Causes confusion in codebase

12. **Zero SoftDeletes**
    - No data recovery possible
    - Audit trails incomplete
    - Risk of accidental deletion losses

13. **Minimal Middleware**
    - No CORS support
    - No rate limiting
    - No request logging
    - No encryption enforcement

14. **Only 2 Form Request Classes**
    - 13+ endpoints with inline validation
    - Code duplication
    - Inconsistent error messages

15. **Incomplete Policies**
    - 1 policy (all returning false)
    - 15 models with no policies
    - No authorization checks in controllers

16. **Duplicate Routes**
    - `/dispatch-center` and `/admin/dispatch-center`
    - `/admin/gps-monitoring` and duplicate
    - Confusing navigation

---

## RECOMMENDATIONS PRIORITY MATRIX

```
IMMEDIATE (Today):
├─ Fix hijack endpoint authentication
├─ Fix SystemSetting model corruption
├─ Add GPS validation
└─ Fix empty system_settings table

THIS WEEK:
├─ Add missing database indexes
├─ Add foreign key constraints
├─ Consolidate relationship names
├─ Add SoftDeletes to all models
└─ Create missing Form Requests

THIS MONTH:
├─ Implement all Policies
├─ Add rate limiting middleware
├─ Remove CDN dependencies
├─ Add error handling components
└─ Add request logging

NEXT MONTH:
├─ API route structure
├─ WebSocket support for real-time
├─ Comprehensive test suite
├─ Performance optimization
└─ Security audit
```

---

## FILE REFERENCE QUICK LINKS

**Controllers:** 48 total
- [app/Http/Controllers/Admin/](app/Http/Controllers/Admin/) - 21 files
- [app/Http/Controllers/Driver/](app/Http/Controllers/Driver/) - 11 files
- [app/Http/Controllers/SuperAdmin/](app/Http/Controllers/SuperAdmin/) - 6 files
- [app/Http/Controllers/Auth/](app/Http/Controllers/Auth/) - 9 files
- [app/Http/Controllers/ProfileController.php](app/Http/Controllers/ProfileController.php)

**Models:** 16 total
- [app/Models/](app/Models/) - All models

**Routes:** 
- [routes/web.php](routes/web.php) - Main routes
- [routes/auth.php](routes/auth.php) - Auth routes

**Middleware:**
- [app/Http/Middleware/EnsureUserApproved.php](app/Http/Middleware/EnsureUserApproved.php)

**Policies:**
- [app/Policies/IncidentPolicy.php](app/Policies/IncidentPolicy.php)

**Validation:**
- [app/Http/Requests/ProfileUpdateRequest.php](app/Http/Requests/ProfileUpdateRequest.php)
- [app/Http/Requests/Auth/LoginRequest.php](app/Http/Requests/Auth/LoginRequest.php)

**Migrations:** 27 total
- [database/migrations/](database/migrations/)

**Views:**
- [resources/views/](resources/views/)

---

**End of Analysis**
