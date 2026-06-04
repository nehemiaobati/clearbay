# API Specification

## ClearBay MVP — RESTful Endpoint Directory

**Version**: 1.0  
**Date**: 2026-06-04

---

## 5.1 Global Configuration & Security Protocol

### 5.1.1 Base Configuration

| Parameter | Value |
|-----------|-------|
| Base URL | Application root (CI4 `base_url()`) |
| Content Type | `application/json` (all API endpoints) |
| Authentication | Session-based (not JWT) |
| CSRF Protection | Token included in EVERY JSON response |
| Error Format | `{"status": "error", "message": "...", "errors": [...], "csrf_token": "..."}` |
| Success Format | `{"status": "success", "message": "...", "result": {...}, "csrf_token": "..."}` |

### 5.1.2 CSRF Token Synchronization Protocol

Every JSON response (success, error, or edge case) MUST include a `csrf_token` field containing the current `csrf_hash()`:

```php
// From HospitalController::getQueue()
return $this->response->setJSON([
    'status'     => 'success',
    'result'     => $data,
    'csrf_token' => csrf_hash()
]);
```

**Frontend Handler**: The frontend JS (`public/js/app.js`) MUST:
1. Extract `csrf_token` from every JSON response payload
2. Update the CSRF header/token for subsequent requests
3. Never rely on cookie-based CSRF synchronization

### 5.1.3 Content Type

All endpoints return `application/json` unless otherwise specified. View-rendering endpoints return HTML.

---

## 5.2 RESTful Endpoint Directory

### 5.2.1 Authentication Endpoints

#### `GET /login`

Renders the login form (SC-01).

| Attribute | Value |
|-----------|-------|
| Route Name | `auth.login` |
| Controller | `AuthController::loginView()` |
| Filter | None (public) |
| Response | HTML view |

#### `POST /login`

Processes login credentials and establishes session.

| Attribute | Value |
|-----------|-------|
| Route Name | `auth.login.submit` |
| Controller | `AuthController::login()` |
| Filter | None (public) |

| Parameter | Type | Required | Validation |
|-----------|------|----------|------------|
| `email` | string | Yes | `required\|valid_email` |
| `password` | string | Yes | `required\|min_length[6]` |

| Response | HTTP Code | Body |
|----------|-----------|------|
| Success | 302 Redirect | Redirect to role-based dashboard |
| Validation Error | 302 Redirect | Back with `errors` flash data |

#### `GET /logout`

Terminates user session.

| Attribute | Value |
|-----------|-------|
| Route Name | `auth.logout` |
| Controller | `AuthController::logout()` |
| Filter | None (public — accessible to all) |
| Response | 302 Redirect to login |

---

### 5.2.2 Hospital Endpoints (role: `nurse`, `hospital_admin`)

All endpoints in this group require `filter: ['auth', 'role:hospital_admin,nurse']`.

#### `GET /hospital/dashboard`

Renders the Emergency Department Dashboard (SC-02).

| Attribute | Value |
|-----------|-------|
| Route Name | `hospital.dashboard` |
| Controller | `HospitalController::dashboard()` |
| Response | HTML view with queue data |
| Edge Cases | If hospital mapping missing → redirects to logout with error |

#### `GET /hospital/queue`

JSON endpoint for AJAX polling of active queue and metrics.

| Attribute | Value |
|-----------|-------|
| Route Name | `hospital.queue` |
| Controller | `HospitalController::getQueue()` |
| Poll Frequency | Every 10 seconds (frontend) |

| Response Field | Type | Description |
|---------------|------|-------------|
| `status` | string | `"success"` or `"error"` |
| `result.queue` | array | Active handover records with ambulance details |
| `result.metrics.avg_wait_today` | int | Average wait time in minutes |
| `result.metrics.baseline_difference` | int | Difference from 60-min baseline |
| `result.metrics.completed_today` | int | Completed handovers today |
| `result.metrics.ambulances_in_queue` | int | Count of arrived/queued ambulances |
| `csrf_token` | string | Fresh CSRF token |

