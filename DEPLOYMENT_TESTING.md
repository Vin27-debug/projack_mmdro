# MuniResQ Dashboard Upgrade - Deployment & Testing Guide

## Prerequisites

### Required Knowledge:

- Laravel basics
- Git version control
- Browser developer tools
- Basic JavaScript debugging

### System Requirements:

- PHP 8.0+
- Laravel 9.0+
- MySQL 5.7+
- Modern browser (Chrome, Firefox, Edge, Safari)
- Internet connection (for CDN libraries)

---

## Pre-Deployment Checklist

### Code Review:

- [ ] Review DashboardController changes
- [ ] Verify all new routes are added
- [ ] Check database models for required fields
- [ ] Confirm Bootstrap/CSS variables are defined

### Database Validation:

```sql
-- Verify required tables and columns exist
SELECT * FROM ambulances WHERE latitude IS NOT NULL AND longitude IS NOT NULL;
SELECT * FROM incidents WHERE latitude IS NOT NULL AND longitude IS NOT NULL;
SELECT * FROM drivers;
SELECT * FROM dispatches;
```

### File Verification:

```bash
# Verify files exist
ls -la app/Http/Controllers/Admin/DashboardController.php
ls -la resources/views/admin/dashboard.blade.php
ls -la routes/web.php
```

---

## Deployment Steps

### Step 1: Backup Current Files

```bash
cd /path/to/muniresq-project

# Backup current dashboard
cp resources/views/admin/dashboard.blade.php resources/views/admin/dashboard.blade.php.backup

# Backup controller
cp app/Http/Controllers/Admin/DashboardController.php app/Http/Controllers/Admin/DashboardController.php.backup

# Backup routes
cp routes/web.php routes/web.php.backup
```

### Step 2: Update Files

All files have already been updated. Verify the changes:

```bash
# Check DashboardController has new methods
grep -n "gpsLocations\|responseLoadAnalytics\|situationOverview\|fleetReadiness" \
  app/Http/Controllers/Admin/DashboardController.php

# Check routes are added
grep -n "gps-locations\|response-load-analytics\|situation-overview\|fleet-readiness" \
  routes/web.php
```

### Step 3: Clear Cache

```bash
# Clear application cache
php artisan cache:clear

# Clear route cache
php artisan route:cache

# Clear view cache
php artisan view:clear

# Clear config cache
php artisan config:clear
```

### Step 4: Verify Routes

```bash
# List all admin dashboard routes
php artisan route:list | grep -i "admin/dashboard"

# Output should include:
# admin/dashboard/counters
# admin/dashboard/gps-locations
# admin/dashboard/response-load-analytics
# admin/dashboard/situation-overview
# admin/dashboard/fleet-readiness
```

### Step 5: Test Endpoints

```bash
# Test counters endpoint
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost/admin/dashboard/counters

# Test GPS locations endpoint
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost/admin/dashboard/gps-locations

# Test response load analytics
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost/admin/dashboard/response-load-analytics

# Test situation overview
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost/admin/dashboard/situation-overview

# Test fleet readiness
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost/admin/dashboard/fleet-readiness
```

---

## Manual Testing

### Test Environment Setup:

1. Login to admin dashboard at `/admin/dashboard`
2. Open browser Developer Tools (F12)
3. Go to Console tab
4. Go to Network tab

### Test 1: Map Loading

**Steps**:

1. Wait for page to fully load
2. Check console for errors
3. Verify map appears with markers
4. Click on markers to view popups

**Expected Result**:

- Map loads with OpenStreetMap tiles
- Blue markers for ambulances
- Red markers for incidents
- Popups show correct information

### Test 2: Response Load Analytics Chart

**Steps**:

1. Scroll to "Response Load Analytics" section
2. Verify doughnut chart displays
3. Check chart has 4 colored segments
4. Hover over segments to see values

**Expected Result**:

- Chart displays with incident types
- Red for Fire, Blue for Medical, Green for Rescue, Orange for Crime
- Legend shows all types
- Values update in real-time

### Test 3: Situation Overview Panel

**Steps**:

1. Look for 4-part panel in right sidebar
2. Check all metrics display numbers
3. Monitor for 10-second updates

**Expected Result**:

- Shows: Active Incidents, Dispatched Units, Completed Responses, Pending Reports
- Numbers update automatically
- Values are non-negative integers

### Test 4: Fleet Readiness Card

**Steps**:

1. Check fleet readiness section
2. Verify 4 metrics display:
    - Available Ambulances
    - Active Ambulances
    - Vehicles Under Maintenance
    - Drivers Online
3. Monitor for updates

**Expected Result**:

- All metrics display correct values
- Icons are color-coded
- Updates every 10 seconds
- Total adds up to expected fleet size

### Test 5: Auto-Refresh Functionality

**Steps**:

