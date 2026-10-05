# MuniResQ User Manual

## Document purpose

This manual explains how the current MuniResQ web application is used by Super Admins, Admins/Dispatchers, and Drivers. It is based on the routes, controllers, views, middleware, models, and Feature tests in this project. It describes the application as implemented; it does not promise functions that the code does not provide.

MuniResQ is an operational record and dispatch tool. It does not replace the municipality's emergency call-taking process, radio communications, clinical judgment, or other approved emergency procedures.

## 1. Introduction

MuniResQ is a web-based ambulance and rescue dispatch management system for the Municipal Disaster Risk Reduction and Management Office (MDRRMO). Its implemented functions support incident recording, resource dispatch, driver response coordination, GPS submissions and monitoring, driver reports, notifications, fleet records, and administrative review.

The current system is intended to help authorized personnel:

1. Record and search incident information.
2. Assign drivers and vehicles to eligible incidents.
3. View driver-submitted location information and response milestones.
4. Collect driver reports and have an administrator approve them.
5. Review operational, fleet, notification, and audit information.

Emergency calls are not shown as arriving directly into MuniResQ. Staff enter incident records into the application.

## 2. System requirements

| Requirement | Guidance based on the current implementation |
|---|---|
| Browser | Use a current browser with JavaScript enabled. The project does not define or test a formal supported-browser/version matrix. |
| Network | The application must be reachable over the organization's configured network or hosted URL. Network access is also needed for map tiles and certain map/geocoding services used by the interface. |
| Driver location | A device with location services and a browser that supports the Geolocation API is needed for browser GPS tracking. Grant the browser location permission when prompted. |
| Secure location access | Browser geolocation generally requires an allowed secure browser context; if location is unavailable, use the troubleshooting section and contact the system administrator. |
| Session access | Sign in with an approved account and use the role assigned to that account. Some pages require an active authenticated session. |

The application does not establish a required phone model, GPS receiver accuracy, minimum network speed, or offline operating mode.

## 3. Getting started

### 3.1 Access and sign in

1. Open the MuniResQ URL supplied by the municipality.
2. Select **Login** and enter the email address and password registered for your account.
3. After successful sign-in, the application redirects according to the account role.
4. If the account is not approved, access to approved operational areas is denied and the session is redirected to the login page with an approval message.

The login screen is available at `/login`. Password-reset routes are present if the user needs to request a reset.

### 3.2 Registration and approval

The application has multiple registration paths:

- **General registration** collects name, email, and password. It does not itself assign an operational MuniResQ role.
- **Driver registration** collects contact details and creates a pending driver account. License number and expiry are not required or shown in the current registration/driver-management screens.
- **Admin registration** creates an account that remains pending until an authorized Super Admin approves it.
- Super Admins can also create an administrator account through **Admins**.

After submitting an account request, wait for approval. Pending accounts cannot use the approved role-protected operational pages. Approval and role assignment are separate from simply registering.

### 3.3 Sign out

Use **Logout** in the role navigation. The application ends the authenticated session.

## 4. Super Admin user guide

Super Admin pages are available only to authenticated, approved users with the `super-admin` role.

### 4.1 Dashboard

Open **Dashboard** from the Super Admin navigation to view the system summary cards currently supplied by the dashboard controller. The dashboard is a summary view; it is not a replacement for inspecting each incident or account record.

### 4.2 Review pending users

1. Open **Pending Users**.
2. Review each pending row, including the displayed name, email, badge information when available, and registration time.
3. Use **Approve** or **Reject** only after verifying the account request through the municipality's normal process.
4. Confirm the resulting status and role before the person attempts to use MuniResQ.

The approval action in this queue provisions a driver profile and assigns the driver role. Use the separate **Admins** section for administrator-account review; do not use the driver approval queue as an administrator approval substitute.

### 4.3 Manage administrator accounts

1. Open **Admins**.
2. Search by name, employee ID, or email, and optionally filter by status.
3. Select **Create Admin** to add an administrator account. Newly created accounts are pending.
4. For a pending account, review its details and choose **Approve** or **Reject**.
5. For an approved account, use **Edit** when changes are needed or **Suspend** to suspend access.

Available statuses displayed by the administrator-management page include pending, approved, rejected, and suspended.

### 4.4 Manage drivers and vehicle assignments

1. Open **Drivers** to review driver profiles and their displayed vehicle assignment.
2. Use the driver's assignment action to choose a vehicle from the options presented.
3. Confirm the assignment in the driver listing.

