# MuniResQ Schema Verification

## Inspection result

The Laravel-configured project database was reachable and inspected using read-only schema metadata calls. Its configured driver is **SQLite**. No application/business records were queried or changed. Laravel's migration-status metadata was read to identify applied and pending migrations. No credentials or connection secrets were included in the output.

This is the locally configured project database, not a verified connection to the Railway/production database. Therefore, this report must not be treated as a production schema audit.

## Sources inspected

- All 45 PHP files under `database/migrations/`, including additive migrations, the hijack table archive/rename, and the currently pending incident-report status migration.
- `database/schema/` and SQL schema dump locations: no schema dump files were found.
- All files under `app/Models/`, including fillable attributes, casts, scopes, and Eloquent relationships.
- Relevant validation and application use in GPS, incident, vulnerable-area, driver, dispatch, notification, and administrative code.
- Read-only Laravel migration status (`php artisan migrate:status`).
- Live SQLite schema metadata for tables, columns, declared SQLite types, defaults, nullability, primary/unique/ordinary indexes, foreign keys, and live `CREATE TABLE` declarations.

The live metadata inspection returned 32 tables, 270 columns, 52 index records (including primary and unique indexes), and 27 foreign-key constraints.

## Table inventory and counts

| Category | Tables | Count |
|---|---|---:|
| Active MuniResQ application schema | `ambulance_locations`, `ambulances`, `audit_logs`, `backup_logs`, `dispatches`, `drivers`, `gps_locations`, `incident_attachments`, `incident_reports`, `incidents`, `notifications`, `panic_alerts`, `response_equipment`, `system_settings`, `vehicle_driver_assignments`, `vehicle_maintenances`, `vulnerable_areas`, `users` | 18 |
| Possible legacy schema | `archived_hijack_alerts` | 1 |
| Laravel/framework support tables | `cache`, `cache_locks`, `failed_jobs`, `job_batches`, `jobs`, `migrations`, `password_reset_tokens`, `sessions` | 8 |
| Spatie access-control support tables | `model_has_permissions`, `model_has_roles`, `permissions`, `role_has_permissions`, `roles` | 5 |
| **Total live tables** |  | **32** |

The data dictionary documents all 32 live tables and all 270 live columns. Framework and package tables have separate sections; the archived hijack table is documented as legacy, not as an active feature.

## Comparison with the proposed academic 3NF

The following are differences between the configured database documented here and the proposed normalized academic schema. The academic schema is not the live database schema, and none of these differences were changed:

| Topic | Current database, verified from the CSV and migrations | Difference from proposed 3NF |
|---|---|---|
| User primary key | `users.id` is the primary key. The users table does not have a `user_id` column; `user_id` may occur as a foreign-key column in other tables. | Use `users.id` when referring to the actual user key. |
| GPS records | The driver GPS history table is `gps_locations`; it has a `driver_id` foreign key referencing `drivers.id`. A separate `ambulance_locations` table stores ambulance-associated coordinates and has an `ambulance_id` column without an enforced foreign key. | Do not conflate driver GPS history with ambulance location records or rename either table in actual-schema documentation. |
| User department and office | `users.department` and `users.office` are nullable `varchar` columns, created as Laravel string fields; neither is a foreign key. | The current schema has no `departments` or `offices` lookup table. |
| Incident geography | `incidents.barangay`, `incidents.city`, and `incidents.province` are nullable `varchar` columns, added as Laravel string fields; they are not foreign keys. | The current schema has no `barangays`, `cities`, or `provinces` lookup tables. |
| Notification read timestamps | `notifications` includes `is_read`, `created_at`, and `updated_at`. The table has no `read_at` column. | The proposed `read_at` field is not present in the current database. |
| Discontinued hijack feature | `archived_hijack_alerts` remains in the database and is listed separately as a legacy table. | Its presence documents retained legacy schema only; it is not treated as active MuniResQ functionality. |
| Incident-report status default | Migration `2026_10_09_000001_set_incident_reports_default_status_pending` is pending. The live `incident_reports.status` default remains `'available'`. | The migration would change the default to `pending`; it has not been applied to this database. |

