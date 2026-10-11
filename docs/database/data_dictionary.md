# MuniResQ Database Data Dictionary

This dictionary documents the schema of the configured project database as inspected read-only through Laravel. The active configured connection was SQLite. Types, nullability, defaults, indexes, and foreign-key constraints below are from live SQLite metadata; SQLite does not enforce declared VARCHAR lengths or NUMERIC precision/scale, so those dimensions are marked unavailable where applicable. Descriptions are based on migrations, models, relationships, casts, and relevant application usage; inferred purpose is not database-enforced meaning.

**Live schema totals:** 32 tables; 270 columns; 52 index records (including primary and unique indexes); 27 foreign-key constraints.

Application tables are listed first. The archived hijack table is documented separately as a legacy schema item. Laravel framework and Spatie permission package tables are in the appendix.

## MuniResQ application tables

### `ambulance_locations`

Stores timestamped GPS coordinates associated with an ambulance.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this ambulance location record. |
| ambulance_id | integer | Not applicable or not declared | - | - | No | None declared | - | Ambulance associated with this location record; the live database has no enforced foreign-key constraint for this column. |
| latitude | numeric | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Latitude coordinate recorded for the ambulance. |
| longitude | numeric | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Longitude coordinate recorded for the ambulance. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |

### `ambulances`

Stores fleet vehicle identifiers, type, operational status, coordinates, and archive metadata.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this ambulance record. |
| plate_number | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | UNIQUE `ambulances_plate_number_unique` (plate_number) | Vehicle registration plate; uniquely indexed. |
| vehicle_name | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Display name or model name used for the vehicle. |
| vehicle_type | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'ambulance' | - | Classification of the response vehicle. |
| status | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'available' | - | Current fleet availability or duty status. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |
| latitude | numeric | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Latitude coordinate stored on the ambulance record. |
| longitude | numeric | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Longitude coordinate stored on the ambulance record. |
| archived_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | INDEX `ambulances_archived_at_index` (archived_at) | Date and time the ambulance record was archived, if applicable. |
| archived_by | integer | Not applicable or not declared | FK | users.id | Yes | None declared | - | User who archived this ambulance, if applicable. |

### `audit_logs`

Records user-associated administrative or application actions for audit purposes.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this audit log record. |
| user_id | integer | Not applicable or not declared | FK | users.id | Yes | None declared | - | User associated with the audited action, if available. |
| action | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Action performed. |
| module | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Application module where the action occurred. |
| description | text | Not applicable or not declared | - | - | Yes | None declared | - | Details of the audited action. |
| ip_address | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | IP address associated with the action, if recorded. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |

### `backup_logs`

Records backup operation metadata and outcomes.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this backup log record. |
| type | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Type of backup operation. |
| filename | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Name of the generated backup file. |
| status | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Outcome or state of the backup operation. |
| path | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Storage path of the backup, if available. |
| message | text | Not applicable or not declared | - | - | Yes | None declared | - | Additional result or error details, if supplied. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |

### `dispatches`

Records assignments and response workflow timestamps connecting incidents, drivers, and vehicles.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this dispatch record. |
| incident_id | integer | Not applicable or not declared | FK | incidents.id | No | None declared | - | Incident for which this dispatch was created. |
| driver_id | integer | Not applicable or not declared | FK | drivers.id | No | None declared | - | Driver assigned to this dispatch. |
| vehicle_id | integer | Not applicable or not declared | FK | ambulances.id | Yes | None declared | - | Ambulance assigned to this dispatch, if any. |
| status | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'pending' | - | Current dispatch workflow state. |
| assigned_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time the dispatch was assigned. |
| accepted_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time the driver accepted the dispatch. |
| arrived_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time the driver arrived at the incident. |
| completed_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time the dispatch was completed. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |
| assigned_by | integer | Not applicable or not declared | FK | users.id | Yes | None declared | - | User who assigned the dispatch, if recorded. |
| en_route_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time the driver began travelling to the incident. |
| declined_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time the dispatch was declined. |

### `drivers`

