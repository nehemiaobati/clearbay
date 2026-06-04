# ClearBay MVP — Documentation & User Workflow Generation Prompt

> **Purpose**: This prompt generates comprehensive technical documentation and user workflow guides for the ClearBay MVP platform. It is structured to produce deliverables aligned with Section 7.2 (System Design & Architecture) and Section 7.4 (Technical & User Documentation) templates, mapped against the actual implemented CodeIgniter 4 codebase and the PRD specification.

---

## System Context

### Platform Identity
- **Product**: ClearBay — Real-time ambulance off-load management platform
- **Version**: MVP 1.0
- **Location**: Nairobi County, Kenya
- **Tagline**: *Clear the Bay. Free the Crew. Save the Next Life.*
- **Architecture**: CodeIgniter 4 Modular MVC-S (Model-View-Controller-Service)
- **Database**: MySQL/MariaDB (schema seeded via `clearbayschema.sql`)
- **Maps**: Mapbox GL JS (replaces PRD's Google Maps recommendation)
- **Real-time**: Server-Sent Events (SSE) for dispatcher telemetry; AJAX polling for hospital dashboard
- **Auth**: Session-based with Role-Based Access Control (RBAC) via `AuthFilter` + `RoleFilter`

### Module Map
```
app/Modules/
├── Admin/       → System admin CRUD (users, hospitals, ambulances, handovers, pilots)
├── Ambulance/   → Paramedic mobile-responsive screens (home map, hospital detail, pre-notify, active run)
├── Auth/        → Login/logout with role-based redirect
├── Dispatcher/  → Command centre with SSE telemetry, fleet/alerts/capacity panels
├── Hospital/    → ED dashboard, queue table, status control, handover completion, analytics
├── Pilot/       → Pilot program signup forms
└── Queue/       → Legacy entity aliases (used by Admin module)
```

### User Roles & Access Matrix

| Role | Device | Module | Routes | Dashboard Route |
|------|--------|--------|--------|-----------------|
| `nurse` | Desktop/Tablet | Hospital | `/hospital/*` | `/hospital/dashboard` (SC-02) |
| `hospital_admin` | Desktop | Hospital + Analytics | `/hospital/*` | `/hospital/dashboard` (SC-02) |
| `paramedic` | Mobile (Android) | Ambulance | `/ambulance/*` | `/ambulance` (SC-07) |
| `dispatcher` | Desktop (large screen) | Dispatcher | `/dispatcher/*` | `/dispatcher` (SC-12) |
| `admin` | Desktop | Admin | `/admin/*` | `/admin` (SC-16) |

### Database Schema (9 Tables)

| Table | Key Fields | Purpose |
|-------|-----------|---------|
| `users` | id, name, email, password_hash, role, hospital_id, ems_provider_id, active | All user accounts with role-based access |
| `hospitals` | id, code, name, category, status, bays_available, lat, lng, address, contact_phone, active | Hospital facilities with GPS coordinates |
| `ems_providers` | id, name, type, contact_phone, active | Ambulance service provider registry |
| `ambulances` | id, unit_id, provider, ems_provider_id, registration, current_lat, current_lng, status, last_updated | Fleet vehicles with real-time GPS |
| `hospital_status` | id, hospital_id, status, bays_available, updated_by, updated_at | ED status change audit log |
| `pre_notifications` | id, ambulance_id, hospital_id, paramedic_id, patient_age, patient_sex, chief_complaint, acuity, notes, eta_minutes, sent_at | Paramedic pre-arrival alerts |
| `handovers` | id, pre_notification_id, ambulance_id, hospital_id, patient_age, patient_gender, acuity, eta_minutes, wait_time_minutes, status, arrived_at, bay_number, notes | Core handover lifecycle records |
| `alerts` | id, ambulance_id, hospital_id, alert_type, triggered_at, acknowledged_at, acknowledged_by | Automated 30-minute delay alerts |
| `audit_log` | id, user_id, action, table_name, record_id, timestamp | Security and compliance log |

---

## Prompt: Generate Documentation Deliverables

Using the system context above, generate the following documentation sections. Each section must reference the actual implemented code (file paths, method names, database fields) rather than generic descriptions. All content must be accurate to the current codebase state.

---

### SECTION 1: Functional Requirements Specification (FRS)

#### 1.1 System Introduction & Architectural Scope

Generate a formal system introduction documenting:
- ClearBay's operational scope: real-time ambulance off-load coordination for Nairobi County emergency departments
- The three-module architecture: Hospital ED Dashboard (web), Paramedic Navigator (mobile-responsive web), Dispatcher Command Centre (web with SSE)
- Core objective: reduce ambulance handover wait times by providing pre-arrival visibility, capacity-based routing, and automated delay detection
- The modular CodeIgniter 4 MVC-S architecture with 7 feature modules
- Integration parameters: Mapbox GL JS for mapping, SSE for real-time dispatcher telemetry, AJAX polling for hospital dashboard, session-based RBAC authentication

#### 1.2 System Actors & User Role Matrix

Generate a comprehensive role matrix covering:
- **ED Charge Nurse** (`nurse`): Views SC-02/SC-03/SC-04/SC-05. Can update ED status and complete handovers. Cannot modify bay configuration.
- **Hospital Administrator** (`hospital_admin`): Views SC-02/SC-03/SC-04/SC-05/SC-06. Full ED status control including bay configuration. Analytics dashboard access.
- **Paramedic/EMT** (`paramedic`): Views SC-07/SC-08/SC-09/SC-11. Map-based hospital selection, pre-notification dispatch, active GPS tracking.
- **EMS Dispatcher** (`dispatcher`): Views SC-12/SC-13/SC-14/SC-15. Fleet telemetry via SSE, automated 30-minute alerts, hospital capacity monitoring.
- **System Admin** (`admin`): Views SC-16 plus full CRUD for users, hospitals, ambulances, handovers. Admin-only arrival declaration.

For each role, specify: CRUD permissions per entity, route access, screen access, and data visibility boundaries.

#### 1.3 Functional Requirements & Modular Workflows

Generate step-by-step process flows for each core workflow:

1. **Pre-Notification Dispatch Workflow** (Paramedic → Hospital)
   - SC-07: Paramedic opens home map → early GPS acquisition → Mapbox Matrix API sorts hospitals by driving distance
   - SC-08: Taps hospital → views capacity details → taps "Send Pre-Notification"
   - SC-09: Fills patient age, sex, chief complaint, acuity → auto-calculated ETA → submits
   - System: Creates `pre_notification` + `handover` records in transaction, sets ambulance status to "Transporting"
   - SC-11: Active run screen with GPS watchPosition telemetry, ETA countdown, status polling

2. **Handover Completion Workflow** (Nurse → Paramedic)
   - SC-02/SC-03: Nurse sees queue table with auto-refreshing ambulance list
   - SC-05: Nurse clicks "Clear Bay" → fills bay number + notes → confirms
   - System: Sets `handover.status = 'Cleared'`, records `handover_complete_at`, calculates `wait_time_minutes`, releases ambulance to "Available"
   - SC-11: Paramedic sees "Handover Confirmed" → GPS tracking stops → "New Run" button appears

3. **30-Minute Alert Workflow** (System → Dispatcher)
   - `DispatcherService::_checkAndTriggerAlerts()` runs on each telemetry request
   - Calculates wait time from `arrived_at` (or `created_at`)
   - If >30 minutes: creates `alert` record, sets ambulance status to "Queued", logs to `audit_log`
   - SC-14: Dispatcher sees alert with unit ID, hospital, duration → clicks "Acknowledge"

4. **Admin Arrival Declaration Workflow** (Admin → System)
   - SC-16: Admin edits handover status from "En route" to "Arrived"
   - System: Sets `handover.arrived_at = current_timestamp` inside transaction
   - This is the ONLY pathway to set `arrived_at` (paramedics and nurses cannot mark as arrived)

#### 1.4 Non-Functional Requirements & Performance SLA

Document performance metrics:
- Dashboard refresh: AJAX polling every 10 seconds (hospital), SSE streaming every 5 seconds (dispatcher)
- GPS update frequency: `watchPosition()` with `enableHighAccuracy: true` during active runs
- Authentication: Session-based with `role` filter on all route groups
- CSRF: Token rotation in every JSON response via `csrf_hash()`
- Data encryption: Password hashing via `password_hash(PASSWORD_BCRYPT)`
- Patient data: No names or IDs stored — only age, sex, clinical information
- Scalability: Pagination on all list views (`paginate(15)`)

---

### SECTION 2: Technical Design Document (TDD)

#### 2.1 System Architecture Overview

Document the production architecture:
- **Presentation Tier**: Bootstrap 5 responsive views extending `layouts/default`, Blueprint Method (container → header → card)
- **Application Middleware**: `AuthFilter` (session check) + `RoleFilter` (role authorization) + CSRF (global)
- **Service Layer**: `AmbulanceService`, `HospitalService`, `DispatcherService`, `AdminService`, `AuthService`
- **Persistence**: CodeIgniter 4 Query Builder + Entity pattern (`$returnType = Entity::class`)
- **Real-time**: SSE in `DispatcherController::sseStream()` with `session_write_close()` and `ob_flush()`
- **External APIs**: Mapbox Matrix API (client-side), Mapbox GL JS (mapping)

#### 2.2 Database Schema & Entity-Relationship Design

Generate an ERD description covering:
- 1:N relationships: `ems_providers` → `ambulances`, `hospitals` → `hospital_status`, `hospitals` → `handovers`
- 1:1 relationships: `pre_notifications` → `handovers` (via `pre_notification_id`)
- Composite indexes: `handovers(hospital_id, status)`, `ambulances(ems_provider_id, status)`
- Foreign key structure and cascade rules
- Entity classes with `$casts` for type enforcement

#### 2.3 Multi-Tier Security Model

Document security implementation:
- **Authentication**: Session-based (`is_logged_in` flag) with `AuthFilter` on all protected routes
- **Authorization**: `RoleFilter` with parameterized role checking (`role:admin`, `role:hospital_admin,nurse`)
- **CSRF Protection**: Global `csrf` filter, token rotation in every JSON response, frontend token update from response payload
- **Password Security**: `password_hash(PASSWORD_BCRYPT)` storage, temporary passwords for new accounts
- **Data Escaping**: `esc()` on all dynamic view output
- **Input Validation**: CI4 Validation library on every POST endpoint
- **Throttling**: Available but not currently active on auth routes (planned)

#### 2.4 Geospatial & GIS Engine Integration

Document the mapping implementation:
- **Mapbox GL JS v3.3.0** for both paramedic and dispatcher maps
- **Mapbox Matrix API** for driving distance/duration calculation (client-side fetch)
- **Early GPS Acquisition**: `navigator.geolocation.getCurrentPosition()` on page load for live coordinates
- **Active GPS Tracking**: `navigator.geolocation.watchPosition()` during active runs with telemetry POST to `/ambulance/location`
- **Haversine Formula**: Server-side fallback for ETA calculation when GPS unavailable
- **Coordinate Format**: `decimal(10,8)` for lat, `decimal(11,8)` for lng

---

### SECTION 3: Prototype Interfaces Plan

#### 3.1 UX/UI Standards & Branding Guidelines

Document the design system:
- **Framework**: Bootstrap 5 with Blueprint Method
- **Layout Structure**: `<div class="container my-5">` → `<div class="blueprint-header">` → `<div class="card blueprint-card">`
- **Color Palette**: Theme-aware CSS variables (`--sage`, `--sage-l`, `--red`, `--amber`) — no hardcoded colors
- **Typography**: Monospaced labels (`mono-label`), sans-serif body, stat values (`admin-stat-val`)
- **Responsive**: Mobile-first for paramedic (`container-fluid`), desktop for hospital/dispatcher (`container`)
- **Accessibility**: `aria-label`, `aria-hidden`, `role` attributes, `min-height: 48px` touch targets, `focus-ring` classes

#### 3.2 Core Wireframe Directory

Map the 16 screens to their implemented views:

| Screen | View File | Module | Layout |
|--------|-----------|--------|--------|
| SC-01 | `Auth/Views/login.php` | Auth | Centered card |
| SC-02 | `Hospital/Views/dashboard.php` | Hospital | 4-zone layout (nav, banner, metrics, queue) |
| SC-03 | (Integrated in SC-02) | Hospital | Table within dashboard |
| SC-04 | (Modal in SC-02) | Hospital | Bootstrap modal with status buttons |
| SC-05 | (Modal in SC-02) | Hospital | Bootstrap modal with form fields |
| SC-06 | `Hospital/Views/analytics.php` | Hospital | Charts + provider table + CSV export |
| SC-07 | `Ambulance/Views/home.php` | Ambulance | Split layout: map (2/3) + hospital list (1/3) |
| SC-08 | `Ambulance/Views/detail.php` | Ambulance | Card with hospital details |
| SC-09 | `Ambulance/Views/pre_notify.php` | Ambulance | Form with patient fields + acuity buttons |
| SC-10 | (Skipped — redirects to SC-11) | — | — |
| SC-11 | `Ambulance/Views/active_run.php` | Ambulance | Centered card with countdown + status |
| SC-12 | `Dispatcher/Views/map.php` | Dispatcher | Split layout: map (2/3) + sidebar panels (1/3) |
| SC-13 | (Panel in SC-12) | Dispatcher | Scrollable fleet list in sidebar |
| SC-14 | (Panel in SC-12) | Dispatcher | Alert cards with acknowledge buttons |
| SC-15 | (Panel in SC-12) | Dispatcher | Hospital capacity list in sidebar |
| SC-16 | `Admin/Views/users/edit.php` | Admin | Form with role-based conditional fields |

---

### SECTION 4: Data Dictionary & Metadata Standards

#### 4.1 Database Entity & Field Definitions

For each table, generate a field-by-field definition including:
- Field name, data type, constraints, default value, nullable status
- Semantic description of what the field represents
- Index status (primary key, foreign key, composite index)
- Entity class property with PHPDoc type and `$casts` mapping

Cover all 9 tables with every column documented.

#### 4.2 System Metadata & Auditing Standards

Document:
- `created_at` / `updated_at` fields on all operational entities (managed by CI4 `$createdField` / `$updatedField`)
- `audit_log` table structure and when records are inserted (automated alert generation)
- `hospital_status` table as a historical log of ED status changes
- Soft delete policy: users are deactivated (`active = 0`), not hard-deleted

---

### SECTION 5: API Specification

#### 5.1 Global Configuration & Security Protocol

Document the API layer:
- **Base URL**: Application served from CI4 public directory
- **Authentication**: Session-based (not JWT — adapted from PRD recommendation)
- **CSRF**: Token included in every JSON response, rotated per request
- **Content Type**: `application/json` for all API endpoints
- **Error Format**: `{"status": "error", "message": "...", "errors": [...], "csrf_token": "..."}`

#### 5.2 RESTful Endpoint Directory

Document all implemented endpoints:

| Method | Route | Controller::Method | Purpose |
|--------|-------|-------------------|---------|
| GET | `/login` | `AuthController::loginView` | Render login form |
| POST | `/login` | `AuthController::login` | Process authentication |
| GET | `/logout` | `AuthController::logout` | Destroy session |
| GET | `/hospital/dashboard` | `HospitalController::dashboard` | SC-02 dashboard |
| GET | `/hospital/queue` | `HospitalController::getQueue` | AJAX queue data |
| POST | `/hospital/status` | `HospitalController::updateStatus` | Update ED status |
| POST | `/hospital/handover` | `HospitalController::completeHandover` | Complete handover |
| GET | `/hospital/analytics` | `HospitalController::analytics` | SC-06 analytics |
| GET | `/hospital/analytics/export` | `HospitalController::exportPdf` | CSV export |
| GET | `/ambulance` | `AmbulanceController::home` | SC-07 home map |
| GET | `/ambulance/hospital/(:num)` | `AmbulanceController::detail` | SC-08 hospital detail |
| GET | `/ambulance/pre-notify/(:num)` | `AmbulanceController::preNotifyForm` | SC-09 pre-notify form |
| POST | `/ambulance/pre-notify` | `AmbulanceController::sendPreNotification` | Submit pre-notification |
| GET | `/ambulance/run/(:num)` | `AmbulanceController::activeRun` | SC-11 active run |
| POST | `/ambulance/location` | `AmbulanceController::updateLocation` | GPS telemetry update |
| GET | `/dispatcher` | `DispatcherController::index` | SC-12 dispatcher map |
| GET | `/dispatcher/fleet-status` | `DispatcherController::fleetStatus` | Fleet JSON |
| POST | `/dispatcher/alerts/(:num)/acknowledge` | `DispatcherController::acknowledgeAlert` | Acknowledge alert |
| GET | `/dispatcher/sse-updates` | `DispatcherController::sseStream` | SSE telemetry stream |

For each endpoint, document: HTTP method, URL pattern, request parameters, response schema, error codes, and which filter is applied.

---

### SECTION 6: User Manuals & Quick Guides

#### 6.1 Role-Based Navigational Pathways

**Generate a step-by-step user guide for each role:**

##### ED Charge Nurse Workflow
1. Navigate to hospital login page → enter credentials → system redirects to SC-02
2. Dashboard loads showing: ED status banner (GREEN/AMBER/RED), 4 metric cards, ambulance queue table
3. Queue table auto-refreshes every 10 seconds — new pre-notifications appear automatically
4. To update status: click the status banner → modal opens (SC-04) → select GREEN/AMBER/RED → enter available bays → click "Update Status"
5. To complete handover: find arrived ambulance in queue → click "Clear Bay" → modal opens (SC-05) → enter bay number (optional) → add notes (optional) → click "Confirm Handover Complete"
6. Handover completes: ambulance row disappears from queue, paramedic sees "Cleared" on their screen, ambulance status returns to "Available" on dispatcher map
7. To view analytics: click "View Analytics" → select date range (7/30/90 days) → view charts and provider table → click "Export" for CSV

##### Paramedic Workflow
1. Navigate to ambulance login → enter credentials → system redirects to SC-07 (home map)
2. GPS acquires immediately — hospital list sorts by driving distance via Mapbox Matrix API
3. Map shows hospitals as colour-coded pins (green/amber/red). Hospital list shows name, distance, ETA
4. Tap a hospital card → navigates to SC-08 (detail view) showing capacity, bays, queue length
5. If hospital is GREEN or AMBER: tap "Send Pre-Notification" → SC-09 form opens
6. Fill patient age, select sex, choose chief complaint, select acuity level → tap "Send Pre-Notification"
7. System creates records → screen transitions to SC-11 (active run)
8. SC-11 shows: live ETA countdown, hospital name, bay preparation status
9. GPS telemetry POSTs coordinates every 5 seconds, server recalculates ETA dynamically
10. When hospital clears handover → screen shows "Handover Confirmed" → GPS tracking stops → "New Run" button appears
11. If page is reloaded during active run → system detects active handover → auto-redirects back to SC-11

##### EMS Dispatcher Workflow
1. Navigate to dispatcher login → enter credentials → system redirects to SC-12
2. Map loads showing all ambulances as coloured dots, all hospitals as square markers
3. Right sidebar shows: Active Alerts (SC-14), Fleet Status (SC-13), Hospital Capacities (SC-15)
4. SSE connection streams live updates every 5 seconds — map markers move, fleet status updates
5. Alert triggers automatically when any ambulance exceeds 30-minute wait threshold
6. Click "Ack" on alert → alert marked as acknowledged (remains visible until ambulance is cleared)
7. Use search box (top-right) to find specific unit ID on map
8. Click any ambulance dot → map flies to location and shows popup with details

##### System Admin Workflow
1. Navigate to admin login → enter credentials → system redirects to SC-16
2. Dashboard shows metrics: pilot signups, handovers, hospitals, ambulances, users
3. Navigate to "Manage Users" → list view with pagination → create/edit/deactivate accounts
4. Navigate to "Manage Hospitals" → edit facility with full fields (code, name, category, status, bays, lat/lng, address, phone, active toggle)
5. Navigate to "Manage Ambulances" → edit vehicle with unit ID, provider, EMS provider dropdown, registration, status, GPS coordinates
6. Navigate to "Manage Handovers" → view/edit handover records including bay number and notes
7. To declare arrival: edit handover status from "En route" to "Arrived" → system records `arrived_at` timestamp

---

### SECTION 7: System Administration Guide

#### 7.1 Production Server Setup & Environment Configurations

Document deployment steps:
1. `composer install --no-dev --optimize-autoloader`
2. Configure `.env` with database credentials, Mapbox access token, base URL
3. `php spark migrate --all`
4. `php spark db:seed MainSeeder`
5. `php spark optimize`
6. Point document root to `/public` directory
7. Ensure `writable/` directory is web-user writable
8. Set `CI_ENVIRONMENT = production` in `.env`

#### 7.2 Operational Maintenance Runbooks

Document:
- Database backup: `php spark db:backup` (uses mysqldump wrapper)
- Database restore: `php spark db:restore` (interactive selection from `writable/backups/`)
- Log location: `writable/logs/` (auto-rotated daily)
- Session storage: `ci_sessions` table with `MEDIUMBLOB` data column
- Migration management: `php spark migrate --all` for schema updates

---

### SECTION 8: Data Migration Manual

#### 8.1 Schema Overview

Document the `clearbayschema.sql` seed file structure:
- Full table definitions with indexes
- Seed data: 5 hospitals (KNH, MLK, MBG, AKU, Nairobi Hospital) with GPS coordinates
- Seed data: 6 ambulances across 3 EMS providers (AAR, Kenya Red Cross, Nairobi County)
- Seed data: 7 users covering all 5 roles (nurse×2, hospital_admin, paramedic, dispatcher, admin)
- Session data included (can be truncated in production)

---

### SECTION 9: Deployment Guide

#### 9.1 Production Environment Rollout

Step-by-step deployment:
1. Clone repository to production server
2. Run `composer install --no-dev --optimize-autoloader`
3. Copy `env` to `.env` and configure production values
4. Run `php spark migrate --all`
5. Run `php spark db:seed MainSeeder` (if fresh database)
6. Run `php spark optimize`
7. Set document root to `/public`
8. Verify `writable/` permissions (755 or 775)
9. Test login for all 5 roles
10. Verify SSE stream on dispatcher page
11. Verify GPS telemetry on paramedic active run

#### 9.2 Disaster Recovery

Document:
- Database backup frequency: daily via `php spark db:backup`
- Backup location: `writable/backups/`
- Restore procedure: `php spark db:restore` → select backup file
- Session recovery: Database-backed sessions survive server restarts

---

### SECTION 10: Training Curriculum

#### 10.1 Technical Course Modules

Outline training modules:
- **Module 1**: System Overview (30 min) — What ClearBay does, who uses it, why it matters
- **Module 2**: Nurse Dashboard (45 min) — Hands-on: login, view queue, update status, complete handover
- **Module 3**: Paramedic Navigator (45 min) — Hands-on: login, find hospital, send pre-notification, active run
- **Module 4**: Dispatcher Command Centre (30 min) — Hands-on: view fleet, acknowledge alerts, search units
- **Module 5**: Admin Management (30 min) — Hands-on: create users, manage hospitals/ambulances

For each module, specify: target audience, learning objectives, hands-on exercises, and assessment criteria.

---

## Output Format Requirements

1. Generate all documentation as Markdown (`.md`) files
2. Use consistent heading hierarchy (H1 → H2 → H3)
3. Include file path references to actual code (e.g., `app/Modules/Hospital/Controllers/HospitalController.php`)
4. Include database field references (e.g., `handovers.status`, `ambulances.current_lat`)
5. Include route references (e.g., `/hospital/dashboard` → `HospitalController::dashboard`)
6. Tables must use Markdown table syntax
7. Code blocks must use appropriate language tags (php, javascript, sql)
8. Each section must be self-contained but cross-reference related sections