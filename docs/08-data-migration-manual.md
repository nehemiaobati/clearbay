# Data Migration Manual

## ClearBay MVP — Schema & Seed Management

**Version**: 1.0  
**Date**: 2026-06-04

---

## 8.1 Schema Overview

The ClearBay database schema is fully defined in `clearbayschema.sql`, which serves as the authoritative seed file for fresh environment setup. The schema is also managed incrementally through CodeIgniter 4 migrations for ongoing deployments.

### 8.1.1 Complete Table List

| # | Table | Records (Seed) | Purpose | Created By |
|---|-------|---------------|---------|------------|
| 1 | `users` | 7 | User accounts with role-based access | Migration batch 1 (core) |
| 2 | `hospitals` | 5 | Hospital facilities with GPS | Migration batch 2 (Queue) |
| 3 | `ambulances` | 6 | Fleet vehicles with GPS tracking | Migration batch 2 |
| 4 | `ems_providers` | 3 | EMS service provider registry | Migration batch 2 |
| 5 | `pre_notifications` | 11 | Pre-arrival clinical alerts | Migration batch 2 |
| 6 | `handovers` | 5 (completed) | Handover lifecycle records | Migration batch 2 |
| 7 | `alerts` | 10 | 30-min delay alerts | Migration batch 2 |
| 8 | `hospital_status` | 0 | ED status change history | Migration batch 2 |
| 9 | `audit_log` | 10 | Automated action audit trail | Migration batch 2 |
| 10 | `pilot_signups` | 8 | Pilot program applications | Migration batch 3 (Pilot) |
| 11 | `ci_sessions` | Variable | Database-backed sessions | Migration batch 1 (core) |
| 12 | `migrations` | 5 | CI4 migration tracking | System-managed |

### 8.1.2 Migration History

| Batch | Migration | Namespace | Description |
|-------|-----------|-----------|-------------|
| 1 | `2026-05-18-204814` | `App` | Create `ci_sessions` table (core session storage) |
| 2 | `2026-05-21-171800` | `App\Modules\Pilot` | Create `pilot_signups` table |
| 2 | `2026-05-21-172000` | `App\Modules\Queue` | Create core operational tables (hospitals, ambulances, providers, pre_notifications, handovers, alerts, hospital_status, audit_log) |
| 3 | `2026-05-28-120000` | `App\Modules\Hospital` | Upgrade hospital schema (add category, status, GPS fields, index optimization) |
| 4 | `2026-06-03-171300` | `App` | Add composite indexes (`ambulances_provider_status`, `handovers_hosp_status`) |

### 8.1.3 Seed Data by Table

#### Users (`users`)

| ID | Name | Email | Role | Hospital | EMS Provider |
|----|------|-------|------|----------|--------------|
| 1 | Nurse Wanjiru | `nurse@clearbay.com` | `nurse` | KNH (1) | — |
| 2 | Nurse Atieno | `nurse2@clearbay.com` | `nurse` | MBG (3) | — |
| 3 | KNH Administrator | `hospadmin@clearbay.com` | `hospital_admin` | KNH (1) | — |
| 4 | Paramedic Otieno | `paramedic@clearbay.com` | `paramedic` | — | Kenya Red Cross (2) |
| 5 | Dispatcher Mwangi | `dispatcher@clearbay.com` | `dispatcher` | — | — |
| 6 | System Admin | `admin@clearbay.com` | `admin` | — | — |
| 8 | mbagathi admin | `mbagathi@admi.com` | `hospital_admin` | MBG (3) | — |

All passwords: `12345678` (bcrypt hash: `$2y$12$DGJplHGPhmGAzbjuPioCC.JpoUMOI0m.7O1bSAfcHUaPhj3CmMv6O`)

#### Hospitals (`hospitals`)

