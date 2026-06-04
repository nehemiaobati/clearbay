# Data Dictionary & Metadata Standards

## ClearBay MVP — Database Entity & Field Definitions

**Version**: 1.0  
**Date**: 2026-06-04

---

## 4.1 Database Entity & Field Definitions

### 4.1.1 Table: `users`

Stores all user accounts with role-based access control. Each user has exactly one role and may be mapped to a hospital (nurse/hospital_admin) or EMS provider (paramedic).

**Schema Source**: `clearbayschema.sql` (lines 600–613)

| Field | Type | Constraints | Default | Nullable | Description |
|-------|------|-------------|---------|----------|-------------|
| `id` | `int UNSIGNED` | PRIMARY KEY, AUTO_INCREMENT | — | No | Unique user identifier |
| `name` | `varchar(255)` | — | — | No | Full display name |
| `email` | `varchar(255)` | UNIQUE INDEX (`email`) | — | No | Login email address |
| `password_hash` | `varchar(255)` | — | — | No | Bcrypt password hash |
| `role` | `varchar(50)` | INDEX (`role`) | — | No | RBAC role: `nurse`, `hospital_admin`, `paramedic`, `dispatcher`, `admin` |
| `hospital_id` | `int UNSIGNED` | INDEX (implicit) | — | Yes | FK → `hospitals.id`. Mapped hospital for nurse/admin roles |
| `ems_provider_id` | `int UNSIGNED` | INDEX (implicit) | — | Yes | FK → `ems_providers.id`. Mapped EMS provider for paramedic role |
| `active` | `tinyint(1)` | INDEX (`active`) | `1` | No | Soft delete flag: `1` = active, `0` = deactivated |
| `created_at` | `datetime` | — | — | Yes | Auto-managed by CI4 `$createdField` |
| `updated_at` | `datetime` | — | — | Yes | Auto-managed by CI4 `$updatedField` |

**Entity Class**: `app/Modules/Auth/Entities/User.php`
**Model**: `app/Modules/Auth/Models/UserModel.php`
**Seed Data**: 7 users (Nurse Wanjiru, Nurse Atieno, KNH Administrator, Paramedic Otieno, Dispatcher Mwangi, System Admin, mbagathi admin)

---

### 4.1.2 Table: `hospitals`

Registered hospital facilities with GPS coordinates for mapping and capacity tracking.

**Schema Source**: `clearbayschema.sql` (lines 455–469)

| Field | Type | Constraints | Default | Nullable | Description |
|-------|------|-------------|---------|----------|-------------|
| `id` | `int UNSIGNED` | PRIMARY KEY, AUTO_INCREMENT | — | No | Unique hospital identifier |
| `code` | `varchar(10)` | UNIQUE INDEX (`code`) | — | No | Short facility code (e.g., `KNH`, `MLK`, `MBG`) |
| `name` | `varchar(255)` | — | — | No | Full facility name |
| `category` | `varchar(100)` | — | — | No | Hospital category (e.g., `National Referral · Public`, `County Referral · Public`) |
| `status` | `varchar(20)` | — | `Green` | No | ED capacity status: `Green`, `Amber`, `Red`, `Recruiting` |
| `bays_available` | `int` | — | `0` | No | Current number of available treatment bays |
| `lat` | `decimal(10,8)` | — | — | Yes | GPS latitude coordinate |
| `lng` | `decimal(11,8)` | — | — | Yes | GPS longitude coordinate |
| `address` | `text` | — | — | Yes | Physical street address |
| `contact_phone` | `varchar(50)` | — | — | Yes | Hospital contact phone number |
| `active` | `tinyint(1)` | — | `1` | Yes | Soft delete flag: `1` = active, `0` = inactive |
| `created_at` | `datetime` | — | — | Yes | Auto-managed by CI4 |
| `updated_at` | `datetime` | — | — | Yes | Auto-managed by CI4 |