Stores driver profile, licence, operational status, and archive information linked to a user.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this driver record. |
| user_id | integer | Not applicable or not declared | FK | users.id | No | None declared | - | User account associated with this driver. |
| badge_id | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | UNIQUE `drivers_badge_id_unique` (badge_id) | Unique driver badge identifier. |
| contact_number | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Driver contact telephone number. |
| license_number | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Driver licence number. |
| license_expiry | date | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Expiry date of the driver licence. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |
| status | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'available' | - | Current operational driver status. |
| management_status | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'active' | - | Management eligibility state; migration default is active. |
| archived_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | INDEX `drivers_archived_at_index` (archived_at) | Date and time the driver record was archived, if applicable. |
| archived_by | integer | Not applicable or not declared | FK | users.id | Yes | None declared | - | User who archived this driver, if applicable. |

### `gps_locations`

Stores driver GPS coordinates, recorded time, and optional speed measurements.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this GPS location record. |
| driver_id | integer | Not applicable or not declared | FK | drivers.id | No | None declared | - | Driver whose location is recorded. |
| latitude | numeric | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Latitude coordinate received from the driver device. |
| longitude | numeric | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Longitude coordinate received from the driver device. |
| recorded_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | No | None declared | - | Date and time the GPS sample was recorded. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |
| speed_kmh | numeric | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Recorded speed in kilometres per hour, if provided. |
| speed_status | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Speed-limit status classification, if provided. |
| speed_limit_kmh | numeric | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Applicable speed limit in kilometres per hour, if provided. |

### `incident_attachments`

Stores file metadata associated with an incident and optional uploader.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this incident attachment record. |
| incident_id | integer | Not applicable or not declared | FK | incidents.id | No | None declared | INDEX `incident_attachments_incident_id_created_at_index` (incident_id, created_at) | Incident to which the attachment belongs. |
| uploaded_by | integer | Not applicable or not declared | FK | users.id | Yes | None declared | - | User who uploaded the attachment, if recorded. |
| disk | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'local' | - | Laravel storage disk holding the file. |
| path | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Storage path of the attachment. |
| original_name | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Original filename supplied for the uploaded file. |
| mime_type | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Media type recorded for the file, if available. |
| size | integer | Not applicable or not declared | - | - | No | '0' | - | Attachment size in bytes. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | INDEX `incident_attachments_incident_id_created_at_index` (incident_id, created_at) | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |

### `incident_reports`

Stores driver-submitted reports associated with incidents.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this incident report record. |
| incident_id | integer | Not applicable or not declared | FK | incidents.id | No | None declared | - | Incident described by this report. |
| driver_id | integer | Not applicable or not declared | FK | drivers.id | No | None declared | - | Driver who submitted or is associated with this report. |
| summary | text | Not applicable or not declared | - | - | No | None declared | - | Summary of the incident response. |
| actions_taken | text | Not applicable or not declared | - | - | No | None declared | - | Actions taken by the responder. |
| casualties | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Casualty information, if supplied. |
| remarks | text | Not applicable or not declared | - | - | Yes | None declared | - | Additional report remarks, if supplied. |
| submitted_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time the report was submitted. |
| status | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'available' | - | Current report workflow state. The live database default remains available; a migration proposing pending is not yet applied. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |

### `incidents`

Stores emergency incident details, location, priority, lifecycle timestamps, and optional assigned resources.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this incident record. |
| incident_number | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | UNIQUE `incidents_incident_number_unique` (incident_number) | Unique incident reference number. |
| reporter_name | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Name of the person reporting the incident. |
| contact_number | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Reporter contact telephone number, if provided. |
| incident_type | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Classification of the emergency incident. |
| location | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Free-text incident location/address. |
| description | text | Not applicable or not declared | - | - | Yes | None declared | - | Narrative description of the incident. |
| status | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'pending' | INDEX `incidents_archived_at_status_index` (archived_at, status) | Current incident workflow state. |
| ambulance_id | integer | Not applicable or not declared | FK | ambulances.id | Yes | None declared | - | Ambulance assigned to the incident, if any. |
| driver_id | integer | Not applicable or not declared | FK | drivers.id | Yes | None declared | - | Driver assigned to the incident, if any. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |
| latitude | numeric | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Latitude coordinate of the incident location. |
| longitude | numeric | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Longitude coordinate of the incident location. |
| priority | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'Medium' | - | Incident priority classification. |
| archived_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | INDEX `incidents_archived_at_status_index` (archived_at, status) | Date and time the incident record was archived, if applicable. |
| archived_by | integer | Not applicable or not declared | FK | users.id | Yes | None declared | - | User who archived this incident, if applicable. |
| house_number | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | House or building number portion of the incident address. |
| street | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Street portion of the incident address. |
| barangay | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Barangay portion of the incident address. |
| city | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | City or municipality portion of the incident address. |
| province | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Province portion of the incident address. |
| completed_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time incident handling was completed. |
| closed_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time the incident was closed. |
| call_received_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time the emergency call was received. |
| response_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time response began. |
| at_scene_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time the response unit arrived at the scene. |
| at_patient_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time the response unit reached the patient. |
| depart_scene_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time the response unit departed the scene. |
| at_hospital_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time the response unit reached the hospital. |
| return_to_base_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time the response unit returned to base. |

