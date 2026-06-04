# User Manuals & Quick Guides

## ClearBay MVP — Role-Based Navigational Pathways

**Version**: 1.0  
**Date**: 2026-06-04

---

## 6.1 Role-Based Login Credentials

All users log in at `/login` with their email and password. The system redirects each role to their respective dashboard:

| Role | Email | Temporary Password | Redirect Route |
|------|-------|-------------------|----------------|
| Nurse | `nurse@clearbay.com` | Password: `12345678` | `/hospital/dashboard` |
| Nurse | `nurse2@clearbay.com` | Password: `12345678` | `/hospital/dashboard` |
| Hospital Admin | `hospadmin@clearbay.com` | Password: `12345678` | `/hospital/dashboard` |
| Paramedic | `paramedic@clearbay.com` | Password: `12345678` | `/ambulance` |
| Dispatcher | `dispatcher@clearbay.com` | Password: `12345678` | `/dispatcher` |
| System Admin | `admin@clearbay.com` | Password: `12345678` | `/admin` |

**Authentication Mechanism**: Defined in `AuthController::login()` and `AuthService::login()`. Role-based redirect determined by `AuthController::_getRedirectRoute()`:

```php
private function _getRedirectRoute(User $user): string
{
    switch ($user->role) {
        case 'nurse':
        case 'hospital_admin':
            return 'hospital.dashboard';
        case 'paramedic':
            return 'ambulance.home';
        case 'dispatcher':
            return 'dispatcher.index';
        case 'admin':
            return 'admin.dashboard';
        default:
            return 'auth.login';
    }
}
```

---

## 6.2 ED Charge Nurse Workflow

### 6.2.1 Quick Start

1. **Navigate** to ClearBay login page → enter `nurse@clearbay.com` / `12345678`
2. **System** redirects to **SC-02 Emergency Department Dashboard** (`/hospital/dashboard`)
3. The dashboard auto-loads your hospital's current queue and metrics

### 6.2.2 Dashboard Overview (SC-02)

The dashboard displays four key zones:

```
┌─────────────────────────────────────────────────────┐
│  [Status Banner: GREEN/AMBER/RED]  [Update Status]  │
├────────────┬────────────┬────────────┬───────────────┤
│ Avg Wait   │ vs Baseline│ Completed  │ In Queue      │
│   12 min   │   -48 min  │   14 today │    3          │
├────────────┴────────────┴────────────┴───────────────┤
│ Ambulance Queue Table (auto-refreshes every 10s)     │
│ ┌─────────┬──────────┬──────┬─────┬──────┬────────┐ │
│ │ Unit ID │ Provider │Acuity│ ETA │ Wait │ Action │ │
│ ├─────────┼──────────┼──────┼─────┼──────┼────────┤ │
│ │KRC-12   │Red Cross │Crit. │ 5m  │ 12m  │[Clear] │ │
│ │AAR-04   │AAR       │Stable│ 7m  │ 8m   │[Clear] │ │
│ └─────────┴──────────┴──────┴─────┴──────┴────────┘ │
└─────────────────────────────────────────────────────┘
```

**Metrics Explained**:
- **Avg Wait Today**: Average handover wait time for today's completed handovers
- **vs Baseline**: Difference from the 60-minute baseline target (negative = better)
- **Completed Today**: Number of handovers cleared today
- **In Queue**: Ambulances that have arrived or are in preparation

### 6.2.3 Updating ED Status (SC-04)

1. Click the **"Update Status"** button on the status banner
2. A Bootstrap modal opens with:
   - **Status selection**: GREEN (normal), AMBER (limited), RED (full)
   - **Bays available**: Number of open treatment bays
3. Select the appropriate status and bay count
4. Click **"Update Status"**
5. System confirms with success flash message
6. The banner colour updates immediately

**Note**: Nurses can only change the status colour (GREEN/AMBER/RED). Bay count changes require `hospital_admin` role.

**Code Reference**: `HospitalController::updateStatus()` — role guard at line 131:
```php
if ($user_role !== 'hospital_admin' && $user_role !== 'admin') {
    $bays_available = (int) $hospital->bays_available; // Locked for nurses
}
```