The approval flow may create a driver profile and establish an assignment when a vehicle is available. Super Admins can also manage ambulance/vehicle records from **Ambulances / Vehicles**.

Use **Active** or **Suspended** in the driver row to control whether an approved driver can be selected for new work. A suspended driver's existing incidents, dispatch history, and reports remain recorded. Use **Archive** only when there is no active dispatch; archived drivers appear under **Archived Drivers** and can be restored.

License number and expiry are legacy nullable fields retained in the database, but are not required or displayed in the current registration or driver-management workflow.

### 4.5 Manage vehicles

The **Ambulances / Vehicles** section supports creating and editing vehicle records. Vehicle statuses include available, on duty, and maintenance. Use **Archive** instead of deleting a vehicle record; vehicles with active dispatches cannot be archived. Archived vehicles remain available under **Archived Vehicles** and can be restored. Confirm vehicle data and availability before dispatch.

### 4.6 Backup and restore

1. Open **Backup & Restore**.
2. Select **Backup Now** to request a database backup.
3. Review the backup history and status/message shown.
4. Use **Download** to retrieve a listed backup file.
5. Use **Restore** only when authorized and after verifying the correct file. The page warns that restoring overwrites the current MySQL database.

Backup generation depends on the configured MySQL utilities and database environment. The current automated tests include a check that backup creation is rejected when the database is not MySQL; confirm successful production backup behavior in the deployed environment.

### 4.7 Settings

The **Settings** screen displays fields for system name, municipality name, contact number, email, hotline, and maintenance mode. The current update action returns a success message but does not persist those form values. Do not treat this page as a verified configuration-management facility.

### 4.8 Other available Super Admin functions

Super Admin accounts are admitted to the admin role route group as well as Super Admin routes. This means an approved Super Admin can also access the Admin/Dispatcher pages described below, subject to the routes' admin-or-super-admin middleware. The Admin sidebar includes **Audit Logs**; no separate Super Admin-only audit-log page is shown in its own navigation.

## 5. Admin/Dispatcher user guide

Admin/Dispatcher pages require authentication, an approved account, and either the `admin` or `super-admin` role.

### 5.1 Dashboard

Open **Dashboard** to review the operational summary and dashboard information, including the active panic-alert area and its empty state. Follow the municipality's communication procedures for a panic alert; the application does not provide a demonstrated panic-resolution workflow.

### 5.2 Create an incident

1. Open **Incidents**, then select **Create Incident**.
2. Enter the reporter name and select the incident type.
3. Select one of the available priorities:

   | Priority value | Display |
   |---|---|
   | Low | Green — Minor/Minimal |
   | Medium | Yellow — Delayed |
   | High | Red — Immediate/Critical |
   | Critical | Black — Critical |

4. Select the province, municipality/city, and barangay as prompted.
5. Enter the house number and street, purok, or landmark when available.
6. Review the generated full-location field. Use the map search, current-location, or map-point controls to set incident coordinates when appropriate. Check the map marker before saving.
7. Enter the reporter contact number, description, and attachments if available. These fields are optional in the current incident validation.
8. Select **Save Incident**.
9. Review the created incident number and saved details on the incident list or detail page.

Reporter name, incident type, and priority are required. Address components and coordinates are accepted as optional by server-side validation, although the create-page selectors are marked required in the browser interface. If browser validation prevents submission, complete the location selectors or contact the administrator.

Attachments accept up to 10 files, each up to 10 MB, with the file types constrained by the application. Do not upload unrelated or unnecessarily sensitive documents.

### 5.3 Search, edit, and view incidents

1. In **Incidents**, search by incident number, reporter, or location.
2. Narrow the list by incident type, status, and date range if needed.
3. Select **View** to inspect the incident details, dispatch and emergency time record, address, attachments, and available actions.
4. Select **Edit** to correct editable incident details. Workflow status is managed through dispatch/response actions rather than the ordinary incident edit form.
5. Use **Archived Records** to review archived incidents. Select **Restore** to return an archived record to the active list.

Archiving does not delete an incident record. Follow organizational retention and access procedures.

### 5.4 Dispatch a driver and vehicle

1. Open an eligible incident and select **Dispatch**, or open the **Dispatch Center**.
2. Review available drivers and vehicles. The incident dispatch screen may show recommended resources and ranked eligible resources when location data supports a distance calculation.
3. Select an available driver and vehicle from the lists.
4. Submit **Dispatch Incident**.
5. Verify that the dispatch is assigned to the intended incident and monitor the driver's response.