**Entity Class**: `app/Modules/Hospital/Entities/Hospital.php`
**Model**: `app/Modules/Hospital/Models/HospitalModel.php`
**Seed Data**: 5 hospitals (KNH, Mama Lucy Kibaki, Mbagathi, Aga Khan, Nairobi Hospital)

---

### 4.1.3 Table: `ambulances`

Emergency response vehicle registry with real-time GPS tracking fields.

**Schema Source**: `clearbayschema.sql` (lines 64–76)

| Field | Type | Constraints | Default | Nullable | Description |
|-------|------|-------------|---------|----------|-------------|
| `id` | `int UNSIGNED` | PRIMARY KEY, AUTO_INCREMENT | — | No | Unique ambulance identifier |
| `unit_id` | `varchar(50)` | INDEX (`unit_id`) | — | No | Human-readable unit identifier (e.g., `AAR-04`, `KRC-12`) |
| `provider` | `varchar(100)` | — | — | No | EMS provider name (denormalized for performance) |
| `ems_provider_id` | `int UNSIGNED` | INDEX (`ems_provider_id`) | — | Yes | FK → `ems_providers.id` |
| `registration` | `varchar(50)` | — | — | Yes | Vehicle registration plate |
| `current_lat` | `decimal(10,8)` | — | — | Yes | Live GPS latitude coordinate |
| `current_lng` | `decimal(11,8)` | — | — | Yes | Live GPS longitude coordinate |
| `status` | `varchar(50)` | INDEX (composite: `ems_provider_id,status`) | `Available` | Yes | Fleet status: `Available`, `Transporting`, `On Scene`, `Queued`, `Off Duty` |
| `last_updated` | `datetime` | — | — | Yes | Timestamp of last GPS coordinate update |
| `created_at` | `datetime` | — | — | Yes | Auto-managed by CI4 |
| `updated_at` | `datetime` | — | — | Yes | Auto-managed by CI4 |

**Entity Class**: `app/Modules/Ambulance/Entities/Ambulance.php`
**Model**: `app/Modules/Ambulance/Models/AmbulanceModel.php`
**Seed Data**: 6 ambulances (AAR-04, KRC-12, NBO-07, AAR-09, KRC-05, AAR-02)

---

### 4.1.4 Table: `ems_providers`

Ambulance service provider registry.

**Schema Source**: `clearbayschema.sql` (lines 393–401)

| Field | Type | Constraints | Default | Nullable | Description |
|-------|------|-------------|---------|----------|-------------|
| `id` | `int UNSIGNED` | PRIMARY KEY, AUTO_INCREMENT | — | No | Unique provider identifier |
| `name` | `varchar(255)` | — | — | No | Provider organization name |
| `type` | `varchar(100)` | — | — | No | Provider type: `Private`, `NGO`, `Public` |
| `contact_phone` | `varchar(50)` | — | — | No | Provider contact phone |
| `active` | `tinyint(1)` | INDEX (`active`) | `1` | No | Active status |
| `created_at` | `datetime` | — | — | Yes | Auto-managed by CI4 |
| `updated_at` | `datetime` | — | — | Yes | Auto-managed by CI4 |

**Seed Data**: 3 providers (AAR Healthcare, Kenya Red Cross, Nairobi County Services)

---

### 4.1.5 Table: `pre_notifications`

Paramedic-initiated pre-arrival alerts sent to emergency departments.

**Schema Source**: `clearbayschema.sql` (lines 561–577)

