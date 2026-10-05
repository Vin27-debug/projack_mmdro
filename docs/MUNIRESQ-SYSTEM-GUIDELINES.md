# MuniResQ System Guidelines

## Document Purpose and Status

This document describes the current MuniResQ Laravel implementation as evidenced by its routes, controllers, middleware, models, migrations, Blade views, services, and Feature tests. It is an operational guide, not a statement of legal or regulatory compliance.

Throughout this document:

- **Implemented** describes behavior present in the application code.
- **Recommended practice** describes prudent operator behavior; it is not necessarily enforced by the software.
- **Limitation** identifies behavior that is absent, incomplete, inconsistent, or not verifiable from the repository.

## 1. System Overview

### 1.1 Purpose

MuniResQ is a web-based ambulance and rescue dispatch management system for municipal emergency response coordination. It provides role-specific interfaces for administration, dispatch, driver response, location monitoring, incident reporting, and operational review.

### 1.2 Current objectives

The implementation supports:

1. Recording incident details, address components, priority, coordinates, and attachments.
2. Assigning drivers and vehicles to incidents and tracking dispatch states.
3. Recording response milestones and maintaining incident/dispatch records.
4. Receiving driver GPS submissions, showing location freshness, and retaining location history.
5. Triggering limited geofence-based arrival and departure timestamp updates.
6. Collecting driver reports and allowing an administrator to approve them.
7. Displaying notifications, operational dashboards, reports, and audit records.

### 1.3 Scope

The application is a Laravel web application using Blade interfaces, database-backed records, session authentication, role middleware, and browser geolocation on the driver dashboard. It includes a Leaflet map interface and uses external map tiles/geocoding services in some views and workflows.

The current implementation does not establish that emergency calls are received directly by the application, that dispatch notifications are delivered by SMS/email or a mobile push service, or that background GPS collection continues when the driver dashboard is not active.

## 2. User Roles and Responsibilities

Role checks are implemented primarily through `auth`, `approved`, and Spatie `role` middleware groups in [`routes/web.php`](../routes/web.php). The `approved` alias maps to [`EnsureUserApproved`](../app/Http/Middleware/EnsureUserApproved.php).

| Role | Implemented responsibilities |
|---|---|
| **Super Admin** | Manages administrator accounts; approves/rejects/suspends administrators; approves or rejects pending users/drivers; manages drivers and driver–vehicle assignments; manages ambulances; accesses the super-admin dashboard, settings page, and backup functions. Super-admin users are also admitted to routes protected by `role:admin|super-admin`. |
| **Admin / Dispatcher** | Creates and edits incidents; reviews incident and dispatch information; assigns drivers and vehicles; uses dispatch recommendations, monitoring, notification, report, maintenance, vulnerable-area, response-equipment, and audit-log screens; approves incident reports; may edit emergency timestamps. |
| **Driver** | Uses the driver dashboard and assignment/history pages; accepts or declines assigned dispatches; submits GPS updates; records response milestones using available dashboard actions; triggers a panic alert; submits an incident report for an eligible completed incident. |

### Access-control qualification

The application defines an [`IncidentPolicy`](../app/Policies/IncidentPolicy.php), but its policy methods all return `false`, and the inspected incident controller routes do not call `authorize()` or otherwise invoke that policy. The effective route protection is therefore the route middleware and controller-specific ownership checks described in this document; the policy should not be treated as an implemented authorization grant.

## 3. Authentication and Account Guidelines

### 3.1 Login and logout

- Users sign in using an email address and password through the Laravel authentication routes in [`routes/auth.php`](../routes/auth.php).
- The login request validates the fields and rate-limits failed attempts to five per email/IP key before a lockout response.
- User passwords are assigned a hashed cast, and password/remember-token fields are hidden from normal model serialization.
- On a successful login, the session is regenerated and an authentication audit entry is written. The user is redirected according to the assigned role.
- Logout writes an audit entry, logs out the web guard, invalidates the session, regenerates the CSRF token, and redirects to `/`.
- Password reset and password update routes are present.

### 3.2 Registration and approval

