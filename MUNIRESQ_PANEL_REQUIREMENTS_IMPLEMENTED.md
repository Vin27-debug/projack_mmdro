# MuniResQ Panel Requirements - Implementation Update

This update addresses the functional requirements identified in the panel prioritization sheet.

## Implemented / strengthened

1. Incident records
   - Create incident records.
   - Edit incident records.
   - Search and filter by incident number, reporter, location, type, status, and date range.
   - Government-record approach: incidents are not permanently deleted.
   - Archive and restore workflow keeps historical records searchable.

2. Incident attachments
   - Multiple photos/documents can be attached to an incident.
   - Supported: JPG, JPEG, PNG, WEBP, PDF, DOC, DOCX, XLS, XLSX.
   - Maximum 10 MB per file and up to 10 files per submission.
   - Files remain associated with the incident after archive.
   - Authorized admin users can download retained attachments.

3. Reports
   - Existing PDF and Excel exports are preserved.
   - Added direct browser print for the Reports Center.
   - Added direct browser print for incident records and history.

4. Vulnerable areas / households
   - Added a dedicated database table and admin CRUD screens.
   - Stores area/cluster name, address, household count, population, vulnerability level, coordinates, notes, and active/inactive status.

5. Disaster response equipment inventory
   - Added a dedicated inventory table and admin CRUD screens.
   - Stores equipment name, category, serial number, quantity, condition, status, storage location, and notes.

6. Backup and restore
   - Existing Backup & Restore module was preserved; no replacement was made.

## Important government-record behavior

Incident records intentionally do **not** have a permanent delete action. The archive action changes the record to a closed/archive state while retaining the incident, its history, and attachments for accountability and future searching.

## Migration required after copying the update

Run:

```bash
php artisan migrate
php artisan optimize:clear
```

No `storage:link` is required for incident attachments because the new attachment files use Laravel's private `local` disk and are served through an authenticated download route.