| Field | Type | Constraints | Default | Nullable | Description |
|-------|------|-------------|---------|----------|-------------|
| `id` | `int UNSIGNED` | PRIMARY KEY, AUTO_INCREMENT | — | No | Unique pre-notification identifier |
| `ambulance_id` | `int UNSIGNED` | INDEX (`ambulance_id`) | — | No | FK → `ambulances.id` |
| `hospital_id` | `int UNSIGNED` | INDEX (`hospital_id`) | — | No | FK → `hospitals.id` |
| `paramedic_id` | `int UNSIGNED` | INDEX (`paramedic_id`) | — | No | FK → `users.id` (role: paramedic) |
| `patient_age` | `int` | — | — | No | Patient age in years |
| `patient_sex` | `varchar(20)` | — | — | No | Patient sex: `Male`, `Female`, `Not Specified` |
| `chief_complaint` | `varchar(100)` | — | — | No | Primary clinical complaint (e.g., `Stroke / CVA`, `Cardiac Arrest`) |
| `acuity` | `varchar(20)` | — | — | No | Clinical acuity: `Critical`, `Serious`, `Stable` |
| `notes` | `varchar(150)` | — | — | Yes | Optional free-text notes from paramedic |
| `eta_minutes` | `int` | — | — | No | Estimated time of arrival in minutes (dynamically recalculated) |
| `status` | `varchar(50)` | INDEX (`status`) | `Pending` | No | Pre-notification status |
| `sent_at` | `datetime` | INDEX (`sent_at`) | — | No | Timestamp when pre-notification was dispatched |
| `received_at` | `datetime` | — | — | Yes | Timestamp when hospital acknowledged reception |
| `created_at` | `datetime` | — | — | Yes | Auto-managed by CI4 |
| `updated_at` | `datetime` | — | — | Yes | Auto-managed by CI4 |

**Entity Class**: `app/Modules/Hospital/Entities/PreNotification.php`
**Model**: `app/Modules/Hospital/Models/PreNotificationModel.php`

---

### 4.1.6 Table: `handovers`

Core lifecycle record tracking each ambulance handover from dispatch to clearance.

**Schema Source**: `clearbayschema.sql` (lines 418–436)

| Field | Type | Constraints | Default | Nullable | Description |
|-------|------|-------------|---------|----------|-------------|
| `id` | `int UNSIGNED` | PRIMARY KEY, AUTO_INCREMENT | — | No | Unique handover identifier |
| `pre_notification_id` | `int UNSIGNED` | INDEX (implicit) | — | Yes | FK → `pre_notifications.id` (1:1 relationship) |
| `ambulance_id` | `int UNSIGNED` | INDEX (`ambulance_id`) | — | No | FK → `ambulances.id` |
| `hospital_id` | `int UNSIGNED` | INDEX (`hospital_id`), composite INDEX (`hospital_id, status`) | — | No | FK → `hospitals.id` |
| `patient_age` | `int` | — | — | No | Patient age in years |
| `patient_gender` | `char(1)` | — | — | No | Patient gender: `M`, `F` |
| `acuity` | `varchar(20)` | — | — | No | Clinical acuity: `Critical`, `Serious`, `Stable` |
| `eta_minutes` | `int` | — | `0` | No | Estimated time of arrival (dynamically recalculated) |
| `wait_time_minutes` | `int` | — | `0` | No | Current wait time at hospital (dynamically updated) |
| `status` | `varchar(50)` | INDEX (`status`), composite INDEX (`hospital_id, status`) | `En route` | No | Lifecycle status: `En route`, `Arrived`, `Acknowledged`, `Preparing`, `Cleared` |
| `arrived_at` | `datetime` | — | — | Yes | Timestamp when ambulance arrived at hospital (admin-only set) |
| `handover_complete_at` | `datetime` | — | — | Yes | Timestamp when handover was completed (nurse action) |
| `bay_number` | `varchar(50)` | — | — | Yes | ED bay number where patient was accepted |
| `notes` | `varchar(200)` | — | — | Yes | Clinical or administrative notes |
| `completed_by` | `int UNSIGNED` | — | — | Yes | FK → `users.id` (nurse who completed handover) |
| `created_at` | `datetime` | INDEX (`created_at`) | — | Yes | Auto-managed by CI4 |
| `updated_at` | `datetime` | — | — | Yes | Auto-managed by CI4 |

**Entity Class**: `app/Modules/Hospital/Entities/Handover.php`
**Model**: `app/Modules/Hospital/Models/HandoverModel.php`

---

### 4.1.7 Table: `alerts`

