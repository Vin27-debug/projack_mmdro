# MuniResQ System - Client Flowchart Guide

**What is MuniResQ?**  
MuniResQ is an emergency response system that coordinates ambulance dispatch, tracks real-time locations, and manages emergency incidents from report to closure.

**Flowchart Purpose**: Show how the system works from a user's perspective  
**Audience**: Clients, stakeholders, capstone presentations  
**Complexity Level**: Simple - business processes only, no technical details

---

## 📊 What This Flowchart Shows

This flowchart visualizes the MuniResQ system from a **user's perspective**, showing:

1. **How users log in** and access their role-based dashboard
2. **What each role does** (Super Admin, Admin, Driver)
3. **The complete incident flow** from report to closure
4. **Emergency alert handling** (Panic & Hijack situations)
5. **Key decision points** where users make choices

---

## 🎯 Main User Roles

### 👨‍💼 **Super Admin**
Responsible for system management and user administration.

**Functions**:
- Approve or reject pending user accounts
- Manage driver profiles
- Manage ambulance fleet
- Create system backups
- Restore from backups

### 👨‍💼 **Admin**
Responsible for emergency response coordination and resource management.

**Functions**:
- View system dashboard (incidents, resources)
- Receive and assess incidents
- Dispatch ambulances to incidents
- Track ambulance GPS in real-time
- Manage emergency alerts (Panic/Hijack)
- Review incident reports and approve closure
- Schedule and track vehicle maintenance
- View system audit logs

### 🚗 **Driver**
Responsible for emergency response and patient transport.

**Functions**:
- View pending dispatch assignments
- Accept or decline dispatch
- Navigate to incident location
- Update response status (en route, arrived, completed)
- Send real-time GPS location updates
- Trigger emergency alerts if needed
- Submit incident reports after completion
- View dispatch history

---

## 📋 Main Process Flows

### 1. **LOGIN & ACCOUNT ACCESS**
```
User Attempts Login
    ↓
Credentials Valid?
    ├─ NO → Login Failed (Try Again)
    └─ YES → Check Account Status
        ├─ Pending → Wait for Super Admin Approval
        ├─ Rejected → Cannot Access System
        └─ Approved → Proceed to Dashboard
    ↓
Determine User Role
    ├─ Super Admin Dashboard
    ├─ Admin Dashboard
    └─ Driver Dashboard
```

### 2. **INCIDENT LIFECYCLE** (The Main Business Process)
```
🔴 Citizen Reports Emergency
    ↓
👨‍💼 Admin Receives & Reviews Incident
    ├─ Assess severity
    ├─ Identify resources needed
    └─ Determine location
    ↓
🚨 Admin Dispatches Ambulance
    ├─ Selects available driver
    ├─ Selects available ambulance
    └─ Driver receives assignment
    ↓
🚗 Driver Makes Decision
    ├─ Accept → Proceed to Incident
    └─ Decline → Reassign to Another Driver
    ↓
✅ Ambulance Responds
    ├─ En Route (Driving to scene)
    ├─ Arrived (At location)
    └─ Responding (Providing care)
    ↓
✔️ Incident Complete
    ↓
📝 Driver Submits Report
    ↓
👨‍💼 Admin Reviews & Approves
    ↓
🏁 Incident Closed (Archived)
```

### 3. **EMERGENCY ALERT FLOW** (Panic/Hijack)
```
⚠️ Emergency Situation Occurs

Driver Triggers Alert Button
    ├─ 🆘 PANIC ALERT → Personal Danger
    └─ 🔴 HIJACK ALERT → Vehicle Hijacking
    ↓
📍 Driver's GPS Location Sent
    ↓
📢 Admin Receives Alert Notification
    ├─ Alert details displayed
    ├─ Driver location shown on map
    └─ (For Hijack: Authorities also notified)
    ↓
👨‍💼 Admin Takes Action
    ├─ Contact driver
    ├─ Send backup assistance
    └─ Monitor situation
    ↓
✅ Situation Resolved (Alert Closed)
```

---

## 🎨 Flowchart Visual Guide

### Color Legend

| Color | Meaning | Examples |
|-------|---------|----------|
| 🟢 Green | Start / Success / Completion | Login Success, Incident Closed |
| 🔴 Red | Error / Rejection / Failure | Login Failed, Account Rejected |
| 🔵 Blue | Process / Action / Update | Dispatch Ambulance, Submit Report |
| 🟠 Orange | Decision / Question | Does User Accept? Is Report Approved? |
| 🟣 Purple | Dashboard / User Interface | Admin Dashboard, Driver Dashboard |

### Arrow Meanings

- **→ Straight Arrow**: Normal flow progression
- **Decision Box**: A choice the user must make
- **Multiple Paths**: Different outcomes based on decision

---

## 📖 Key Features Explained

### **Approval Flow (Super Admin)**
New users register with a status of **Pending**. Super Admins review accounts and:
- **Approve** → User can now log in with their role
- **Reject** → User cannot access the system

### **Dispatch & Assignment (Admin & Driver)**
When an incident occurs:
1. Admin selects an available driver and ambulance
2. System sends dispatch to driver's phone/interface
3. Driver can **Accept** (proceed to incident) or **Decline** (system reassigns)
4. Once accepted, driver's status changes to "Assigned"

### **GPS Tracking (Real-time)**
- Drivers continuously send GPS location updates
- Admin sees real-time ambulance position on a map
- Historical GPS data stored for post-incident analysis

### **Report Approval (Incident Closure)**
After incident is completed:
1. Driver submits incident report with details
2. Admin reviews report for accuracy
3. Admin approves → Incident marked closed
4. If rejected → Driver revises and resubmits