1. Open Developer Tools Network tab
2. Watch for API requests to:
    - `/admin/dashboard/counters` (every 15 seconds)
    - `/admin/dashboard/gps-locations` (every 10 seconds)
    - `/admin/dashboard/response-load-analytics` (every 10 seconds)
    - `/admin/dashboard/situation-overview` (every 10 seconds)
    - `/admin/dashboard/fleet-readiness` (every 10 seconds)

**Expected Result**:

- Requests appear at regular intervals
- All requests return HTTP 200
- Response time < 500ms
- No errors in console

### Test 6: Map Marker Updates

**Steps**:

1. Open a second browser window with real ambulance data
2. Update ambulance location in database
3. Watch map in dashboard
4. Verify marker moves within 10 seconds

**Expected Result**:

- Markers update smoothly
- No duplicate markers appear
- Old markers are removed
- Map stays centered appropriately

### Test 7: Responsive Design

**Steps**:

1. Resize browser to different widths:
    - Desktop (1920px)
    - Tablet (768px)
    - Mobile (375px)
2. Check layout adapts
3. Test touch interactions on mobile

**Expected Result**:

- Layout reflows correctly
- All cards remain readable
- Map is usable at all sizes
- No horizontal scrolling
- Touch interactions work smoothly

### Test 8: Error Handling

**Steps**:

1. Stop the Laravel server
2. Try to refresh the page
3. Check console for error messages
4. Verify graceful error handling

**Expected Result**:

- Console shows clear error messages
- Page doesn't crash
- User sees informative error
- Recovery is possible

### Test 9: Performance

**Steps**:

1. Open DevTools > Performance tab
2. Record page load
3. Check:
    - First Contentful Paint (FCP)
    - Largest Contentful Paint (LCP)
    - Cumulative Layout Shift (CLS)

**Expected Result**:

- FCP < 2 seconds
- LCP < 3 seconds
- CLS < 0.1
- No jank during animations

---

## Automated Testing

### Unit Tests

Create test file: `tests/Feature/DashboardApiTest.php`

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ambulance;
use App\Models\Incident;
use App\Models\Dispatch;

class DashboardApiTest extends TestCase
{
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_gps_locations_endpoint_returns_correct_structure()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/dashboard/gps-locations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'ambulances' => [
                    '*' => [
                        'id', 'name', 'plate_number',
                        'latitude', 'longitude', 'status', 'driver_name'
                    ]
                ],
                'incidents' => [
                    '*' => [
                        'id', 'incident_number', 'latitude', 'longitude',
                        'type', 'status', 'location'
                    ]
                ]
            ]);
    }

    public function test_response_load_analytics_returns_incident_counts()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/dashboard/response-load-analytics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'Fire', 'Medical', 'Rescue', 'Crime'
            ]);

        $data = $response->json();
        foreach ($data as $count) {
            $this->assertIsInt($count);
            $this->assertGreaterThanOrEqual(0, $count);
        }
    }

    public function test_situation_overview_returns_metrics()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/dashboard/situation-overview');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'active_incidents',
                'dispatched_units',
                'completed_responses',
                'pending_reports'
            ]);
    }

    public function test_fleet_readiness_returns_fleet_data()
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/admin/dashboard/fleet-readiness');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'available_ambulances',
                'active_ambulances',
                'vehicles_maintenance',
                'drivers_online',
                'fleet_utilization'
            ]);
    }

    public function test_unauthorized_user_cannot_access_dashboard_api()
    {
        $response = $this->getJson('/admin/dashboard/gps-locations');
        $response->assertStatus(401);
    }
}
```

Run tests:

```bash
php artisan test tests/Feature/DashboardApiTest.php
```

---

## Performance Testing

### Load Testing with Apache Bench:

```bash
# Test single endpoint with 100 requests
ab -n 100 -c 10 -H "Authorization: Bearer TOKEN" \
  http://localhost/admin/dashboard/gps-locations

# Parameters:
# -n 100    : 100 total requests
# -c 10     : 10 concurrent requests
# Output shows: requests/sec, mean time, min/max time
```

### Browser Performance Metrics:

```javascript
// Log performance metrics in console
window.addEventListener("load", function () {
    const perfData = window.performance.timing;
    const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
    console.log("Page load time: " + pageLoadTime + "ms");

    // Log Core Web Vitals
    web_vitals.getCLS((metric) => console.log("CLS:", metric.value));
    web_vitals.getFID((metric) => console.log("FID:", metric.value));
    web_vitals.getLCP((metric) => console.log("LCP:", metric.value));
});
```

---

## Rollback Plan

If issues occur, rollback is simple:

### Quick Rollback:

```bash
# Restore backups
cp resources/views/admin/dashboard.blade.php.backup resources/views/admin/dashboard.blade.php
cp app/Http/Controllers/Admin/DashboardController.php.backup app/Http/Controllers/Admin/DashboardController.php
cp routes/web.php.backup routes/web.php