These findings agree with the CSV column names and metadata and the relevant migration definitions. No lookup-table creation migrations for departments, offices, barangays, cities, or provinces were found. The `users.department` and `users.office` columns are declared as Laravel strings (represented as `varchar` in the CSV), while the incident geography columns are likewise declared as strings. No `read_at` field is defined in the notifications migration or present in the CSV.

## Migration comparison

Laravel reports **44 migrations as applied and 1 as pending**. The pending migration is:

```text
2026_10_09_000001_set_incident_reports_default_status_pending
```

It changes the default for `incident_reports.status` to `pending`. The live SQLite table currently has the default **`'available'`**. This is a confirmed migration/live-schema difference. No migration was run as part of this inspection.

All current live tables correspond to the project’s table-creation migrations, the configured Spatie permission tables, or the documented hijack-table rename. The original `hijack_alerts` table name is not live; it was renamed to `archived_hijack_alerts`. No additional live-only table or other missing migration-created table was identified.

No other table or column-name differences were identified in the inspected migration set. SQLite reports declared types such as `varchar`, `numeric`, `datetime`, and `integer`, but does not preserve or enforce Laravel string maximum lengths or numeric precision/scale in its live schema metadata. Those dimensions are therefore identified as unavailable in the data dictionary rather than inferred as live constraints.

## Foreign-key review

### Foreign keys enforced by the live database

The data dictionary lists each live foreign key with its actual target table and column. The SQLite metadata reports 27 constraints.

### Relationship-like columns without a live foreign-key constraint

- `panic_alerts.driver_id` has no enforced foreign key. Its creation migration declares a `foreignId` column without `constrained()`.
- `ambulance_locations.ambulance_id` has no enforced foreign key. Its creation migration likewise declares a `foreignId` column without `constrained()`.
- `archived_hijack_alerts.driver_id` has no enforced foreign key in the legacy table; its original migration did not declare one.
- `sessions.user_id` is nullable and indexed, but the standard Laravel session migration does not declare it as a foreign key.
- `notifications.related_type` and `notifications.related_id` are a polymorphic reference pair and have no single fixed target key.
- `model_has_roles.model_type` / `model_id` and `model_has_permissions.model_type` / `model_id` are polymorphic package references; `model_id` intentionally has no single target table.

The first two application relationships are plausible omissions relative to the Eloquent relationships, but no database constraint was added or repaired.

Two Eloquent one-to-one relationships are also not enforced by database uniqueness constraints: `User::driver()` maps to `drivers.user_id`, which is not unique, and `Incident::report()` maps to `incident_reports.incident_id`, which is not unique. The database therefore permits multiple matching rows even though those particular model methods select a one-to-one relationship.

## Legacy and code/schema observations

### Discontinued hijack functionality

The live database contains `archived_hijack_alerts` with eight columns. Its presence results from the migration that renamed `hijack_alerts`; the table is treated here as possible legacy data, not an active MuniResQ feature. No hijack route or application-code reference was found in the inspected `app/` and `routes/` trees. This inspection did not read or count table records and did not alter or delete the legacy table.

### User approval columns remain physically present

The live `users` table contains `status`, `created_by`, `approved_by`, and `approved_at`. These are included in the data dictionary because they are real live columns, regardless of whether the application still uses approval workflows. The migrations define both self-referencing foreign keys, and the database enforces them. Project guidance describes account approval fields as retained for compatibility rather than used to gate login.

### `system_settings` model/migration discrepancy

The live `system_settings` table has only `id`, `created_at`, and `updated_at`, matching its migration. `app/Models/SystemSetting.php` contains a top-level `Schema::create` block referring to `system_name`, `agency_name`, `hotline`, `contact_number`, `email`, `logo`, and `maintenance_mode`; those columns are absent from both the live table and its migration. They are not included as existing database columns in the dictionary.

## Values not verified

- The Railway/production database was not accessed; only the locally configured SQLite database was inspected.
- SQLite does not expose enforced VARCHAR lengths or NUMERIC precision/scale for this schema. These details are marked unavailable in the data dictionary.
- Table contents, row counts, and referential-integrity violations among stored records were not inspected.
- The exact intended business meaning of fields is not enforced by database metadata. Descriptions are based on migrations, model relationships/casts, validation rules, and application usage, and are explanatory rather than additional constraints.
- `incident_reports.status` migration is pending; the current live default remains `available` until that migration is applied.
