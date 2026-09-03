# MuniResQ System - Client Flowchart Guide

**What is MuniResQ?**  
MuniResQ is an emergency response system that coordinates ambulance dispatch, tracks real-time locations, and manages emergency incidents from report to closure.

**Flowchart Purpose**: Show how the system works from a user's perspective  
**Audience**: Clients, stakeholders, capstone presentations  
**Complexity Level**: Simple - business processes only, no technical details

---

## 📊 System Overview

The flowchart shows how three user roles interact with MuniResQ:

### **👨‍💼 Super Admin**
Manages system users, ambulances, and database
- Approve or reject new user accounts
- Manage driver profiles
- Manage ambulance fleet
- Create and restore system backups

### **👨‍💼 Admin**
Coordinates emergency response and allocates resources
- Receive emergency incident reports
- Dispatch ambulances to incidents
- Track ambulance locations in real-time via GPS
- Monitor emergency alerts (panic/hijack)
- Review and approve incident reports
- View performance analytics and reports

### **🚗 Driver**
Responds to emergency incidents and provides care
- Receive dispatch assignments
- Accept or decline dispatch
- Navigate to incident location
- Complete incident response
- Submit incident report
- Send GPS location updates

---

## 🚨 Main Emergency Workflow

```
1. Emergency Reported
   ↓
2. Admin Receives & Reviews Incident
   ↓
3. Ambulance Dispatched (Driver & Vehicle Assigned)
   ↓
4. Driver Accepts Assignment
   ├─ OR Declines → Reassigned to Another Driver
   ↓
5. Ambulance Travels to Scene (GPS Tracked)
   ↓
6. Ambulance Arrives at Location
   ↓
7. Emergency Response Provided
   ↓
8. Incident Complete
   ↓
9. Driver Submits Incident Report
   ↓
10. Admin Approves Report
    ↓
11. Incident Closed
```

---

## 🆘 Emergency Alert System

**If a driver encounters an emergency:**

1. Driver triggers: **Panic Alert** (personal danger) OR **Hijack Alert** (vehicle stolen)
2. Driver's GPS location is sent to admin
3. Admin receives alert and takes immediate action
4. Situation is resolved

---

## 🎨 Color Guide

| Color | Meaning |
|-------|---------|
| 🟢 Green | Start / End / Success |
| 🔵 Blue | Process / Action |
| 🟠 Orange | Decision / Choice |
| 🟣 Purple | User Dashboard / Role |

---

## 📋 Quick Facts

✅ **Real-time GPS tracking** - Track ambulances in real-time  
✅ **Automatic dispatch** - System can auto-assign ambulances  
✅ **Emergency alerts** - Panic and hijack buttons with location  
✅ **Incident reports** - Driver submits, admin approves  
✅ **Performance analytics** - View reports and metrics  
✅ **System security** - User approval and role-based access  

---

## 🚀 Perfect For

- Capstone presentations to stakeholders
- Client meetings and demonstrations
- Training materials for new staff
- System documentation overview
- Project proposals

---

## 📖 How to View the Flowchart

### **Option 1: Mermaid Live Editor** (Recommended)
1. Visit: https://mermaid.live
2. Copy contents of: `muniresq-client-flowchart.mmd`
3. Paste into the editor
4. Flowchart renders instantly
5. Export as PNG/SVG/PDF for presentations

### **Option 2: GitHub**
- Upload `.mmd` file to repository
- Mermaid renders automatically

### **Option 3: VS Code**
- Install "Markdown Preview Mermaid Support" extension
- Open `.mmd` file and click preview

---

**This client-friendly flowchart presents MuniResQ in simple, easy-to-understand terms for non-technical stakeholders and clients.**