The Dispatch Center checks driver/vehicle availability and active assignments. Recommendations are advisory and depend on available and sufficiently fresh GPS data; they are not a guarantee of fastest response. A driver may be able to choose a different eligible vehicle when accepting the dispatch.

If no eligible driver or vehicle is listed, check resource availability and GPS status. Do not dispatch a vehicle that the system identifies as unavailable without resolving its status through the appropriate operational workflow.

### 5.5 Monitor GPS and operations

1. Open **GPS Monitoring** to view reported ambulance/vehicle positions on the map.
2. Check the displayed GPS status and freshness before relying on a position.
3. Use **Operations Center** and other available dashboard panels to review current operational information.
4. Where the interface offers GPS history, use it to review retained past location submissions.

The monitor refreshes location data, but it displays browser/device GPS submissions received by the application. It is not an independent vehicle-mounted tracking guarantee.

### 5.6 Notifications

1. Open **Notifications**.
2. Review each title, message, date, and read/unread status.
3. Select a notification to open it and mark it read, or use **Mark Read**.
4. Use **Mark All Read** only after reviewing the outstanding items.

Notifications are database-backed. A global notification is visible to administrators; a notification with a user owner is restricted to that user by the notification controller. The system does not establish SMS, email, or mobile push delivery.

### 5.7 Review and approve driver reports

1. Open the incident reports page (the reports list is provided by the application route `admin.reports.index`).
2. Review the incident number, driver, summary, status, and submitted time.
3. Open associated incident details as needed to check the response record.
4. Select **Approve** for a pending report only after reviewing its information.
5. Verify the resulting incident, dispatch, driver, and vehicle states.

In the current implementation, approval marks the report approved, changes the incident to closed, completes the latest dispatch if necessary, marks the driver and vehicle available, creates a private unread notification for the reporting driver, and writes an audit record. A report-rejection action is not provided on the reviewed report page.

### 5.8 Reports and analytics

The application contains a Reports Center and separate reporting endpoints/pages, including response-time analytics, driver-performance analytics, vehicle-utilization information, and report export functions. Use the available reporting links/screens to review the data shown; values depend on the completeness of recorded incident, dispatch, GPS, and fleet records. A report statistic should not be interpreted as verified real-world performance unless the underlying records have been checked.

### 5.9 Vehicle maintenance

1. Open **Vehicle Maintenance** from the available admin navigation.
2. Select **New Maintenance Record**.
3. Select a vehicle; enter the maintenance type and scheduled date; choose a maintenance status; and provide a description if useful.
4. Set the vehicle status deliberately. A non-completed maintenance record normally places the vehicle in maintenance unless a different vehicle status is selected.
5. Save the record and review it in the maintenance list.
6. Use **Edit**, **Complete**, or **Archive** as appropriate. Completing a record marks the vehicle available. Archived maintenance records remain available under **Archived Records** and can be restored.

The maintenance form exposes vehicle statuses such as available, active/on duty, maintenance, and out of service. Its controller normalizes active to on duty and out of service to maintenance in the vehicle record.

## 6. Driver user guide

Driver pages require an authenticated, approved account with the `driver` role and a driver profile.

### 6.1 Driver dashboard and assignment

1. Open **Dashboard** or **My Assignment**.
2. Review the incident number, location/address, classification, and dispatch information.
3. Check the displayed driver and GPS status.
4. Use **Navigation** when the assigned incident provides usable location information. The application does not guarantee a route when the incident has no valid coordinates.
5. Use **Dispatch History** to review past assignments.

### 6.2 Accept or decline a dispatch

For a new assignment:

1. Review the incident and dispatch information.
2. In **Choose Vehicle**, select an available vehicle shown by the application.
3. Select **Accept Dispatch** to accept and begin the response workflow.
4. If unable to respond, select **Decline Dispatch** promptly so the incident can return to the pending queue for reassignment.

Acceptance requires an eligible assigned dispatch and a currently available vehicle. If the chosen vehicle has become busy, the application returns an error and asks for another available vehicle. Declining cancels the dispatch, returns the incident to pending, and releases the vehicle when it is no longer used by another active dispatch.

### 6.3 GPS tracking

1. Keep the Driver Dashboard open during an active response.
2. Allow location access when the browser asks.
3. Keep location services enabled and the device connected to the network.
4. Check the GPS status shown on the dashboard. If the application reports unavailable, permission denied, delayed, or stale data, follow the Troubleshooting section.