| Error | Status | Message |
|-------|--------|---------|
| Session expired | `"error"` | "Session expired." |

#### `POST /hospital/status`

Updates ED status and bay availability (SC-04 modal).

| Attribute | Value |
|-----------|-------|
| Route Name | `hospital.status.update` |
| Controller | `HospitalController::updateStatus()` |

| Parameter | Type | Required | Validation |
|-----------|------|----------|------------|
| `status` | string | Yes | `required\|in_list[GREEN,AMBER,RED]` |
| `bays_available` | int | Yes | `required\|integer\|greater_than_equal_to[0]` |

**Role Guard**: `nurse` role can only change status color (bay count is locked to hospital default). `hospital_admin` role can change both.

| Response | HTTP Code | Body |
|----------|-----------|------|
| Success | 200 JSON | `{"status":"success","message":"ED status updated successfully.","csrf_token":"..."}` |
| Validation Error | 200 JSON | `{"status":"error","message":"Invalid input parameters.","errors":{...},"csrf_token":"..."}` |
| Session Expired | 200 JSON | `{"status":"error","message":"Session expired.","csrf_token":"..."}` |
| DB Failure | 200 JSON | `{"status":"error","message":"Failed to update capacity status in database.","csrf_token":"..."}` |

#### `POST /hospital/handover`

Completes an active handover (SC-05 modal). Marks status as `Cleared`, releases ambulance.

| Attribute | Value |
|-----------|-------|
| Route Name | `hospital.handover.complete` |
| Controller | `HospitalController::completeHandover()` |

| Parameter | Type | Required | Validation |
|-----------|------|----------|------------|
| `handover_id` | int | Yes | `required\|integer` |
| `bay_number` | string | No | `permit_empty\|alpha_numeric_space\|max_length[50]` |
| `notes` | string | No | `permit_empty\|max_length[2000]` |

| Response | HTTP Code | Body |
|----------|-----------|------|
| Success | 200 JSON | `{"status":"success","message":"Handover completed successfully.","csrf_token":"..."}` |
| Validation Error | 200 JSON | `{"status":"error","message":"Validation error.","errors":{...},"csrf_token":"..."}` |
| Session Expired | 200 JSON | `{"status":"error","message":"Session expired.","csrf_token":"..."}` |
| DB Failure | 200 JSON | `{"status":"error","message":"Failed to finalize patient handover.","csrf_token":"..."}` |

#### `GET /hospital/analytics`

Renders the analytics dashboard (SC-06) with charts and provider performance table.

| Attribute | Value |
|-----------|-------|
| Route Name | `hospital.analytics` |
| Controller | `HospitalController::analytics()` |

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `range` | string | No | `7` | Date range: `7`, `30`, or `90` days |

| Response | HTTP Code | Body |
|----------|-----------|------|
| Success | 200 | HTML view with analytics data |
| Session Expired | 302 | Redirect to logout |

#### `GET /hospital/analytics/export`

Generates CSV export of handover performance data.

| Attribute | Value |
|-----------|-------|
| Route Name | `hospital.analytics.export` |
| Controller | `HospitalController::exportPdf()` |
| Response | `text/csv` file download (30-day range) |

| Response Header | Value |
|----------------|-------|
| `Content-Type` | `text/csv` |
| `Content-Disposition` | `attachment; filename="clearbay_report_[hospital_code].csv"` |

---

### 5.2.3 Ambulance Endpoints (role: `paramedic`)

All endpoints in this group require `filter: ['auth', 'role:paramedic']`.

#### `GET /ambulance`

Renders the paramedic home map and hospital list (SC-07).

| Attribute | Value |
|-----------|-------|
| Route Name | `ambulance.home` |
| Controller | `AmbulanceController::home()` |

| Edge Cases | Behaviour |
|------------|-----------|
| Session invalid | Redirect to logout with error |
| Active run exists | Redirect to SC-11 (Tab State Restorer) |
| GPS unavailable | Fallback to Nairobi centre coordinates |

