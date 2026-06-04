# Prototype Interfaces Plan

## ClearBay MVP — UI/UX Standards & Screen Map

**Version**: 1.0  
**Date**: 2026-06-04

---

## 3.1 UX/UI Standards & Branding Guidelines

### 3.1.1 Design Framework

| Component | Standard |
|-----------|----------|
| CSS Framework | Bootstrap 5 (utility-first, dark theme enabled) |
| Layout Method | Blueprint Method: Container → Header → Card |
| Base Layout File | `app/Views/layouts/default.php` |
| Theme Mode | Dark (`data-bs-theme="dark"` on `<html>` element) |

### 3.1.2 Layout Structure

Every page follows this structural hierarchy:

```html
<div class="container my-5">          <!-- Outer wrapper -->
    <div class="blueprint-header">     <!-- Page header (title, breadcrumbs) -->
        <h1>Page Title</h1>
    </div>
    <div class="card blueprint-card">   <!-- Main content card -->
        <!-- Content -->
    </div>
</div>
```

### 3.1.3 Color Palette

The system uses theme-aware CSS variables — no hardcoded colors:

| Variable | Purpose | Hex Equivalent |
|----------|---------|---------------|
| `--sage` | Primary accent (buttons, links) | `#84a98c` |
| `--sage-l` | Light sage (hover states, backgrounds) | `#a3c4a9` |
| `--red` | Destructive/error states, RED status | `#dc3545` |
| `--amber` | Warning/AMBER status | `#ffc107` |
| `--green` | Success/GREEN status | Bootstrap `success` |
| `--card-bg` | Card background | `#2b3035` (dark) |

### 3.1.4 Typography Stack

| Usage | Font Family | Weight |
|-------|-------------|--------|
| Headings (h1-h3) | `Playfair Display` | 700, 900 |
| Labels, Stats | `IBM Plex Mono` (monospaced) | 400, 500 |
| Body text | `Outfit` | 300, 400, 500, 600 |

Loaded via Google Fonts in `app/Views/layouts/default.php`:
```php
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=IBM+Plex+Mono:wght@400;500&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
```

### 3.1.5 Responsive Strategy

| Interface | Container Class | Breakpoints |
|-----------|----------------|-------------|
| Paramedic (mobile) | `container-fluid` | Mobile-first (320px+) |
| Hospital (desktop) | `container` | Desktop (992px+) |
| Dispatcher (large) | `container-fluid` | Extra large (1200px+) |
| Admin (desktop) | `container` | Desktop (992px+) |

### 3.1.6 Accessibility Standards

| Requirement | Implementation |
|-------------|---------------|
| Touch targets | `min-height: 48px` on all interactive elements |
| Focus indicators | `focus-ring` Bootstrap class |
| ARIA labels | `aria-label`, `aria-hidden`, `role` attributes |
| Color contrast | Ensured by Bootstrap 5 dark theme base |
| Screen reader | Semantic HTML structure, proper heading hierarchy |

### 3.1.7 SEO & Social Meta

Every view-rendering controller method prepares a standard `$data` array:

```php
$data = [
    'page_title'       => 'Page Name | ClearBay',
    'meta_description' => 'Description for search engines.',
    'canonical_url'    => url_to('route.name'),
    'robots_tag'       => 'noindex, nofollow',  // or 'index, follow' for public pages
];
```

The layout (`app/Views/layouts/default.php`) renders:
- **Open Graph**: `og:type`, `og:url`, `og:title`, `og:description`, `og:image`
- **Twitter Card**: `twitter:card`, `twitter:site`, `twitter:title`, `twitter:description`, `twitter:image`, `twitter:image:alt`

Default meta image: `base_url('assets/images/brand.png')`

### 3.1.8 UI Component Standards

| Component | Bootstrap Class | Usage |
|-----------|----------------|-------|
| Primary button | `btn btn-primary` | Main CTA, form submit |
| Secondary button | `btn btn-outline-secondary` | Cancel, back |
| Destructive button | `btn btn-danger` | Delete, irreversible actions |
| Status badge | `badge bg-success/warning/danger` | ED status indicators |
| Text inputs | Bootstrap floating labels | All form fields |
| Tables | `table table-striped table-hover` | Queue lists, admin CRUD |
| Modal dialogs | Bootstrap modal | SC-04 status, SC-05 handover |
| Alerts (persistent) | Bootstrap alert | Operation success/error feedback |
| Toasts | Bootstrap toast | Connectivity issues only |

