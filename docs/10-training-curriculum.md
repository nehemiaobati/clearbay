# Training Curriculum

## ClearBay MVP — Technical Course Modules & User Onboarding

**Version**: 1.0  
**Date**: 2026-06-04

---

## 10.1 Technical Course Modules

### Module 1: System Overview (30 minutes)

**Target Audience**: All users (nurses, paramedics, dispatchers, admins)

**Learning Objectives**:
- Understand what ClearBay does and why it exists
- Identify the five user roles and their responsibilities
- Recognize the tagline: *Clear the Bay. Free the Crew. Save the Next Life.*

**Outline**:

| Segment | Duration | Content |
|---------|----------|---------|
| 1.1 The Problem | 10 min | Explanation of ambulance off-load delays in Nairobi County. Real-world scenario: ambulance stuck in bay for 45+ minutes while another emergency goes unanswered. |
| 1.2 The Solution | 10 min | How ClearBay reduces wait times: pre-arrival visibility, capacity-based routing, automated delay alerts. Walkthrough of the three interfaces (Hospital, Ambulance, Dispatcher). |
| 1.3 The Workflow | 10 min | End-to-end lifecycle: Pre-notification → Transport → Arrival → Handover → Clear. Trace a patient from paramedic dispatch to bay clearance. |

**Key Terminology**:
- **Pre-notification**: Clinical alert sent by paramedic before arrival
- **Handover**: The patient transfer from paramedic to ED staff
- **Clear**: The completion of handover, releasing the ambulance
- **ED Status**: GREEN (accepting), AMBER (limited), RED (full)

---

### Module 2: Nurse Dashboard (45 minutes)

**Target Audience**: ED Charge Nurses (`nurse`), Hospital Administrators (`hospital_admin`)

**Learning Objectives**:
- Log in and navigate the ED dashboard (SC-02)
- Read and interpret the queue table and metrics
- Update ED status using the modal (SC-04)
- Complete handovers using the modal (SC-05)
- Handle edge cases and error states

**Hands-On Exercises**:

| Exercise | Duration | Steps |
|----------|----------|-------|
| 2.1 Login & Dashboard Familiarization | 10 min | 1. Navigate to login page. 2. Enter `nurse@clearbay.com` / `12345678`. 3. Identify the four metric cards. 4. Locate the queue table. 5. Note the status banner colour. |
| 2.2 Update ED Status | 10 min | 1. Click "Update Status" button. 2. Select status: AMBER. 3. Leave bay count unchanged. 4. Click "Update Status". 5. Verify banner changes colour. 6. Change back to GREEN. |
| 2.3 Complete a Handover | 15 min | 1. Find an ambulance row in the queue (status: "En route" or "Arrived"). 2. Click "Clear Bay". 3. Enter bay number (e.g., "Bay 2"). 4. Add notes (e.g., "Patient stable, transferred to ward"). 5. Click "Confirm". 6. Verify row disappears from queue. |
| 2.4 Error Handling | 5 min | 1. Attempt to submit empty handover form. 2. Note validation prevents submission. 3. Attempt to access another hospital's dashboard (should fail). |
| 2.5 Session Timeout | 5 min | 1. Wait 5+ minutes idle. 2. Attempt a queue refresh. 3. Note redirect to login. |

**Assessment Criteria**:
- Successfully updates ED status and verifies visual change
- Correctly completes a handover and confirms queue update
- Identifies when status update failed (validation error)
- Navigates between dashboard and analytics (admin only)

---

### Module 3: Paramedic Navigator (45 minutes)

**Target Audience**: Paramedics/EMTs (`paramedic`)

**Learning Objectives**:
- Log in and navigate the home map (SC-07)
- Interpret hospital pin colours and distance sorting
- View hospital capacity details (SC-08)
- Send a complete pre-notification (SC-09)
- Monitor active run status (SC-11)
- Handle concurrency lock and error states