Automated alerts triggered when ambulance handover wait exceeds 30 minutes.

**Schema Source**: `clearbayschema.sql` (lines 30–40)

| Field | Type | Constraints | Default | Nullable | Description |
|-------|------|-------------|---------|----------|-------------|
| `id` | `int UNSIGNED` | PRIMARY KEY, AUTO_INCREMENT | — | No | Unique alert identifier |
| `ambulance_id` | `int UNSIGNED` | INDEX (`ambulance_id`) | — | No | FK → `ambulances.id` |
| `hospital_id` | `int UNSIGNED` | INDEX (`hospital_id`) | — | No | FK → `hospitals.id` |
| `alert_type` | `varchar(100)` | — | — | No | Alert type: `Wait Time Exceeded` |
| `triggered_at` | `datetime` | INDEX (`triggered_at`) | — | No | Timestamp when alert was generated |
| `acknowledged_at` | `datetime` | — | — | Yes | Timestamp when dispatcher acknowledged |
| `acknowledged_by` | `int UNSIGNED` | — | — | Yes | FK → `users.id` (dispatcher who acknowledged) |
| `created_at` | `datetime` | — | — | Yes | Auto-managed by CI4 |
| `updated_at` | `datetime` | — | — | Yes | Auto-managed by CI4 |

**Entity Class**: `app/Modules/Dispatcher/Entities/Alert.php`
**Model**: `app/Modules/Dispatcher/Models/AlertModel.php`

---

### 4.1.8 Table: `hospital_status`

Historical audit log of ED status changes.

**Schema Source**: `clearbayschema.sql` (lines 488–495)

| Field | Type | Constraints | Default | Nullable | Description |
|-------|------|-------------|---------|----------|-------------|
| `id` | `int UNSIGNED` | PRIMARY KEY, AUTO_INCREMENT | — | No | Unique status log identifier |
| `hospital_id` | `int UNSIGNED` | INDEX (`hospital_id`) | — | No | FK → `hospitals.id` |
| `status` | `varchar(20)` | INDEX (`status`) | — | No | ED status at time of change: `GREEN`, `AMBER`, `RED` |
| `bays_available` | `int` | — | — | No | Available bay count at time of change |
| `updated_by` | `int UNSIGNED` | — | — | No | FK → `users.id` (nurse or hospital_admin) |
| `updated_at` | `datetime` | — | — | No | Timestamp of status change |

**Entity Class**: `app/Modules/Hospital/Entities/HospitalStatus.php`
**Model**: `app/Modules/Hospital/Models/HospitalStatusModel.php`

---

### 4.1.9 Table: `audit_log`

Security and compliance log for automated system actions.

**Schema Source**: `clearbayschema.sql` (lines 96–103)

| Field | Type | Constraints | Default | Nullable | Description |
|-------|------|-------------|---------|----------|-------------|
| `id` | `int UNSIGNED` | PRIMARY KEY, AUTO_INCREMENT | — | No | Unique audit log identifier |
| `user_id` | `int UNSIGNED` | INDEX (`user_id`) | — | Yes | FK → `users.id` (null for system-generated actions) |
| `action` | `varchar(255)` | — | — | No | Description of action performed |
| `table_name` | `varchar(100)` | INDEX (`table_name`) | — | No | Database table affected |
| `record_id` | `int UNSIGNED` | — | — | No | ID of the affected record |
| `timestamp` | `datetime` | INDEX (`timestamp`) | — | No | Timestamp of the action |

---

### 4.1.10 Table: `pilot_signups`

Pilot program registration applications from external organizations.

**Schema Source**: `clearbayschema.sql` (lines 530–540)