# Clear cache
php artisan cache:clear
php artisan route:cache
php artisan view:clear
```

### Complete Rollback:

```bash
# Using Git
git checkout resources/views/admin/dashboard.blade.php
git checkout app/Http/Controllers/Admin/DashboardController.php
git checkout routes/web.php
git checkout HEAD

# Clear cache
php artisan cache:clear
```

---

## Data Requirements

For full functionality, ensure:

1. **Ambulances Table**: Has at least one record with:
    - `latitude` (not null)
    - `longitude` (not null)
    - `status` (available, in_use, or maintenance)

2. **Incidents Table**: Has records with:
    - `incident_type` (Fire, Medical, Rescue, Crime)
    - `latitude` (not null)
    - `longitude` (not null)
    - `status` (pending, active, completed, closed)

3. **Drivers Table**: Has records with:
    - `status` (available, busy, offline)

4. **Dispatches Table**: Has records with:
    - `status` (assigned, en_route, arrived, completed)

### Sample Data for Testing:

```sql
-- Insert test ambulances
INSERT INTO ambulances (plate_number, vehicle_name, vehicle_type, status, latitude, longitude, created_at, updated_at)
VALUES
('AMB-001', 'Ambulance 1', 'Ambulance', 'available', 15.4866, 120.9675, NOW(), NOW()),
('AMB-002', 'Ambulance 2', 'Ambulance', 'in_use', 15.4900, 120.9700, NOW(), NOW()),
('AMB-003', 'Ambulance 3', 'Ambulance', 'maintenance', 15.4850, 120.9650, NOW(), NOW());

-- Insert test incidents
INSERT INTO incidents (incident_number, incident_type, location, latitude, longitude, status, created_at, updated_at)
VALUES
('INC-2024-001', 'Medical', 'Downtown District', 15.4866, 120.9675, 'active', NOW(), NOW()),
('INC-2024-002', 'Fire', 'Commercial Area', 15.4900, 120.9700, 'pending', NOW(), NOW()),
('INC-2024-003', 'Rescue', 'Residential Zone', 15.4850, 120.9650, 'completed', NOW(), NOW());
```

---

## Monitoring & Maintenance

### Daily Checks:

- [ ] Dashboard loads without errors
- [ ] Map updates show real-time data
- [ ] Charts render correctly
- [ ] Auto-refresh is working
- [ ] No console errors

### Weekly Checks:

- [ ] Performance metrics are acceptable
- [ ] No memory leaks detected
- [ ] API response times are consistent
- [ ] Database queries are optimized

### Monthly Checks:

- [ ] Review usage patterns
- [ ] Optimize slow queries
- [ ] Update external libraries (if needed)
- [ ] Review error logs

### Log Locations:

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Web server logs
tail -f /var/log/nginx/error.log    # Nginx
tail -f /var/log/apache2/error.log  # Apache

# Database logs (if enabled)
tail -f /var/log/mysql/error.log
```

---

## Common Issues & Solutions

### Issue: Map Not Displaying

**Cause**: Leaflet library not loaded
**Solution**:

```javascript
// In console, check:
console.log(typeof L); // Should be 'object'
console.log(L.map); // Should be a function
```

### Issue: Charts Show NaN

**Cause**: API returning non-numeric data
**Solution**:

```javascript
// Check API response in Network tab
// Verify API returns: {"Fire": 5, "Medical": 10, ...}
```

### Issue: Updates Not Happening

**Cause**: Auto-refresh interval too long or not started
**Solution**:

```javascript
// Check in console:
console.log(setInterval); // Should be a function
// Manually trigger refresh:
refreshMapData();
updateCounters();
```

### Issue: Permission Denied

**Cause**: User not authenticated or lacks admin role
**Solution**:

```javascript
// Check authentication:
// User must be logged in with admin role
// Verify in User model: $user->role === 'admin'
```

---

## Success Criteria

Dashboard upgrade is successful when:

✅ All 5 new components display  
✅ Map updates every 10 seconds  
✅ Charts render without errors  
✅ Counters refresh every 15 seconds  
✅ No console errors  
✅ Response time < 500ms per API call  
✅ Works on desktop, tablet, mobile  
✅ Professional EOC design maintained  
✅ All data is accurate and real-time  
✅ Auto-refresh functions properly

---

## Support & Documentation

**Internal Resources**:

- `/DASHBOARD_UPGRADE.md` - Feature overview
- `/API_REFERENCE.md` - API endpoint documentation
- Laravel Documentation: https://laravel.com/docs

**External Resources**:

- Leaflet: https://leafletjs.com/
- Chart.js: https://www.chartjs.org/
- Bootstrap: https://getbootstrap.com/

---

**Deployment Completed**: ✅  
**Last Updated**: July 14, 2024
