# MUNIRESQ - CRITICAL BUGS QUICK FIX GUIDE

## 🔴 5 CRITICAL BUGS - FIX IMMEDIATELY

⏱️ **Total Fix Time: 4 hours**

---

## BUG #1: Driver Panic Alert Crash

**Status:** 🔴 WILL NOT WORK  
**Severity:** CRITICAL - Safety feature broken  
**File:** `app/Http/Controllers/Driver/PanicController.php`  
**Line:** 14  
**Fix Time:** 5 minutes

### Current Code (BROKEN):

```php
<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\PanicAlert;
use Illuminate\Support\Facades\Auth;

class PanicController extends Controller
{
    public function trigger()
    {
        $driver = auth()->user()?->driver;  // ❌ WRONG: auth() doesn't have ->user() method

        if (!$driver) {
            abort(403, 'Driver record not found');
        }

        PanicAlert::create([
            'driver_id' => $driver->id,
            'latitude' => request('latitude'),
            'longitude' => request('longitude'),
        ]);

        // ... rest of code
    }
}
```

### Fixed Code:

```php
<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\PanicAlert;
use Illuminate\Support\Facades\Auth;

class PanicController extends Controller
{
    public function trigger()
    {
        $driver = Auth::user()?->driver;  // ✅ CORRECT: Use Auth::user()

        if (!$driver) {
            abort(403, 'Driver record not found');
        }

        PanicAlert::create([
            'driver_id' => $driver->id,
            'latitude' => request('latitude'),
            'longitude' => request('longitude'),
        ]);

        // ... rest of code
    }
}
```

**Change:** Line 14 - Replace `auth()->user()` with `Auth::user()`

---

## BUG #2: Driver Hijack Alert Crash

**Status:** 🔴 WILL NOT WORK  
**Severity:** CRITICAL - Safety feature broken  
**File:** `app/Http/Controllers/Driver/HijackController.php`  
**Line:** 15  
**Fix Time:** 5 minutes

### Current Code (BROKEN):

```php
<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\HijackAlert;
use Illuminate\Support\Facades\Auth;

class HijackController extends Controller
{
    public function trigger()
    {
        $driver = auth()->user()?->driver;  // ❌ WRONG: auth() doesn't have ->user() method

        if (!$driver) {
            abort(403, 'Driver record not found');
        }

        HijackAlert::create([
            'driver_id' => $driver->id,
            'latitude' => request('latitude'),
            'longitude' => request('longitude'),
        ]);

        // ... rest of code
    }
}
```

### Fixed Code:

```php
<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\HijackAlert;
use Illuminate\Support\Facades\Auth;

class HijackController extends Controller
{
    public function trigger()
    {
        $driver = Auth::user()?->driver;  // ✅ CORRECT: Use Auth::user()

        if (!$driver) {
            abort(403, 'Driver record not found');
        }

        HijackAlert::create([
            'driver_id' => $driver->id,
            'latitude' => request('latitude'),
            'longitude' => request('longitude'),
        ]);

        // ... rest of code
    }
}
```

**Change:** Line 15 - Replace `auth()->user()` with `Auth::user()`

---

## BUG #3: Driver Assignment Page Crash

**Status:** 🔴 WILL NOT WORK  
**Severity:** CRITICAL - Drivers cannot see assignments  
**File:** `app/Http/Controllers/Driver/MyAssignmentController.php`  
**Line:** 13  
**Fix Time:** 5 minutes

### Current Code (BROKEN):

```php
<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;

class MyAssignmentController extends Controller
{
    public function index()
    {
        $driver = auth()->user()->driver;  // ❌ WRONG: No null coalescing, will crash if driver missing

        // This will cause null reference error
        $assignments = $driver->dispatches
            ->whereNotIn('status', ['completed', 'declined'])
            ->all();

        return view('driver.assignment.index', compact('assignments'));
    }
}
```

### Fixed Code:

```php
<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use Illuminate\Support\Facades\Auth;

class MyAssignmentController extends Controller
{
    public function index()
    {
        $driver = Auth::user()?->driver;  // ✅ CORRECT: Use proper null coalescing

        if (!$driver) {
            abort(403, 'Driver record not found');
        }

        // Safe to use driver now
        $assignments = $driver->dispatches
            ->whereNotIn('status', ['completed', 'declined'])
            ->all();

        return view('driver.assignment.index', compact('assignments'));
    }
}
```