### `notifications`

Stores notifications, optional user ownership, read state, and polymorphic related-record fields.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this notification record. |
| user_id | integer | Not applicable or not declared | FK | users.id | Yes | None declared | - | User receiving the notification; null may indicate a notification visible globally. |
| title | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Notification heading. |
| message | text | Not applicable or not declared | - | - | No | None declared | - | Notification body text. |
| type | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Notification category or event type. |
| is_read | tinyint | SQLite declared tinyint; Boolean/width semantics are application-level | - | - | No | '0' | - | Indicates whether the notification has been marked as read. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |
| related_id | integer | Not applicable or not declared | - | - | Yes | None declared | INDEX `notifications_related_type_related_id_index` (related_type, related_id) | Identifier of the associated record; interpreted together with related_type and not protected by a conventional foreign key. |
| related_type | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | INDEX `notifications_related_type_related_id_index` (related_type, related_id) | Model or record type associated with related_id. |

### `panic_alerts`

Stores driver panic-alert coordinates, trigger time, and resolution state.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this panic alert record. |
| driver_id | integer | Not applicable or not declared | - | - | No | None declared | - | Driver associated with the panic alert; the live database has no enforced foreign-key constraint for this column. |
| latitude | numeric | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Latitude coordinate captured for the panic alert. |
| longitude | numeric | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Longitude coordinate captured for the panic alert. |
| triggered_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | No | None declared | - | Date and time the panic alert was triggered. |
| resolved | tinyint | SQLite declared tinyint; Boolean/width semantics are application-level | - | - | No | '0' | - | Indicates whether the panic alert has been resolved. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |

### `response_equipment`

Stores emergency response equipment inventory and condition.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this response equipment item record. |
| name | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Equipment name. |
| category | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'General' | INDEX `response_equipment_status_category_index` (status, category) | Equipment category. |
| serial_number | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | UNIQUE `response_equipment_serial_number_unique` (serial_number) | Optional equipment serial number; unique when populated. |
| quantity | integer | Not applicable or not declared | - | - | No | '1' | - | Quantity of this equipment item. |
| condition | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'Good' | - | Recorded physical condition. |
| status | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'available' | INDEX `response_equipment_status_category_index` (status, category) | Current inventory availability state. |
| storage_location | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Storage location, if recorded. |
| notes | text | Not applicable or not declared | - | - | Yes | None declared | - | Additional inventory notes. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |

### `system_settings`

Currently stores only its identifier and Laravel timestamps in the live database.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Primary identifier. No setting attributes exist in the live table. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |

### `users`

Stores user account, profile, approval-state, and account-creation references.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this user record. |
| name | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | The account holder's display name. |
| email | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | UNIQUE `users_email_unique` (email) | The account email address; uniquely indexed and used for account identification. |
| email_verified_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time the account email address was verified. |
| password | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | The account password hash; application code casts this value as a hashed password. |
| remember_token | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Token used by Laravel persistent authentication. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |
| badge_id | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | UNIQUE `users_badge_id_unique` (badge_id) | Optional user badge identifier. |
| status | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'pending' | - | Account approval or suspension state; the live SQLite schema enforces pending, approved, rejected, or suspended. |
| created_by | integer | Not applicable or not declared | FK | users.id | Yes | None declared | - | Optional user who created this account; self-references users.id. |
| approved_by | integer | Not applicable or not declared | FK | users.id | Yes | None declared | - | Optional user who approved this account; self-references users.id. |
| approved_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time this account was approved, when recorded. |
| employee_id | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | UNIQUE `users_employee_id_unique` (employee_id) | Optional employee identifier; unique when populated under SQLite unique-index semantics. |
| position | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | The user's job position. |
| department | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | The user's department label. |
| contact_number | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | The user's contact telephone number. |
| office | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | The user's office label. |