Several registration paths exist:

- The standard Laravel `/register` path creates and logs in a user. The users migration defaults account status to `pending`; that generic path does not assign an MuniResQ operational role.
- The driver registration path requires name, email, password confirmation, and contact number. License number and expiry remain nullable legacy database fields and are not requested by registration or driver management. Registration creates a pending user, assigns the driver role, and creates a driver profile.
- The public admin-registration path creates a pending user and assigns the admin role.
- A Super Admin can create administrator accounts with additional employment/contact fields; these accounts are also pending until approved.

Only approved accounts pass `EnsureUserApproved`. If the account is missing or its status is not exactly `approved`, the middleware redirects to `/login`; for a non-approved signed-in account, it logs the user out and flashes a pending-approval message.

The user status values in the schema are `pending`, `approved`, `rejected`, and `suspended`. Super Admin approval of a pending driver creates or updates a driver profile, sets the driver available, ensures a vehicle assignment when possible, and synchronizes the user’s role to `driver`. Driver operational management uses a separate `management_status` of `active` or `suspended`; a suspended driver remains an approved account but is excluded from new assignments and dispatch recommendations. Administrator approval is handled by the administrator-management controller.

### 3.3 Role-based access

- Driver routes require authentication, approval, and the `driver` role.
- Admin routes generally require authentication, approval, and either `admin` or `super-admin`.
- Super-admin management routes require the `super-admin` role.
- Profile routes require authentication but are not placed in the approved/role groups.

Role membership and account status are separate checks: an assigned role does not bypass the approved-account middleware.

## 4. Incident Management Guidelines

### 4.1 Creating an incident

An Admin/Dispatcher creates an incident through the incident form and submits it to the incident store action. The server validates the request and creates the incident in a transaction. It sets a generated incident number, status `pending`, and `call_received_at` to the current time, then creates a global “New Incident Reported” notification.

### 4.2 Incident information

| Information | Current validation/behavior |
|---|---|
| Reporter name | Required string, maximum 255 characters |
| Incident type | Required string, maximum 255 characters |
| Priority | Required; must be `Low`, `Medium`, `High`, or `Critical` |
| Contact number | Optional/nullable string, maximum 255 characters |
| General location | Optional/nullable string, maximum 255 characters |
| House number, street, barangay, city, province | Each optional/nullable string, maximum 255 characters |
| Description | Optional nullable text |
| Latitude and longitude | Optional numeric values bounded to valid latitude/longitude ranges |
| Attachments | Optional array of up to 10 files; each file is limited to 10 MB and accepted MIME/extensions are constrained by validation |

When a non-empty general `location` is provided without both coordinates, the controller may request geocoding from Nominatim. If geocoding fails or returns no result, the request continues using manually supplied coordinates. This call is an external dependency.

Address components are persisted on the incident and are displayed as a comma-separated address when present. The model’s `formattedAddress()` helper falls back to the general location and then “Address unavailable”; the incident detail page also displays the address components alongside the general location.

### 4.3 Priority

The stored priority values and current user-facing labels are:

| Stored value | Display label |
|---|---|
| Low | Green — Minor/Minimal |
| Medium | Yellow — Delayed |
| High | Red — Immediate/Critical |
| Critical | Black — Critical |

### 4.4 Incident statuses

The incident model defines `pending`, `dispatched`, `responding`, `completed`, `closed`, and `cancelled`. Creating an incident sets it to `pending`. Assignment changes it to `dispatched`; at-scene and subsequent response actions generally use `responding`; driver completion sets `completed`; report approval closes the incident as `closed`.

Archiving is represented separately by `archived_at` and `archived_by`. Archived incidents are read-only through the update action and can be restored. This is not a separate incident status.

Driver profiles, vehicles, and vehicle maintenance records also use non-destructive archive timestamps. Archived entries are hidden from normal management lists and shown by an Archived filter, with restore actions where provided. Drivers and vehicles cannot be archived while they have active dispatches. Historical incident, dispatch, report, maintenance, GPS, and assignment relationships are retained.

### 4.5 Updating incidents