| ID | Code | Name | Category | Status | Bays | Lat | Lng |
|----|------|------|----------|--------|------|-----|-----|
| 1 | KNH | Kenyatta National Hospital | National Referral · Public | Red | 3 | -1.30130000 | 36.80800000 |
| 2 | MLK | Mama Lucy Kibaki Hospital | County Referral · Public | Amber | 1 | -1.27850000 | 36.90300000 |
| 3 | MBG | Mbagathi County Hospital | County Referral · Public | Red | 0 | -1.30900000 | 36.80100000 |
| 4 | AKU | Aga Khan University Hospital | Teaching Hospital · Private | Green | 5 | -1.26100000 | 36.80900000 |
| 5 | NBO | Nairobi Hospital | Referral Hospital · Private | Green | 4 | -1.29520000 | 36.80480000 |

#### EMS Providers (`ems_providers`)

| ID | Name | Type | Contact |
|----|------|------|---------|
| 1 | AAR Healthcare | Private | +254711090000 |
| 2 | Kenya Red Cross | NGO | +254700395395 |
| 3 | Nairobi County Services | Public | +254202222181 |

#### Ambulances (`ambulances`)

| ID | Unit ID | Provider | EMS Provider | Registration | Status | Lat | Lng |
|----|---------|----------|-------------|-------------|--------|-----|-----|
| 1 | AAR-04 | AAR Healthcare | 1 | KBY 104A | Queued | -1.30800000 | 36.80200000 |
| 2 | KRC-12 | Kenya Red Cross | 2 | KBZ 512B | Transporting | -1.29800000 | 36.81500000 |
| 3 | NBO-07 | Nairobi County | 3 | KCG 007G | Transporting | -1.28800000 | 36.88500000 |
| 4 | AAR-09 | AAR Healthcare | 1 | KBY 109A | Transporting | -1.29220000 | 36.80900000 |
| 5 | KRC-05 | Kenya Red Cross | 2 | KBZ 505B | Transporting | -1.26100000 | 36.80900000 |
| 6 | AAR-02 | AAR Healthcare | 1 | KBY 102A | Queued | -1.30900000 | 36.80100000 |

---

## 8.2 Primary Key & Auto-Increment Seed Reset

When deploying to a fresh production environment, the `clearbayschema.sql` seed data auto-increment values are pre-set:

| Table | Next Auto-Increment ID |
|-------|----------------------|
| `alerts` | 11 |
| `ambulances` | 7 |
| `audit_log` | 11 |
| `ems_providers` | 4 |
| `handovers` | 29 |
| `hospitals` | 6 |
| `hospital_status` | 1 |
| `migrations` | 6 |
| `pilot_signups` | 10 |
| `pre_notifications` | 12 |
| `users` | 9 |

### 8.2.1 Session Data

The `ci_sessions` table seed contains historical session data from development. In production, truncate this table:

```sql
TRUNCATE TABLE ci_sessions;
```

---

## 8.3 Schema Index Summary

| Table | Primary Key | Unique Index | Single Indexes | Composite Indexes |
|-------|-------------|-------------|----------------|-------------------|
| `alerts` | `id` | — | `ambulance_id`, `hospital_id`, `triggered_at` | — |
| `ambulances` | `id` | — | `unit_id` | `(ems_provider_id, status)` |
| `audit_log` | `id` | — | `user_id`, `table_name`, `timestamp` | — |
| `ems_providers` | `id` | — | `active` | — |
| `handovers` | `id` | — | `ambulance_id`, `hospital_id`, `status`, `created_at` | `(hospital_id, status)` |
| `hospitals` | `id` | `code` | — | — |
| `hospital_status` | `id` | — | `hospital_id`, `status` | — |
| `pilot_signups` | `id` | — | `email_address`, `created_at` | — |
| `pre_notifications` | `id` | — | `ambulance_id`, `hospital_id`, `paramedic_id`, `status`, `sent_at` | — |
| `users` | `id` | `email` | `role`, `active` | — |

---

*End of Section 8 — Data Migration Manual*