### `vehicle_driver_assignments`

Records driver-to-ambulance assignments and assignment state.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this vehicle-driver assignment record. |
| driver_id | integer | Not applicable or not declared | FK | drivers.id | No | None declared | - | Driver assigned to the ambulance. |
| ambulance_id | integer | Not applicable or not declared | FK | ambulances.id | No | None declared | - | Ambulance assigned to the driver. |
| status | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'active' | - | Assignment state, such as active or inactive. |
| assigned_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time the assignment was made. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |

### `vehicle_maintenances`

Records scheduled and completed ambulance maintenance and archive metadata.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this vehicle maintenance record. |
| ambulance_id | integer | Not applicable or not declared | FK | ambulances.id | No | None declared | - | References ambulances.id for this record. |
| maintenance_type | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Category of maintenance work scheduled or performed. |
| description | text | Not applicable or not declared | - | - | Yes | None declared | - | Details describing the maintenance work. |
| scheduled_date | date | SQLite date/time affinity; storage format not enforced | - | - | No | None declared | - | Date scheduled for maintenance. |
| completed_date | date | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date maintenance was completed. |
| status | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'scheduled' | - | Current maintenance workflow state. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |
| archived_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | INDEX `vehicle_maintenances_archived_at_index` (archived_at) | Date and time when this record was archived, if applicable. |
| archived_by | integer | Not applicable or not declared | FK | users.id | Yes | None declared | - | References users.id for this record. |

### `vulnerable_areas`

Stores mapped vulnerable areas, population counts, classification, and notes.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this vulnerable area record. |
| name | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Name assigned to the vulnerable area. |
| area_type | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'Household Cluster' | - | Classification of the area. |
| address | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Textual address of the vulnerable area. |
| household_count | integer | Not applicable or not declared | - | - | No | '0' | - | Number of households recorded for the area. |
| population_count | integer | Not applicable or not declared | - | - | No | '0' | - | Population count recorded for the area. |
| vulnerability_level | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'Medium' | INDEX `vulnerable_areas_status_vulnerability_level_index` (status, vulnerability_level) | Assigned vulnerability classification. |
| latitude | numeric | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Latitude coordinate marking the area. |
| longitude | numeric | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Longitude coordinate marking the area. |
| notes | text | Not applicable or not declared | - | - | Yes | None declared | - | Additional notes about the area. |
| status | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'active' | INDEX `vulnerable_areas_status_vulnerability_level_index` (status, vulnerability_level) | Current management status of the area. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |


## Possible legacy table (not an active feature)

### `archived_hijack_alerts`

Retained legacy records from the discontinued hijack-alert feature; not an active feature table.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this legacy archived hijack alert record. |
| driver_id | integer | Not applicable or not declared | - | - | No | None declared | - | Driver identifier retained in the legacy table; no foreign-key constraint is enforced. |
| latitude | numeric | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Latitude coordinate associated with this record. |
| longitude | numeric | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Longitude coordinate associated with this record. |
| status | varchar | Not represented/enforced in live SQLite schema | - | - | No | 'active' | - | Legacy alert status retained in the archived table; this is not an active workflow. |
| triggered_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | No | None declared | - | Date and time the legacy alert was triggered. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |


## Laravel framework tables

### `cache`

Stores application cache values and expiration timestamps.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| key | varchar | Not represented/enforced in live SQLite schema | PK | - | No | None declared | PRIMARY `sqlite_autoindex_cache_1` (key) | Unique cache entry key. |
| value | text | Not applicable or not declared | - | - | No | None declared | - | Serialized cached value. |
| expiration | integer | Not applicable or not declared | - | - | No | None declared | INDEX `cache_expiration_index` (expiration) | Cache expiration time represented as an integer timestamp. |

### `cache_locks`

