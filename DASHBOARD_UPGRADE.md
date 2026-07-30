# MuniResQ Admin Dashboard Upgrade

## Overview

Complete upgrade of the MuniResQ Admin Dashboard with real-time emergency operations monitoring, fleet management, and advanced analytics.

---

## Features Implemented

### 1. **Live Command Map** ✅

**File**: `resources/views/admin/dashboard.blade.php`

#### Features:

- **LeafletJS Integration**: Uses OpenStreetMap tiles for mapping
- **Real-time GPS Tracking**:
    - Displays all ambulance locations with blue circle markers
    - Shows active incident locations with red circle markers
    - Auto-updates every 10 seconds
- **Interactive Popups**: Click markers to view details:
    - **Ambulances**: Name, plate code, assigned driver, status
    - **Incidents**: Incident number, type, location, status
- **Auto-fit Bounds**: Map automatically centers on all markers
- **Status Indicators**: Color-coded markers for quick identification

#### API Endpoint:

```
GET /admin/dashboard/gps-locations
```

Returns JSON with:

```json
{
    "ambulances": [
        {
            "id": 1,
            "name": "Ambulance 1",
            "plate_number": "AMB-001",
            "latitude": 15.4866,
            "longitude": 120.9675,
            "status": "available",
            "driver_name": "John Doe"
        }
    ],
    "incidents": [
        {
            "id": 1,
            "incident_number": "INC-2024-001",
            "latitude": 15.49,
            "longitude": 120.97,
            "type": "Medical",
            "status": "active",
            "location": "Downtown District"
        }
    ]
}
```

---

### 2. **Response Load Analytics Card** ✅

**File**: `resources/views/admin/dashboard.blade.php`

#### Features:

- **Chart.js Doughnut Chart**: Visualizes incident distribution
- **Incident Type Breakdown**:
    - 🔴 **Fire** - Red (#dc3545)
    - 🔵 **Medical** - Blue (#0d6efd)
    - 🟢 **Rescue** - Green (#198754)
    - 🟠 **Crime** - Orange (#fd7e14)
- **30-Day Window**: Analyzes incidents from the last 30 days
- **Legend**: Color-coded legend below chart
- **Real-time Updates**: Refreshes every 10 seconds

#### API Endpoint:

```
GET /admin/dashboard/response-load-analytics
```

Returns JSON with incident counts by type:

```json
{
    "Fire": 5,
    "Medical": 12,
    "Rescue": 3,
    "Crime": 2
}
```

---

### 3. **Situation Overview Panel** ✅

**File**: `resources/views/admin/dashboard.blade.php`

#### Features:

- **Four Key Metrics**:
    1. **Active Incidents**: Current incidents requiring response
    2. **Dispatched Units**: Ambulances assigned to incidents
    3. **Completed Responses**: Finished dispatch operations
    4. **Pending Reports**: Incident reports awaiting approval
- **Grid Layout**: Responsive 2x2 grid design
- **Large Numbers**: Easy-to-read metrics display
- **Professional Styling**: White card with soft shadows

#### API Endpoint:

```
GET /admin/dashboard/situation-overview
```

Returns JSON:

```json
{
    "active_incidents": 5,
    "dispatched_units": 3,
    "completed_responses": 18,
    "pending_reports": 2
}
```

---

### 4. **Fleet Readiness Card** ✅

**File**: `resources/views/admin/dashboard.blade.php`

#### Features:

- **Real-time Fleet Status**:
    - 🚑 **Available Ambulances**: Ready to respond
    - ⚡ **Active Ambulances**: Currently in service
    - 🔧 **Vehicles Under Maintenance**: Not available
    - 👤 **Drivers Online**: Available personnel
- **Utilization Percentage**: Calculates fleet utilization rate
- **Color-Coded Icons**:
    - Info (blue) for available
    - Success (green) for active
    - Warning (orange) for maintenance
    - Primary (blue) for drivers
- **Auto-refresh**: Updates every 10 seconds

#### API Endpoint:

```
GET /admin/dashboard/fleet-readiness
```

Returns JSON:

```json
{
    "available_ambulances": 4,
    "active_ambulances": 3,
    "vehicles_maintenance": 1,
    "drivers_online": 8,
    "fleet_utilization": 75.0
}
```

---

### 5. **Real-time Auto-Refresh** ✅

#### Implementation:

- **Map Refresh**: Every 10 seconds
    - Updates ambulance and incident locations
    - Refreshes all markers
- **Analytics Refresh**: Every 10 seconds
    - Response Load Analytics chart
    - Situation Overview metrics
    - Fleet Readiness data
- **Counters Refresh**: Every 15 seconds
    - Top KPI cards (Active Incidents, Dispatches, etc.)

#### Configuration:

```javascript
// Map and analytics: 10 seconds
setInterval(function () {
    refreshMapData();
    updateCounters();
    loadSituationOverview();
    loadFleetReadiness();
}, 10000);

// Detailed counters: 15 seconds
setInterval(updateCounters, 15000);
```

---

## API Endpoints Summary

### New Endpoints Created:

| Endpoint                                   | Method | Purpose                                  |
| ------------------------------------------ | ------ | ---------------------------------------- |
| `/admin/dashboard/gps-locations`           | GET    | Get all ambulance and incident locations |
| `/admin/dashboard/response-load-analytics` | GET    | Get incident distribution by type        |
| `/admin/dashboard/situation-overview`      | GET    | Get operational status metrics           |
| `/admin/dashboard/fleet-readiness`         | GET    | Get fleet and driver status              |
| `/admin/dashboard/counters`                | GET    | Get all KPI counters (existing)          |

---

## Database Models Used

### Key Models:

- **Ambulance**: Location (latitude, longitude), status, vehicle details
- **Incident**: Type, status, location, GPS coordinates
- **Dispatch**: Assignment status, vehicle assignment
- **Driver**: Status, online/offline state
- **GpsLocation**: Driver tracking data
- **IncidentReport**: Report status tracking

### Status Enums:

- **Ambulance Status**: `available`, `in_use`, `maintenance`
- **Incident Status**: `pending`, `active`, `completed`, `closed`
- **Dispatch Status**: `assigned`, `en_route`, `arrived`, `completed`
- **Driver Status**: `available`, `busy`, `offline`

---

## Frontend Technologies

### Libraries Used:

1. **LeafletJS** (v1.9.4)
    - Mapping library
    - OpenStreetMap integration
    - Marker management
    - CDN: `https://unpkg.com/leaflet@1.9.4`

2. **Chart.js** (v3.9.1)
    - Data visualization
    - Doughnut/pie charts
    - CDN: `https://cdn.jsdelivr.net/npm/chart.js@3.9.1`

3. **Bootstrap** (v5)
    - Responsive layout
    - Card components
    - Grid system

### JavaScript Features:

- **AJAX Calls**: Fetch API for real-time data
- **Auto-refresh**: SetInterval for periodic updates
- **DOM Updates**: Real-time element manipulation
- **Error Handling**: Try-catch blocks and console logging

---

## Design Features

### Professional Emergency Operations Center (EOC) Style:

1. **Color Scheme**:
    - Primary Red (#dc3545): Critical alerts
    - Primary Blue (#0d6efd): Information
    - Success Green (#198754): Completed/Available
    - Warning Orange (#fd7e14): Maintenance/Caution

2. **Visual Hierarchy**:
    - Large KPI numbers (2.25rem)
    - Clear section headers
    - Color-coded border accents
    - Icon-supported content

3. **Responsive Layout**:
    - Mobile-first approach
    - 6-column grid on desktop
    - Stacks to 1-2 columns on tablet/mobile
    - Full-width map on all devices

4. **Interactive Elements**:
    - Hover effects on cards
    - Clickable map markers
    - Dropdown menus
    - Live indicator pulse animation

---

## Testing Checklist

### Functional Testing:

- [ ] Map loads and displays markers
- [ ] Ambulance markers update every 10 seconds
- [ ] Incident markers appear with correct colors
- [ ] Response Load Analytics chart displays correctly
- [ ] Situation Overview metrics update in real-time
- [ ] Fleet Readiness card shows accurate data
- [ ] KPI counters update automatically
- [ ] All popups display correct information

### Responsive Testing:

- [ ] Desktop view (1920px+)
- [ ] Tablet view (768px-1024px)
- [ ] Mobile view (320px-767px)
- [ ] Touch interactions work on mobile

### Performance Testing:

- [ ] Map loads within 2 seconds
- [ ] Charts render smoothly
- [ ] Auto-refresh doesn't cause lag
- [ ] No memory leaks from intervals

---

## Configuration

### Environment Variables (if needed):

```env
MAP_DEFAULT_LAT=15.4866
MAP_DEFAULT_LNG=120.9675
MAP_DEFAULT_ZOOM=12
REFRESH_INTERVAL_MAP=10000
REFRESH_INTERVAL_COUNTERS=15000
```

### Routes (in `routes/web.php`):

All new routes are protected with:

- `auth` middleware
- `approved` middleware
- `role:admin` role check

---

## Future Enhancements

1. **Advanced Features**:
    - Real-time notifications using WebSockets
    - Historical map playback
    - Custom incident filtering
    - Route optimization display
    - Weather overlay on map
    - Traffic incident integration

2. **Analytics**:
    - Average response time trends
    - Incident prediction models
    - Heat maps of incident locations
    - Driver performance analytics
    - Peak hour analysis

3. **Customization**:
    - User-configurable refresh rates
    - Configurable incident type colors
    - Custom dashboard layouts
    - Saved dashboard views

---

## Files Modified/Created

### Modified:

1. **`app/Http/Controllers/Admin/DashboardController.php`**
    - Added 4 new API methods:
        - `gpsLocations()`
        - `responseLoadAnalytics()`
        - `situationOverview()`
        - `fleetReadiness()`

2. **`routes/web.php`**
    - Added 4 new API routes
    - All protected with admin middleware

3. **`resources/views/admin/dashboard.blade.php`**
    - Complete redesign with new components
    - Enhanced styling and layout
    - New Chart.js integration
    - LeafletJS map implementation
    - AJAX auto-refresh logic

### No Database Changes Required

- Uses existing models and tables
- No migrations needed
- Backward compatible

---

## Troubleshooting

### Map Not Loading:

```javascript
// Check if Leaflet is loaded
console.log(L); // Should not be undefined
```

### Charts Not Rendering:

- Verify Chart.js is loaded
- Check canvas element exists
- Verify API returns valid JSON

### Auto-refresh Not Working:

- Check browser console for errors
- Verify routes are accessible
- Check AJAX response status

### GPS Data Missing:

- Verify ambulances have latitude/longitude
- Check incidents table for location data
- Verify drivers have GPS tracking enabled

---

## Support & Documentation

For more information:

- LeafletJS Docs: https://leafletjs.com/
- Chart.js Docs: https://www.chartjs.org/
- Laravel Documentation: https://laravel.com/docs

---

## Version History

### v2.0 (Current)

- Live Command Map with LeafletJS
- Response Load Analytics with Chart.js
- Situation Overview Panel
- Fleet Readiness Card
- 10-second auto-refresh for maps and analytics
- 15-second auto-refresh for counters
- Professional EOC-style design

### v1.0 (Previous)

- Basic dashboard with static data
- Simple incident and dispatch lists
- Basic KPI counters

---

**Last Updated**: 2024-07-14
**Upgrade Completed**: ✅