#### `GET /ambulance/hospital/{id}`

Renders hospital capacity details (SC-08).

| Attribute | Value |
|-----------|-------|
| Route Name | `ambulance.hospital.detail` |
| Controller | `AmbulanceController::detail($id)` |

| Edge Cases | Behaviour |
|------------|-----------|
| Hospital not found | Redirect to SC-07 with error |

#### `GET /ambulance/pre-notify/{id}`

Renders pre-notification form for a specific hospital (SC-09).

| Attribute | Value |
|-----------|-------|
| Route Name | `ambulance.pre_notify` |
| Controller | `AmbulanceController::preNotifyForm($id)` |

| Edge Cases | Behaviour |
|------------|-----------|
| Active run exists | Redirect to SC-07 with error "You already have an active run" |
| Hospital is RED | Redirect back with error "Facility is full" |
| Hospital not found | Redirect to SC-07 with error |

#### `POST /ambulance/pre-notify`

Submits a new pre-notification from paramedic to hospital.

| Attribute | Value |
|-----------|-------|
| Route Name | `ambulance.pre_notify.submit` |
| Controller | `AmbulanceController::sendPreNotification()` |

| Parameter | Type | Required | Validation |
|-----------|------|----------|------------|
| `hospital_id` | int | Yes | `required\|integer` |
| `patient_age` | int | Yes | `required\|integer\|greater_than_equal_to[0]` |
| `patient_sex` | string | Yes | `required\|in_list[Male,Female,Not Specified]` |
| `chief_complaint` | string | Yes | `required\|string\|max_length[100]` |
| `acuity` | string | Yes | `required\|in_list[Critical,Serious,Stable]` |
| `notes` | string | No | `permit_empty\|max_length[150]` |
| `eta_minutes` | int | Yes | `required\|integer\|greater_than_equal_to[0]` |

| Response | HTTP Code | Body |
|----------|-----------|------|
| Success | 200 JSON | `{"status":"success","message":"...","redirect_to":"/ambulance/run/{id}","csrf_token":"..."}` |
| Validation Error | 200 JSON | `{"status":"error","message":"Form validation failed.","errors":{...},"csrf_token":"..."}` |
| Concurrency Lock | 200 JSON | `{"status":"error","message":"You already have an active run.","csrf_token":"..."}` |
| DB Error | 200 JSON | `{"status":"error","message":"Database error dispatching pre-alert.","csrf_token":"..."}` |

#### `GET /ambulance/run/{id}`

Renders or returns JSON for active run status (SC-11).

| Attribute | Value |
|-----------|-------|
| Route Name | `ambulance.active_run` |
| Controller | `AmbulanceController::activeRun($id)` |

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `ajax` | string | No | Set to `1` to return JSON instead of HTML |

| Response (ajax=1) | Fields |
|--------------------|--------|
| `status` | Handover lifecycle status |
| `eta_minutes` | Dynamic ETA in minutes |
| `hospital_name` | Destination hospital name |
| `hospital_status` | ED status (GREEN/AMBER/RED) |
| `bay_preparation` | Boolean: true if status is Preparing/Arrived/Acknowledged |

#### `POST /ambulance/location`

Updates paramedic GPS coordinates and recalculates dynamic ETA.

| Attribute | Value |
|-----------|-------|
| Route Name | `ambulance.location.update` |
| Controller | `AmbulanceController::updateLocation()` |

| Parameter | Type | Required | Validation |
|-----------|------|----------|------------|
| `lat` | float | Yes | `required\|decimal` |
| `lng` | float | Yes | `required\|decimal` |

| Response | HTTP Code | Body |
|----------|-----------|------|
| Success (with active run) | 200 JSON | `{"status":"success","result":{"eta_minutes":int},"csrf_token":"..."}` |
| Success (no active run) | 200 JSON | `{"status":"success","message":"Location synchronized.","csrf_token":"..."}` |
| Validation Error | 200 JSON | `{"status":"error","message":"Invalid coordinate data.","csrf_token":"..."}` |
| DB Error | 200 JSON | `{"status":"error","message":"Failed to save location.","csrf_token":"..."}` |