**Change:** Line 13 - Replace `auth()->user()->driver` with `Auth::user()?->driver` and add null check

---

## BUG #4: Dashboard GPS Location Crash

**Status:** 🔴 WILL NOT WORK  
**Severity:** CRITICAL - Dashboard will crash on load  
**File:** `app/Http/Controllers/Admin/DashboardController.php`  
**Lines:** 240-255  
**Fix Time:** 30 minutes

### Current Code (BROKEN):

```php
public function gpsLocations()
{
    $ambulances = Ambulance::with('dispatches')->get()
        ->map(function ($ambulance) {
            return [
                'id' => $ambulance->id,
                'latitude' => $ambulance->latitude,
                'longitude' => $ambulance->longitude,
                'vehicle_name' => $ambulance->vehicle_name,
                'status' => $ambulance->status,
                'driver_name' => $ambulance->dispatches()  // ❌ WRONG: dispatches() called on mapped item (stdClass)
                    ->latest()
                    ->first()?->driver->user->name ?? 'Unassigned'
            ];
        });

    return response()->json($ambulances);
}
```

### Fixed Code:

```php
public function gpsLocations()
{
    $ambulances = Ambulance::with(['dispatches.driver.user'])  // ✅ Eager load relationships
        ->get()
        ->map(function ($ambulance) {
            $latestDispatch = $ambulance->dispatches()
                ->latest()
                ->first();

            return [
                'id' => $ambulance->id,
                'latitude' => $ambulance->latitude,
                'longitude' => $ambulance->longitude,
                'vehicle_name' => $ambulance->vehicle_name,
                'status' => $ambulance->status,
                'driver_name' => $latestDispatch?->driver?->user?->name ?? 'Unassigned'  // ✅ Safe access
            ];
        });

    return response()->json($ambulances);
}
```

**Change:**

- Line 240: Add eager loading with `->with(['dispatches.driver.user'])`
- Lines 252-254: Extract dispatch query before mapping, use safe access

---

## BUG #5: Vehicle Maintenance Store Crash

**Status:** 🔴 WILL NOT WORK  
**Severity:** CRITICAL - Maintenance records cannot be created  
**File:** `app/Http/Controllers/Admin/VehicleMaintenanceController.php`  
**Lines:** 74-79  
**Fix Time:** 10 minutes

### Current Code (BROKEN):

```php
public function store(Request $request)
{
    $request->validate([
        'ambulance_id' => 'required|exists:ambulances,id',
        'maintenance_type' => 'required|string',
        'description' => 'nullable|string',
        'scheduled_date' => 'nullable|date',
        'estimated_cost' => 'nullable|numeric',
    ]);

    $maintenance = VehicleMaintenance::create($request->validated());

    // ❌ WRONG: Undefined variable $report - this is copy-paste error from another controller
    AuditService::log(
        'Approve Report',
        'Reports',
        'Approved report #' . $report->id  // ❌ $report doesn't exist!
    );

    return redirect()->route('admin.maintenance.index')
        ->with('success', 'Maintenance record created');
}
```

### Fixed Code:

```php
public function store(Request $request)
{
    $request->validate([
        'ambulance_id' => 'required|exists:ambulances,id',
        'maintenance_type' => 'required|string',
        'description' => 'nullable|string',
        'scheduled_date' => 'nullable|date',
        'estimated_cost' => 'nullable|numeric',
    ]);

    $maintenance = VehicleMaintenance::create($request->validated());

    // ✅ CORRECT: Log the maintenance creation, not a report
    AuditService::log(
        'Maintenance Scheduled',
        'Maintenance',
        'Scheduled maintenance for ambulance #' . $maintenance->ambulance_id
    );

    return redirect()->route('admin.maintenance.index')
        ->with('success', 'Maintenance record created');
}
```

**Change:**

- Lines 74-79: Delete the broken audit log code, replace with correct maintenance logging

---

## BUG #6 (BONUS): SQL Injection in Backup Restore

**Status:** 🟠 HIGH PRIORITY SECURITY  
**Severity:** CRITICAL - Security vulnerability  
**File:** `app/Http/Controllers/SuperAdmin/BackupController.php`  
**Lines:** 55-65  
**Fix Time:** 2 hours

### Current Code (VULNERABLE):