The update action uses the same incident validation for editable report details and coordinates, stores attachments, and redirects to the incident detail. It removes `status` from validated input; status transitions are controlled by workflow actions rather than a general incident edit form.

Emergency timestamps are separately editable by Admin/Super Admin. The controller supports individual event updates and a bulk timestamp path, checks chronology, writes incident/dispatch fields as configured, and creates an audit log entry for an actual correction. The legacy request field `at_scene_at` resolves to the existing `arrived-at-scene` event.

## 5. Dispatch Guidelines

### 5.1 Dispatch Center assignment

The Dispatch Center lists pending incidents, available drivers, and available ambulances. Its assignment action:

1. Validates the driver and optional vehicle identifiers.
2. Checks that the driver and selected vehicle are available.
3. Rejects an active dispatch already associated with that driver, vehicle, or incident.
4. Creates a dispatch with status `assigned` and an assignment time.
5. Updates the incident to `dispatched` and records the assigned driver/vehicle.
6. Updates the driver to `assigned` and a selected vehicle to `on_duty`.
7. Writes a dispatch audit entry.

The driver’s dashboard then presents accept/decline actions for an assigned dispatch.

### 5.2 Acceptance and vehicle selection

The driver must own the assigned dispatch. The accept action only accepts a dispatch whose status is `assigned`. The driver submits a vehicle selection; the server verifies that the vehicle is available (or is the dispatch’s current on-duty vehicle) and is not used by another active dispatch. On success, the dispatch status becomes `en_route`, `accepted_at` and `en_route_at` are set, the vehicle becomes `on_duty`, the incident is set to `dispatched`, the driver becomes `en_route`, and a “Vehicle Selected for Dispatch” notification and audit entry are created.

### 5.3 Declining

The assigned driver may decline an active dispatch. The system marks it `cancelled` and records `declined_at`; returns the incident to `pending` and clears its driver/vehicle IDs; releases the vehicle if no other active dispatch uses it; makes the driver available if the driver has no other active dispatch; and logs the decline. The incident can then be reassigned.

### 5.4 Dispatch statuses

The dispatch model defines: `pending`, `assigned`, `accepted`, `en_route`, `arrived`, `completed`, `closed`, and `cancelled`.

### 5.5 Multiple dispatch entry points

There is more than one admin dispatch path. The Dispatch Center path performs explicit availability checks. An incident-specific dispatch action and an auto-dispatch action also exist; their validations and state transitions are not identical. In particular, auto-dispatch selects the first available driver and vehicle rather than using the nearest-resource ranking service. Operators should use the Dispatch Center’s availability-aware workflow unless the application interface explicitly directs otherwise.

## 6. GPS and Location Tracking Guidelines

### 6.1 Driver GPS updates

The driver dashboard uses browser `navigator.geolocation.watchPosition`. While the page is visible, it submits GPS coordinates at a maximum frequency of approximately once every 15 seconds. The browser must support geolocation and the driver must grant permission. The POST includes the page’s CSRF token and may include GPS accuracy and speed.

The GPS endpoint validates latitude and longitude ranges, optional non-negative accuracy, an optional date, and optional speed fields. A successful update creates a GPS history record. Invalid input returns a JSON error with HTTP 422; a missing driver profile returns 404. The protected web route also requires an authenticated, approved driver.

### 6.2 Freshness categories

The GPS freshness service uses configurable thresholds. The defaults in application configuration are:

| State | Default age |
|---|---|
| Fresh | Less than 60 seconds |
| Delayed | 60 seconds through 5 minutes |
| Stale | More than 5 minutes |
| Missing | No recorded GPS location |

The monitoring and live-map responses include GPS age/freshness metadata. Resource recommendation excludes drivers with missing coordinates or GPS older than the configured stale limit.

### 6.3 Ambulance location synchronization

On an accepted driver GPS update, the coordinates are copied to the vehicle associated with that driver’s active dispatch. Admin monitoring can display each driver’s latest GPS and associate it with the active-dispatch vehicle or the driver’s active vehicle assignment. GPS history is retained as individual records; the code inspected does not define a retention window.

