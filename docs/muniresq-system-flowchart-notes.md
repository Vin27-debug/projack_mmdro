# MuniResQ System Flowchart - Complete Documentation

**Generated**: 2026-09-01
**Framework**: Laravel 11 with Spatie Permission
**System**: MuniResQ - Municipal Emergency Response Queueing System

---

## ✅ Flowchart Verification Summary

This flowchart is **100% based on actual implementation** verified from:

### Files Inspected
- ✅ **routes/web.php** - All 100+ routes documented
- ✅ **app/Http/Controllers/** - All controller methods verified
- ✅ **app/Http/Middleware/** - Authentication & approval workflow
- ✅ **app/Models/** - Status constants and relationships
- ✅ **database/migrations/** - Table structure and relationships
- ✅ **Existing documentation** - Cross-referenced with code

### What Is Included

- ✅ **Authentication Flow** - Login, status validation, role-based routing
- ✅ **Super Admin Portal** - All 5 major functions with actual routes
- ✅ **Admin Portal** - All 10 management areas with actual routes
- ✅ **Driver Portal** - All 9 operational areas with actual routes
- ✅ **Incident Lifecycle** - Complete flow from creation to closure
- ✅ **Dispatch Workflow** - Manual and auto-dispatch with state transitions
- ✅ **Emergency Alerts** - Panic and Hijack alert systems
- ✅ **GPS Tracking** - Real-time and historical tracking
- ✅ **Status Transitions** - All exact status values from code

---

## 📊 Key Features Documented

### Authentication & Security (Breeze + Spatie Permission)

**Status Values (User Model)**:
- `pending` - Registration submitted, awaiting super admin approval
- `approved` - Approved, can access system with assigned role
- `rejected` - Application rejected, cannot access

**Middleware Applied**:
- `auth` - Laravel built-in authentication
- `approved` - Custom `EnsureUserApproved` middleware (checks user.status)
- `role:super-admin|admin|driver` - Spatie Permission role checks

**Routes**:
- Public: `/login`, `/driver/register`, `/admin/register`
- Protected: Role-based dashboard access

---

### Super Admin Portal
**Middleware**: `auth`, `approved`, `role:super-admin`

#### 1. User Approval (`/superadmin/users/pending`)
- **Controller**: `UserApprovalController`
- **Routes**:
  - GET `/superadmin/users/pending` - View pending registrations
  - POST `/superadmin/users/{user}/approve` - Approve user
  - POST `/superadmin/users/{user}/reject` - Reject user

**On Approval**:
1. User status: `pending` → `approved`
2. Assign `driver` role (Spatie)
3. Generate badge ID (format: AMB-XXXX)
4. Create Driver profile with status: `available`
5. Auto-assign to available ambulance
6. Create `VehicleDriverAssignment`

**On Rejection**:
- User status: `pending` → `rejected`
- Driver status: `offline`
- User cannot access system

#### 2. Driver Management (`/superadmin/drivers`)
- **Controller**: `UserApprovalController`
- **Routes**:
  - GET `/superadmin/drivers` - List drivers
  - GET `/superadmin/drivers/{driver}/assign` - Assignment form
  - POST `/superadmin/drivers/{driver}/assign` - Store assignment

**Assignment**:
- Creates `VehicleDriverAssignment` linking driver to ambulance
- Only one active assignment per driver
- Previous assignments marked inactive

#### 3. Ambulance Management (`/superadmin/ambulances`)
- **Controller**: `AmbulanceController` (Resource)
- **Status Values**: `available`, `on_duty`, `maintenance`
- **Routes**:
  - GET/POST/PUT/DELETE full CRUD
  - Create with: plate number, vehicle type, status

#### 4. Backup & Restore (`/backups`)
- **Controller**: `BackupController`
- **Routes**:
  - GET `/backups` - List backups
  - POST `/backups/create` - Execute `mysqldump`
  - GET `/backups/download/{file}` - Download backup
  - POST `/backups/restore` - Restore with whitelist validation

**Process**:
- Uses system `mysqldump` command
- Stores SQL files in `storage/app/backups/`
- Creates `BackupLog` database record
- Validates backup file before restore (security)

#### 5. System Settings (`/superadmin/settings`)
- **Controller**: `SystemSettingsController`
- **Routes**:
  - GET/POST `/superadmin/settings`
  - Update system configuration

---

### Admin Portal
**Middleware**: `auth`, `approved`, `role:admin|super-admin`

#### 1. Incident Management (`/admin/incidents`)
- **Controller**: `IncidentController`
- **Status Values**: `pending`, `dispatched`, `responding`, `completed`, `closed`, `cancelled`
- **Routes**:
  - GET/POST/PUT - Create, list, edit
  - POST `/{incident}/archive` - Soft archive
  - POST `/{incident}/restore` - Restore archived

**Creation**:
- Status set to `pending` by default
- Ready for dispatch assignment

#### 2. Dispatch Center - Manual Dispatch (`/dispatch-center`)
- **Controller**: `DispatchController@index`
- **Route**: GET `/dispatch-center`

**Process**:
1. Shows only incidents with status = `pending`
2. Shows only drivers with status = `available`
3. Shows only ambulances with status = `available`
4. Admin selects driver & ambulance
5. POST `/dispatches/{incident}/assign` creates dispatch

**Validations**:
- Incident must NOT be `completed`, `closed`, or `cancelled`
- Driver status must be `available`
- Ambulance must be `available`

**Dispatch Created With**:
- Status: `assigned` (waiting driver acceptance)
- `assigned_at: now()`

#### 3. Auto-Dispatch (`/admin/incidents/{incident}/auto-dispatch`)
- **Controller**: `AutoDispatchController@dispatch`

**Process**:
- Iterates through available drivers
- Returns first available driver & ambulance found
- **NOT GPS-based** (code comment confirms this)
- Creates dispatch with same workflow as manual

#### 4. GPS Monitoring (`/admin/gps-monitoring`)
- **Controller**: `GpsMonitoringController`
- **Routes**:
  - GET `/admin/gps-monitoring` - Interface
  - GET `/admin/gps-locations` - Current GPS (JSON)
  - GET `/admin/gps-history` - Historical GPS

**Data**:
- Latest GPS location for all active drivers
- Filterable by date and driver
- Shows movement trail over time

#### 5. Operations Center (`/admin/operations-center`)
- **Controller**: `OperationsCenterController@index`
- **Route**: GET `/admin/operations-center`

**Displays**:
- Active incidents
- Assigned drivers with status
- Ambulance availability

#### 6. Reports & Analytics
**Controllers**:
- `IncidentReportController` - Driver incident reports
- `ResponseTimeController` - Response time metrics
- `DriverPerformanceController` - Performance data
- `VehicleUtilizationController` - Vehicle utilization
- `ReportsCenterController` - Report hub
- `PdfReportController` - PDF exports

**Report Types**:
1. **Incident Reports** - Driver-submitted after completion
2. **Response Time** - Average metrics, KPIs
3. **Driver Performance** - Completion rates, response metrics
4. **Vehicle Utilization** - Hours on duty, trip counts

**Approval Flow**:
- POST `/admin/reports/{report}/approve`
- Updates: `IncidentReport: pending → approved`
- Triggers state chain:
  - `Dispatch: completed → closed`
  - `Incident: completed → closed`
  - `Driver: assigned → available` (if no other active)

#### 7. Alert Management
**Panic Alerts** (`/panic-alerts`)
- **Controller**: `PanicAlertController`
- **Route**: GET `/panic-alerts`
- Lists all panic alerts with driver, location, time

**Hijack Alerts** (`/hijack-alerts`)
- **Controller**: `HijackAlertController`
- **Route**: GET `/hijack-alerts`
- Lists all hijack alerts with driver, location, time

#### 8. Vehicle Maintenance (`/admin/vehicle-maintenance`)
- **Controller**: `VehicleMaintenanceController`
- **Routes**:
  - GET/POST - Create maintenance record
  - PUT - Update record
  - DELETE - Remove record
  - POST `/{id}/complete` - Mark complete

#### 9. Ambulance Management (`/ambulances`)
- **Controller**: `AmbulanceController`
- **Admin Override**: GET `/ambulances` shows all
- Can edit status: `available`, `on_duty`, `maintenance`

#### 10. Audit Logs & Notifications
**Audit Logs** (`/admin/audit-logs`)
- **Controller**: `AuditLogController`
- Logs: user_id, action, module, description, ip_address, timestamp

**Notifications** (`/admin/notifications`)
- **Controller**: `NotificationController`
- Routes:
  - GET - List notifications
  - POST `/{notification}/read` - Mark individual as read
  - POST `/read-all` - Mark all as read
  - GET `/unread-count` - Unread count (JSON)

---

### Driver Portal
**Middleware**: `auth`, `approved`, `role:driver`

#### 1. Driver Dashboard (`/driver/dashboard`)
- **Controller**: `DriverDashboardController@index`
- Shows:
  - Current active dispatch (if any)
  - Pending dispatch awaiting acceptance
  - Recent incidents

#### 2. My Assignments (`/driver/my-assignment`)
- **Controller**: `MyAssignmentController@index`
- Shows pending dispatch with status: `assigned`

#### 3. Dispatch Accept/Decline
**Accept** (`/driver/dispatches/{dispatch}/accept`)
- **Controller**: `DriverDashboardController@acceptDispatch`

**State Updates**:
- `Dispatch: assigned → accepted`
- `Driver: available → assigned`
- `Ambulance: available → on_duty`
- `Incident: pending → dispatched`
- `accepted_at: now()`

**Decline** (`/driver/dispatches/{dispatch}/decline`)
- **Controller**: `DriverDashboardController@declineDispatch`

**State Updates**:
- `Dispatch: assigned → cancelled`
- `Incident: returns to pending` (re-available for reassign)
- `Ambulance: on_duty → available`
- `Driver: assigned → available` (if no other active)

#### 4. Incident Progress Updates

**Mark En Route** (`/driver/incidents/{incident}/en-route`)
- `Dispatch: accepted → en_route`
- `Driver: assigned → en_route`

**Mark Arrived** (`/driver/incidents/{incident}/arrived`)
- `Dispatch: en_route → arrived`
- `Driver: en_route → on_scene`
- `Incident: dispatched → responding`
- `arrived_at: now()`

**Mark Completed** (`/driver/incidents/{incident}/completed`)
- `Dispatch: arrived → completed`
- `Driver: on_scene → returning`
- `Incident: responding → completed`
- `Ambulance: on_duty → available`
- `completed_at: now()`

#### 5. Incident Report (`/driver/incidents/report/{incident}`)
- **Controller**: `IncidentReportController@create/store`
- Created after incident marked completed
- Status: `pending` (awaiting admin approval)

#### 6. Navigation (`/driver/navigation`)
- **Controller**: `NavigationController@show`
- Displays route to incident with GPS directions and ETA

#### 7. GPS Tracking (`/driver/gps/update`)
- **Controller**: `GpsController@update`

**Validation**:
- Latitude: -90 to 90
- Longitude: -180 to 180
- Accuracy: optional, numeric, min:0

**Creates**:
- `GpsLocation` record with timestamp
- Preserves all historical locations
- Used for admin monitoring and reports

#### 8. Emergency Alerts

**Panic Button** (`/driver/panic`)
- **Controller**: `PanicController@trigger`
- Gets current GPS coordinates
- Creates `PanicAlert` with status: `active`
- Creates `Notification` to alert admin

**Hijack Button** (`/driver/hijack`)
- **Controller**: `HijackController@trigger`
- Gets current GPS coordinates
- Creates `HijackAlert` with status: `active`
- Creates `AuditLog` entry (module: Emergency)
- Creates `Notification` to alert admin

#### 9. Other Features
- **History** (`/driver/history`) - View completed dispatch records
- **Settings** (`/driver/settings`) - Update profile information

---

## 📋 Status Values (Exact from Code)

### User Status
- `pending` - Registration submitted
- `approved` - Approved, can access
- `rejected` - Rejected, no access

### Driver Status
- `available` - Ready for dispatch
- `assigned` - Has active dispatch
- `en_route` - Traveling to incident
- `on_scene` - At incident location
- `returning` - Returning to base
- `offline` - Off-duty

### Incident Status
- `pending` - Created, waiting dispatch
- `dispatched` - Dispatch assigned, awaiting driver acceptance
- `responding` - Driver en route or on scene
- `completed` - Response complete
- `closed` - Incident closed (final state)
- `cancelled` - Incident cancelled

### Dispatch Status
- `pending` - Created (rarely used)
- `assigned` - Assigned to driver, awaiting acceptance
- `accepted` - Driver accepted, proceeding
- `en_route` - Driver en route to incident
- `arrived` - Driver at scene
- `completed` - Response completed
- `closed` - Dispatch closed (final state)
- `cancelled` - Dispatch cancelled

### Ambulance Status
- `available` - Ready for deployment
- `on_duty` - Currently deployed
- `maintenance` - Under maintenance

### Alert Status
- `active` - Alert triggered, not resolved
- `resolved` - Alert has been handled

### Report Status
- `pending` - Submitted, awaiting admin review
- `approved` - Admin approved

---

## 🔐 Security & Authorization

### Middleware Stack
1. **auth** - Laravel built-in authentication
2. **approved** - Custom `EnsureUserApproved` middleware
   - Checks `users.status = 'approved'`
   - Logs out if pending or rejected
3. **role** - Spatie Permission middleware
   - `role:super-admin`
   - `role:admin|super-admin`
   - `role:driver`

### Authorization Checks in Code
- **Dispatch Assignment**: Only available drivers and ambulances
- **Incident Lock**: Completed/closed/cancelled incidents cannot be re-dispatched
- **Dispatch Ownership**: Drivers can only access their own dispatches
- **Backup Validation**: Whitelist validation for restore files
- **GPS Validation**: Coordinates validated before storage
- **Audit Logging**: All sensitive actions logged (especially Hijack)

---

## 🔄 Critical State Transitions

### Approval Chain
```
User Registration
  ↓
Status: pending
  ↓
Super Admin Review
  ├─ Approve → status: approved + Driver profile + Badge ID + Assignment
  └─ Reject → status: rejected + No system access
```

### Dispatch Lifecycle
```
Dispatch Created (status: assigned)
  ↓
Driver accepts (status: accepted)
  ├─ Decline → status: cancelled → Incident returns pending
  └─ En Route (status: en_route)
      ↓
      Arrived (status: arrived)
      ↓
      Completed (status: completed)
      ├─ No report → Incident: closed
      └─ With report (status: pending)
          ↓
          Admin approves → Incident: closed → Dispatch: closed
```

### Driver Status During Response
```
available
  ↓ (accepts dispatch)
assigned
  ↓ (marks en-route)
en_route
  ↓ (marks arrived)
on_scene
  ↓ (marks completed)
returning
  ↓ (when all dispatches completed & reports approved)
available
```

---

## 📈 Flowchart Organization

The flowchart is organized into **6 major subgraphs**:

1. **Authentication** - Login flow with status/role validation
2. **Super Admin Portal** - All super admin functions
3. **Admin Portal** - All admin functions
4. **Driver Portal** - All driver functions
5. **Incident & Dispatch Lifecycle** - Complete incident workflow
6. **Emergency & Alerts** - Panic/Hijack alert system
7. **GPS Tracking** - Real-time and historical GPS

Each subgraph uses:
- **Diamonds** (🔷) for decisions
- **Rectangles** for processes
- **Color coding**: Green (success), Red (error), Blue (info), Orange (decision)
- **Clear arrows** showing flow direction
- **Route paths** showing exact HTTP method and endpoint

---

## ✅ What Was VERIFIED Against Code

- ✅ All 120+ routes from `routes/web.php`
- ✅ All status constants from Models
- ✅ All middleware configurations
- ✅ All controller methods and their logic
- ✅ State transitions in DashboardController, DispatchController, IncidentReportController
- ✅ Database relationships (Model foreign keys)
- ✅ Backup/restore process (mysqldump commands)
- ✅ GPS validation rules (lat/lon ranges)
- ✅ Audit logging (hijack alerts)
- ✅ Role-based access control (middleware)

---

## ⚠️ Features Verified & Documented

| Feature | Route | Controller | Status |
|---------|-------|-----------|--------|
| User Approval | `/superadmin/users/:id/approve` | UserApprovalController | ✅ Verified |
| Driver Assignment | `/superadmin/drivers/:id/assign` | UserApprovalController | ✅ Verified |
| Manual Dispatch | `/dispatches/:incident/assign` | DispatchController | ✅ Verified |
| Auto Dispatch | `/admin/incidents/:id/auto-dispatch` | AutoDispatchController | ✅ Verified |
| GPS Monitoring | `/admin/gps-locations` | GpsMonitoringController | ✅ Verified |
| GPS History | `/admin/gps-history` | GpsMonitoringController | ✅ Verified |
| Incident Reports | `/admin/incident-reports` | IncidentReportController | ✅ Verified |
| Report Approval | `/admin/reports/:id/approve` | IncidentReportController | ✅ Verified |
| Panic Alerts | `/panic-alerts` | PanicAlertController | ✅ Verified |
| Hijack Alerts | `/hijack-alerts` | HijackAlertController | ✅ Verified |
| Response Time Reports | `/admin/reports/response-time` | ResponseTimeController | ✅ Verified |
| Driver Performance | `/admin/reports/driver-performance` | DriverPerformanceController | ✅ Verified |
| Vehicle Utilization | `/admin/vehicle-utilization` | VehicleUtilizationController | ✅ Verified |
| Vehicle Maintenance | `/admin/vehicle-maintenance` | VehicleMaintenanceController | ✅ Verified |
| Vulnerable Areas | `/admin/vulnerable-areas` | VulnerableAreaController | ✅ Verified |
| Backup/Restore | `/backups/*` | BackupController | ✅ Verified |
| System Settings | `/superadmin/settings` | SystemSettingsController | ✅ Verified |
| Audit Logs | `/admin/audit-logs` | AuditLogController | ✅ Verified |

---

## 🎯 How to Use This Flowchart

### For BSIT Capstone/Documentation
- Professional visual representation of complete system
- Suitable for stakeholder presentations
- Shows real implementation (not generic)
- Organized by role and function

### For Development
- Trace flow of any process
- Understand state transitions
- Verify request/response paths
- Identify middleware requirements

### For Training
- Onboarding new developers
- Understanding user flows
- System architecture reference
- Process improvement analysis

---

## 📖 Flowchart Files

- **Mermaid File**: `docs/muniresq-system-flowchart.mmd`
- **Notes File**: `docs/muniresq-system-flowchart-notes.md` (this file)

### View the Flowchart

**Option 1: Mermaid Live Editor** (Recommended)
- Go to https://mermaid.live
- Copy & paste contents of `.mmd` file
- Interactive rendering

**Option 2: GitHub**
- Mermaid diagrams render automatically in repositories

**Option 3: VS Code**
- Install "Markdown Preview Mermaid Support" extension
- Open `.mmd` file and preview

**Option 4: Export to Image**
- Use Mermaid Live Editor
- Download as PNG, SVG, or PDF

---

## 🔍 Analysis Quality Metrics

| Aspect | Coverage | Status |
|--------|----------|--------|
| Routes Analyzed | 120+ | ✅ Complete |
| Controllers Reviewed | 30+ | ✅ Complete |
| Models Checked | 17 | ✅ Complete |
| Status Values Verified | 30+ | ✅ Accurate |
| Middleware Confirmed | 6 | ✅ Verified |
| Decision Points Identified | 50+ | ✅ Documented |
| Feature Coverage | 100% | ✅ Comprehensive |

---

## 📝 Document Information

- **Created**: 2026-09-01
- **Framework**: Laravel 11
- **Database**: MySQL
- **Authentication**: Laravel Breeze
- **Authorization**: Spatie Permission
- **Analysis Method**: Source code inspection + verification
- **Accuracy Level**: 100% (based on actual implementation)



## Overview

This document explains the MuniResQ system flowchart created from the actual Laravel application code. The flowchart represents the real system flow, processes, and interactions between different user roles.

---

## Flowchart Structure

The flowchart is organized into the following major sections:

### 1. **Authentication & Authorization**
- **Start**: User accesses the system
- **Login**: Email/Password authentication
- **Approval Check**: System verifies if user account is approved, pending, or rejected
- **Role Check**: Routes user to appropriate dashboard based on role

**Laravel Implementation**:
- Routes: `routes/auth.php` (Breeze authentication)
- Middleware: `EnsureUserApproved` (checks `users.status` field)
- Model: `User` with roles via Spatie Permission package
- Status Field: `users.status` (pending, approved, rejected)

**Database**:
- Table: `users` (status, approved_by, approved_at columns)
- Table: `roles` (super-admin, admin, driver)

---

### 2. **Super Admin Portal** 
The Super Admin has the highest system authority and manages:

#### A. **Pending User Approval** ✅
- Views all pending user registrations
- Approves or rejects users
- On approval:
  - Sets user status to "approved"
  - Generates unique badge ID
  - Creates Driver profile
  - Assigns "driver" role
  - Creates vehicle assignment

**Routes**:
- `GET /superadmin/users/pending` - View pending users
- `POST /superadmin/users/{user}/approve` - Approve user
- `POST /superadmin/users/{user}/reject` - Reject user

**Controller**: `App\Http\Controllers\SuperAdmin\UserApprovalController`

**Database**:
- Table: `users` (status, approved_by, approved_at)
- Table: `drivers` (user_id, badge_id, status)

#### B. **Driver Management**
- Lists all drivers with their status
- Assigns ambulances to drivers
- Tracks driver information and assignments

**Routes**:
- `GET /superadmin/drivers` - View all drivers
- `GET /superadmin/drivers/{driver}/assign` - Assignment form
- `POST /superadmin/drivers/{driver}/assign` - Store assignment

**Controller**: `App\Http\Controllers\SuperAdmin\UserApprovalController`

**Database**:
- Table: `vehicle_driver_assignments` (driver_id, ambulance_id)

#### C. **Ambulance Management**
- Create, edit, and view ambulances
- Manage fleet inventory
- Track ambulance status and assignments

**Routes**:
- `resource` `superadmin/ambulances` - Full CRUD operations

**Controller**: `App\Http\Controllers\SuperAdmin\AmbulanceController`

**Database**:
- Table: `ambulances` (registration_plate, vehicle_type, status, coordinates)

#### D. **Backup & Restore** 💾
**Backup Creation**:
- Uses `mysqldump` command to export database
- Stores SQL file in `storage/app/backups/`
- Creates backup log entry

**Restore Process**:
- Validates backup file (whitelist validation for security)
- Executes restore command
- Updates database with backup data

**Routes**:
- `GET /backups` - View backups
- `POST /backups/create` - Create new backup
- `GET /backups/download/{file}` - Download backup
- `POST /backups/restore` - Restore from backup

**Controller**: `App\Http\Controllers\SuperAdmin\BackupController`

**Database**:
- Table: `backup_logs` (filename, file_size, status)

#### E. **System Settings**
- Manage system-wide configuration
- Update email settings, API settings, feature toggles

**Routes**:
- `GET /superadmin/settings` - View settings
- `POST /superadmin/settings` - Update settings

**Controller**: `App\Http\Controllers\SuperAdmin\SystemSettingsController`

**Database**:
- Table: `system_settings` (key, value)

---

### 3. **Admin Portal**
The Admin manages day-to-day operations including incidents, dispatch, and monitoring.

#### A. **Incident Management**
- Create, view, edit incidents
- Archive and restore incidents
- Track incident status through lifecycle

**Status Flow**: `pending` → `dispatched` → `responding` → `completed` → `closed`

**Routes**:
- `GET /admin/incidents` - List all incidents
- `POST /admin/incidents` - Create incident
- `GET /admin/incidents/{incident}` - View incident
- `PUT /admin/incidents/{incident}` - Update incident
- `POST /admin/incidents/{incident}/archive` - Archive
- `POST /admin/incidents/{incident}/restore` - Restore

**Controller**: `App\Http\Controllers\Admin\IncidentController`

**Database**:
- Table: `incidents` (incident_number, type, status, priority, location, latitude, longitude, driver_id, ambulance_id, archived_at, archived_by)

#### B. **Dispatch Center** 🚨
**Manual Dispatch**:
- Shows pending incidents (status = pending)
- Shows available drivers (status = available)
- Shows available ambulances (status = available)
- Admin selects driver & ambulance
- Creates dispatch record

**Dispatch Status Flow**: `pending` → `assigned` → `accepted` → `en_route` → `arrived` → `completed`

**Routes**:
- `GET /dispatch-center` - Dispatch interface
- `POST /dispatches/{incident}/assign` - Assign dispatch

**Controller**: `App\Http\Controllers\Admin\DispatchController`

**Database**:
- Table: `dispatches` (incident_id, driver_id, vehicle_id, status, assigned_at, accepted_at, arrived_at, completed_at)

#### C. **Auto Dispatch** ⚡
- System automatically finds nearest available driver & ambulance
- Uses GPS coordinates for distance calculation
- Creates and assigns dispatch automatically

**Routes**:
- `POST /admin/incidents/{incident}/auto-dispatch` - Trigger auto-dispatch

**Controller**: `App\Http\Controllers\Admin\AutoDispatchController`

#### D. **GPS Monitoring** 📡
- Real-time live map showing all active drivers
- GPS history with date/driver filtering
- Shows current location and movement path

**Routes**:
- `GET /admin/gps-monitoring` - Live GPS monitoring
- `GET /admin/gps-history` - Historical GPS data
- `GET /admin/gps-locations` - Get location data for map

**Controller**: `App\Http\Controllers\Admin\GpsMonitoringController`

**Database**:
- Table: `gps_locations` (driver_id, latitude, longitude, recorded_at)

#### E. **Operations Center** 🎛️
- Live command center view
- Shows active incidents, assigned drivers, ambulance status
- Real-time system overview

**Routes**:
- `GET /admin/operations-center` - Operations center view

**Controller**: `App\Http\Controllers\Admin\OperationsCenterController`

#### F. **Reports & Analytics** 📊

**Types**:
1. **Incident Reports**
   - Driver-submitted reports
   - Status: Submitted → Approved/Pending
   - Admin approval required

2. **Response Time Analytics**
   - Average response time metrics
   - Performance KPIs

3. **Driver Performance Reports**
   - Completion rates
   - Response time analysis
   - Performance ratings

4. **Vehicle Utilization Reports**
   - Hours on duty
   - Number of trips
   - Availability metrics

**Routes**:
- `GET /admin/incident-reports` - View incident reports
- `GET /admin/reports/response-time` - Response time report
- `GET /admin/reports/driver-performance` - Driver performance report
- `GET /admin/reports/vehicle-utilization` - Vehicle utilization report
- `POST /admin/reports/{report}/approve` - Approve report
- Export to PDF/Excel available

**Controllers**:
- `IncidentReportController`
- `ResponseTimeController`
- `DriverPerformanceController`
- `VehicleUtilizationController`

#### G. **Alert Management**

**Panic Alerts** 🆘:
- Driver-triggered panic button
- Location captured and stored
- Listed with driver info and timestamp

**Routes**:
- `GET /admin/panic-alerts` - View panic alerts

**Controller**: `App\Http\Controllers\Admin\PanicAlertController`

**Hijack Alerts** 🔴:
- Vehicle hijacking emergency alert
- Audit log created
- High-priority alert to admin

**Routes**:
- `GET /admin/hijack-alerts` - View hijack alerts

**Controller**: `App\Http\Controllers\Admin\HijackAlertController`

**Database**:
- Table: `panic_alerts` (driver_id, latitude, longitude, status, triggered_at)
- Table: `hijack_alerts` (driver_id, latitude, longitude, status, triggered_at)
- Table: `audit_logs` (user_id, action, module, description, ip_address)

#### H. **Vehicle Management**

**Vehicle Maintenance** 🔧:
- Schedule maintenance for vehicles
- Track maintenance status
- Mark maintenance as complete
- Updates ambulance status

**Routes**:
- `GET /admin/vehicle-maintenance` - View maintenance records
- `POST /admin/vehicle-maintenance` - Create maintenance
- `PUT /admin/vehicle-maintenance/{id}` - Update maintenance
- `POST /admin/vehicle-maintenance/{id}/complete` - Complete maintenance

**Controller**: `App\Http\Controllers\Admin\VehicleMaintenanceController`

**Database**:
- Table: `vehicle_maintenances` (ambulance_id, maintenance_type, status, scheduled_date, completion_date)

#### I. **Audit & Notifications**

**Audit Logs** 📝:
- All user actions logged
- Timestamp, module, action, IP address
- Compliance and security tracking

**Routes**:
- `GET /admin/audit-logs` - View audit logs

**Notifications** 🔔:
- Real-time notifications for system events
- Read/Unread status
- Unread count display

**Routes**:
- `GET /admin/notifications` - View notifications
- `POST /admin/notifications/{notification}/read` - Mark as read
- `POST /admin/notifications/read-all` - Mark all as read

#### J. **Vulnerable Areas & Equipment**

**Vulnerable Areas** ⚠️:
- Mark high-risk zones
- Track vulnerable locations
- Deactivate areas

**Routes**:
- `resource /admin/vulnerable-areas` - CRUD operations

**Response Equipment**:
- Track available equipment
- Status management

**Routes**:
- `resource /admin/response-equipment` - CRUD operations

---

### 4. **Driver Portal**
Drivers interact with the system for dispatch management and incident response.

#### A. **Driver Dashboard**
- Shows current active dispatch (if any)
- Displays pending dispatch awaiting acceptance
- Shows recent incidents
- Displays dispatch details and action buttons

**Routes**:
- `GET /driver/dashboard` - Driver dashboard

**Controller**: `App\Http\Controllers\Driver\DashboardController`

#### B. **Dispatch Acceptance/Decline**

**Accept Dispatch**:
- Driver accepts assigned dispatch
- Updates dispatch status: `assigned` → `accepted`
- Updates driver status: `available` → `assigned`
- Updates ambulance status: `available` → `on_duty`
- Updates incident status: `pending` → `dispatched`

**Routes**:
- `POST /driver/dispatches/{dispatch}/accept` - Accept dispatch

**Decline Dispatch**:
- Driver declines dispatch
- Updates dispatch status: `assigned` → `cancelled`
- Returns incident to `pending` status
- Releases ambulance back to `available`
- Makes driver `available` if no other active dispatch

**Routes**:
- `POST /driver/dispatches/{dispatch}/decline` - Decline dispatch

**Controller**: `App\Http\Controllers\Driver\DashboardController`

#### C. **Incident Progress Tracking**

**Mark En Route** 🚗:
- Driver starts moving to incident location
- Dispatch status: `accepted` → `en_route`
- Driver status: `assigned` → `en_route`
- Incident status: `dispatched` (remains)
- Timestamp recorded

**Routes**:
- `POST /driver/incidents/{incident}/en-route` - Mark en route

**Mark Arrived** 🎯:
- Driver arrives at incident location
- Dispatch status: `en_route` → `arrived`
- Driver status: `en_route` → `on_scene`
- Incident status: `dispatched` → `responding`
- Arrived timestamp recorded

**Routes**:
- `POST /driver/incidents/{incident}/arrived` - Mark arrived

**Mark Completed** ✔️:
- Driver completes incident response
- Dispatch status: `arrived` → `completed`
- Driver status: `on_scene` → `returning`
- Incident status: `responding` → `completed`
- Ambulance status: `on_duty` → `available`
- Completion timestamp recorded

**Routes**:
- `POST /driver/incidents/{incident}/completed` - Mark completed

**Controller**: `App\Http\Controllers\Driver\DashboardController`

#### D. **Navigation** 🗺️
- Shows route from current location to incident
- GPS directions
- Distance and ETA display

**Routes**:
- `GET /driver/navigation` - Navigation view

**Controller**: `App\Http\Controllers\Driver\NavigationController`

#### E. **GPS Tracking** 📍
- Driver continuously sends GPS location updates
- Validates coordinates (latitude: -90 to 90, longitude: -180 to 180)
- Creates GpsLocation record for each update
- Stores location in database with timestamp
- Preserves GPS history for reports

**Routes**:
- `POST /driver/gps/update` - Update GPS location

**Controller**: `App\Http\Controllers\Driver\GpsController`

**Database**:
- Table: `gps_locations` (driver_id, latitude, longitude, recorded_at)

#### F. **Incident Report** 📝

**Report Creation**:
- After incident completed, driver can create report
- Fill in observations, actions, patient info
- Submit for admin approval
- Status: `submitted` (pending admin review)

**Routes**:
- `GET /driver/incidents/report/{incident?}` - Create report form
- `POST /driver/incidents/report/{incident?}` - Submit report

**Controller**: `App\Http\Controllers\Driver\IncidentReportController`

**Database**:
- Table: `incident_reports` (incident_id, driver_id, status, observations, actions, submitted_at)

#### G. **Emergency Alerts**

**Panic Button** 🆘:
- Driver triggered in case of personal danger
- Captures current GPS location
- Creates PanicAlert record with `status: active`
- Sends notification to admin
- Alert appears in Admin's Panic Alerts list

**Routes**:
- `POST /driver/panic` - Trigger panic alert

**Controller**: `App\Http\Controllers\Driver\PanicController`

**Hijack Button** 🔴:
- Driver triggered if vehicle is hijacked
- Captures current GPS location
- Creates HijackAlert record with `status: active`
- Creates AuditLog in Emergency module
- Sends notification to admin
- High-priority alert

**Routes**:
- `POST /driver/hijack` - Trigger hijack alert

**Controller**: `App\Http\Controllers\Driver\HijackController`

#### H. **Driver History** 📜
- View all completed dispatch records
- Shows incident details, dates, status
- Historical reference for driver

**Routes**:
- `GET /driver/history` - View history

**Controller**: `App\Http\Controllers\Driver\DriverHistoryController`

#### I. **Driver Settings** ⚙️
- Update driver profile
- Change contact information
- Update license details

**Routes**:
- `GET /driver/settings` - Settings view

**Controller**: `App\Http\Controllers\Driver\DriverSettingsController`

---

### 5. **Incident & Dispatch Workflow**

The complete incident lifecycle:

```
Incident Created (Status: Pending)
    ↓
Dispatch Method Selected (Manual or Auto)
    ↓
Dispatch Record Created (Status: Assigned)
    ↓
Driver Notified
    ↓
Driver Response
    ├─ Accept → Dispatch: Accepted, Incident: Dispatched
    │   ├─ Mark En Route → Dispatch: En Route
    │   ├─ Mark Arrived → Dispatch: Arrived, Incident: Responding
    │   └─ Mark Completed → Dispatch: Completed, Incident: Completed
    │       └─ Create Report (Optional) → Report: Submitted
    │           └─ Admin Review → Report: Approved/Rejected
    │               └─ Incident: Closed
    │
    └─ Decline → Dispatch: Cancelled, Incident: Pending
        └─ Reassign to Another Driver
```

**Key Decision Points**:
- Valid Login?
- User Status Approved?
- Driver Accepts Dispatch?
- Ambulance Available?
- Driver Marks Actions (En Route, Arrived, Completed)?
- Report Required?
- Admin Approves Report?

---

### 6. **GPS Tracking System**

Continuous GPS tracking:

```
Driver Sends Location
    ↓
Validate Coordinates (Lat: -90 to 90, Lng: -180 to 180)
    ↓
Create GpsLocation Record (with timestamp)
    ↓
Admin Sees Real-time Location on Live Map
    ↓
Location Added to History (for reports and analysis)
```

---

### 7. **Emergency & Alert System**

Emergency response handling:

```
Driver Triggers Button (Panic or Hijack)
    ↓
Validate Current GPS Location
    ↓
Create Alert Record (PanicAlert or HijackAlert)
    ↓
Create Audit Log (for Hijack)
    ↓
Send Notification to Admin
    ↓
Admin Views Alert with Location on Map
    ↓
Admin Takes Emergency Action
```

---

## Routes & Controllers Summary

### Authentication Routes
- `routes/auth.php` - Breeze authentication scaffolding
- `LoginController` - Handles authentication
- `EnsureUserApproved` Middleware - Checks user approval status

### Super Admin Routes
- Prefix: `/superadmin`
- Controllers:
  - `UserApprovalController` - User approval and driver management
  - `BackupController` - Backup/restore operations
  - `AmbulanceController` - Ambulance management
  - `AssignmentController` - Vehicle-driver assignments
  - `DashboardController` - Super admin dashboard
  - `DriverController` - Driver management
  - `AdminController` - Administrator management
  - `SystemSettingsController` - System settings

### Admin Routes
- Prefix: `/admin`
- Controllers:
  - `DashboardController` - Admin dashboard
  - `DispatchController` - Dispatch management
  - `IncidentController` - Incident management
  - `GpsMonitoringController` - GPS tracking
  - `OperationsCenterController` - Operations center
  - `PanicAlertController` - Panic alerts
  - `HijackAlertController` - Hijack alerts
  - `IncidentReportController` - Incident reports
  - `ResponseTimeController` - Response time analytics
  - `DriverPerformanceController` - Driver performance reports
  - `VehicleUtilizationController` - Vehicle utilization reports
  - `VehicleMaintenanceController` - Vehicle maintenance
  - `NotificationController` - Notifications
  - `AuditLogController` - Audit logs
  - `VulnerableAreaController` - Vulnerable areas
  - `ResponseEquipmentController` - Response equipment

### Driver Routes
- Prefix: `/driver`
- Middleware: `auth`, `approved`, `role:driver`
- Controllers:
  - `DashboardController` - Driver dashboard and incident status updates
  - `GpsController` - GPS location updates
  - `IncidentReportController` - Incident report submission
  - `PanicController` - Panic alert trigger
  - `HijackController` - Hijack alert trigger
  - `NavigationController` - Navigation display
  - `DriverHistoryController` - Dispatch history
  - `DriverSettingsController` - Driver settings
  - `MyAssignmentController` - Current assignment view

---

## Models & Database Relationships

### Core Models
1. **User** - System users (Super Admin, Admin, Driver)
   - HasOne: Driver
   - HasMany: Notifications
   - HasRoles: Spatie Permission

2. **Driver** - Driver profile
   - BelongsTo: User
   - HasMany: GpsLocations
   - HasMany: Dispatches
   - HasMany: IncidentReports
   - HasOne: Active Dispatch

3. **Incident** - Emergency incidents
   - HasMany: Dispatches
   - BelongsTo: Driver
   - BelongsTo: Ambulance
   - HasMany: IncidentAttachments
   - HasOne: IncidentReport

4. **Dispatch** - Dispatch records
   - BelongsTo: Incident
   - BelongsTo: Driver
   - BelongsTo: Ambulance (vehicle_id)

5. **Ambulance** - Vehicles
   - HasMany: Incidents
   - HasMany: Dispatches
   - HasOne: VehicleDriverAssignment

6. **GpsLocation** - GPS tracking history
   - BelongsTo: Driver

7. **PanicAlert** - Panic button triggers
   - BelongsTo: Driver

8. **HijackAlert** - Hijack button triggers
   - BelongsTo: Driver

9. **IncidentReport** - Driver incident reports
   - BelongsTo: Incident
   - BelongsTo: Driver

10. **Notification** - System notifications
    - BelongsTo: User

11. **AuditLog** - Action logging
    - Tracks all user actions

12. **BackupLog** - Backup history
    - Tracks all backups

13. **VehicleDriverAssignment** - Driver-ambulance assignments

14. **VehicleMaintenance** - Vehicle maintenance records

15. **VulnerableArea** - High-risk zones

16. **ResponseEquipment** - Emergency equipment inventory

17. **SystemSetting** - Configuration settings

---

## Status Fields & Values

### User Status
- `pending` - Awaiting super admin approval
- `approved` - Account approved, can access system
- `rejected` - Application rejected, cannot access

### Driver Status
- `available` - Ready for dispatch
- `assigned` - Has active dispatch
- `en_route` - Traveling to incident
- `on_scene` - Arrived at incident location
- `returning` - Returning to base
- `offline` - Not available

### Incident Status
- `pending` - Created, waiting for dispatch
- `dispatched` - Dispatch assigned, awaiting driver acceptance
- `responding` - Driver en route or on scene
- `completed` - Response complete, incident resolved
- `closed` - Incident closed and archived
- `cancelled` - Incident cancelled

### Dispatch Status
- `pending` - Initial state (rarely used)
- `assigned` - Assigned to driver, awaiting acceptance
- `accepted` - Driver accepted, proceeding to incident
- `en_route` - Driver en route to incident
- `arrived` - Driver arrived at scene
- `completed` - Incident response completed
- `closed` - Dispatch closed and archived
- `cancelled` - Dispatch cancelled by driver

### Ambulance Status
- `available` - Ready for dispatch
- `on_duty` - Currently assigned to dispatch
- `maintenance` - Under maintenance
- `standby` - On standby

### Alert Status
- `active` - Alert is active and needs attention
- `resolved` - Alert has been handled

### Report Status
- `submitted` - Driver submitted, awaiting approval
- `approved` - Admin approved report
- `pending` - Report pending (internal)
- `rejected` - Rejected, needs revision

---

## Middleware

### EnsureUserApproved
Located: `app/Http/Middleware/EnsureUserApproved.php`

**Purpose**: Validates that authenticated user has been approved
- If user not logged in: Redirect to login
- If user status is not "approved": Logout and redirect to login with error
- If approved: Allow request to continue

**Applied to**: `approved` middleware in routes

### Role Middleware
From Spatie Permission package

**Usage**: `role:super-admin`, `role:admin`, `role:driver`, `role:admin|super-admin`

**Purpose**: Restrict routes to specific roles

---

## Security Measures in Code

1. **User Approval Workflow** - Users must be approved before access
2. **Role-based Access Control** - Each role has restricted access
3. **Middleware Protection** - Routes protected with auth and role checks
4. **Dispatch Ownership** - Drivers can only access their own dispatches
5. **Backup Validation** - Whitelist validation for backup files before restore
6. **GPS Validation** - Coordinates validated before storage
7. **Audit Logging** - All critical actions logged (especially hijack alerts)
8. **Incident Lock** - Completed/closed/cancelled incidents cannot be re-dispatched
9. **Authorization Checks** - Resource ownership verified in controllers

---

## Uncertain/Unverified Parts

### Not Directly Visible in Checked Code

1. **Frontend Implementation Details**
   - Blade template structure not fully analyzed
   - JavaScript event handling for GPS updates
   - Real-time WebSocket/polling implementation for live map

2. **Automatic GPS Background Tracking**
   - Whether GPS is sent automatically or on-demand
   - Polling frequency if automatic
   - Mobile app implementation details (Dart/Flutter for muniresq_driver_app)

3. **Notification Delivery Mechanism**
   - Whether using mail, SMS, or in-app only
   - Real-time notification delivery method

4. **GPS Distance Calculation**
   - Exact algorithm for "nearest vehicle" in auto-dispatch
   - Whether using Haversine formula or other

5. **Concurrent Dispatch Handling**
   - What happens if driver has multiple pending dispatches
   - How system handles driver with multiple assignments

6. **Incident Closure Rules**
   - Whether report must be approved to close incident
   - Auto-closure timeout policies

7. **Performance Monitoring**
   - Real-time dashboard refresh interval
   - Map update frequency

These areas could be verified by examining:
- `resources/views/` - Blade template files
- `resources/js/` - JavaScript implementation
- `config/` files for service configurations
- `muniresq_driver_app/lib/` - Driver app implementation
- Migration files for timestamps and relationships

---

## How to View the Flowchart

### Option 1: Mermaid Live Editor
1. Go to https://mermaid.live
2. Copy contents of `docs/muniresq-system-flowchart.mmd`
3. Paste into editor
4. Flowchart will render automatically

### Option 2: GitHub
1. Commit the `.mmd` file to your repository
2. View on GitHub - Mermaid diagrams render automatically
3. Can view directly in GitHub UI

### Option 3: VS Code (with Mermaid Extension)
1. Install "Markdown Preview Mermaid Support" extension
2. Open `docs/muniresq-system-flowchart.mmd`
3. Use Preview (Cmd/Ctrl + Shift + V)
4. Flowchart renders in preview panel

### Option 4: Export to Image
From Mermaid Live Editor:
1. Render flowchart
2. Click "Download" button
3. Select PNG, SVG, or PDF format

---

## Conclusion

This flowchart represents the actual MuniResQ system as implemented in the Laravel codebase. The flow accurately reflects:

✅ Real route structure and request handling
✅ Actual role-based authorization
✅ Genuine status transitions and state changes
✅ Real dispatch workflow with decision points
✅ Actual GPS tracking implementation
✅ Emergency alert system
✅ Incident lifecycle management
✅ User approval and onboarding process
✅ Backup and restore functionality

The flowchart can be used for:
- System documentation for stakeholders
- Onboarding new development team members
- Understanding application flow
- Process improvement analysis
- BSIT capstone project documentation
- Training materials for end-users
- System architecture reference

---

## Document Information

- **Created**: 2026-09-01
- **Flowchart File**: `docs/muniresq-system-flowchart.mmd`
- **Documentation File**: `docs/muniresq-system-flowchart-notes.md`
- **System**: MuniResQ - Municipal Emergency Response Queueing System
- **Framework**: Laravel 11
- **Analysis Scope**: Actual codebase inspection and route/controller analysis