The dashboard uses browser geolocation and submits updates periodically while the page is visible. GPS positions received during an active dispatch are recorded and may synchronize to the assigned vehicle. Geofence automation can record arrival at scene and departure from scene only when the required coordinates, freshness, and accuracy conditions are satisfied.

### 6.4 Update response milestones

Use the next action presented on the Dashboard for the active incident. Depending on dispatch state and recorded timestamps, the interface supports:

- En Route (normally set as part of dispatch acceptance/GPS-driven response behavior)
- Arrived at Scene (automatically recorded by eligible GPS/geofence data)
- At Patient (manual action)
- Departed from Scene (automatically recorded after At Patient when eligible GPS shows departure; a route action also exists)
- Arrived at Hospital (manual action)
- Returned to Base (manual confirmation)
- Complete Incident
- Ready for Next Mission

The application enforces state and timestamp prerequisites. For example, At Patient requires an at-scene time; hospital arrival requires a departure time; and the incident must be completed before the driver/vehicle is marked ready for a new mission. If a milestone action is not available or returns an eligibility message, do not attempt to bypass it; contact the dispatcher and continue using approved radio/phone procedures.

The current implementation has both automatic geofence transitions and manual milestone endpoints. The interface/action eligibility depends on the active dispatch state, so do not assume every milestone is automatically recorded.

### 6.5 Complete the mission and report

1. Record the required response milestones using the dashboard's available action.
2. Select **Complete Incident** when the application enables it.
3. Follow the displayed readiness flow. The vehicle remains on duty until ready-for-next-mission is confirmed.
4. Open **Reports** when the completed incident is available for reporting.
5. Enter the required **Summary of Response**, **Actions Taken**, and **Casualties**. Add remarks when useful.
6. Select **Submit Report** and confirm that the submission succeeded.

The report form is available only for an eligible completed incident without an existing report. A submitted report is pending until an administrator reviews and approves it. Submission notifies the admin side; approval creates a private notification to the driver.

### 6.6 Profile and settings

Use **Profile** for the account profile page. **Settings** opens the driver settings screen; consult the current page for what can be changed. A separate driver notification center is not present in the reviewed navigation.

## 7. Emergency response workflow

The principal workflow supported by the current application is:

1. **Record:** Admin/Dispatcher creates the incident and records caller, location, classification, and priority details.
2. **Assign:** Admin/Dispatcher assigns an available driver and vehicle.
3. **Respond:** Driver reviews the dispatch and accepts it with a currently available vehicle, or declines it.
4. **Track:** Driver dashboard submits browser GPS while active; eligible location data updates GPS history and vehicle location.
5. **Record milestones:** The application records response events through dispatch acceptance, GPS/geofence events, and available manual controls.
6. **Complete mission:** Driver completes the incident and confirms readiness when available. A completed mission does not by itself equal administrative report approval.
7. **Submit report:** Driver submits required report fields for a completed, reportable incident.
8. **Review:** Admin approves the pending report; this closes the incident and completes/releases associated resources as implemented.

The exact status labels on the incident and dispatch records are distinct. The application uses incident statuses such as pending, dispatched, responding, completed, and closed, while dispatch records have their own statuses. The driver dashboard presents applicable next actions; follow those displayed actions rather than treating these labels as interchangeable.

## 8. Notifications

The Admin Notifications page lists title, message, creation date, and read/unread status. Administrators can open a notification to mark it read, mark one notification read, or mark all listed notifications read.

Implemented notification examples include new incidents, panic alerts, maintenance events, driver report submissions, and vehicle selection/dispatch events. Report-approval notifications are associated with the specific reporting driver and are private to that user. Other generated operational notifications may be global.

Viewing the notification list does not itself mark all notifications read. No external email, SMS, or mobile push delivery is established by the current code.

## 9. Troubleshooting