### 6.4 Geofence behavior

Geofence processing is conditional and does not replace manual driver actions:

- Coordinates must be valid and an incident must have valid coordinates.
- Geofence processing requires a non-null accuracy no worse than the configured maximum (default 50 metres) and a recorded time within the configured maximum age (default 120 seconds).
- While the dispatch is `en_route`, a driver within the default 0.15 km arrival radius can automatically set the incident’s `at_scene_at`, incident status `responding`, dispatch status `arrived`, dispatch `arrived_at`, and driver status `on_scene`.
- Automatic departure can set `depart_scene_at` only after an at-patient time exists, while the dispatch is `arrived`, and when distance meets the configured departure radius (default 0.30 km, never below the arrival radius).
- Repeated GPS submissions do not overwrite the first at-scene time once set.

### 6.5 GPS limitations and failures

Permission denial, unsupported browser geolocation, unavailable position, browser GPS timeout, and server update failure are surfaced by the driver interface with different status messages. Browser fixes depend on the device, permissions, and connectivity. Missing, stale, inaccurate, or incident-less GPS does not trigger geofence transitions. Vehicle coordinates without a timestamp cannot be independently freshness-rated using the vehicle record alone.

## 7. Emergency Response Status and Timestamp Guidelines

The application has incident status values, dispatch status values, driver status values, and timestamp milestones. These are distinct concepts; for example, “At Patient” and “At Hospital” are incident timestamps, not separate incident or dispatch status values.

### 7.1 Milestones

| Milestone | Stored field | Implemented behavior |
|---|---|---|
| Incident reported | Incident `created_at` | Record creation time; individual timestamp can be edited by an authorized administrator. |
| Call received | Incident `call_received_at` | Set at incident creation; driver action also exists when the field has not already been recorded. |
| Response started | Incident `response_at` | Driver action is available for an eligible accepted dispatch. |
| En Route | Dispatch `en_route_at` | Acceptance sets this immediately; a driver action can also transition an accepted dispatch to en route after response time is recorded. |
| At Scene | Incident `at_scene_at`, dispatch `arrived_at` | Can be manually recorded for an eligible en-route dispatch or set by qualifying geofence GPS. |
| At Patient | Incident `at_patient_at` | Requires an arrived dispatch and a previously recorded at-scene time. |
| Departed Scene | Incident `depart_scene_at` | Requires at-patient time; can be recorded manually or by qualifying geofence departure. |
| At Hospital | Incident `at_hospital_at` | Requires departed-scene time; the action does not itself change incident/dispatch status. |
| Completed | Incident and dispatch `completed_at` | Driver completion requires an arrived dispatch; sets incident and dispatch completed and driver returning. |
| Return to Base | Incident `return_to_base_at` | Completion sets this timestamp if it is not already present. A separate action also exists with additional prerequisites. |
| Ready for next mission | No incident milestone | Driver and vehicle can become available after a completed dispatch and incident. |

### 7.2 State-flow qualification

The controller contains manual actions for call-received, response, en-route, at-scene, at-patient, departed-scene, at-hospital, completed, returning, and ready. The accepted-driver path currently sets a dispatch directly from `assigned` to `en_route`, while the separate response/en-route actions expect an `accepted` dispatch. Also, the standalone return-to-base action requires an `arrived` dispatch, whereas the completion action changes the dispatch to `completed`. These actions therefore do not form one fully consistent linear state machine. Follow the actions available in the current dashboard and treat the stored statuses/timestamps as the authoritative record; consult the dispatcher if a required action is unavailable.

## 8. Notification Guidelines

Notifications are database records with a title, message, type, read state, optional owning user, and optional related-record type/ID.

| Event | Current notification behavior |
|---|---|
| Incident creation | Creates a global unread `incident` notification. |
| Driver selects a vehicle when accepting a dispatch | Creates a global unread `dispatch` notification. The initial admin assignment does not create this notification. |
| Driver submits a report | Creates a global unread `report` notification linked to the incident. |
| Admin approves a report | Creates an unread `report` notification addressed to that driver’s user account. |
| Driver triggers panic | Creates a global unread `panic` notification. |
| Maintenance scheduled | Creates a global unread `maintenance` notification. |

