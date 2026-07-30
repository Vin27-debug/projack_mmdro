# MuniResQ Dashboard API Reference

## Base URL

```
/admin/dashboard
```

## Endpoints

### 1. GPS Locations

**Route**: `/admin/dashboard/gps-locations`  
**Method**: GET  
**Authentication**: Required (admin role)

**Response**:

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
        },
        {
            "id": 2,
            "name": "Ambulance 2",
            "plate_number": "AMB-002",
            "latitude": 15.49,
            "longitude": 120.97,
            "status": "in_use",
            "driver_name": "Jane Smith"
        }
    ],
    "incidents": [
        {
            "id": 1,
            "incident_number": "INC-2024-001",
            "latitude": 15.485,
            "longitude": 120.968,
            "type": "Medical",
            "status": "active",
            "location": "Barangay 1, Downtown"
        },
        {
            "id": 2,
            "incident_number": "INC-2024-002",
            "latitude": 15.492,
            "longitude": 120.975,
            "type": "Fire",
            "status": "pending",
            "location": "Commercial District"
        }
    ]
}
```

**Usage**:

```javascript
fetch("/admin/dashboard/gps-locations")
    .then((res) => res.json())
    .then((data) => {
        console.log("Ambulances:", data.ambulances);
        console.log("Incidents:", data.incidents);
    });
```

---

### 2. Response Load Analytics

**Route**: `/admin/dashboard/response-load-analytics`  
**Method**: GET  
**Authentication**: Required (admin role)  
**Time Period**: Last 30 days

**Response**:

```json
{
    "Fire": 5,
    "Medical": 12,
    "Rescue": 3,
    "Crime": 2
}
```

**Usage**:

```javascript
fetch("/admin/dashboard/response-load-analytics")
    .then((res) => res.json())
    .then((data) => {
        // Create doughnut chart
        const chartCtx = document
            .getElementById("responseLoadChart")
            .getContext("2d");
        new Chart(chartCtx, {
            type: "doughnut",
            data: {
                labels: ["Fire", "Medical", "Rescue", "Crime"],
                datasets: [
                    {
                        data: [
                            data.Fire,
                            data.Medical,
                            data.Rescue,
                            data.Crime,
                        ],
                        backgroundColor: [
                            "#dc3545",
                            "#0d6efd",
                            "#198754",
                            "#fd7e14",
                        ],
                    },
                ],
            },
        });
    });
```

---

### 3. Situation Overview

**Route**: `/admin/dashboard/situation-overview`  
**Method**: GET  
**Authentication**: Required (admin role)

**Response**:

```json
{
    "active_incidents": 5,
    "dispatched_units": 3,
    "completed_responses": 18,
    "pending_reports": 2
}
```

**Usage**:

```javascript
fetch("/admin/dashboard/situation-overview")
    .then((res) => res.json())
    .then((data) => {
        document.getElementById("overviewActiveIncidents").textContent =
            data.active_incidents;
        document.getElementById("overviewDispatchedUnits").textContent =
            data.dispatched_units;
        document.getElementById("overviewCompletedResponses").textContent =
            data.completed_responses;
        document.getElementById("overviewPendingReports").textContent =
            data.pending_reports;
    });
```

---

### 4. Fleet Readiness

**Route**: `/admin/dashboard/fleet-readiness`  
**Method**: GET  
**Authentication**: Required (admin role)

**Response**:

```json
{
    "available_ambulances": 4,
    "active_ambulances": 3,
    "vehicles_maintenance": 1,
    "drivers_online": 8,
    "fleet_utilization": 75.0
}
```

**Usage**:

```javascript
fetch("/admin/dashboard/fleet-readiness")
    .then((res) => res.json())
    .then((data) => {
        document.getElementById("readinessAvailable").textContent =
            data.available_ambulances;
        document.getElementById("readinessActive").textContent =
            data.active_ambulances;
        document.getElementById("readinessMaintenance").textContent =
            data.vehicles_maintenance;
        document.getElementById("readinessDrivers").textContent =
            data.drivers_online;
    });
```

---

### 5. Dashboard Counters

**Route**: `/admin/dashboard/counters`  
**Method**: GET  
**Authentication**: Required (admin role)

**Response**:

```json
{
    "totalIncidents": 25,
    "activeIncidents": 5,
    "closedIncidents": 20,
    "totalDrivers": 10,
    "availableDrivers": 8,
    "availableVehicles": 4,
    "maintenanceVehicles": 1,
    "activeDispatches": 3,
    "completedDispatches": 15,
    "panicCount": 0,
    "responseTime": 12
}
```

**Usage**:

```javascript
fetch("/admin/dashboard/counters")
    .then((res) => res.json())
    .then((data) => {
        document.querySelector('[data-counter="activeIncidents"]').textContent =
            data.activeIncidents;
        document.querySelector(
            '[data-counter="activeDispatches"]',
        ).textContent = data.activeDispatches;
        // ... update other counters
    });