| Field | Type | Constraints | Default | Nullable | Description |
|-------|------|-------------|---------|----------|-------------|
| `id` | `int UNSIGNED` | PRIMARY KEY, AUTO_INCREMENT | — | No | Unique signup identifier |
| `full_name` | `varchar(255)` | — | — | No | Applicant's full name |
| `email_address` | `varchar(255)` | INDEX (`email_address`) | — | No | Applicant email |
| `organisation` | `varchar(255)` | — | — | No | Organization name |
| `user_role` | `varchar(100)` | — | — | No | Role within organization |
| `phone_number` | `varchar(50)` | — | — | Yes | Contact phone number |
| `message` | `text` | — | — | Yes | Free-text message from applicant |
| `created_at` | `datetime` | INDEX (`created_at`) | — | Yes | Auto-managed by CI4 |
| `updated_at` | `datetime` | — | — | Yes | Auto-managed by CI4 |

**Entity Class**: `app/Modules/Pilot/Entities/PilotSignup.php`
**Model**: `app/Modules/Pilot/Models/PilotSignupModel.php`

---

### 4.1.11 Table: `ci_sessions`

Database-backed session storage (CI4 default with DatabaseHandler driver).

**Schema Source**: `clearbayschema.sql` (line 122, data only — schema from migration `2026-05-18-204814`)

| Field | Type | Constraints | Default | Nullable | Description |
|-------|------|-------------|---------|----------|-------------|
| `id` | `varchar(128)` | PRIMARY KEY | — | No | Session ID |
| `ip_address` | `varchar(45)` | — | — | No | Client IP address |
| `timestamp` | `int UNSIGNED` | INDEX | — | No | Last activity timestamp |
| `data` | `mediumblob` | — | — | No | Serialized session data (`MEDIUMBLOB` for 16MB capacity) |

---

## 4.2 System Metadata & Auditing Standards

### 4.2.1 Timestamp Management

All operational entities use CI4's automatic timestamp management:

| Model Property | Default Value | Managed By |
|----------------|---------------|------------|
| `$useTimestamps` | `true` | CI4 Model base class |
| `$createdField` | `created_at` | CI4 Model |
| `$updatedField` | `updated_at` | CI4 Model |

Tables with automatic timestamps: `users`, `hospitals`, `ambulances`, `ems_providers`, `pre_notifications`, `handovers`, `alerts`, `pilot_signups`

### 4.2.2 Audit Log Usage

The `audit_log` table records automated system actions. Current usage:

| Action | Trigger | Entry Example |
|--------|---------|---------------|
| Alert generated | `DispatcherService::_checkAndTriggerAlerts()` when wait > 30 min | `"Automated alert generated: wait time > 30 min"` on `alerts` table |

**Insertion Code** (`DispatcherService.php`, lines 127–133):
```php
$db->table('audit_log')->insert([
    'user_id'    => null,
    'action'     => 'Automated alert generated: wait time > 30 min',
    'table_name' => 'alerts',
    'record_id'  => $this->alert_model->getInsertID(),
    'timestamp'  => date('Y-m-d H:i:s')
]);
```

### 4.2.3 ED Status Change History

The `hospital_status` table serves as a historical log of all ED capacity changes. Each time a nurse or hospital_admin updates the status via `HospitalService::updateStatus()`, a new row is inserted with the previous values, the user who made the change, and the timestamp.

**Insertion Code** (`HospitalService.php`, lines 144–151):
```php
$log = new HospitalStatus([
    'hospital_id'    => $hospital_id,
    'status'         => $status,
    'bays_available' => $bays_available,
    'updated_by'     => $user_id,
    'updated_at'     => date('Y-m-d H:i:s'),
]);
$this->status_model->save($log);
```

### 4.2.4 Soft Delete Policy

- **Users**: Deactivated via `active = 0` (not hard-deleted). Admin can toggle via `AdminController::userDelete()` which calls `AdminService::deleteUser()`
- **Hospitals**: Deactivated via `active = 0`
- **Ambulances**: Hard-deleted via `AdminController::ambulanceDelete()`
- **Handovers**: Hard-deleted via `AdminController::handoverDelete()`
- **Pilot Signups**: Hard-deleted via `AdminController::pilotDelete()`

---

*End of Section 4 — Data Dictionary & Metadata Standards*