Notifications with `user_id = null` are global; notifications with a user ID are visible to that user and not other users. Loading the notification list does not mark items read. Opening a notification or using its mark-read action marks that item read. “Mark all read” affects only notifications visible to the current user.

## 9. Incident Report Guidelines

### 9.1 Driver submission

A driver may submit a report for an incident assigned to that driver when the incident status is `completed` and no report already exists. Summary, actions taken, and casualties are required; remarks are optional. The report is stored with status `pending` and a submission timestamp. Duplicate submission is rejected.

### 9.2 Admin approval

An Admin/Dispatcher can approve a report. Approval sets the report to `approved`, closes the incident and timestamps it, ensures the latest dispatch is completed, makes the driver available, makes the incident ambulance available, creates a private approval notification for the driver, and writes audit entries.

### 9.3 Completion qualification

Driver completion and report approval are different steps. Driver completion marks the incident/dispatch completed and exposes the incident for report submission. Admin report approval subsequently closes the incident. The inspected routes provide report approval but no report rejection action.

## 10. Vehicle and Driver Guidelines

### Vehicle states

The ambulance model defines `available`, `on_duty`, and `maintenance`. Available vehicles are eligible for the standard Dispatch Center assignment. A selected vehicle becomes on duty; it remains unavailable during return-to-base until readiness is recorded. Maintenance records can set a vehicle to maintenance or return it to available when completed.

### Driver states

The driver model defines `available`, `assigned`, `en_route`, `on_scene`, `returning`, and `offline`. Assignment makes the driver assigned; acceptance/response actions update the operational state; at-scene and return actions update it further. Readiness makes a driver available if the workflow permits.

### Vehicle assignment and return

Super Admin can maintain an active driver–vehicle assignment. During dispatch acceptance, the driver can select a different available vehicle subject to active-dispatch checks. GPS synchronization uses the vehicle on the active dispatch, not merely any historically assigned vehicle. The driver’s return/ready workflow is intended to keep the vehicle on duty until crew readiness is recorded; the current transition inconsistency is described in Section 7.2.

## 11. Reports and Monitoring

Admin-facing monitoring and reporting routes include:

- **GPS monitoring/history:** Current/latest driver location, associated dispatch/vehicle, GPS freshness, optional speed metadata, and paginated location history.
- **Driver performance:** Dispatch counts/completions and calculated response, arrival, and completion metrics; PDF and Excel export routes exist.
- **Vehicle utilization:** Vehicle dispatch counts, maintenance counts, availability-rate calculations, and an admin report page.
- **Response time:** A response-time analytics view and broader reports center, with filters, charts/summary calculations, and PDF/Excel exports.
- **Audit logs:** Paginated action/module/description/IP records.
- **Operational dashboards:** Admin and Super Admin dashboards summarize incidents, resources, notifications, and panic alerts.
- **Vehicle maintenance:** Admins can create, update, complete, and delete maintenance records.

Metric definitions depend on the available timestamps. Some response calculations prefer call-received-to-at-scene, others response-to-at-scene, and others dispatch-assigned-to-arrived. These should not be interpreted as a single guaranteed SLA without confirming the specific report’s calculation.

## 12. Security and Access Control

### Implemented controls

- Session-based authentication is used for web routes.
- Role-protected groups restrict driver, admin, and super-admin route areas.
- The approval middleware blocks non-approved users from protected role areas.
- Driver dispatch actions verify that the authenticated driver owns the dispatch.
- Driver incident report access verifies assignment, completion status, and that a report does not already exist.
- Incident attachment downloads verify that the attachment belongs to the requested incident and that its stored file exists.
- Private notifications are checked against their owning user ID; global notifications remain visible to users in the admin notification area.
- The Laravel `web` route group supplies the framework’s session and CSRF middleware. Blade forms include CSRF fields, and the driver GPS browser request sends its CSRF token.
- Authentication, dispatch transitions, incident timestamp corrections, and report approval include selected audit logging.

