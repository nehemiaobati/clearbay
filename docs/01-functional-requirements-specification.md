# Functional Requirements Specification (FRS)

## ClearBay MVP — Real-Time Ambulance Off-Load Management

**Version**: 1.0  
**Date**: 2026-06-04  
**Location**: Nairobi County, Kenya  
**Tagline**: *Clear the Bay. Free the Crew. Save the Next Life.*

---

## 1.1 System Introduction & Architectural Scope

### 1.1.1 Operational Scope

ClearBay is a real-time ambulance off-load coordination platform designed for emergency departments in Nairobi County, Kenya. The system addresses the critical problem of prolonged ambulance handover delays — commonly known as "off-load delays" — where paramedic crews are stuck in hospital bays waiting for patient handover acceptance, preventing them from responding to the next emergency.

### 1.1.2 The Three-Module Architecture

ClearBay is organized into three primary operational interfaces, each targeting a specific user role and device profile:

| Interface | User Role | Device | Module |
|-----------|-----------|--------|--------|
| **Hospital ED Dashboard** (SC-02 – SC-06) | Charge Nurse, Hospital Admin | Desktop / Tablet | `app/Modules/Hospital/` |
| **Paramedic Navigator** (SC-07 – SC-11) | Paramedic/EMT | Mobile (Android/Responsive Web) | `app/Modules/Ambulance/` |
| **Dispatcher Command Centre** (SC-12 – SC-15) | EMS Dispatcher | Desktop (Large Screen) | `app/Modules/Dispatcher/` |
| **System Admin Panel** (SC-16) | System Admin | Desktop | `app/Modules/Admin/` |

### 1.1.3 Core Objective

Reduce ambulance handover wait times by:
- Providing **pre-arrival visibility** — paramedics send patient clinical data and ETA before arrival
- Enabling **capacity-based routing** — paramedics see real-time ED status (GREEN/AMBER/RED) to select optimal destination
- Implementing **automated delay detection** — dispatchers receive alerts when any ambulance exceeds 30-minute wait threshold
- Tracking **end-to-end lifecycle** — every handover is recorded from pre-notification through arrival to clearance

### 1.1.4 Technical Architecture