### 6.2.4 Completing a Handover (SC-05)

1. Find the arriving ambulance row in the queue table
2. Click the **"Clear Bay"** button in the Actions column
3. A Bootstrap modal opens with optional fields:
   - **Bay Number**: ED bay where the patient was accepted (e.g., "Bay 3A")
   - **Notes**: Any clinical or administrative notes (max 2000 chars)
4. Fill in optional details → click **"Confirm Handover Complete"**
5. System:
   - Sets handover status to `Cleared`
   - Records `handover_complete_at` timestamp
   - Calculates and stores `wait_time_minutes`
   - Releases ambulance status to `Available`
   - Updates pre-notification status to `Handover Complete`
6. The ambulance row disappears from the queue table
7. The paramedic sees "Handover Confirmed" on their active run screen

**Code Reference**: `HospitalController::completeHandover()` → `HospitalService::completeHandover()`

### 6.2.5 Viewing Analytics (SC-06) — Hospital Admin Only

1. Click **"View Analytics"** link on the dashboard
2. Select date range: **7 days**, **30 days**, or **90 days**
3. The analytics view shows:
   - **Daily Wait Time Chart**: Average wait time per day
   - **Daily Handover Count**: Number of handovers completed per day
   - **Provider Performance Table**: Each EMS provider's total handovers and average wait time
4. Click **"Export"** to download a CSV report (30-day range)

**Code Reference**: `HospitalController::analytics()` → `HospitalService::getAnalytics()`

---

## 6.3 Paramedic Workflow

### 6.3.1 Quick Start

1. **Navigate** to ClearBay login page → enter `paramedic@clearbay.com` / `12345678`
2. **System** redirects to **SC-07 Paramedic Home** (`/ambulance`)
3. GPS acquires your location immediately (fallback: Nairobi centre)

### 6.3.2 Home Map Overview (SC-07)

The home screen is a split layout:

```
┌───────────────────────┬──────────────────────────────┐
│                       │  HOSPITALS (sorted by         │
│    MAPBOX MAP         │  driving distance)            │
│                       │                               │
│   🟢 KNH (GREEN)     │  ┌─────────────────────────┐  │
│   🟠 MLK (AMBER)     │  │ KNH - 2.3 km - 8 min    │  │
│   🔴 MBG (RED)       │  │ 🟢 Bays: 3 | Queue: 1   │  │
│                       │  │ [Select]                │  │
│                       │  ├─────────────────────────┤  │
│                       │  │ MLK - 3.1 km - 10 min   │  │
│                       │  │ 🟠 Bays: 1 | Queue: 2   │  │
│                       │  │ [Select]                │  │
│                       │  └─────────────────────────┘  │
└───────────────────────┴──────────────────────────────┘
```

**Map Pin Colours**:
- 🟢 **Green**: Hospital is accepting (GREEN status)
- 🟠 **Orange/Amber**: Limited capacity (AMBER status)
- 🔴 **Red**: Full, not accepting (RED status)

### 6.3.3 Creating a Pre-Notification (SC-08 → SC-09 → Submit)

**Step 1 — Select Hospital** (SC-08):
1. Tap a hospital card from the list → navigates to `/ambulance/hospital/{id}`
2. View hospital details: status colour, bays available, queue length, average wait time
3. If hospital is RED: "Facility Full" warning displayed — cannot proceed

**Step 2 — Fill Pre-Notification Form** (SC-09):
1. Tap **"Send Pre-Notification"** → navigates to `/ambulance/pre-notify/{id}`
2. The form opens with the following fields:

| Field | Type | Required | Options / Validation |
|-------|------|----------|---------------------|
| Patient Age | Number | Yes | Integer, >= 0 |
| Patient Sex | Dropdown | Yes | Male, Female, Not Specified |
| Chief Complaint | Text | Yes | Max 100 characters |
| Acuity | Buttons | Yes | Critical, Serious, Stable |
| Notes | Text | No | Max 150 characters |
| ETA | Hidden | — | Auto-calculated |