**Hands-On Exercises**:

| Exercise | Duration | Steps |
|----------|----------|-------|
| 3.1 Login & Home Map | 10 min | 1. Navigate to login page. 2. Enter `paramedic@clearbay.com` / `12345678`. 3. Observe Mapbox map loading with coloured hospital pins. 4. Review hospital list sorted by distance. 5. Note hospital status colours (GREEN/AMBER/RED). |
| 3.2 Select Hospital | 5 min | 1. Tap a GREEN hospital card. 2. Review capacity details: status, bays, queue count, average wait. 3. Tap "Send Pre-Notification". |
| 3.3 Fill Pre-Notification Form | 10 min | 1. Enter patient age: 45. 2. Select sex: Male. 3. Enter chief complaint: "Chest pain". 4. Select acuity: Serious. 5. Add notes: "Patient diaphoretic, BP 160/90". 6. Verify auto-calculated ETA is shown. 7. Tap "Send". |
| 3.4 Active Run Monitoring | 10 min | 1. Observe the active run screen. 2. Note ETA countdown, hospital name, status badge. 3. Tap "Check Status" to poll for updates. 4. Wait for "Handover Confirmed" notification. 5. Tap "New Run" to return to home. |
| 3.5 Concurrency Lock | 5 min | 1. During an active run, attempt to navigate to `/ambulance`. 2. Verify auto-redirect to active run screen. 3. Try to open pre-notify form for another hospital. 4. Verify error: "You already have an active run." |
| 3.6 RED Hospital Handling | 5 min | 1. Find a RED hospital in the list. 2. Try to send pre-notification. 3. Verify blocked: "Facility is full." |

**Assessment Criteria**:
- Successfully sends a pre-notification
- Correctly fills all required fields (age, sex, complaint, acuity)
- Monitors active run and detects handover completion
- Understands concurrency lock prevents double-dispatch
- Identifies RED hospital as unsuitable for pre-notification

---

### Module 4: Dispatcher Command Centre (30 minutes)

**Target Audience**: EMS Dispatchers (`dispatcher`)

**Learning Objectives**:
- Log in and navigate the dispatcher command centre (SC-12)
- Interpret ambulance markers on the map (colour-coded by status)
- Monitor fleet status, alerts, and hospital capacity panels
- Acknowledge automated 30-minute delay alerts
- Search for specific ambulance units

**Hands-On Exercises**:

| Exercise | Duration | Steps |
|----------|----------|-------|
| 4.1 Login & Command Centre | 10 min | 1. Enter `dispatcher@clearbay.com` / `12345678`. 2. Observe Mapbox map with ambulance markers. 3. Identify the three right-side panels: Alerts, Fleet Status, Capacity. 4. Note ambulance marker colours (green=available, yellow=transporting, red=queued). |
| 4.2 Monitor Fleet | 5 min | 1. Watch the fleet status panel update every 5 seconds (SSE stream). 2. Note unit IDs, destination hospitals, and wait times. 3. Click an ambulance on the map. 4. Observe popup with unit details. |
| 4.3 Acknowledge Alert | 10 min | 1. Wait for an alert to appear (or use existing seed data). 2. Read the alert card: unit ID, hospital, wait time. 3. Click "Acknowledge". 4. Verify the alert is marked as acknowledged. 5. Note the ambulance marker changes to red. |
| 4.4 Search Unit | 5 min | 1. Type a unit ID (e.g., "KRC-12") in the search box. 2. Verify map flies to the unit's location. 3. Observe the popup with unit details. |

**Assessment Criteria**:
- Successfully acknowledges an alert
- Correctly interprets ambulance marker colours
- Identifies fleet units with long wait times
- Uses search function to locate specific unit

---

### Module 5: Admin Management (30 minutes)

**Target Audience**: System Administrators (`admin`)