### **Emergency Alerts (Panic/Hijack)**
If driver encounters danger:
- **Panic Button** → Personal safety emergency
- **Hijack Button** → Vehicle being stolen/hijacked
- Alert sends driver's exact GPS location to admin
- (Hijack alerts also notify authorities)

---

## 🔐 Security & Approval

The system enforces account approval at every stage:

1. **Registration** → User status: Pending
2. **Super Admin Review** → Status becomes Approved or Rejected
3. **Only Approved Users** can log in to their dashboard
4. **Role-Based Access** → Different views for each role

This ensures only authorized personnel can access the emergency response system.

---

## 🚀 Typical User Experience

### **For a Super Admin**
```
1. Log in
2. Check Dashboard
3. Review Pending Accounts
4. Approve or Reject Users
5. Manage Drivers & Ambulances
6. Create System Backup
7. Log out
```

### **For an Admin (During Active Incident)**
```
1. Log in
2. View Dashboard (Active Incidents, Resources)
3. Receive Incident Report
4. Review Incident Details
5. Dispatch Ambulance (Select Driver & Vehicle)
6. Monitor GPS Tracking in Real-time
7. Receive Driver Status Updates
8. Receive Incident Report from Driver
9. Review & Approve Report
10. Incident Marked Closed
11. Log out
```

### **For a Driver (During Dispatch)**
```
1. Log in
2. See "Dispatch Received" notification
3. Review Incident Details
4. Accept Dispatch
5. Navigate to Incident Location
6. Send GPS Updates
7. Arrive at Scene
8. Update Status to "Responding"
9. Complete Incident
10. Submit Incident Report
11. Return to Available Status
12. Log out
```

---

## 📱 System Statuses

### **User Account Status**
- **Pending** → Waiting for Super Admin approval
- **Approved** → Can log in and access system
- **Rejected** → Cannot access system

### **Driver Status**
- **Available** → Ready to receive dispatch
- **Assigned** → Has active dispatch
- **En Route** → Traveling to incident
- **On Scene** → At incident location
- **Returning** → Returning to base

### **Incident Status**
- **Pending** → Incident created, waiting dispatch
- **Dispatched** → Ambulance assigned, awaiting acceptance
- **Responding** → Ambulance en route or on scene
- **Completed** → Incident handled, report pending
- **Closed** → Report approved, incident archived

### **Ambulance Status**
- **Available** → Ready for deployment
- **On Duty** → Currently responding to incident
- **Maintenance** → Under maintenance, unavailable

---

## 🎯 Design Philosophy

This flowchart is designed to:
- ✅ Be **understandable in 1-2 minutes**
- ✅ Show **business processes**, not technical details
- ✅ Use **simple language** (not code terminology)
- ✅ Highlight **major decision points** where users interact
- ✅ Show **all main workflows** without overwhelming detail
- ✅ Be **appropriate for client presentations**
- ✅ Serve as **training material** for new users

### What This Flowchart DOES Show
- User roles and their main functions
- How users interact with the system
- Complete incident response flow
- Emergency alert handling
- Major business decisions and outcomes

### What This Flowchart DOES NOT Show
- Technical implementation details
- Route names or URLs
- Controller or database names
- HTTP methods or API endpoints
- Validation rules or error codes
- Middleware or security implementation
- Line-by-line system logic

---

## 📖 How to Use This Flowchart

### **For Client Presentations**
"This is how your emergency response system works from the user's perspective..."

### **For System Onboarding**
"New users should understand this flow before accessing the system..."

### **For Training**
"Let's walk through what each role does in the system..."

### **For Project Documentation**
"This is a high-level overview of the MuniResQ system architecture..."

### **For Stakeholder Communication**
"Here's how we coordinate emergency response and ambulance dispatch..."

---

## 📊 System Overview at a Glance

| Component | Purpose | Key Users |
|-----------|---------|-----------|
| **Account Management** | Control who can access system | Super Admin |
| **Incident Management** | Track emergency reports | Admin |
| **Dispatch System** | Assign ambulances to incidents | Admin |
| **GPS Tracking** | Real-time vehicle monitoring | Admin |
| **Emergency Alerts** | Handle urgent situations | Admin, Driver |
| **Report & Closure** | Document incident response | Admin, Driver |

---

## ✅ Comparing to Detailed Flowchart

| Aspect | Client Flowchart | Technical Flowchart |
|--------|-----------------|-------------------|
| **Audience** | Clients, Non-technical | Developers, Technical Staff |
| **Focus** | Business Processes | Implementation Details |
| **Nodes** | ~45 | ~200+ |
| **Shows Routes?** | No | Yes |
| **Shows Controllers?** | No | Yes |
| **Shows Middleware?** | No | Yes |
| **Shows Validation?** | No | Yes |
| **Purpose** | Easy Understanding | Complete Reference |
| **Read Time** | 1-2 minutes | 10-15 minutes |

Both flowcharts represent the same system - just at different levels of detail.

---

## 🔗 File Information

- **Flowchart File**: `docs/muniresq-client-flowchart.mmd`
- **This Notes File**: `docs/muniresq-client-flowchart-notes.md`
- **Technical Flowchart**: `docs/muniresq-system-flowchart.mmd` (detailed version)

---

## 📖 To View the Flowchart

### **Option 1: Mermaid Live Editor** (Recommended)
```
1. Visit https://mermaid.live
2. Copy contents of: docs/muniresq-client-flowchart.mmd
3. Paste into editor
4. Flowchart will render instantly
5. Click "Export" to save as image
```

### **Option 2: GitHub**
- Upload file to repository
- Mermaid renders automatically

### **Option 3: VS Code**
- Install "Markdown Preview Mermaid Support" extension
- Open `.mmd` file and preview

---

**This flowchart represents your MuniResQ system in the simplest, most understandable way possible for clients and stakeholders.**