3. Select the appropriate acuity level — this determines priority for the ED team
4. Tap **"Send Pre-Notification"**

**System Response**:
- Success: Creates `pre_notification` + `handover` records in a DB transaction
- Sets ambulance status to `Transporting`
- Redirects to SC-11 Active Run screen

**Code Reference**: `AmbulanceController::sendPreNotification()` → `AmbulanceService::sendPreNotification()`

### 6.3.4 Active Run Screen (SC-11)

After sending pre-notification, the active run screen shows:

```
┌─────────────────────────────────────────────┐
│                                             │
│         ACTIVE RUN                          │
│                                             │
│   🏥 KENYATTA NATIONAL HOSPITAL             │
│                                             │
│   ETA:  5 minutes                           │
│   Status: 🟢 En route                       │
│   Bay: Bay preparing...                     │
│                                             │
│   [Check Status]                            │
│                                             │
│   🟢 Handover confirmed!                    │
│   [New Run]                                 │
│                                             │
└─────────────────────────────────────────────┘
```

**During the run**:
- GPS `watchPosition()` sends coordinates every ~5 seconds
- Server recalculates ETA dynamically (`AmbulanceService::updateLocation()`)
- Paramedic can tap **"Check Status"** to poll for handover status
- When hospital clears the handover → screen shows "Handover Confirmed"
- **"New Run"** button appears → navigates back to SC-07

**Tab State Restorer**: If the browser page is reloaded during an active run, `AmbulanceController::home()` detects the active handover and auto-redirects back to SC-11.

**Code Reference**: `AmbulanceController::activeRun()` → `AmbulanceService::getActiveRunStatus()`

### 6.3.5 Concurrency Lock

- If a paramedic already has an active (non-cleared) run, they cannot send a new pre-notification
- The system blocks both the form view and the submission endpoint
- Error message: "You already have an active run. Complete it before starting a new one."

---

## 6.4 EMS Dispatcher Workflow

### 6.4.1 Quick Start

1. **Navigate** to ClearBay login page → enter `dispatcher@clearbay.com` / `12345678`
2. **System** redirects to **SC-12 Dispatcher Command Centre** (`/dispatcher`)

### 6.4.2 Command Centre Overview (SC-12)

The dispatcher view is a two-panel layout:

```
┌─────────────────────────┬────────────────────────────┐
│                         │  ALERTS PANEL (SC-14)       │
│   MAPBOX MAP            │  ┌────────────────────┐     │
│   (Live ambulance       │  │ 🔴 KRC-12 @ KNH    │     │
│    positions)           │  │ Wait: 35 min        │     │
│                         │  │ [Acknowledge]       │     │
│   🟢 = Available        │  └────────────────────┘     │
│   🟡 = Transporting     │                             │
│   🔴 = Queued           │  FLEET STATUS (SC-13)       │
│                         │  ┌────────────────────┐     │
│                         │  │ KRC-12 🟡 KNH   5m │     │
│                         │  │ AAR-04 🔴 MBG  35m │     │
│                         │  │ NBO-07 🟢 AKU   2m │     │
│                         │  └────────────────────┘     │
│                         │                             │
│   [Search Unit ID]      │  CAPACITY (SC-15)           │
│                         │  ┌────────────────────┐     │
│                         │  │ KNH  🟥  3 bays    │     │
│                         │  │ MLK  🟧  1 bay     │     │
│                         │  │ MBG  🟥  0 bays    │     │
│                         │  └────────────────────┘     │
└─────────────────────────┴────────────────────────────┘
```

### 6.4.3 Real-Time Updates

- The SSE stream (`/dispatcher/sse-updates`) pushes updated telemetry every 5 seconds
- Ambulance markers move automatically on the map
- Fleet status panel updates unit positions and wait times
- Hospital capacity panel updates when nurses change ED status

### 6.4.4 Acknowledging Alerts (SC-14)

When an ambulance exceeds 30 minutes wait time:
1. An alert card appears in the **Alerts Panel** (right sidebar)
2. Each card shows: unit ID, hospital name, alert type, triggered time
3. Click **"Acknowledge"** on the alert card
4. System records `acknowledged_at` and `acknowledged_by` (your user ID)
5. The alert remains visible (dimmed) until the ambulance is cleared

