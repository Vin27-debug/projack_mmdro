# Capstone Data Dictionary Scope

The full [data dictionary CSV](./data_dictionary.csv) retains every table and column found in the inspected SQLite schema: **32 tables and 270 column rows**. This scope classification is for capstone documentation only; it does not alter or omit any CSV records.

## MuniResQ application tables (18)

- `ambulance_locations`
- `ambulances`
- `audit_logs`
- `backup_logs`
- `dispatches`
- `drivers`
- `gps_locations`
- `incident_attachments`
- `incident_reports`
- `incidents`
- `notifications`
- `panic_alerts`
- `response_equipment`
- `system_settings`
- `vehicle_driver_assignments`
- `vehicle_maintenances`
- `vulnerable_areas`
- `users`

## Laravel/framework and authorization support tables (13)

### Laravel framework tables (8)

- `cache`
- `cache_locks`
- `failed_jobs`
- `job_batches`
- `jobs`
- `migrations`
- `password_reset_tokens`
- `sessions`

### Spatie authorization tables (5)

- `model_has_permissions`
- `model_has_roles`
- `permissions`
- `role_has_permissions`
- `roles`

## Legacy table (1)

- `archived_hijack_alerts` - retained schema from the discontinued hijack feature; documented as legacy, not as an active MuniResQ workflow.

## Classification totals

| Group | Tables |
|---|---:|
| MuniResQ application | 18 |
| Laravel/framework and authorization support | 13 |
| Legacy | 1 |
| **Total** | **32** |

This classification describes the locally inspected project schema. The Railway/production database was not verified as part of this documentation task.