Stores cache lock ownership and expiration timestamps.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| key | varchar | Not represented/enforced in live SQLite schema | PK | - | No | None declared | PRIMARY `sqlite_autoindex_cache_locks_1` (key) | Unique cache lock key. |
| owner | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Owner token for the cache lock. |
| expiration | integer | Not applicable or not declared | - | - | No | None declared | INDEX `cache_locks_expiration_index` (expiration) | Lock expiration time represented as an integer timestamp. |

### `failed_jobs`

Stores queued jobs that failed, including their serialized payload and exception text.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this failed job record. |
| uuid | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | UNIQUE `failed_jobs_uuid_unique` (uuid) | Unique identifier of the failed job. |
| connection | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | INDEX `failed_jobs_connection_queue_failed_at_index` (connection, queue, failed_at) | Queue connection used by the job. |
| queue | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | INDEX `failed_jobs_connection_queue_failed_at_index` (connection, queue, failed_at) | Queue name. |
| payload | text | Not applicable or not declared | - | - | No | None declared | - | Serialized job payload. |
| exception | text | Not applicable or not declared | - | - | No | None declared | - | Captured exception details. |
| failed_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | No | CURRENT_TIMESTAMP | INDEX `failed_jobs_connection_queue_failed_at_index` (connection, queue, failed_at) | Date and time the job failure was recorded. |

### `job_batches`

Stores queue batch progress and result metadata.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | varchar | Not represented/enforced in live SQLite schema | PK | - | No | None declared | PRIMARY `sqlite_autoindex_job_batches_1` (id) | Batch identifier. |
| name | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Batch display name. |
| total_jobs | integer | Not applicable or not declared | - | - | No | None declared | - | Total jobs in the batch. |
| pending_jobs | integer | Not applicable or not declared | - | - | No | None declared | - | Jobs still pending in the batch. |
| failed_jobs | integer | Not applicable or not declared | - | - | No | None declared | - | Jobs that failed in the batch. |
| failed_job_ids | text | Not applicable or not declared | - | - | No | None declared | - | Serialized identifiers of jobs that failed. |
| options | text | Not applicable or not declared | - | - | Yes | None declared | - | Serialized batch options, if supplied. |
| cancelled_at | integer | Not applicable or not declared | - | - | Yes | None declared | - | Integer timestamp when the batch was cancelled, if applicable. |
| created_at | integer | Not applicable or not declared | - | - | No | None declared | - | Integer timestamp when the batch was created. |
| finished_at | integer | Not applicable or not declared | - | - | Yes | None declared | - | Integer timestamp when the batch finished, if complete. |

### `jobs`

Stores queued job payloads and scheduling state.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this job record. |
| queue | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | INDEX `jobs_queue_index` (queue) | Queue name for the job. |
| payload | text | Not applicable or not declared | - | - | No | None declared | - | Serialized queued job payload. |
| attempts | integer | Not applicable or not declared | - | - | No | None declared | - | Number of processing attempts. |
| reserved_at | integer | Not applicable or not declared | - | - | Yes | None declared | - | Integer timestamp when the job was reserved, if reserved. |
| available_at | integer | Not applicable or not declared | - | - | No | None declared | - | Integer timestamp when the job becomes available. |
| created_at | integer | Not applicable or not declared | - | - | No | None declared | - | Integer timestamp when the job was enqueued. |

### `migrations`

Tracks migrations recorded as applied by Laravel.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this migration record. |
| migration | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Migration name recorded as applied. |
| batch | integer | Not applicable or not declared | - | - | No | None declared | - | Migration batch number. |

### `password_reset_tokens`

Stores password-reset tokens keyed by account email.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| email | varchar | Not represented/enforced in live SQLite schema | PK | - | No | None declared | PRIMARY `sqlite_autoindex_password_reset_tokens_1` (email) | Email address used to identify the account requesting a password reset. |
| token | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | - | Password-reset token value. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time the token was created. |

### `sessions`