---

## 3.2 Core Wireframe Directory

### 3.2.1 Screen-to-View Mapping

| Screen ID | View File | Module | Route Name | Controller::Method | Layout |
|-----------|-----------|--------|------------|-------------------|--------|
| **SC-01** | `Auth/Views/login.php` | Auth | `auth.login` | `AuthController::loginView()` | Centered card (no sidebar) |
| **SC-02** | `Hospital/Views/dashboard.php` | Hospital | `hospital.dashboard` | `HospitalController::dashboard()` | 4-zone: nav, banner, metrics, queue |
| **SC-03** | (integrated in SC-02) | Hospital | `hospital.queue` (AJAX) | `HospitalController::getQueue()` | Table within dashboard |
| **SC-04** | (Bootstrap modal in SC-02) | Hospital | `hospital.status.update` | `HospitalController::updateStatus()` | Modal with status radio + bay input |
| **SC-05** | (Bootstrap modal in SC-02) | Hospital | `hospital.handover.complete` | `HospitalController::completeHandover()` | Modal with bay number + notes fields |
| **SC-06** | `Hospital/Views/analytics.php` | Hospital | `hospital.analytics` | `HospitalController::analytics()` | Charts + provider table + export button |
| **SC-07** | `Ambulance/Views/home.php` | Ambulance | `ambulance.home` | `AmbulanceController::home()` | Split: map (2/3) + hospital list (1/3) |
| **SC-08** | `Ambulance/Views/detail.php` | Ambulance | `ambulance.hospital.detail` | `AmbulanceController::detail()` | Card: hospital specs, queue, actions |
| **SC-09** | `Ambulance/Views/pre_notify.php` | Ambulance | `ambulance.pre_notify` | `AmbulanceController::preNotifyForm()` | Form with patient fields + acuity buttons |
| **SC-10** | (Skipped — redirects to SC-11) | — | — | — | — |
| **SC-11** | `Ambulance/Views/active_run.php` | Ambulance | `ambulance.active_run` | `AmbulanceController::activeRun()` | Centered card: countdown + status + ETA |
| **SC-12** | `Dispatcher/Views/map.php` | Dispatcher | `dispatcher.index` | `DispatcherController::index()` | Split: map (2/3) + sidebar panels (1/3) |
| **SC-13** | (Panel in SC-12) | Dispatcher | `dispatcher.fleet` | `DispatcherController::fleetStatus()` | Scrollable fleet list in sidebar |
| **SC-14** | (Panel in SC-12) | Dispatcher | `dispatcher.alert.acknowledge` | `DispatcherController::acknowledgeAlert()` | Alert cards with "Ack" buttons |
| **SC-15** | (Panel in SC-12) | Dispatcher | — | — | Hospital capacity list in sidebar |
| **SC-16** | `Admin/Views/dashboard.php` | Admin | `admin.dashboard` | `AdminController::dashboard()` | Metric cards + nav links |

### 3.2.2 Admin Sub-Views

| View | Route Name | Controller::Method | Purpose |
|------|-----------|-------------------|---------|
| `Admin/Views/users/list.php` | `admin.users.list` | `AdminController::usersList()` | Paginated user table |
| `Admin/Views/users/edit.php` | `admin.users.edit` | `AdminController::userEdit()` | User form (create/edit) |
| `Admin/Views/hospitals/list.php` | `admin.hospitals.list` | `AdminController::hospitalsList()` | Paginated hospital table |
| `Admin/Views/hospitals/edit.php` | `admin.hospitals.edit` | `AdminController::hospitalEdit()` | Hospital form |
| `Admin/Views/ambulances/list.php` | `admin.ambulances.list` | `AdminController::ambulancesList()` | Paginated ambulance table |
| `Admin/Views/ambulances/edit.php` | `admin.ambulances.edit` | `AdminController::ambulanceEdit()` | Ambulance form |
| `Admin/Views/handovers/list.php` | `admin.handovers.list` | `AdminController::handoversList()` | Paginated handover table |
| `Admin/Views/handovers/edit.php` | `admin.handovers.edit` | `AdminController::handoverEdit()` | Handover form with status transition |
| `Admin/Views/pilots/list.php` | `admin.pilots.list` | `AdminController::pilotsList()` | Paginated pilot signup table |
| `Admin/Views/pilots/edit.php` | `admin.pilots.edit` | `AdminController::pilotEdit()` | Pilot signup form |