### Access-control limitations

- The inspected `IncidentPolicy` methods all deny access and are not invoked by the incident routes/controllers examined.
- Route middleware provides role-level access; this is not a fine-grained permission matrix for every administrative action.
- Audit logging is implemented for selected actions, not demonstrably for every change in the system.

## 13. Data Privacy Guidelines

### Data handled

Records may contain reporter names, contact numbers, address/location, incident descriptions, attachments, driver account/contact/license data, GPS coordinates/history, panic-alert coordinates, incident reports, notifications, and audit data including request IP addresses.

### Operational practices

1. Collect reporter and driver details only when necessary for response and administration.
2. Do not copy personal details, precise locations, or report contents into channels not approved for operational use.
3. Restrict access to approved accounts and the appropriate role; avoid sharing credentials or leaving signed-in workstations unattended.
4. Handle downloaded reports, attachments, backups, and GPS history as sensitive operational data.
5. Follow the organization’s retention, disclosure, and incident-response rules; the repository does not establish a complete retention/deletion policy.

The application does not, by itself, demonstrate compliance with a privacy law or an end-to-end encryption/retention program. The incident geocoding fallback sends a supplied general location query to Nominatim; map interfaces also use external tile resources. These external dependencies should be considered when deciding what location data to enter and when reviewing deployment privacy requirements.

## 14. Operational Guidelines

The following procedures describe the current web workflow. Items marked **Recommended practice** are operational guidance rather than software-enforced requirements.

### 14.1 Receive and create an emergency record

1. **Recommended practice:** Receive and verify the emergency details through the organization’s existing call/intake channel. The application does not establish an integrated telephone intake workflow.
2. Sign in as an approved Admin/Dispatcher.
3. Open incident creation and enter the reporter, incident type, priority, available contact/address details, description, and coordinates/attachments when available.
4. Review the address and coordinate data before saving. A general location may be geocoded externally if coordinates are missing.
5. Save the incident. Confirm its incident number and detail page; it begins as `pending` and creates a global incident notification.

### 14.2 Dispatch a driver and vehicle

1. Open Dispatch Center and select a pending incident.
2. Select an available driver and, when required/appropriate, an available vehicle.
3. Review the availability and active-dispatch feedback. Use the nearest-resource recommendation only as decision support; it depends on valid incident coordinates and fresh driver GPS.
4. Submit assignment and verify the assigned dispatch and incident state.

### 14.3 Driver acceptance and response

1. The driver signs in to an approved driver account and opens the dashboard.
2. Review the incident and assignment, then accept or decline.
3. On acceptance, choose an eligible vehicle when prompted. The dispatch is moved to `en_route`; vehicle selection is logged and a notification is created.
4. **Recommended practice:** Keep the driver dashboard available, permit browser location access, and confirm that GPS status is live. Use manual dashboard milestones when the corresponding action is available.
5. Record at-scene, patient, departure, and hospital events in the order enforced by the relevant actions. If the geofence requirements are met, at-scene or departure may be set automatically.

### 14.4 Complete the emergency and submit the report

1. Record the required response milestones that are available for the dispatch state.
2. Use the dashboard’s completion action when eligible. The incident and dispatch become completed; the driver/vehicle remain in return/on-duty states until the application’s readiness step is completed.
3. The assigned driver opens the report form for the completed incident and submits summary, actions taken, casualties, and any remarks.
4. An Admin/Dispatcher reviews the report list and approves the report when appropriate.
5. Confirm the report approval notification, audit entry, and incident closure. Report rejection is not currently exposed as an implemented route.

## 15. Error and Exception Handling

