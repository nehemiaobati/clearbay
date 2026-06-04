# Technical Design Document (TDD)

## ClearBay MVP — System Architecture & Implementation

**Version**: 1.0  
**Date**: 2026-06-04

---

## 2.1 System Architecture Overview

### 2.1.1 Presentation Tier

- **Framework**: Bootstrap 5 (utility-first, dark theme via `data-bs-theme="dark"`)
- **Layout Structure**: All operational views extend `app/Views/layouts/default.php`
- **Blueprint Method**: `<div class="container my-5">` → `<div class="blueprint-header">` → `<div class="card blueprint-card">`
- **CSS Variables**: Theme-aware styling via `--sage`, `--sage-l`, `--red`, `--amber`
- **Map Integration**: Mapbox GL JS v3.3.0 in paramedic home map and dispatcher command centre

### 2.1.2 Application Middleware

| Middleware | Class | Purpose |
|-----------|-------|---------|
| CSRF Protection | `CodeIgniter\Filters\CSRF` | Global filter on all POST/PUT/DELETE requests |
| AuthFilter | `app/Filters/AuthFilter.php` | Session validation → redirects to login if not authenticated |
| RoleFilter | `app/Filters/RoleFilter.php` | Role-based authorization → 403 redirect if wrong role |
| ForceHTTPS | `CodeIgniter\Filters\ForceHTTPS` | Enforce HTTPS in production |
| PageCache | `CodeIgniter\Filters\PageCache` | Web page caching |
| DebugToolbar | `CodeIgniter\Filters\DebugToolbar` | Development-only CI4 debug toolbar |

Defined in: `app/Config/Filters.php`

### 2.1.3 Service Layer

| Service | Location | Responsibilities |
|---------|----------|------------------|
| `HospitalService` | `app/Modules/Hospital/Libraries/HospitalService.php` | ED status updates, queue data, handover completion, analytics |
| `AmbulanceService` | `app/Modules/Ambulance/Libraries/AmbulanceService.php` | GPS tracking, ETA calculation, pre-notification dispatch, active run status |
| `DispatcherService` | `app/Modules/Dispatcher/Libraries/DispatcherService.php` | Fleet telemetry compilation, 30-min alert detection, alert acknowledgment |
| `AdminService` | `app/Modules/Admin/Libraries/AdminService.php` | Dashboard metrics, CRUD operations for all entities |
| `AuthService` | `app/Modules/Auth/Libraries/AuthService.php` | Login verification, session management, user lookup |

### 2.1.4 Persistence Layer

- **ORM**: CodeIgniter 4 Query Builder with Entity pattern
- **Model Configuration**: All models set `$returnType = Entity::class`
- **Migration Strategy**: Incremental update migrations (5 migration batches in `migrations` table)

### 2.1.5 Real-Time Communication

| Channel | Technology | Endpoint | Frequency |
|---------|-----------|----------|-----------|
| Dispatcher Telemetry | Server-Sent Events (SSE) | `GET /dispatcher/sse-updates` | 5 seconds |
| Hospital Queue | AJAX Polling | `GET /hospital/queue` | 10 seconds |
| GPS Location | HTTP POST | `POST /ambulance/location` | Per `watchPosition` callback (~5s) |
| Active Run Status | AJAX Polling | `GET /ambulance/run/{id}?ajax=1` | On demand |

### 2.1.6 External API Integrations

| API | Type | Usage |
|-----|------|-------|
| Mapbox GL JS | Client-side library | Interactive map rendering, marker management |
| Mapbox Matrix API | Client-side fetch | Planned for driving distance/duration (not implemented server-side) |
| Geolocation API | Browser API | Paramedic GPS coordinate acquisition |

---

## 2.2 Database Schema & Entity-Relationship Design

### 2.2.1 Entity-Relationship Summary

```
ems_providers 1──N ambulances
               │        │
               │        ├──N pre_notifications
               │        │        │
               │        │        └──1 handovers (via pre_notification_id)
               │        │
               │        ├──N handovers (via ambulance_id)
               │        │
               │        └──N alerts
               │
hospitals 1──N hospital_status
               │
               ├──N handovers (via hospital_id)
               │
               ├──N pre_notifications (via hospital_id)
               │
               └──N alerts (via hospital_id)

users 1──N handovers (via completed_by)
users 1──N pre_notifications (via paramedic_id)
```