---

### 5.2.4 Dispatcher Endpoints (role: `dispatcher`)

All endpoints in this group require `filter: ['auth', 'role:dispatcher']`.

#### `GET /dispatcher`

Renders the Dispatcher Command Centre map and panels (SC-12/13/14/15).

| Attribute | Value |
|-----------|-------|
| Route Name | `dispatcher.index` |
| Controller | `DispatcherController::index()` |
| Response | HTML view with Mapbox GL JS map |

#### `GET /dispatcher/fleet-status`

JSON endpoint returning current fleet telemetry, hospital capacities, and alerts.

| Attribute | Value |
|-----------|-------|
| Route Name | `dispatcher.fleet` |
| Controller | `DispatcherController::fleetStatus()` |

| Response Field | Type | Description |
|---------------|------|-------------|
| `result.ambulances` | array | All active ambulances with GPS, status, provider |
| `result.hospitals` | array | All active hospitals with capacity data |
| `result.alerts` | array | Unacknowledged alerts with ambulance/hospital names |
| `result.waits` | object | Wait time data keyed by ambulance_id |
| `csrf_token` | string | Fresh CSRF token |

#### `POST /dispatcher/alerts/{id}/acknowledge`

Marks an alert as acknowledged.

| Attribute | Value |
|-----------|-------|
| Route Name | `dispatcher.alert.acknowledge` |
| Controller | `DispatcherController::acknowledgeAlert($id)` |

| Response | HTTP Code | Body |
|----------|-----------|------|
| Success | 200 JSON | `{"status":"success","message":"Alert acknowledged successfully.","result":{telemetry},"csrf_token":"..."}` |
| Session Expired | 200 JSON | `{"status":"error","message":"Session expired.","csrf_token":"..."}` |
| DB Failure | 200 JSON | `{"status":"error","message":"Failed to acknowledge alert.","csrf_token":"..."}` |

#### `GET /dispatcher/sse-updates`

Server-Sent Events stream for real-time telemetry updates.

| Attribute | Value |
|-----------|-------|
| Route Name | `dispatcher.sse` |
| Controller | `DispatcherController::sseStream()` |
| Content-Type | `text/event-stream` |
| Frequency | Every 5 seconds |
| Max Cycles | 10 (50 seconds total, browser auto-reconnects) |

**Initial Packet**:
```json
data: {"status": "connected", "csrf_token": "..."}
```

**Update Packet**:
```json
data: {"status": "update", "result": {"ambulances": [...], "hospitals": [...], "alerts": [...], "waits": {...}}}
```

**Protocol Requirements**:
- `session_write_close()` called before loop to prevent session locking
- `ob_flush(); flush()` after each packet
- `X-Accel-Buffering: no` header for Nginx compatibility

---

### 5.2.5 Admin Endpoints (role: `admin`)

All endpoints in this group require `filter: ['auth', 'role:admin']`.

#### `GET /admin`

Renders the admin dashboard with summary metrics.

| Attribute | Value |
|-----------|-------|
| Route Name | `admin.dashboard` |
| Controller | `AdminController::dashboard()` |
| Metrics | pilotCount, handoverCount, hospitalCount, ambulanceCount, userCount |

#### Pilot Signups CRUD

| Method | Route | Controller::Method | Route Name |
|--------|-------|-------------------|------------|
| GET | `/admin/pilots` | `AdminController::pilotsList()` | `admin.pilots.list` |
| GET | `/admin/pilots/new` | `AdminController::pilotNew()` | `admin.pilots.new` |
| POST | `/admin/pilots/create` | `AdminController::pilotCreate()` | `admin.pilots.create` |
| GET | `/admin/pilots/edit/{id}` | `AdminController::pilotEdit($id)` | `admin.pilots.edit` |
| POST | `/admin/pilots/update/{id}` | `AdminController::pilotUpdate($id)` | `admin.pilots.update` |
| GET | `/admin/pilots/delete/{id}` | `AdminController::pilotDelete($id)` | `admin.pilots.delete` |