**Learning Objectives**:
- Log in and navigate the admin dashboard (SC-16)
- Create, edit, and deactivate user accounts
- Manage hospital, ambulance, and handover records
- Declare ambulance arrival via status transition

**Hands-On Exercises**:

| Exercise | Duration | Steps |
|----------|----------|-------|
| 5.1 Login & Dashboard | 5 min | 1. Enter `admin@clearbay.com` / `12345678`. 2. Review dashboard metrics (pilot signups, handovers, hospitals, ambulances, users). 3. Navigate to each management section. |
| 5.2 Create User | 10 min | 1. Navigate to `/admin/users/new`. 2. Fill in: Name="Test Nurse", Email="test@clearbay.com", Role="nurse", Hospital="KNH", Active=1. 3. Click "Create". 4. Verify success message: "User account registered successfully with temporary password '12345678'!" 5. Login as the new user to verify access. |
| 5.3 Edit User & Reset Password | 5 min | 1. Edit the created user. 2. Check "Reset Password". 3. Click "Update". 4. Verify password reset message. |
| 5.4 Declare Arrival | 10 min | 1. Navigate to `/admin/handovers`. 2. Find a handover with status "En route". 3. Click "Edit". 4. Change status from "En route" to "Arrived". 5. Click "Update". 6. Verify `arrived_at` timestamp is now set. |
| 5.5 Manage Hospitals | 5 min | 1. Navigate to `/admin/hospitals`. 2. Edit a hospital. 3. Change status or bay count. 4. Save and verify on paramedic map. |

**Assessment Criteria**:
- Successfully creates a user account
- Correctly performs password reset
- Declares arrival via status transition (En route → Arrived)
- Verifies `arrived_at` is recorded

---

## 10.2 Training Schedule

| Module | Duration | Trainees | Prerequisites | Location |
|--------|----------|----------|---------------|----------|
| 1. System Overview | 30 min | All staff | None | Classroom / Video call |
| 2. Nurse Dashboard | 45 min | Nurses, Hospital Admins | Module 1 | Computer lab with ClearBay access |
| 3. Paramedic Navigator | 45 min | Paramedics/EMTs | Module 1 | Mobile device with GPS + ClearBay access |
| 4. Dispatcher Command Centre | 30 min | Dispatchers | Module 1 | Large-screen desktop with ClearBay access |
| 5. Admin Management | 30 min | System Admins | Modules 1–4 | Desktop with ClearBay admin access |

**Total Training Time**: 3 hours (including breaks)

---

## 10.3 Training Materials Required

| Material | Quantity | Source |
|----------|----------|--------|
| Functional ClearBay instance | 1 (shared or per-trainee) | Production or staging server |
| Test accounts (all 5 roles) | 5 per trainee group | Pre-configured in seed data |
| Mobile device with GPS (paramedic) | 1 per paramedic trainee | Trainee's personal device |
| Desktop with large screen (dispatcher) | 1 per dispatcher trainee | Training computer lab |
| User manual (this document, Section 6) | 1 per trainee | `docs/06-user-manuals.md` |
| Quick reference card (role-specific) | 1 per trainee | Printed/PDF |

---

## 10.4 Competency Assessment

| Competency | Module | Pass Criteria |
|------------|--------|---------------|
| Login and role-based redirect | 1 | Successfully logs in and reaches correct dashboard |
| Update ED status | 2 | Changes status from GREEN → AMBER → GREEN without errors |
| Complete handover | 2 | Clears an ambulance from the queue with valid bay number |
| Send pre-notification | 3 | Submits valid pre-notification; auto-redirected to active run |
| Monitor active run | 3 | Tracks ETA and detects status change to "Cleared" |
| Acknowledge alert | 4 | Successfully acknowledges a 30-minute delay alert |
| Create user account | 5 | Creates a functional user with correct role assignment |
| Declare arrival | 5 | Transitions handover from "En route" to "Arrived" |

---

*End of Section 10 — Training Curriculum*