### 2.2.2 Table Relationships

| Parent | Child | Relationship | Foreign Key |
|--------|-------|-------------|-------------|
| `ems_providers` | `ambulances` | 1:N | `ambulances.ems_provider_id` → `ems_providers.id` |
| `ambulances` | `pre_notifications` | 1:N | `pre_notifications.ambulance_id` → `ambulances.id` |
| `ambulances` | `handovers` | 1:N | `handovers.ambulance_id` → `ambulances.id` |
| `ambulances` | `alerts` | 1:N | `alerts.ambulance_id` → `ambulances.id` |
| `hospitals` | `handovers` | 1:N | `handovers.hospital_id` → `hospitals.id` |
| `hospitals` | `pre_notifications` | 1:N | `pre_notifications.hospital_id` → `hospitals.id` |
| `hospitals` | `alerts` | 1:N | `alerts.hospital_id` → `hospitals.id` |
| `hospitals` | `hospital_status` | 1:N | `hospital_status.hospital_id` → `hospitals.id` |
| `pre_notifications` | `handovers` | 1:1 | `handovers.pre_notification_id` → `pre_notifications.id` |
| `users` | `handovers` | 1:N | `handovers.completed_by` → `users.id` |
| `users` | `hospital_status` | 1:N | `hospital_status.updated_by` → `users.id` |
| `users` | `alerts` | 1:N | `alerts.acknowledged_by` → `users.id` |
| `users` | `pre_notifications` | 1:N | `pre_notifications.paramedic_id` → `users.id` |

### 2.2.3 Composite Indexes

Defined in `clearbayschema.sql`:

```sql
-- Performance-optimized composite indexes
ADD KEY `ambulances_provider_status` (`ems_provider_id`,`status`);
ADD KEY `handovers_hosp_status` (`hospital_id`,`status`);
```

Additional single-column indexes on:
- `alerts`: `ambulance_id`, `hospital_id`, `triggered_at`
- `ambulances`: `unit_id`
- `audit_log`: `user_id`, `table_name`, `timestamp`
- `ems_providers`: `active`
- `handovers`: `ambulance_id`, `hospital_id`, `status`, `created_at`
- `hospitals`: `code`
- `hospital_status`: `hospital_id`, `status`
- `pilot_signups`: `email_address`, `created_at`
- `pre_notifications`: `ambulance_id`, `hospital_id`, `paramedic_id`, `status`, `sent_at`
- `users`: `email`, `role`, `active`

### 2.2.4 Entity Classes with Casts

**Handover** (`app/Modules/Hospital/Entities/Handover.php`):
```php
protected $dates = ['arrived_at', 'handover_complete_at', 'created_at', 'updated_at'];
protected $casts = [
    'id' => 'integer', 'pre_notification_id' => 'integer',
    'ambulance_id' => 'integer', 'hospital_id' => 'integer',
    'patient_age' => 'integer', 'patient_gender' => 'string',
    'acuity' => 'string', 'eta_minutes' => 'integer',
    'wait_time_minutes' => 'integer', 'status' => 'string',
    'bay_number' => 'string', 'notes' => 'string', 'completed_by' => 'integer',
];
```

**Hospital** (`app/Modules/Hospital/Entities/Hospital.php`):
```php
protected $dates = ['created_at', 'updated_at'];
protected $casts = [
    'id' => 'integer', 'code' => 'string', 'name' => 'string',
    'category' => 'string', 'status' => 'string',
    'bays_available' => 'integer', 'lat' => 'float', 'lng' => 'float',
    'address' => 'string', 'contact_phone' => 'string', 'active' => 'integer',
];
```

**Ambulance** (`app/Modules/Ambulance/Entities/Ambulance.php`):
```php
protected $dates = ['last_updated', 'created_at', 'updated_at'];
protected $casts = [
    'id' => 'integer', 'ems_provider_id' => 'integer',
    'unit_id' => 'string', 'registration' => 'string',
    'current_lat' => 'float', 'current_lng' => 'float', 'status' => 'string',
];
```

