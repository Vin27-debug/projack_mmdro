# MuniResQ - QUICK REFERENCE & ACTION ITEMS

**Last Updated:** 2026-07-24

---

## CRITICAL ACTIONS - DO THIS NOW

### 1. 🚨 FIX HIJACK ENDPOINT AUTHENTICATION

**Issue:** `/driver/hijack` is accessible without authentication

**File:** [routes/web.php](routes/web.php#L89-L91)

**Current:**
```php
Route::post('/driver/hijack', [HijackController::class, 'trigger'])
    ->name('driver.hijack.trigger');
```

**Fix:**
```php
Route::middleware([
    'auth',
    'approved',
    'role:driver'
])->group(function () {
    Route::post('/driver/hijack', [HijackController::class, 'trigger'])
        ->name('driver.hijack.trigger');
});
```

---

### 2. 🚨 FIX CORRUPTED SystemSetting MODEL

**File:** [app/Models/SystemSetting.php](app/Models/SystemSetting.php)

**Issue:** Contains migration schema code inside model class

**Current (Invalid):**
```php
class SystemSetting extends Model
{
    // ... class definition
    Schema::create('system_settings', function (Blueprint $table) {
        // Schema code here - WRONG PLACE!
    });
}
```

**Fix - Replace entire file with:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'system_name',
        'agency_name',
        'hotline',
        'contact_number',
        'email',
        'logo',
        'maintenance_mode'
    ];

    protected $casts = [
        'maintenance_mode' => 'boolean',
    ];
}
```

---

### 3. 🚨 FIX EMPTY system_settings MIGRATION

**File:** [database/migrations/2026_07_09_105343_create_system_settings_table.php](database/migrations/2026_07_09_105343_create_system_settings_table.php)

**Current (Useless):**
```php
public function up(): void
{
    Schema::create('system_settings', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
    });
}
```

**Fix - Replace with:**
```php
public function up(): void
{
    Schema::create('system_settings', function (Blueprint $table) {
        $table->id();
        
        $table->string('system_name')->default('MuniResQ');
        $table->string('agency_name')->nullable();
        $table->string('hotline')->nullable();
        $table->string('contact_number')->nullable();
        $table->string('email')->nullable();
        $table->string('logo')->nullable();
        $table->boolean('maintenance_mode')->default(false);
        
        $table->timestamps();
    });
}
```

Then run: `php artisan migrate:refresh --path="database/migrations/2026_07_09_105343_create_system_settings_table.php"`

---

### 4. 🚨 ADD GPS VALIDATION

**File:** [app/Http/Controllers/Driver/GpsController.php](app/Http/Controllers/Driver/GpsController.php#L16-L35)

**Current (NO VALIDATION):**
```php
public function update(Request $request)
{
    // ... no validation!
    $gpsLocation = GpsLocation::create([
        'driver_id' => $driver->id,
        'latitude' => $request->latitude,
        'longitude' => $request->longitude,
        'recorded_at' => now(),
    ]);
```

**Fix - Add validation:**
```php
public function update(Request $request)
{
    $driver = Auth::user()?->driver;
    
    if (!$driver) {
        return response()->json(['success' => false, 'message' => 'Driver not found'], 404);
    }

    // ADD THIS VALIDATION:
    $validated = $request->validate([
        'latitude' => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
    ]);

    // ... rest of code
```

---

### 5. ❌ ADD FOREIGN KEY CONSTRAINTS

Three tables missing foreign key constraints:

**panic_alerts:** [database/migrations/2026_07_03_135037_create_panic_alerts_table.php](database/migrations/2026_07_03_135037_create_panic_alerts_table.php#L15)

Change from:
```php
$table->foreignId('driver_id');
```

To:
```php
$table->foreignId('driver_id')->constrained()->cascadeOnDelete();
```

**hijack_alerts:** [database/migrations/2026_07_04_110443_create_hijack_alerts_table.php](database/migrations/2026_07_04_110443_create_hijack_alerts_table.php#L15)

Change from:
```php
$table->foreignId('driver_id');
```

To:
```php
$table->foreignId('driver_id')->constrained()->cascadeOnDelete();
```

**ambulance_locations:** [database/migrations/2026_07_11_070504_create_ambulance_locations_table.php](database/migrations/2026_07_11_070504_create_ambulance_locations_table.php#L11)

Change from:
```php
$table->foreignId('ambulance_id');
```

To:
```php
$table->foreignId('ambulance_id')->constrained()->cascadeOnDelete();
```

---

## HIGH PRIORITY IMPROVEMENTS

### 6. ADD DATABASE INDEXES

Create migration: `php artisan make:migration add_indexes_to_tables`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Drivers
        Schema::table('drivers', function (Blueprint $table) {
            $table->index('status');
            $table->index('user_id');
            $table->index('license_expiry');
        });

        // Ambulances
        Schema::table('ambulances', function (Blueprint $table) {
            $table->index('status');
            $table->index('vehicle_type');
        });

        // Incidents
        Schema::table('incidents', function (Blueprint $table) {
            $table->index('status');
            $table->index('incident_type');
            $table->index(['driver_id', 'status']);
            $table->index('created_at');
        });

        // Dispatches
        Schema::table('dispatches', function (Blueprint $table) {
            $table->index(['incident_id', 'driver_id']);
            $table->index('status');
            $table->index('created_at');
        });

        // GPS Locations
        Schema::table('gps_locations', function (Blueprint $table) {
            $table->index(['driver_id', 'recorded_at']);
            $table->index('recorded_at');
        });

        // Panic Alerts
        Schema::table('panic_alerts', function (Blueprint $table) {
            $table->index(['driver_id', 'status']);
            $table->index('triggered_at');
        });

        // Hijack Alerts
        Schema::table('hijack_alerts', function (Blueprint $table) {
            $table->index(['driver_id', 'status']);
            $table->index('triggered_at');
        });

        // Audit Logs
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at']);
            $table->index('action');
            $table->index('module');
        });

        // Ambulance Locations
        Schema::table('ambulance_locations', function (Blueprint $table) {
            $table->index(['ambulance_id', 'created_at']);
        });
    }

    public function down(): void {
        // Drop all indexes
    }
};
```

---

### 7. CREATE MISSING FORM REQUESTS

Create these files:

**app/Http/Requests/StoreIncidentRequest.php:**
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'reporter_name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:20|regex:/^[0-9\-\+\(\)\s]+$/',
            'incident_type' => 'required|string|max:255',
            'location' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:2000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];
    }
}
```

**app/Http/Requests/UpdateDispatchRequest.php:**
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDispatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['admin', 'super-admin']);
    }

    public function rules(): array
    {
        return [
            'driver_id' => 'required|exists:drivers,id',
            'ambulance_id' => 'required_without:vehicle_id|exists:ambulances,id',
            'vehicle_id' => 'required_without:ambulance_id|exists:ambulances,id',
            'status' => 'required|in:pending,assigned,accepted,en_route,arrived,completed,cancelled',
        ];
    }
}
```