| Condition | Current behavior |
|---|---|
| Missing GPS | Monitoring reports GPS state as `missing`; recommendations exclude that driver; no geofence transition occurs. |
| Stale GPS | Monitoring marks it `stale` past the configured limit; dispatch recommendations exclude stale drivers; geofence ignores stale submissions. |
| Invalid GPS coordinates | GPS endpoint returns HTTP 422 JSON with a validation error. Browser geolocation errors show permission, unavailable-position, timeout/waiting, or generic GPS status. |
| Inaccurate GPS | Geofence transitions require a present accuracy value within its configured maximum; failure prevents the automatic transition. |
| No incident coordinates | Geofence cannot calculate distance and does not set arrival/departure; manual driver actions remain available where dispatch preconditions permit. |
| Vehicle becomes unavailable | Acceptance rejects a vehicle that is no longer available or already used by another active dispatch and asks the driver to select another. |
| Driver declines | Dispatch is cancelled, the incident returns to pending, and the driver/vehicle are released if no other active dispatch requires them. |
| Unauthenticated access | Authentication middleware redirects web requests to login; the GPS controller also has a JSON 401 guard if reached unauthenticated. |
| Unapproved account | Approval middleware logs out the user and redirects to login with an approval message. |
| Unauthorized role or resource ownership | Role middleware denies access; driver ownership checks abort with 403 when the driver attempts another driver’s dispatch/report. |
| Invalid incident data | Laravel validation returns errors and redirects back for web requests; invalid priority is rejected. |
| Geocoding failure | The incident save continues without coordinates returned by geocoding; manually supplied coordinates are retained. |
| Dispatch availability conflict | Standard Dispatch Center returns a user-facing error for unavailable resources or an existing active dispatch; it does not create the assignment. |

## 16. Testing and Quality Assurance

Feature tests in `tests/Feature` verify selected application behavior. They are not proof of live deployment availability, external geocoder reliability, real-device GPS performance, or every route/controller branch.

| Test area | Examples of behavior verified |
|---|---|
| Authentication | Login page, valid/invalid login, logout, email verification, password reset/update |
| Dashboard access | Role-specific dashboard access, driver registration/role, pending account behavior, GPS coordinate validation |
| Incidents and approval | Coordinate/address persistence, optional contact/location, priority, incident editing, driver approval |
| Dispatch workflow | Assignment statuses/audit, recommendations excluding stale/missing GPS, accept/decline, switching vehicles, GPS-to-vehicle synchronization, geofence arrival/departure, return/readiness, report approval |
| GPS monitoring | Fresh/delayed/stale/missing states, map payloads, admin/super-admin GPS history access |
| Notifications | Incident/report notifications, global/private visibility, read/unread state, read-all, timestamp audit |
| Reports | Driver performance, report persistence, vehicle utilization metrics, response time analytics |
| Operations | Admin module rendering, panic resolved filtering, vehicle maintenance, Super Admin account management, MySQL-only backup guard |
| Priority | Stored valid priority values, rejection of invalid priority, display-label consistency |

The existing implementation’s important test files include `DispatchStatusTest`, `NotificationCenterTest`, `DashboardAccessTest`, `DispatchAndApprovalFlowTest`, `GpsLocationFreshnessTest`, `GpsHistoryAccessTest`, `ReportsModuleTest`, `IncidentPriorityConsistencyTest`, `AdminModuleRoutesTest`, `VehicleMaintenanceModuleTest`, `SuperAdminAdminManagementTest`, `PanicAlertStatusTest`, and the Laravel authentication feature tests.

## 17. System Limitations and Unconfirmed Behavior

The following observations describe repository limitations, not recommended operating assumptions:

1. **Dispatch paths differ.** The standard Dispatch Center performs availability checks, but alternative incident-specific and auto-dispatch paths do not implement identical safeguards or transition behavior. Auto-dispatch picks the first available resources rather than the nearest ranked resources.
2. **Response statuses are not a single consistent linear state machine.** Acceptance sets `en_route`, while a separate response action expects `accepted`; the return-to-base action expects `arrived`, while completion makes the dispatch `completed`. See Section 7.2.
3. **Panic resolution is not exposed in the inspected routes.** An admin controller contains a resolution method, but the web routes inspected expose the panic-alert list and no route invoking that method.
4. **Report rejection is not exposed.** The inspected report routes provide approval, not rejection.
5. **Admin incident-report create/store routes exist but the inspected controller only implements index/approve.** Those route actions are not confirmed as functional.
6. **System settings persistence is not implemented in the inspected controller.** Its update action returns a success message without storing submitted settings.
7. **Backup support is MySQL-only.** The backup controller rejects other database drivers; its feature test verifies this unsupported-database response.
8. **GPS depends on browser/device availability and network conditions.** The code does not establish guaranteed background tracking or independent vehicle GPS hardware feeds.
9. **No complete data retention, deletion, or legal-compliance controls are established by the inspected source.**
10. **No external SMS/email/push delivery for the listed operational notifications is established.** The confirmed notification behavior is database-backed.
11. **Some administrative screens and features exist without clear dedicated Feature coverage.** Current tests verify representative paths, not the full system.