**User** (`app/Modules/Auth/Entities/User.php`):
```php
protected $dates = ['created_at', 'updated_at'];
protected $casts = [
    'id' => 'integer', 'name' => 'string', 'email' => 'string',
    'password_hash' => 'string', 'role' => 'string',
    'hospital_id' => 'integer', 'ems_provider_id' => 'integer', 'active' => 'integer',
];
```

---

## 2.3 Multi-Tier Security Model

### 2.3.1 Authentication Layer

| Component | Implementation |
|-----------|---------------|
| Session Identifier | `is_logged_in` boolean flag stored in session |
| Session Storage | Database-backed (`ci_sessions` table, `MEDIUMBLOB` data column) |
| Auth Mechanism | `AuthService::login()` validates email + bcrypt password |
| Session Persistence | CI4 `session()` service with database handler |

### 2.3.2 Authorization Layer

**AuthFilter** (`app/Filters/AuthFilter.php`):
- Applied to route groups: `hospital`, `ambulance`, `dispatcher`, `admin`
- Checks `session()->get('is_logged_in')`
- If missing: stores `current_url()` as `redirect_url`, redirects to login

**RoleFilter** (`app/Filters/RoleFilter.php`):
- Parameterized: `role:hospital_admin,nurse`, `role:paramedic`, etc.
- Compares `session()->get('user_role')` against allowed roles
- On mismatch: sets flash error "You do not have permission to access this resource", redirects to login

Configured in `app/Config/Filters.php`:
```php
public array $aliases = [
    'auth' => \App\Filters\AuthFilter::class,
    'role' => \App\Filters\RoleFilter::class,
];
```

Route group application (example from `app/Modules/Hospital/Config/Routes.php`):
```php
$routes->group('hospital', [
    'namespace' => 'App\Modules\Hospital\Controllers',
    'filter'    => ['auth', 'role:hospital_admin,nurse']
], static function ($routes) { ... });
```

### 2.3.3 CSRF Protection

- **Global**: `csrf` filter in `app/Config/Filters.php` globals `before` array
- **Form Usage**: `csrf_field()` in all HTML POST forms
- **JSON Responses**: Every JSON response (success, error, edge case) includes `csrf_token`
- **Token Rotation**: Fresh `csrf_hash()` generated per request
- **Frontend Synchronization**: JS extracts `csrf_token` from response payload and updates the header for subsequent requests

### 2.3.4 Password Security

```php
// User creation (AdminController::userCreate)
$user->password_hash = password_hash('12345678', PASSWORD_BCRYPT);

// Password reset (AdminController::userUpdate)
if ($this->request->getPost('reset_password')) {
    $user->password_hash = password_hash('12345678', PASSWORD_BCRYPT);
}
```

### 2.3.5 Data Escaping

- All dynamic output in views uses `esc($var)` for HTML escaping
- Example (from `app/Views/layouts/default.php`):
  ```php
  <meta name="description" content="<?= (string) esc($meta_description) ?>">
  <title><?= (string) esc($page_title) ?></title>
  ```

### 2.3.6 Input Validation

- All POST endpoints use CI4 Validation library
- Validation rules defined inline in controller methods
- Pattern: define `$rules` array → `$this->validate($rules)` → check → process
- Error messages returned as JSON `errors` array

### 2.3.7 Throttling

- Throttler is available via `CodeIgniter\Throttle\Throttler` but not currently active on any route
- Planned for: auth routes, pre-notification submission, resource-heavy endpoints

---

## 2.4 Geospatial & GIS Engine Integration

### 2.4.1 Mapping Stack

| Component | Version | Usage |
|-----------|---------|-------|
| Mapbox GL JS | 3.3.0 | Paramedic home map, dispatcher command centre map |
| Mapbox Token | Configured via `.env` `mapboxgl.accessToken` | Authenticated map tile loading |
| HTML Sanitizer | `@mapbox/mapbox-gl-geocoder` dependency | Input sanitization for geocoding (not used) |

