# Deployment Guide

## ClearBay MVP — Production Environment Rollout

**Version**: 1.0  
**Date**: 2026-06-04

---

## 9.1 Production Environment Rollout

### 9.1.1 Prerequisites

Before deployment, ensure the production server has:

| Requirement | Minimum Version | Verification Command |
|-------------|----------------|---------------------|
| PHP | 8.1+ (tested 8.5.5) | `php -v` |
| MySQL/MariaDB | 8.0+/10.5+ | `mysql --version` |
| Composer | 2.x | `composer --version` |
| Web Server | Apache 2.4+ or Nginx 1.18+ | `nginx -v` or `apache2 -v` |
| `mysqldump` | System utility | `which mysqldump` |
| `mysql` client | System utility | `which mysql` |
| Git | 2.x | `git --version` |
| Node.js | 18.x+ (if building frontend) | `node --version` |
| npm | 9.x+ (if building frontend) | `npm --version` |

### 9.1.2 Step-by-Step Deployment

#### Step 1: Clone Repository

```bash
# Navigate to web root
cd /var/www

# Clone repository
git clone <repository-url> clearbay
cd clearbay

# Checkout specific release tag (if applicable)
git checkout v1.0.0
```

#### Step 2: Install PHP Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

This command:
- Installs only production dependencies (excludes PHPUnit, Debug Toolbar)
- Generates optimized autoloader for faster class loading
- Resolves all dependency versions

#### Step 3: Configure Environment

```bash
# Create environment file from template
cp env .env

# Generate a secure application key
php spark key:generate
```

Edit `.env` with these required values:

```ini
# Application
CI_ENVIRONMENT = production
app.baseURL = https://clearbay.example.com

# Database (create database first, see Step 3a)
database.default.hostname = localhost
database.default.database = clearbay
database.default.username = clearbay_user
database.default.password = <secure-random-password>
database.default.DBDriver = MySQLi

# Mapbox
mapboxgl.accessToken = pk.eyJ1IjoibmVvIiwiYSI6ImNsczEydDdsczAweG4ya3BzNWh0cGE3aTMifQ.XJ8zA2iR3qWPK5-EXAMPLE

# Encryption
# encryption.key = hex2bin:...
```

**Step 3a: Create Database & User**

```sql
CREATE DATABASE clearbay
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

CREATE USER 'clearbay_user'@'localhost' IDENTIFIED BY '<secure-random-password>';
GRANT ALL PRIVILEGES ON clearbay.* TO 'clearbay_user'@'localhost';
FLUSH PRIVILEGES;
```

#### Step 4: Run Migrations & Seeds

```bash
# Run all module migrations
php spark migrate --all

# Seed reference data (hospitals, ambulances, users, etc.)
php spark db:seed MainSeeder
```

**Important**: After seeding, change all default passwords on first login.

#### Step 5: Optimize for Production

```bash
php spark optimize
```

This caches configuration and routes for faster bootstrap.

#### Step 6: Set Document Root

Configure your web server to point to `/var/www/clearbay/public`.

**Verification**:
```bash
# Ensure only public/ is accessible
ls -la /var/www/clearbay/public/
# Should contain: index.php, .htaccess, assets/
```

#### Step 7: Set Directory Permissions

```bash
# Make writable directory writable by web server
chmod -R 755 writable/
chown -R www-data:www-data writable/

# Protect sensitive files
chmod 640 .env
chmod 640 app/Config/Database.php
```

#### Step 8: Verify Deployment

1. **Application accessible**: Visit `https://clearbay.example.com`
2. **Login functional**: Test all 5 roles (nurse, hospital_admin, paramedic, dispatcher, admin)
3. **Hospital dashboard**: Login as `nurse@clearbay.com` → should see queue dashboard
4. **Dispatcher SSE**: Login as `dispatcher@clearbay.com` → SSE stream should connect
5. **Ambulance GPS**: Login as `paramedic@clearbay.com` → map should load with hospital pins
6. **Admin panel**: Login as `admin@clearbay.com` → CRUD operations functional

### 9.1.3 Rollback Procedure

```bash
# 1. Restore database from backup
php spark db:restore

# 2. Rollback to previous code version
git checkout <previous-tag>

# 3. Re-run migrations if schema changed
php spark migrate --all

# 4. Clear cache
php spark cache:clear
```

---

## 9.2 Disaster Recovery

### 9.2.1 Database Backup Strategy

| Aspect | Configuration |
|--------|---------------|
| Frequency | Daily (recommended: schedule via cron) |
| Tool | `php spark db:backup` (mysqldump wrapper) |
| Storage Location | `writable/backups/` |
| Retention | 30 days (manual cleanup required) |
| Off-site Backup | Copy to external storage (S3, etc.) |

**Cron Job Setup**:
```bash
# Run daily at 2:00 AM
0 2 * * * cd /var/www/clearbay && php spark db:backup >> writable/logs/backup.log 2>&1
```

### 9.2.2 Restore Procedure

```bash
# Step 1: List available backups
ls -la writable/backups/

# Step 2: Restore (interactive)
php spark db:restore

# OR restore directly by filename
php spark db:restore backup-2026-06-03_14-00-00.sql
```

### 9.2.3 Session Recovery

Database-backed sessions (`ci_sessions` table) survive server restarts. In case of data loss:

1. Restore database from latest backup
2. All active sessions before the backup are lost (users must re-login)
3. No handover data is lost (all transactional data in the same database)

### 9.2.4 Server Failure Recovery

| Scenario | Recovery Action |
|----------|----------------|
| Web server down | Restart service: `systemctl restart nginx` or `apache2` |
| PHP-FPM down | Restart service: `systemctl restart php8.1-fpm` |
| Database down | Restart service: `systemctl restart mysql` |
| Full server failure | Restore from backup to new instance (Steps 9.1.2.1–8) |

---

## 9.3 Post-Deployment Checklist

| Item | Completed | Notes |
|------|-----------|-------|
| `CI_ENVIRONMENT = production` | ☐ | Set in `.env` |
| `display_errors = 0` | ☐ | Auto-handled by CI_ENVIRONMENT |
| Debug toolbar disabled | ☐ | Auto-disabled in production |
| HTTPS enforced | ☐ | Configure Nginx/Apache |
| Document root set to `/public` | ☐ | Web server config |
| `writable/` permissions set | ☐ | 755/775, web-user writable |
| Database backups configured | ☐ | Cron job |
| Default passwords changed | ☐ | Instruct users on first login |
| Mapbox token valid | ☐ | Verify map tiles load |
| `.env` excluded from git | ☐ | In `.gitignore` |
| `composer install --no-dev` | ☐ | Verify no dev dependencies |

---

*End of Section 9 — Deployment Guide*