#### Handovers CRUD

| Method | Route | Controller::Method | Route Name |
|--------|-------|-------------------|------------|
| GET | `/admin/handovers` | `AdminController::handoversList()` | `admin.handovers.list` |
| GET | `/admin/handovers/new` | `AdminController::handoverNew()` | `admin.handovers.new` |
| POST | `/admin/handovers/create` | `AdminController::handoverCreate()` | `admin.handovers.create` |
| GET | `/admin/handovers/edit/{id}` | `AdminController::handoverEdit($id)` | `admin.handovers.edit` |
| POST | `/admin/handovers/update/{id}` | `AdminController::handoverUpdate($id)` | `admin.handovers.update` |
| GET | `/admin/handovers/delete/{id}` | `AdminController::handoverDelete($id)` | `admin.handovers.delete` |

#### Hospitals CRUD

| Method | Route | Controller::Method | Route Name |
|--------|-------|-------------------|------------|
| GET | `/admin/hospitals` | `AdminController::hospitalsList()` | `admin.hospitals.list` |
| GET | `/admin/hospitals/new` | `AdminController::hospitalNew()` | `admin.hospitals.new` |
| POST | `/admin/hospitals/create` | `AdminController::hospitalCreate()` | `admin.hospitals.create` |
| GET | `/admin/hospitals/edit/{id}` | `AdminController::hospitalEdit($id)` | `admin.hospitals.edit` |
| POST | `/admin/hospitals/update/{id}` | `AdminController::hospitalUpdate($id)` | `admin.hospitals.update` |
| GET | `/admin/hospitals/delete/{id}` | `AdminController::hospitalDelete($id)` | `admin.hospitals.delete` |

#### Ambulances CRUD

| Method | Route | Controller::Method | Route Name |
|--------|-------|-------------------|------------|
| GET | `/admin/ambulances` | `AdminController::ambulancesList()` | `admin.ambulances.list` |
| GET | `/admin/ambulances/new` | `AdminController::ambulanceNew()` | `admin.ambulances.new` |
| POST | `/admin/ambulances/create` | `AdminController::ambulanceCreate()` | `admin.ambulances.create` |
| GET | `/admin/ambulances/edit/{id}` | `AdminController::ambulanceEdit($id)` | `admin.ambulances.edit` |
| POST | `/admin/ambulances/update/{id}` | `AdminController::ambulanceUpdate($id)` | `admin.ambulances.update` |
| GET | `/admin/ambulances/delete/{id}` | `AdminController::ambulanceDelete($id)` | `admin.ambulances.delete` |

#### Users CRUD

| Method | Route | Controller::Method | Route Name |
|--------|-------|-------------------|------------|
| GET | `/admin/users` | `AdminController::usersList()` | `admin.users.list` |
| GET | `/admin/users/new` | `AdminController::userNew()` | `admin.users.new` |
| POST | `/admin/users/create` | `AdminController::userCreate()` | `admin.users.create` |
| GET | `/admin/users/edit/{id}` | `AdminController::userEdit($id)` | `admin.users.edit` |
| POST | `/admin/users/update/{id}` | `AdminController::userUpdate($id)` | `admin.users.update` |
| GET | `/admin/users/delete/{id}` | `AdminController::userDelete($id)` | `admin.users.delete` |

---

### 5.2.6 Pilot Endpoints (Public)

#### `GET /pilot`

Renders the pilot program signup form.

| Attribute | Value |
|-----------|-------|
| Route Name | `pilot.index` |
| Controller | `PilotController::index()` |
| Filter | None (public) |
| Response | HTML view |

#### `POST /pilot/submit`

Submits a pilot program signup application.

| Attribute | Value |
|-----------|-------|
| Route Name | `pilot.submit` |
| Controller | `PilotController::submit()` |
| Filter | None (public) |
| Response | 302 Redirect with flash message |

---

*End of Section 5 — API Specification*