**app/Http/Requests/UpdateGpsLocationRequest.php:**
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGpsLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->driver;
    }

    public function rules(): array
    {
        return [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ];
    }
}
```

---

### 8. FIX DUPLICATE RELATIONSHIPS IN MODELS

**Driver Model:** [app/Models/Driver.php](app/Models/Driver.php)

Remove duplicate methods:
```php
// REMOVE ONE OF THESE:
public function report() { ... }
public function reports() { ... }
public function incidentReports() { ... }

// KEEP ONLY:
public function incidentReports()
{
    return $this->hasMany(IncidentReport::class, 'driver_id');
}
```

**Dispatch Model:** [app/Models/Dispatch.php](app/Models/Dispatch.php)

Remove duplicate:
```php
// REMOVE:
public function vehicle()
{
    return $this->belongsTo(Ambulance::class, 'vehicle_id');
}

// KEEP:
public function ambulance()
{
    return $this->belongsTo(Ambulance::class, 'vehicle_id');
}
```

---

### 9. ADD SOFTDELETES TO ALL MODELS

**Create migration:** `php artisan make:migration add_soft_deletes_to_tables`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $tables = [
        'users', 'drivers', 'ambulances', 'incidents', 'dispatches',
        'incident_reports', 'gps_locations', 'panic_alerts', 'hijack_alerts',
        'vehicle_maintenances', 'notifications', 'audit_logs'
    ];

    public function up(): void {
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    public function down(): void {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
```

**Add to each Model:**
```php
use Illuminate\Database\Eloquent\SoftDeletes;

class YourModel extends Model
{
    use SoftDeletes;
    
    protected $dates = ['deleted_at'];
}
```

---

### 10. CREATE MISSING POLICIES

**File:** app/Policies/DriverPolicy.php

```php
<?php

namespace App\Policies;

use App\Models\Driver;
use App\Models\User;

class DriverPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'super-admin']);
    }

    public function view(User $user, Driver $driver): bool
    {
        return $user->hasRole(['admin', 'super-admin']) || $user->id === $driver->user_id;
    }

    public function create(User $user): bool
    {
        return false; // Use controller registration only
    }

    public function update(User $user, Driver $driver): bool
    {
        return $user->hasRole('super-admin') || $user->id === $driver->user_id;
    }

    public function delete(User $user, Driver $driver): bool
    {
        return $user->hasRole('super-admin');
    }
}
```

**Create similar policies for:** AmbulancePolicy, DispatchPolicy, IncidentPolicy (fix), IncidentReportPolicy

---

## STRUCTURAL ISSUES

### GPS Data Duplication

**Problem:** GPS stored in TWO places:
- `ambulances.latitude`/`ambulances.longitude` [added in migration 2026_07_05_122148]
- `ambulance_locations` table (separate tracking)

**Solution:**
1. Decide which table is source of truth
2. Remove one source
3. Consolidate queries

**Recommendation:** Remove GPS from ambulances table, use ambulance_locations exclusively

---

### Inconsistent Status Fields

**Problem:**
- `incidents.status` = enum
- `dispatches.status` = enum
- `panic_alerts.status` = string
- `hijack_alerts.status` = string