**Code Reference**: `DispatcherController::acknowledgeAlert()` → `DispatcherService::acknowledgeAlert()`

### 6.4.5 Searching for a Unit

- Use the search box (top-right of the map) to find a specific unit ID
- The map flies to the unit's location and opens a popup with details

---

## 6.5 System Admin Workflow

### 6.5.1 Quick Start

1. **Navigate** to ClearBay login page → enter `admin@clearbay.com` / `12345678`
2. **System** redirects to **SC-16 Admin Dashboard** (`/admin`)

### 6.5.2 Admin Dashboard (SC-16)

The dashboard shows summary metrics:
- **Pilot Signups**: Total pilot program applications
- **Handovers**: Total handover records
- **Hospitals**: Total registered facilities
- **Ambulances**: Total fleet vehicles
- **Users**: Total user accounts

Navigation links to all CRUD management sections.

### 6.5.3 Managing Users

**View Users**: Navigate to `/admin/users` → paginated table with name, email, role, active status

**Create User** (`AdminController::userCreate()`):
1. Click **"Add User"** → form loads with fields:
   - Name, Email, Role (dropdown), Hospital (if nurse/admin), EMS Provider (if paramedic), Active toggle
2. Fill in the details → click **"Create User"**
3. System creates account with temporary password `12345678`
4. Success message: "User account registered successfully with temporary password '12345678'!"

**Edit User** (`AdminController::userUpdate()`):
1. Click **"Edit"** on a user row → form pre-fills with current values
2. Modify fields as needed
3. Check **"Reset Password"** to reset to temporary password `12345678`
4. Click **"Update User"`

**Deactivate User** (`AdminController::userDelete()`):
- Sets `active = 0` (soft delete — user cannot log in but record is preserved)

### 6.5.4 Managing Hospitals

CRUD operations via `/admin/hospitals/*`:
- Fields: Code, Name, Category, Status (Green/Amber/Red/Recruiting), Bays Available, Lat/Lng, Address, Contact Phone, Active
- GPS coordinates required for map pin placement

### 6.5.5 Managing Ambulances

CRUD operations via `/admin/ambulances/*`:
- Fields: Unit ID, Provider, EMS Provider (dropdown), Registration, Status, GPS Coordinates
- Status options: Available, Transporting, On Scene, Queued, Off Duty

### 6.5.6 Managing Handovers

CRUD operations via `/admin/handovers/*`:
- **Status Transitions**: Admin can change handover status between: `En route`, `Arrived`, `Acknowledged`, `Preparing`, `Cleared`
- **Arrival Declaration**: Changing from `En route` to `Arrived` sets the `arrived_at` timestamp — **this is the only mechanism to mark an ambulance as arrived**
- Code Reference: `AdminController::handoverUpdate()` (line 393):
  ```php
  if ($old_status === 'En route' && $new_status === 'Arrived') {
      $handover->arrived_at = date('Y-m-d H:i:s');
  }
  ```

### 6.5.7 Managing Pilot Signups

CRUD operations via `/admin/pilots/*`:
- View applications, edit details, or delete records

---

## 6.6 Key Behaviours & Edge Cases

| Scenario | Behaviour |
|----------|-----------|
| **Session expired** | Redirect to login with error; `redirect_url` stored for post-login redirect |
| **Wrong role** | 403 error "You do not have permission to access this resource" → redirect to login |
| **Page refresh during active run** | Tab State Restorer detects active handover → auto-redirects to SC-11 |
| **RED hospital selected** | Pre-notification blocked: "Facility is full" |
| **Concurrent run attempt** | Blocked: "You already have an active run" |
| **SSE disconnection** | Browser EventSource auto-reconnects; fresh CSRF token in initial packet |
| **GPS unavailable** | Fallback to Nairobi centre coordinates |
| **Hospital not mapped to nurse** | Redirect to logout: "Your account is not mapped to a hospital facility" |

---

*End of Section 6 — User Manuals & Quick Guides*