### 2.4.2 Hospital Pin Rendering

Colour-coded hospital markers based on `hospitals.status`:
- `GREEN` → green pin
- `AMBER` → orange pin
- `RED` → red pin

Implemented client-side in:
- `app/Modules/Ambulance/Views/home.php` (SC-07)
- `app/Modules/Dispatcher/Views/map.php` (SC-12)

### 2.4.3 GPS Coordinate Acquisition

**Early GPS Acquisition** (SC-07 Home Map):
```javascript
navigator.geolocation.getCurrentPosition(function(pos) {
    // Coordinates available immediately on page load
    currentLat = pos.coords.latitude;
    currentLng = pos.coords.longitude;
});
```

**Active GPS Tracking** (SC-11 Active Run):
```javascript
watchId = navigator.geolocation.watchPosition(function(pos) {
    fetch('/ambulance/location', {
        method: 'POST',
        body: new URLSearchParams({
            lat: pos.coords.latitude,
            lng: pos.coords.longitude
        })
    });
});
```

### 2.4.4 ETA Calculation (Haversine Formula)

**Server-side fallback** (`AmbulanceService::calculateEta()`):
```php
private function _haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
{
    $earth_radius = 6371; // Kilometers
    $d_lat = deg2rad($lat2 - $lat1);
    $d_lon = deg2rad($lon2 - $lon1);
    $a = sin($d_lat / 2) * sin($d_lat / 2) +
        cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
        sin($d_lon / 2) * sin($d_lon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return round($earth_radius * $c, 1);
}

public function calculateEta(float $lat1, float $lon1, float $lat2, float $lon2): int
{
    $distance = $this->_haversineDistance($lat1, $lon1, $lat2, $lon2);
    return (int) round($distance * 2.5 + 2); // Traffic multiplier model
}
```

### 2.4.5 Dynamic ETA Recalculation

During active runs, `AmbulanceService::updateLocation()` recalculates ETA on each GPS update:
```php
$eta = $this->calculateEta($lat, $lng, $hospital_lat, $hospital_lng);
// Updates both pre_notifications.eta_minutes and handovers.eta_minutes
```

Coordinate Format: `decimal(10,8)` for latitude, `decimal(11,8)` for longitude

---

## 2.5 Module Architecture

### 2.5.1 Module Directory Structure

Each module follows the standard MVC-S pattern:

```
app/Modules/[Name]/
├── Config/          # Routes.php (manual registration required)
├── Controllers/     # HTTP orchestration only
├── Database/
│   ├── Migrations/  # Schema changes
│   └── Seeds/       # Test/reference data
├── Entities/        # Business objects with $casts
├── Libraries/       # Services (business logic)
├── Models/          # DB interaction
└── Views/           # Presentation
```

### 2.5.2 Module Dependency Graph

```
Auth (no dependencies)
  ├── Admin (depends on: Auth entities, Hospital entities, Ambulance entities, Pilot entities)
  ├── Hospital (depends on: Auth for user session)
  ├── Ambulance (depends on: Hospital entities, Auth entities)
  ├── Dispatcher (depends on: Ambulance models, Hospital models)
  └── Pilot (no dependencies)
Queue (legacy — used by Admin module for entity aliases)
```

### 2.5.3 Module Registration

Each module namespace registered in `app/Config/Autoload.php`:
```php
public $psr4 = [
    APP_NAMESPACE => APPPATH,
    'App\Modules\Admin'       => APPPATH . 'Modules/Admin',
    'App\Modules\Ambulance'   => APPPATH . 'Modules/Ambulance',
    'App\Modules\Auth'        => APPPATH . 'Modules/Auth',
    'App\Modules\Dispatcher'  => APPPATH . 'Modules/Dispatcher',
    'App\Modules\Hospital'    => APPPATH . 'Modules/Hospital',
    'App\Modules\Pilot'       => APPPATH . 'Modules/Pilot',
    'App\Modules\Queue'       => APPPATH . 'Modules/Queue',
];
```

---

*End of Section 2 — Technical Design Document*