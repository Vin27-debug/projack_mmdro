# MuniResQ Simple System Flowchart Notes

This simple flowchart shows the main user-facing workflow of the MuniResQ system. It is designed for clients, stakeholders, and presentation use, not for technical development details.

## 1. Authentication
- The user opens the system and logs in.
- The system checks the account and role.
- The user is redirected to the appropriate dashboard depending on whether they are a Super Admin, Admin, or Driver.

## 2. Super Admin
- The Super Admin manages users.
- The Super Admin manages drivers and ambulances.
- The Super Admin manages system backup and restore functions.
- The session ends when the user logs out.

## 3. Admin
- The Admin receives emergency incidents.
- The Admin dispatches an ambulance to the incident.
- The Admin monitors the ambulance and GPS location.
- The Admin watches emergency alerts and reviews reports.
- The Admin also views reports and analytics for operational review.

## 4. Driver
- The Driver receives an assignment.
- The Driver decides whether to accept or wait for another assignment.
- If accepted, the Driver navigates to the incident.
- The Driver arrives, responds, completes the emergency task, and submits the report.
- The process ends with logout.

## 5. Incident Flow
- An emergency is reported.
- The Admin reviews the case and dispatches the ambulance.
- The Driver decides whether to accept the assignment.
- If the driver declines, the system reassigns the task.
- If accepted, the ambulance travels, arrives, responds, completes the response, and the driver submits the report.
- The Admin reviews the result and the incident is closed.

## 6. Emergency Alerts
- A driver may trigger a panic or hijack alert.
- The system sends the driver location.
- The Admin receives the alert and responds quickly.
- The incident is resolved.

## Purpose
This flowchart keeps the process simple and client-friendly. It highlights the main actions of the system without showing technical code, database details, or Laravel implementation details.