Stores web-session payloads and activity metadata.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | varchar | Not represented/enforced in live SQLite schema | PK | - | No | None declared | PRIMARY `sqlite_autoindex_sessions_1` (id) | Unique key identifying this session record. |
| user_id | integer | Not applicable or not declared | - | - | Yes | None declared | INDEX `sessions_user_id_index` (user_id) | Authenticated user identifier, when the session belongs to a user; indexed but not declared as a foreign key. |
| ip_address | varchar | Not represented/enforced in live SQLite schema | - | - | Yes | None declared | - | Client IP address associated with the session. |
| user_agent | text | Not applicable or not declared | - | - | Yes | None declared | - | Client user-agent string. |
| payload | text | Not applicable or not declared | - | - | No | None declared | - | Serialized session data. |
| last_activity | integer | Not applicable or not declared | - | - | No | None declared | INDEX `sessions_last_activity_index` (last_activity) | Integer timestamp of the last session activity. |


## Spatie permission package tables

### `model_has_permissions`

Polymorphic pivot records assigning permissions to models.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| permission_id | integer | Not applicable or not declared | PK, FK | permissions.id | No | None declared | PRIMARY `sqlite_autoindex_model_has_permissions_1` (permission_id, model_id, model_type) | Permission assigned to the polymorphic model. |
| model_type | varchar | Not represented/enforced in live SQLite schema | PK | - | No | None declared | INDEX `model_has_permissions_model_id_model_type_index` (model_id, model_type); PRIMARY `sqlite_autoindex_model_has_permissions_1` (permission_id, model_id, model_type) | Fully qualified model type for the polymorphic assignment. |
| model_id | integer | Not applicable or not declared | PK | - | No | None declared | INDEX `model_has_permissions_model_id_model_type_index` (model_id, model_type); PRIMARY `sqlite_autoindex_model_has_permissions_1` (permission_id, model_id, model_type) | Identifier of the polymorphic model; target table depends on model_type. |

### `model_has_roles`

Polymorphic pivot records assigning roles to models.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| role_id | integer | Not applicable or not declared | PK, FK | roles.id | No | None declared | PRIMARY `sqlite_autoindex_model_has_roles_1` (role_id, model_id, model_type) | Role assigned to the polymorphic model. |
| model_type | varchar | Not represented/enforced in live SQLite schema | PK | - | No | None declared | INDEX `model_has_roles_model_id_model_type_index` (model_id, model_type); PRIMARY `sqlite_autoindex_model_has_roles_1` (role_id, model_id, model_type) | Fully qualified model type for the polymorphic assignment. |
| model_id | integer | Not applicable or not declared | PK | - | No | None declared | INDEX `model_has_roles_model_id_model_type_index` (model_id, model_type); PRIMARY `sqlite_autoindex_model_has_roles_1` (role_id, model_id, model_type) | Identifier of the polymorphic model; target table depends on model_type. |

### `permissions`

Stores permission names and authentication guard names.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this permission record. |
| name | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | UNIQUE `permissions_name_guard_name_unique` (name, guard_name) | Permission name. |
| guard_name | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | UNIQUE `permissions_name_guard_name_unique` (name, guard_name) | Authentication guard associated with the permission. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |

### `role_has_permissions`

Associates roles with permissions.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| permission_id | integer | Not applicable or not declared | PK, FK | permissions.id | No | None declared | PRIMARY `sqlite_autoindex_role_has_permissions_1` (permission_id, role_id) | Permission associated with the role. |
| role_id | integer | Not applicable or not declared | PK, FK | roles.id | No | None declared | PRIMARY `sqlite_autoindex_role_has_permissions_1` (permission_id, role_id) | Role receiving the permission. |

### `roles`

Stores role names and authentication guard names.

| Column | Live SQLite type | Length / precision / format | PK / FK | References | Nullable | Default | Indexes / unique constraints | Description |
|---|---|---|---|---|---|---|---|---|
| id | integer | Not applicable or not declared | PK, AUTOINCREMENT | - | No | None declared | PRIMARY `primary` (id) | Unique key identifying this role record. |
| name | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | UNIQUE `roles_name_guard_name_unique` (name, guard_name) | Role name. |
| guard_name | varchar | Not represented/enforced in live SQLite schema | - | - | No | None declared | UNIQUE `roles_name_guard_name_unique` (name, guard_name) | Authentication guard associated with the role. |
| created_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was created. |
| updated_at | datetime | SQLite date/time affinity; storage format not enforced | - | - | Yes | None declared | - | Date and time when this record was last updated. |