```php
public function restore(Request $request)
{
    $request->validate([
        'backup_file' => 'required|string',
    ]);

    $file = storage_path('app/backups/' . $request->backup_file);  // ❌ No validation

    $command = "mysql -u root muniresq < \"$file\"";  // ❌ SQL Injection risk!
    shell_exec($command);

    return redirect()->back()->with('success', 'Backup restored');
}
```

### Fixed Code:

```php
public function restore(Request $request)
{
    $request->validate([
        'backup_file' => 'required|string',
    ]);

    // ✅ Get list of valid backup files
    $backupDir = storage_path('app/backups');
    $backupFiles = collect(File::files($backupDir))
        ->map(fn($f) => $f->getBasename())
        ->toArray();

    // ✅ Validate backup file is in our whitelist
    if (!in_array($request->backup_file, $backupFiles)) {
        return redirect()->back()->with('error', 'Invalid backup file');
    }

    $file = $backupDir . '/' . $request->backup_file;

    // ✅ Use safe command with proper escaping
    $command = sprintf(
        "mysql -u %s -p%s %s < %s",
        escapeshellarg(env('DB_USERNAME')),
        escapeshellarg(env('DB_PASSWORD')),
        escapeshellarg(env('DB_DATABASE')),
        escapeshellarg($file)
    );

    shell_exec($command);

    return redirect()->back()->with('success', 'Backup restored');
}
```

**Changes:**

- Add validation against whitelist of backup files
- Use `escapeshellarg()` for all shell arguments
- Use environment variables instead of hardcoded values

---

## IMPLEMENTATION GUIDE

### Step-by-Step Fix Process:

```
1. Fix Bug #1 (Panic Alert)
   - Open: app/Http/Controllers/Driver/PanicController.php
   - Change line 14: auth()->user() → Auth::user()
   - Save & Test

2. Fix Bug #2 (Hijack Alert)
   - Open: app/Http/Controllers/Driver/HijackController.php
   - Change line 15: auth()->user() → Auth::user()
   - Save & Test

3. Fix Bug #3 (My Assignment)
   - Open: app/Http/Controllers/Driver/MyAssignmentController.php
   - Change line 13: auth()->user()->driver → Auth::user()?->driver
   - Add null check after line 13
   - Save & Test

4. Fix Bug #4 (GPS Locations)
   - Open: app/Http/Controllers/Admin/DashboardController.php
   - Rewrite gpsLocations() method (lines 240-255)
   - Save & Test

5. Fix Bug #5 (Maintenance Store)
   - Open: app/Http/Controllers/Admin/VehicleMaintenanceController.php
   - Delete lines 74-79 (broken AuditService call)
   - Replace with correct logging
   - Save & Test

6. Fix Bug #6 (Backup SQL Injection)
   - Open: app/Http/Controllers/SuperAdmin/BackupController.php
   - Rewrite restore() method with validation
   - Save & Test
```

### Testing After Each Fix:

```bash
# Test if application runs
php artisan serve

# Check for syntax errors
php artisan tinker
>>> exit

# Run specific routes in browser
http://localhost:8000/admin/dashboard  # Should not crash
http://localhost:8000/driver/dashboard # Should not crash
```

---

## VERIFICATION CHECKLIST

After applying all fixes:

- [ ] No "Class not found" errors
- [ ] No "Call to undefined method" errors
- [ ] No "Undefined variable" errors
- [ ] `/admin/dashboard` loads without crashing
- [ ] `/driver/my-assignment` loads without crashing
- [ ] Driver can trigger panic alert
- [ ] Driver can trigger hijack alert
- [ ] Admin can create maintenance record
- [ ] Backup and restore functionality works

---

## Summary

| Bug               | File                             | Line    | Fix                  | Time    |
| ----------------- | -------------------------------- | ------- | -------------------- | ------- |
| Panic Alert       | PanicController.php              | 14      | auth() → Auth::      | 5 min   |
| Hijack Alert      | HijackController.php             | 15      | auth() → Auth::      | 5 min   |
| Assignment        | MyAssignmentController.php       | 13      | Add null check       | 5 min   |
| GPS Locations     | DashboardController.php          | 240-255 | Eager load + rewrite | 30 min  |
| Maintenance Store | VehicleMaintenanceController.php | 74-79   | Remove bad code      | 10 min  |
| SQL Injection     | BackupController.php             | 55-65   | Add validation       | 2 hours |

**Total Fix Time: ~3 hours** ✅

After these fixes, system will be stable enough for testing and further development.