**Solution:** Make all enums for type safety

Create migration:
```php
Schema::table('panic_alerts', function (Blueprint $table) {
    $table->enum('status', ['active', 'resolved'])->change();
});

Schema::table('hijack_alerts', function (Blueprint $table) {
    $table->enum('status', ['active', 'resolved'])->change();
});
```

---

## PERFORMANCE OPTIMIZATIONS

### N+1 Query Problems

**Admin Dashboard Controller:** [app/Http/Controllers/Admin/DashboardController.php](app/Http/Controllers/Admin/DashboardController.php#L20-100)

**Problem:**
```php
$incidents = Incident::latest()->get();  // ❌ No pagination, N+1 risk
```

**Fix:**
```php
$incidents = Incident::with(['driver', 'ambulance'])
    ->latest()
    ->paginate(50);  // Add pagination
```

---

### Route Duplicates

**Remove these duplicate routes from [routes/web.php](routes/web.php):**

1. Lines ~180-185: `/dispatch-center` (appears twice)
2. Lines ~215-220: `/admin/dispatch-center` (same endpoint)

**Keep:** `Route::get('/dispatch-center', [DispatchController::class, 'index'])->name('dispatch.center');`

---

## MIDDLEWARE GAPS

### Create Rate Limiting Middleware

**File:** `app/Http/Middleware/RateLimitCriticalActions.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

class RateLimitCriticalActions
{
    public function __construct(private RateLimiter $limiter) {}

    public function handle(Request $request, Closure $next)
    {
        $routes = [
            'driver.panic.trigger' => 1,     // 1 per minute
            'driver.hijack.trigger' => 1,    // 1 per minute
            'admin.incidents.store' => 60,   // 60 per minute
        ];

        $route = $request->route()?->getName();
        if (isset($routes[$route])) {
            $key = 'action:' . $request->user()?->id . ':' . $route;
            
            if ($this->limiter->tooManyAttempts($key, $routes[$route])) {
                return response()->json(['error' => 'Too many attempts'], 429);
            }

            $this->limiter->hit($key, 60);
        }

        return $next($request);
    }
}
```

---

## VALIDATION CHECKLIST

### Controllers to Review

```
[ ] AdminRegistrationController - Add FormRequest
[ ] DispatchController::assign - Already validated, good
[ ] IncidentController::store - Move to FormRequest
[ ] GpsController::update - ✓ FIXED (added validation)
[ ] PanicController - Add validation
[ ] HijackController - Add validation
[ ] IncidentReportController::store - Move to FormRequest
[ ] VehicleMaintenanceController - Already good
[ ] UserApprovalController - Add validation
```

---

## SECURITY CHECKLIST

```
[ ] ✓ CSRF protection (via middleware)
[ ] ✗ Add CORS middleware
[ ] ✓ Rate limiting on auth
[ ] ✗ Add rate limiting on critical actions
[ ] ✓ Email verification
[ ] ✗ Add two-factor authentication
[ ] ✗ Add request logging
[ ] ✗ Add security headers (X-Frame-Options, etc)
[ ] ✓ Role-based access (middleware)
[ ] ✗ Model-based authorization (policies)
```

---

## DATABASE MIGRATION SCRIPT

**Run these in order:**

```bash
# 1. Backup current database
php artisan db:backup  # If using backup commands

# 2. Fix foreign key constraints
php artisan migrate

# 3. Add missing indexes
php artisan migrate

# 4. Add soft deletes
php artisan migrate

# 5. Fix system settings
php artisan migrate:refresh --path="database/migrations/2026_07_09_105343_create_system_settings_table.php"
```

---

## FILE CHECKLIST FOR FIXING

### Immediate Fixes
- [ ] [routes/web.php](routes/web.php) - Fix hijack auth (Line 89)
- [ ] [app/Models/SystemSetting.php](app/Models/SystemSetting.php) - Rebuild entire file
- [ ] [database/migrations/2026_07_09_105343_create_system_settings_table.php](database/migrations/2026_07_09_105343_create_system_settings_table.php) - Add columns
- [ ] [app/Http/Controllers/Driver/GpsController.php](app/Http/Controllers/Driver/GpsController.php) - Add validation

### High Priority
- [ ] Create database index migration
- [ ] Fix foreign keys in 3 migrations
- [ ] Create 3-5 Form Request classes
- [ ] Fix duplicate relationships in models
- [ ] Create missing Policy files

### Medium Priority
- [ ] Add SoftDeletes to models
- [ ] Fix GPS data duplication
- [ ] Standardize status fields
- [ ] Remove duplicate routes
- [ ] Add rate limiting middleware

---

## TESTING COMMANDS

```bash
# Test routes
php artisan route:list | grep "hijack"

# Test model relationships
php artisan tinker
>>> App\Models\Driver::first()->incidentReports;

# Validate migrations
php artisan migrate:status

# Check model policies
php artisan policy:list

# Run database checks
php artisan db:seed --class=CheckDataIntegritySeeder
```

---

**Generated:** 2026-07-24  
**Next Review:** After implementing critical fixes