### 3.2.3 Screen Layout Specifications

**SC-02 Hospital Dashboard** (`app/Modules/Hospital/Views/dashboard.php`):

```
┌─────────────────────────────────────────────────────┐
│  [Status Banner: GREEN/AMBER/RED]   [Status Button] │  ← Click to open SC-04 modal
├────────────┬────────────┬────────────┬───────────────┤
│ Avg Wait   │ vs Baseline│ Completed  │ In Queue      │  ← 4 metric cards
│   12 min   │   -48 min  │   14 today │    3          │
├────────────┴────────────┴────────────┴───────────────┤
│ Ambulance Queue Table (auto-refresh 10s)             │
│ ┌─────────┬──────────┬──────┬─────┬──────┬────────┐ │
│ │ Unit ID │ Provider │ Acuity│ ETA │ Wait │ Action │ │
│ ├─────────┼──────────┼──────┼─────┼──────┼────────┤ │
│ │KRC-12   │Red Cross │Crit. │ 5m  │ 12m  │[Clear] │ │  ← Click opens SC-05
│ │AAR-04   │AAR       │Stable│ 7m  │ 8m   │[Clear] │ │
│ └─────────┴──────────┴──────┴─────┴──────┴────────┘ │
└─────────────────────────────────────────────────────┘
```

**SC-07 Paramedic Home Map** (`app/Modules/Ambulance/Views/home.php`):

```
┌───────────────────────┬──────────────────────────────┐
│                       │  Hospital List (sorted by     │
│    Mapbox GL JS Map   │  distance)                    │
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
│   [Current Location]  │                               │
└───────────────────────┴──────────────────────────────┘
```

**SC-12 Dispatcher Command Centre** (`app/Modules/Dispatcher/Views/map.php`):

```
┌─────────────────────────┬────────────────────────────┐
│                         │  ALERTS (SC-14)             │
│   Mapbox GL JS Map      │  ┌────────────────────┐     │
│   (Ambulance markers    │  │ 🔴 KRC-12 @ KNH    │     │
│    moving in real-time)  │  │ Wait: 35 min       │     │
│                         │  │ [Ack]              │     │
│   🟢 Available          │  └────────────────────┘     │
│   🟡 Transporting       │                             │
│   🔴 Queued             │  FLEET STATUS (SC-13)       │
│                         │  ┌────────────────────┐     │
│   Search box (top-right)│  │ KRC-12 🟡 KNH   5m │     │
│                         │  │ AAR-04 🔴 MBG  35m │     │
│                         │  │ NBO-07 🟢 AKU   2m │     │
│                         │  └────────────────────┘     │
│                         │                             │
│                         │  HOSPITAL CAPACITY (SC-15)   │
│                         │  ┌────────────────────┐     │
│                         │  │ KNH  🟥  3 bays    │     │
│                         │  │ MLK  🟧  1 bay     │     │
│                         │  │ MBG  🟥  0 bays    │     │
│                         │  └────────────────────┘     │
└─────────────────────────┴────────────────────────────┘
```

### 3.2.4 Loading States & Edge Cases

| Scenario | Implementation |
|----------|---------------|
| **GPS unavailable** | Fallback coordinates: `-1.2921, 36.8219` (Nairobi centre) |
| **Mapbox token missing** | Map tiles fail to load; error logged to console |
| **Session expired during AJAX** | Backend returns `status: 'error'` with message → frontend redirects to login |
| **No active ambulances** | Dispatcher fleet panel shows empty state message |
| **RED hospital selected** | Pre-notify form blocked; paramedic redirected with flash error |
| **Active run exists** | Concurrency lock prevents new pre-notification submission; auto-redirect to SC-11 |
| **Empty queue** | Hospital dashboard shows "No ambulances currently in queue" |
| **SSE disconnection** | Browser EventSource auto-reconnects; initial packet includes CSRF token |

---

## 3.3 Frontend Assets

| Asset | Location | Purpose |
|-------|----------|---------|
| Bootstrap CSS | `public/assets/bootstrap/css/bootstrap.min.css` | Base CSS framework |
| Custom CSS | `public/assets/css/style.css` | Project-specific styles, CSS variables |
| Application JS | `public/js/app.js` | AJAX handlers, SSE client, CSRF rotation |
| Mapbox GL JS | CDN (loaded per view) | Interactive mapping |

---

*End of Section 3 — Prototype Interfaces Plan*