```

---

## Auto-Refresh Configuration

### Current Intervals:

```javascript
// Maps & Analytics: 10 seconds
const REFRESH_INTERVAL_MAP = 10000;

// Counters: 15 seconds
const REFRESH_INTERVAL_COUNTERS = 15000;
```

### Modify Intervals:

```javascript
// Change map refresh to 5 seconds
setInterval(function () {
    refreshMapData();
    loadSituationOverview();
    loadFleetReadiness();
}, 5000); // 5000ms = 5 seconds

// Change counter refresh to 20 seconds
setInterval(updateCounters, 20000); // 20000ms = 20 seconds
```

---

## Error Handling

All endpoints return standard HTTP status codes:

| Code | Meaning      | Action                 |
| ---- | ------------ | ---------------------- |
| 200  | Success      | Use returned data      |
| 401  | Unauthorized | Redirect to login      |
| 403  | Forbidden    | Check user permissions |
| 404  | Not Found    | Check route exists     |
| 500  | Server Error | Check server logs      |

### Example Error Handling:

```javascript
fetch("/admin/dashboard/gps-locations")
    .then((response) => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then((data) => {
        // Process data
    })
    .catch((error) => {
        console.error("Error:", error);
        // Show error message to user
    });
```

---

## Rate Limiting & Performance

### Best Practices:

1. **Avoid Excessive Polling**: Use 10-30 second intervals
2. **Cache Data**: Store recent responses to reduce API calls
3. **Lazy Load**: Load data only when visible
4. **Debounce**: Throttle updates during rapid API calls

### Performance Tips:

```javascript
// Only refresh visible elements
document.addEventListener("visibilitychange", function () {
    if (document.hidden) {
        // Stop intervals when tab not visible
        clearInterval(refreshInterval);
    } else {
        // Resume intervals when tab becomes visible
        refreshInterval = setInterval(refreshMapData, 10000);
    }
});
```

---

## Incident Types

Valid incident types for filtering/display:

- `Fire`
- `Medical`
- `Rescue`
- `Crime`

### Color Mapping:

```javascript
const incidentColors = {
    Fire: "#dc3545", // Red
    Medical: "#0d6efd", // Blue
    Rescue: "#198754", // Green
    Crime: "#fd7e14", // Orange
};
```

---

## Ambulance Status

Valid ambulance status values:

- `available` - Ready for dispatch
- `in_use` - Currently responding to incident
- `maintenance` - Under maintenance

### Status Icons:

```javascript
const statusIcons = {
    available: "🟢", // Green
    in_use: "🔵", // Blue
    maintenance: "🟡", // Yellow
};
```

---

## Example: Real-time Dashboard Implementation

```html
<!-- HTML Elements -->
<div id="liveCommandMap" style="width: 100%; height: 720px;"></div>
<canvas id="responseLoadChart"></canvas>
<div id="overviewActiveIncidents"></div>
<div id="readinessAvailable"></div>

<script>
    // Initialize map
    let map;
    function initMap() {
        map = L.map("liveCommandMap").setView([15.4866, 120.9675], 12);
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(
            map,
        );
        refreshMap();
    }

    // Refresh map data
    function refreshMap() {
        fetch("/admin/dashboard/gps-locations")
            .then((res) => res.json())
            .then((data) => {
                // Clear existing markers
                map.eachLayer((layer) => {
                    if (layer instanceof L.CircleMarker) {
                        map.removeLayer(layer);
                    }
                });

                // Add ambulance markers
                data.ambulances.forEach((ambulance) => {
                    L.circleMarker([ambulance.latitude, ambulance.longitude])
                        .addTo(map)
                        .bindPopup(`${ambulance.name} - ${ambulance.status}`);
                });

                // Add incident markers
                data.incidents.forEach((incident) => {
                    L.circleMarker([incident.latitude, incident.longitude])
                        .addTo(map)
                        .bindPopup(
                            `${incident.incident_number} - ${incident.type}`,
                        );
                });
            });
    }

    // Start auto-refresh
    initMap();
    setInterval(refreshMap, 10000); // Every 10 seconds
</script>
```

---

## Troubleshooting

### Map Markers Not Updating

- Check API endpoint returns valid GPS coordinates
- Verify Leaflet library is loaded
- Check browser console for errors
- Ensure ambulances/incidents have location data

### Charts Not Displaying

- Verify Canvas element exists in HTML
- Check Chart.js library is loaded
- Ensure API returns numeric values
- Check for JavaScript errors in console

### Data Not Refreshing

- Check network tab for API requests
- Verify interval timers are running
- Check for CORS issues
- Look for JavaScript errors

### Performance Issues

- Reduce refresh interval frequency
- Implement request caching
- Use debouncing for updates
- Check database query performance

---

## Support

For issues or questions:

1. Check browser console for errors
2. Verify API endpoints in Network tab
3. Check user permissions
4. Review server logs
5. Contact development team

---

**Last Updated**: July 14, 2024