| Issue | What to check |
|---|---|
| GPS unavailable or permission denied | Enable device location services, allow location access for the site, ensure the browser supports location, and keep the Driver Dashboard open. |
| GPS is delayed or stale | Check network access and device location signal. Wait for a new update; admins should treat stale positions and recommendations cautiously. |
| Invalid or missing coordinates | Confirm the incident map marker is set correctly. Geofence arrival requires valid incident coordinates; without them, automatic arrival cannot be confirmed. |
| Vehicle is unavailable when accepting | Refresh the assignment/dashboard and choose a currently available vehicle. A vehicle that became busy cannot be accepted. Contact the dispatcher if no eligible vehicle is listed. |
| Dispatch was declined | The incident returns to pending and can be reassigned by the dispatcher. Confirm the incident appears in the admin dispatch queue. |
| Unauthorized access or redirected to login | Confirm that the correct account is signed in, the account is approved, and the role matches the page. Ask a Super Admin to review account status and role assignment. |
| Incident form rejects information | Review required reporter name, incident type, priority, and location selectors. Correct invalid field values and submit again. Validation errors are returned by the application. |
| Report is not available | The driver report action requires a completed incident assigned to that driver and no previous report. |
| Backup fails | Backup support depends on the configured MySQL database and utilities. Contact the system administrator; do not repeatedly attempt a production restore. |
| Map is blank or tiles do not load | Check network access to configured map services. Use verified location details and contact the administrator if the map remains unavailable. |

Do not use invalid, guessed, or unrelated coordinates to force a GPS milestone.

## 10. Best practices

### Super Admins

- Verify identity and role before approving or rejecting an account.
- Use the administrator-management workflow for administrator accounts and the pending-user workflow for driver registrations.
- Keep vehicle availability, driver assignment, and account status accurate.
- Treat database backups and downloaded incident information as sensitive operational data.
- Do not assume that the settings screen saves configuration; verify any operational configuration through the responsible system administrator.

### Admins/Dispatchers

- Record accurate reporter, contact, address, priority, and coordinate information.
- Review resource availability before dispatch and treat GPS-based distance recommendations as advisory.
- Monitor the driver's status and contact them through established operational channels if GPS or a milestone is missing.
- Review submitted reports before approval; approval closes the incident and updates associated resources.
- Use audit-sensitive correction functions only for legitimate corrections and preserve accurate source information.

### Drivers

- Review the assigned incident and vehicle before accepting.
- Decline promptly when unable to respond.
- Keep the dashboard active, location permission enabled, and device network available during response.
- Use the available milestone controls accurately; report missing or incorrect GPS data to the dispatcher.
- Complete the report promptly and truthfully, including actions and casualty information requested by the form.

## 11. Frequently asked questions

### Does registration grant access immediately?

No. Driver and administrator accounts created through their role-specific registration paths require approval. Approved status and the appropriate role are both needed for protected role pages.

### Does MuniResQ receive emergency calls automatically?

The reviewed implementation provides an incident-entry form; automatic call-center intake is not established.

### Does driver GPS continue when the dashboard is closed?

Continuous background tracking is not established. The dashboard submits browser location updates while active/visible, so keep it open during a mission.

### Will the system always automatically record arrival at scene?

No. Geofence arrival requires valid incident coordinates and sufficiently fresh, accurate driver GPS. If those conditions are not met, use the displayed controls and coordinate with the dispatcher.

### Does a driver report immediately close an incident?

No. Driver submission creates a pending report. Administrative approval is the step that closes the incident in the current workflow.

### Can a driver reject an incident report?

The reviewed administrator report page provides an approval action for pending reports; a report-rejection action is not shown there.

### Are notifications sent to phones or email?

The confirmed implementation stores notifications in the application database. External SMS, email, or push delivery is not established.

### Why can a position or ETA be missing?

The incident or vehicle may lack valid coordinates, driver GPS may be missing or stale, or the resource may not meet eligibility conditions. Recommendations are not guaranteed when location inputs are unavailable.

### Can the Super Admin settings form change the live system configuration?

The current settings update action displays a success message but does not persist the submitted values. Do not rely on it to change system behavior.

## 12. Functionality verified by Feature tests

The project's Feature tests currently exercise parts of the following areas:

- Login, logout, registration, email verification, password reset, and password confirmation.
- Role-based dashboard access, driver registration, approved/pending account states, and dashboard rendering.
- Incident coordinate storage, address and house-number persistence, incident editing, and priority display consistency.
- Dispatch assignment, driver acceptance and decline, vehicle selection/availability checks, and driver/vehicle status updates.
- GPS coordinate validation, vehicle-location synchronization, stale/missing GPS filtering, geofence arrival/departure conditions, and repeated-update handling.
- Driver dashboard dispatch actions and response milestone sequence.
- Incident report submission and administrator approval effects.
- Notification-center behavior and emergency timestamp corrections.
- Selected admin-module and analytics rendering, plus database-backup rejection in an unsupported database environment.

Tests do not prove that external map services, production GPS devices, backup utilities, network delivery, or all operational scenarios work in every deployment. Consult the deployed system behavior and municipal procedures when operational outcomes are uncertain.