- **Framework**: CodeIgniter 4 Modular MVC-S (Model-View-Controller-Service)
- **Database**: MySQL/MariaDB (schema versioned and seeded via `clearbayschema.sql`)
- **Mapping**: Mapbox GL JS v3.3.0 (replaces PRD's Google Maps recommendation)
- **Real-time**: 
  - Server-Sent Events (SSE) for dispatcher telemetry (`DispatcherController::sseStream()`)
  - AJAX polling (10s interval) for hospital dashboard queue
  - GPS `watchPosition()` for paramedic active run tracking
- **Authentication**: Session-based with Role-Based Access Control (RBAC) via `AuthFilter` + `RoleFilter`
- **Module Count**: 7 feature modules — `Admin`, `Ambulance`, `Auth`, `Dispatcher`, `Hospital`, `Pilot`, `Queue`

### 1.1.5 Integration Parameters

| Integration | Implementation | Usage |
|-------------|---------------|-------|
| Mapbox GL JS | Client-side in `ambulance/home.php` and `dispatcher/map.php` | Interactive mapping, hospital pin display, ambulance marker rendering |
| Mapbox Matrix API | Client-side fetch (not implemented as server-side) | Driving distance/duration calculation (planned) |
| Server-Sent Events | `DispatcherController::sseStream()` (10-cycle loop) | Live telemetry stream for dispatcher fleet view |
| GPS Geolocation API | `navigator.geolocation.watchPosition()` + `getCurrentPosition()` | Paramedic coordinate acquisition and active run tracking |
| Session-based Auth | `AuthFilter`, `RoleFilter`, `BaseController` | Authentication and role authorization on all protected routes |

---

## 1.2 System Actors & User Role Matrix

### 1.2.1 Role Definitions

| Role | System Name | Typical User | Department |
|------|-------------|--------------|------------|
| **ED Charge Nurse** | `nurse` | Registered nurse managing ED bay assignments | Emergency Department |
| **Hospital Administrator** | `hospital_admin` | ED manager or hospital operations lead | Hospital Administration |
| **Paramedic/EMT** | `paramedic` | Field paramedic or emergency medical technician | EMS Provider |
| **EMS Dispatcher** | `dispatcher` | Command centre operator monitoring fleet | EMS Operations |
| **System Admin** | `admin` | IT administrator managing platform configuration | IT / System Operations |

### 1.2.2 CRUD Permissions Matrix

| Entity | `nurse` | `hospital_admin` | `paramedic` | `dispatcher` | `admin` |
|--------|---------|-----------------|-------------|---------------|---------|
| **Users** | — | — | — | — | **CRUD** (`AdminController::userCreate`, `userEdit`, etc.) |
| **Hospitals** | **R** (mapped only) | **R** (mapped only) | **R** (active only) | **R** (all active) | **CRUD** |
| **Ambulances** | **R** (queue only) | **R** (queue only) | **R** (own only) | **R** (all active) | **CRUD** |
| **Handovers** | **RU** (complete) | **RU** (complete) | **R** (own active) | **R** (all active) | **CRUD** |
| **Pre-Notifications** | **R** | **R** | **C** (send) | **R** | **CRUD** |
| **Alerts** | — | — | — | **RU** (acknowledge) | **CRUD** |
| **ED Status** | **U** (color only) | **U** (color + bays) | — | — | **U** |
| **Pilot Signups** | — | — | — | — | **CRUD** |
| **EMS Providers** | — | — | — | — | **CRUD** |

### 1.2.3 Route Access by Role

| Route Group | URL Prefix | Required Role(s) | Filter(s) |
|-------------|-----------|------------------|-----------|
| Auth | `/login`, `/logout` | Public (unauthenticated) | — |
| Hospital | `/hospital/*` | `nurse`, `hospital_admin` | `role:hospital_admin,nurse` |
| Ambulance | `/ambulance/*` | `paramedic` | `role:paramedic` |
| Dispatcher | `/dispatcher/*` | `dispatcher` | `role:dispatcher` |
| Admin | `/admin/*` | `admin` | `role:admin` |
| Pilot | `/pilot/*` | Public (signup form) | — |

Defined in:
- `app/Modules/Auth/Config/Routes.php`
- `app/Modules/Hospital/Config/Routes.php`
- `app/Modules/Ambulance/Config/Routes.php`
- `app/Modules/Dispatcher/Config/Routes.php`
- `app/Modules/Admin/Config/Routes.php`

### 1.2.4 Screen Access by Role

| Screen | `nurse` | `hospital_admin` | `paramedic` | `dispatcher` | `admin` |
|--------|---------|-----------------|-------------|---------------|---------|
| SC-01 Login | ✓ | ✓ | ✓ | ✓ | ✓ |
| SC-02 Dashboard | ✓ | ✓ | — | — | — |
| SC-03 Queue Table | ✓ | ✓ | — | — | — |
| SC-04 Status Modal | ✓ | ✓ | — | — | — |
| SC-05 Handover Modal | ✓ | ✓ | — | — | — |
| SC-06 Analytics | — | ✓ | — | — | — |
| SC-07 Home Map | — | — | ✓ | — | — |
| SC-08 Hospital Detail | — | — | ✓ | — | — |
| SC-09 Pre-Notify Form | — | — | ✓ | — | — |
| SC-11 Active Run | — | — | ✓ | — | — |
| SC-12 Dispatcher Map | — | — | — | ✓ | — |
| SC-13 Fleet Panel | — | — | — | ✓ | — |
| SC-14 Alerts Panel | — | — | — | ✓ | — |
| SC-15 Capacity Panel | — | — | — | ✓ | — |
| SC-16 Admin Dashboard | — | — | — | — | ✓ |

### 1.2.5 Data Visibility Boundaries

| Role | Sees |
|------|------|
| `nurse` | Only their mapped hospital's queue and metrics. No access to other hospitals. |
| `hospital_admin` | Same as nurse, plus analytics dashboard for their hospital. |
| `paramedic` | All active hospitals (status, bays, queue count). Own active run only. |
| `dispatcher` | All active hospitals, all ambulances, all unacknowledged alerts. |
| `admin` | All records across all entities. Full CRUD access. |

---

## 1.3 Functional Requirements & Modular Workflows

### 1.3.1 Pre-Notification Dispatch Workflow (Paramedic → Hospital)

**Trigger**: Paramedic identifies a patient in need of transport and chooses a destination hospital.

**Steps**:

1. **SC-07 Home Map Load** (`AmbulanceController::home()`)
   - Paramedic navigates to `/ambulance`
   - System detects paramedic via session `user_id`
   - Resolves ambulance via `AmbulanceService::getActiveAmbulance()` (lookup by `ems_provider_id`)
   - Checks for active run via `AmbulanceService::hasActiveRun()` — redirects to SC-11 if found (Tab State Restorer)
   - Fetches all active hospitals with status, bays, lat/lng
   - Sorts hospitals by driving distance (Haversine formula as fallback; Marketo Matrix API planned)
   - Renders Mapbox GL JS map with colour-coded hospital pins
   - Hospital list shows name, distance, ETA

2. **SC-08 Hospital Detail** (`AmbulanceController::detail($hospital_id)`)
   - Paramedic taps hospital card → navigates to `/ambulance/hospital/{id}`
   - System fetches detailed specs: status colour, bays available, queue count, average wait
   - If hospital status is `RED`: "Facility Full" warning displayed
   - Paramedic reviews and decides to proceed

3. **SC-09 Pre-Notification Form** (`AmbulanceController::preNotifyForm($hospital_id)`)
   - Paramedic taps "Send Pre-Notification" → navigates to `/ambulance/pre-notify/{id}`
   - System checks concurrency lock: if active run exists, redirects with error
   - Validates RED status: blocks form submission if hospital is `RED`
   - Auto-calculates ETA via `AmbulanceService::calculateEta()` (Haversine × 2.5 + 2 traffic multiplier)
   - Form fields (validated by `sendPreNotification()`):
     - `hospital_id` (hidden)
     - `patient_age` — required, integer >= 0
     - `patient_sex` — required, in_list: `Male`, `Female`, `Not Specified`
     - `chief_complaint` — required, string, max 100 chars
     - `acuity` — required, in_list: `Critical`, `Serious`, `Stable`
     - `notes` — optional, max 150 chars
     - `eta_minutes` — hidden, auto-calculated
   - Paramedic fills form → taps "Send Pre-Notification"

4. **System Transaction** (`AmbulanceService::sendPreNotification()`)
   - Wrapped in DB transaction (`db->transStart()/transComplete()`)
   - **A**: Creates `pre_notifications` record with all clinical data, status='Pending', sent_at=now
   - **B**: Creates `handovers` record linked via `pre_notification_id`, status='En route', wait_time_minutes=0
   - **C**: Updates ambulance status to 'Transporting'
   - On success: returns `pre_id` → controller responds with redirect to SC-11
   - On failure: returns null → controller responds with error JSON

5. **SC-11 Active Run** (`AmbulanceController::activeRun($pre_id)`)
   - Screen shows: live countdown ETA, hospital name, status badge
   - Frontend polls `/ambulance/run/{id}?ajax=1` for status updates
   - GPS `watchPosition()` sends coordinates via POST to `/ambulance/location`
   - `AmbulanceService::updateLocation()` recalculates ETA dynamically
   - When hospital clears handover → status changes to 'Cleared' → screen updates
   - "New Run" button appears after clearance

**Validation Rules** (file: `app/Modules/Ambulance/Controllers/AmbulanceController.php`):
```php
$rules = [
    'hospital_id'     => 'required|integer',
    'patient_age'     => 'required|integer|greater_than_equal_to[0]',
    'patient_sex'     => 'required|in_list[Male,Female,Not Specified]',
    'chief_complaint' => 'required|string|max_length[100]',
    'acuity'          => 'required|in_list[Critical,Serious,Stable]',
    'notes'           => 'permit_empty|max_length[150]',
    'eta_minutes'     => 'required|integer|greater_than_equal_to[0]',
];
```

**Affected Database Tables**: `pre_notifications`, `handovers`, `ambulances`

**Key Code References**:
- `app/Modules/Ambulance/Controllers/AmbulanceController.php` (lines 56–281)
- `app/Modules/Ambulance/Libraries/AmbulanceService.php` (lines 109–393)
- `app/Modules/Ambulance/Views/home.php` (SC-07)
- `app/Modules/Ambulance/Views/detail.php` (SC-08)
- `app/Modules/Ambulance/Views/pre_notify.php` (SC-09)
- `app/Modules/Ambulance/Views/active_run.php` (SC-11)

### 1.3.2 Handover Completion Workflow (Nurse → Paramedic)

**Trigger**: Ambulance arrives at ED and patient is accepted into a bay.

**Steps**:

1. **SC-02 Dashboard Load** (`HospitalController::dashboard()`)
   - Nurse logs in → redirects to `/hospital/dashboard`
   - System resolves mapped hospital via `HospitalService::getMappedHospital()` (reads `hospital_id` from session)
   - Renders dashboard with:
     - Status banner (GREEN/AMBER/RED background)
     - Four metric cards: Avg Wait Today, vs Baseline, Completed Today, In Queue
     - Queue table: unit ID, provider, acuity, ETA, wait time, status

2. **SC-03 Queue Auto-Refresh** (`HospitalController::getQueue()`)
   - Frontend polls `/hospital/queue` every 10 seconds via AJAX
   - `HospitalService::getQueueData()` fetches:
     - Active handovers (status != 'Cleared') for this hospital, ordered by `created_at ASC`
     - Today's completed count and average wait time
     - Calculates baseline difference (current avg vs 60-min baseline)
   - Returns JSON with `status`, `result`, `csrf_token`

3. **SC-05 Handover Completion** (`HospitalController::completeHandover()`)
   - Nurse clicks "Clear Bay" button in queue row
   - Bootstrap modal opens: bay number field (optional), notes field (optional)
   - Nurse fills optional fields → clicks "Confirm Handover Complete"
   - POST to `/hospital/handover` with `handover_id`, `bay_number`, `notes`
   - Validation: `handover_id` required|integer, `bay_number` permit_empty|alpha_numeric_space|max_length[50], `notes` permit_empty|max_length[2000]
   - `HospitalService::completeHandover()`:
     - Wrapped in DB transaction
     - Sets `arrived_at = now` if not already set
     - Calculates `wait_time_minutes` as difference between now and `arrived_at` (or `created_at`)
     - Sets `status = 'Cleared'`, `bay_number`, `notes`, `completed_by`, `handover_complete_at`
     - Updates ambulance status to 'Available'
     - Updates pre_notification status to 'Handover Complete'
   - On success: JSON `status: 'success'` → frontend removes row from queue

4. **SC-11 Paramedic Notification**
   - Paramedic's active run screen polls and detects status change to 'Cleared'
   - Screen shows "Handover Confirmed" message
   - GPS tracking stops
   - "New Run" button appears → navigates to SC-07

**Affected Database Tables**: `handovers`, `ambulances`, `pre_notifications`

**Key Code References**:
- `app/Modules/Hospital/Controllers/HospitalController.php` (lines 52–203)
- `app/Modules/Hospital/Libraries/HospitalService.php` (lines 52–222)
- `app/Modules/Hospital/Views/dashboard.php` (SC-02)

### 1.3.3 30-Minute Alert Workflow (System → Dispatcher)

**Trigger**: An ambulance handover wait exceeds 30 minutes from `arrived_at` (or `created_at`).

**Steps**:

1. **Alert Check** (`DispatcherService::_checkAndTriggerAlerts()`)
   - Called on every `getTelemetry()` request (AJAX poll and SSE stream)
   - Queries all non-cleared handovers via `handover_model->where('status !=', 'Cleared')->findAll()`
   - For each handover:
     - Calculates wait time: `time() - strtotime(arrived_at ?? created_at)`
     - Updates `wait_time_minutes` on the handover record for accuracy
     - If wait > 30 minutes:
       - Checks for existing unacknowledged alert for same ambulance+hospital
       - If no existing alert:
         - **Transaction**: Creates `alerts` record, updates ambulance status to 'Queued', inserts `audit_log` entry
         - Logs SMS notification message
         - On trans success: ambulance appears red on dispatcher map

2. **SC-14 Alert Panel** (Frontend)
   - Alerts appear as cards in the dispatcher sidebar (`dispatcher/map.php`)
   - Each card shows: unit ID, hospital name, alert type, triggered timestamp
   - "Ack" button calls `POST /dispatcher/alerts/{id}/acknowledge`
   - `DispatcherController::acknowledgeAlert()` calls `DispatcherService::acknowledgeAlert()`
   - Sets `acknowledged_at` and `acknowledged_by`

3. **Dispatcher Fleet Update**
   - After acknowledge: alert remains visible until ambulance is cleared
   - Only when handover is completed does the alert resolve

**Affected Database Tables**: `handovers` (wait_time_minutes), `alerts`, `ambulances` (status), `audit_log`

**Key Code References**:
- `app/Modules/Dispatcher/Libraries/DispatcherService.php` (lines 75–149, 156–208)
- `app/Modules/Dispatcher/Controllers/DispatcherController.php` (lines 38–145)
- `app/Modules/Dispatcher/Views/map.php` (SC-12/13/14/15)

### 1.3.4 Admin Arrival Declaration Workflow (Admin → System)

**Trigger**: System Admin manually declares that an en-route ambulance has arrived at the ED.

**Steps**:

1. **SC-16 Admin Handover Edit** (`AdminController::handoverEdit($handover_id)`)
   - Admin navigates to `/admin/handovers/edit/{id}`
   - Form loads with status dropdown: `En route`, `Arrived`, `Acknowledged`, `Preparing`, `Cleared`

2. **Status Transition Detection** (`AdminController::handoverUpdate($handover_id)`)
   - On form submit → validates all handover fields
   - Detects transition from `En route` → `Arrived` via comparison of old and new status
   - **ONLY** when this specific transition occurs:
     ```php
     if ($old_status === 'En route' && $new_status === 'Arrived') {
         $handover->arrived_at = date('Y-m-d H:i:s');
     }
     ```
   - This is the **sole mechanism** for setting `arrived_at` — paramedics and nurses cannot mark as arrived

3. **System Record Update**
   - Saves updated handover via `AdminService::saveHandover()`
   - `arrived_at` timestamp is now set — enables 30-minute alert timer to begin

**Affected Database Tables**: `handovers`

**Key Code References**:
- `app/Modules/Admin/Controllers/AdminController.php` (lines 352–404)

---

## 1.4 Non-Functional Requirements & Performance SLA

### 1.4.1 Dashboard Refresh Intervals

| Interface | Channel | Interval | Implementation |
|-----------|---------|----------|----------------|
| Hospital Queue | AJAX Polling | 10 seconds | `window.setInterval(fetchQueue, 10000)` in `dashboard.php` |
| Dispatcher Fleet | SSE Streaming | 5 seconds | `DispatcherController::sseStream()` with `sleep(5)` |
| Paramedic Active Run | AJAX Polling | On demand (manual) | `active_run.php` with status check button |
| GPS Location Update | HTTP POST | Every 5 seconds | `navigator.geolocation.watchPosition()` callback |

### 1.4.2 GPS Update Constraints

| Parameter | Value |
|-----------|-------|
| `enableHighAccuracy` | `true` |
| `maximumAge` | Not set (always fresh) |
| `timeout` | Not set (waits indefinitely) |
| Fallback coordinates | `-1.2921, 36.8219` (Nairobi city centre) |

### 1.4.3 Authentication & Security

| Requirement | Implementation |
|-------------|---------------|
| Session storage | Database-backed (`ci_sessions` table, `MEDIUMBLOB` data column) |
| Password hashing | `password_hash(PASSWORD_BCRYPT)` |
| CSRF protection | Global CSRF filter, token rotation in every JSON response |
| Route protection | `AuthFilter` (session check) + `RoleFilter` (role-based authorization) |
| Patient privacy | No names or identifiers stored — only age, sex, clinical info |

### 1.4.4 Data Constraints

| Constraint | Limit |
|------------|-------|
| Chief complaint | 100 characters |
| Pre-notify notes | 150 characters |
| Handover notes | 200 characters (admin) / 2000 characters (nurse completion) |
| Hospital contact phone | 50 characters |
| Bay number | 50 characters (alpha_numeric_space) |
| Pagination | 15 records per page (admin lists) |

### 1.4.5 Scalability

- All list views use CI4 `paginate(15)` for server-side pagination (admin lists: users, hospitals, ambulances, handovers, pilots)
- SSE stream is limited to 10 cycles (50 seconds) to prevent thread exhaustion — browser reconnects automatically via EventSource API
- GPS coordinates stored as `decimal(10,8)` for lat and `decimal(11,8)` for lng — provides sub-meter precision
- Composite indexes `handovers(hospital_id, status)` and `ambulances(ems_provider_id, status)` optimize common queries

---

## 1.5 System States & Status Codes

### 1.5.1 ED Status Values

| Status | Meaning | UI Colour | Paramedic Action |
|--------|---------|-----------|------------------|
| `GREEN` | Normal capacity, accepting all | Green | Pre-notification allowed |
| `AMBER` | Limited capacity, evaluate before sending | Orange/Amber | Pre-notification allowed |
| `RED` | Full, not accepting new arrivals | Red | Pre-notification blocked |

Defined in `HospitalController::updateStatus()` validation:
```php
'status' => 'required|in_list[GREEN,AMBER,RED]',
```

### 1.5.2 Handover Status Values

| Status | Meaning | Set By |
|--------|---------|--------|
| `En route` | Ambulance dispatched, not yet arrived | System (on pre-notification submit) |
| `Arrived` | Ambulance at hospital (admin-declared) | Admin via `AdminController::handoverUpdate()` |
| `Acknowledged` | Nurse aware of arrival | HospitalController (planned) |
| `Preparing` | Bay being prepared | HospitalController (planned) |
| `Cleared` | Patient accepted, handover complete | `HospitalController::completeHandover()` |

### 1.5.3 Ambulance Status Values

| Status | Meaning | Set By |
|--------|---------|--------|
| `Available` | Ready for dispatch | System (on handover completion) |
| `Transporting` | En route to hospital | System (on pre-notification submit) |
| `On Scene` | At patient location | Not yet implemented |
| `Queued` | Waiting at hospital >30 min | System (on alert trigger) |
| `Off Duty` | Not available | Admin via CRUD |

### 1.5.4 Alert Types

| Type | Trigger | Threshold |
|------|---------|-----------|
| `Wait Time Exceeded` | Handover wait > 30 minutes | 30 minutes from `arrived_at` or `created_at` |

---

*End of Section 1 — Functional Requirements Specification*