## 18. Recommended User Practices

### Super Admins

- Approve accounts only after verifying the person and role requirements through the organization’s process.
- Maintain accurate driver and vehicle assignments; review availability and maintenance before operation.
- Treat backups and exported records as sensitive. Confirm database compatibility and follow organizational recovery procedures.
- Do not assume the settings screen persists changes unless that behavior is verified in the deployed system.

### Admins / Dispatchers

- Verify incident address, coordinates, priority, and contact details before dispatch.
- Assign only available drivers and vehicles through the availability-aware Dispatch Center.
- Treat nearest-driver/vehicle information as advisory and verify that GPS is fresh.
- Monitor notification, panic, and audit records, and record necessary timeline corrections through authorized controls.
- Review driver reports before approval and confirm the resulting incident closure and resource states.

### Drivers

- Keep account credentials private and use only the assigned account.
- Review the dispatch and selected vehicle before accepting; decline promptly if unable to respond.
- Enable browser location access and keep the dashboard available during response when practicable.
- Confirm GPS status and use manual milestone controls when available; do not assume geofence automation replaces status confirmation.
- Submit a complete, accurate report after the incident becomes eligible and advise the dispatcher of missing or inaccurate location data.

## 19. Implementation Reference Map

The following files are primary evidence for this guide:

- Routes and middleware registration: [`routes/web.php`](../routes/web.php), [`routes/auth.php`](../routes/auth.php), [`bootstrap/app.php`](../bootstrap/app.php)
- Approval middleware: [`EnsureUserApproved.php`](../app/Http/Middleware/EnsureUserApproved.php)
- Core incident, dispatch, GPS, report, and notification behavior: [`Admin/IncidentController.php`](../app/Http/Controllers/Admin/IncidentController.php), [`Admin/DispatchController.php`](../app/Http/Controllers/Admin/DispatchController.php), [`Driver/DashboardController.php`](../app/Http/Controllers/Driver/DashboardController.php), [`Driver/GpsController.php`](../app/Http/Controllers/Driver/GpsController.php), [`Driver/IncidentReportController.php`](../app/Http/Controllers/Driver/IncidentReportController.php), [`Admin/IncidentReportController.php`](../app/Http/Controllers/Admin/IncidentReportController.php), [`Admin/NotificationController.php`](../app/Http/Controllers/Admin/NotificationController.php)
- Models and location services: [`Incident.php`](../app/Models/Incident.php), [`Dispatch.php`](../app/Models/Dispatch.php), [`Driver.php`](../app/Models/Driver.php), [`Ambulance.php`](../app/Models/Ambulance.php), [`GpsLocation.php`](../app/Models/GpsLocation.php), [`Notification.php`](../app/Models/Notification.php), [`IncidentReport.php`](../app/Models/IncidentReport.php), [`GpsFreshnessService.php`](../app/Services/GpsFreshnessService.php), [`IncidentGeofenceService.php`](../app/Services/IncidentGeofenceService.php)
- User-facing views: [`admin/dashboard.blade.php`](../resources/views/admin/dashboard.blade.php), [`admin/incidents/show.blade.php`](../resources/views/admin/incidents/show.blade.php), [`driver/dashboard.blade.php`](../resources/views/driver/dashboard.blade.php)
- Feature evidence: [`tests/Feature`](../tests/Feature)
