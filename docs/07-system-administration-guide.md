# System Administration Guide

## ClearBay MVP — Production Operations & Maintenance

**Version**: 1.0  
**Date**: 2026-06-04

---

## 7.1 Production Server Setup & Environment Configuration

### 7.1.1 Server Requirements

| Requirement | Specification |
|-------------|---------------|
| Web Server | Apache 2.4+ or Nginx 1.18+ |
| PHP | 8.1+ (tested on PHP 8.5.5) |
| Database | MySQL 8.0+ or MariaDB 10.5+ |
| Memory | Minimum 512MB RAM (1GB+ recommended) |
| Storage | 10GB+ for application, logs, backups |

### 7.1.2 Deployment Steps

**Step 1: Clone Repository & Install Dependencies**
```bash
git clone <repository-url> /var/www/clearbay
cd /var/www/clearbay
composer install --no-dev --optimize-autoloader
```

**Step 2: Configure Environment**
```bash
cp env .env
```
Edit `.env` with production values:

```ini
# CI4 Environment
CI_ENVIRONMENT = production

# Database
database.default.hostname = localhost
database.default.database = clearbay
database.default.username = clearbay_user
database.default.password = <secure-password>
database.default.DBDriver = MySQLi

# Mapbox
mapboxgl.accessToken = pk.eyJ...

# Base URL
app.baseURL = https://clearbay.example.com
```

**Step 3: Run Migrations & Seeds**
```bash
php spark migrate --all
php spark db:seed MainSeeder
```

**Step 4: Optimize for Production**
```bash
php spark optimize
```

**Step 5: Set Document Root**
- Point web server document root to `/var/www/clearbay/public`

**Step 6: Set Permissions**
```bash
chmod 755 /var/www/clearbay/writable
chown -R www-data:www-data /var/www/clearbay/writable
```

### 7.1.3 Nginx Configuration Example

```nginx
server {
    listen 443 ssl;
    server_name clearbay.example.com;

    root /var/www/clearbay/public;
    index index.php;

    ssl_certificate /etc/ssl/certs/clearbay.crt;
    ssl_certificate_key /etc/ssl/private/clearbay.key;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff2?)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 7.1.4 Apache Configuration Example

```apache
<VirtualHost *:443>
    ServerName clearbay.example.com
    DocumentRoot /var/www/clearbay/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/clearbay.crt
    SSLCertificateKeyFile /etc/ssl/private/clearbay.key

    <Directory /var/www/clearbay/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## 7.2 Operational Maintenance Runbooks

### 7.2.1 Database Backup

**Command**: `php spark db:backup`

This custom command wraps `mysqldump` and saves output to `writable/backups/`.

**Usage**:
```bash
# Manual backup with default filename
php spark db:backup

# Backup with custom filename
php spark db:backup pre_deploy_2026-06-04
```

**Output Location**: `writable/backups/backup-YYYY-MM-DD_HH-mm-ss.sql`

**Implementation Reference**: `app/Commands/Database/Backup.php`

### 7.2.2 Database Restore

**Command**: `php spark db:restore`

This custom command lists available backups from `writable/backups/` and restores the selected one.

**Usage**:
```bash
# Interactive mode (select from list)
php spark db:restore

# Direct restore by filename
php spark db:restore pre_deploy_2026-06-04.sql
```

**Implementation Reference**: `app/Commands/Database/Restore.php`

### 7.2.3 Log Management

**Log Location**: `writable/logs/`

Log files are auto-rotated daily by CI4's logging system.

**Log Level Usage**:
- `critical`: System unusable (DB down, auth failure). Triggers immediate investigation.
- `error`: Runtime failure (Transaction rollback, upload failed).
- `info`: Key business events (User login, pre-notification dispatch, alert generation).

**Example Log Entry** (from `DispatcherService.php`):
```php
log_message('info', "CLEARBAY ALERT SMS: {$unit_id} has been queued at {$hosp_name} for {$diff_minutes} minutes. Please take action.");
```

### 7.2.4 Session Storage

| Component | Configuration |
|-----------|---------------|
| Driver | `DatabaseHandler` |
| Table | `ci_sessions` |
| Data Column | `MEDIUMBLOB` (16MB capacity) |

Sessions survive server restarts as they are database-backed.

### 7.2.5 Migration Management

```bash
# Run all pending migrations
php spark migrate --all

# Rollback last batch
php spark migrate:rollback

# Refresh all migrations (drop all tables and re-run)
php spark migrate:refresh --all
```

### 7.2.6 Module Generation

```bash
# Create a new module with standard MVC-S structure
php spark make:module ModuleName
```

**Reference**: `app/Commands/MakeModule.php`

---

## 7.3 Monitoring & Health Checks

### 7.3.1 Key Health Indicators

| Check | Method | Expected |
|-------|--------|----------|
| Application accessible | HTTP GET `/` | 200 or 302 response |
| Login functional | POST `/login` | 302 redirect to dashboard |
| Hospital queue not empty | GET `/hospital/queue` (as nurse) | JSON with `status: "success"` |
| Dispatcher SSE stream | GET `/dispatcher/sse-updates` (as dispatcher) | `text/event-stream` content type |
| Ambulance GPS tracking | POST `/ambulance/location` (as paramedic) | JSON with `status: "success"` |
| Database connectivity | `php spark db:connect` (if available) | Connection success |

### 7.3.2 Scheduled Tasks (Cron)

| Task | Frequency | Command |
|------|-----------|---------|
| Database backup | Daily | `php spark db:backup` |
| Log rotation | Built-in (CI4) | Automatic |

---

## 7.4 Security Hardening Checklist

| Item | Status | Action |
|------|--------|--------|
| CSRF protection enabled | ✅ Global | `'csrf'` in `Filters.php` globals |
| HTTPS enforced | ⚠️ Available | `ForceHTTPS` filter available, requires server config |
| Session security | ✅ Database-backed | `DatabaseHandler` driver |
| Password hashing | ✅ bcrypt | `PASSWORD_BCRYPT` with salt |
| Input validation | ✅ All POST endpoints | CI4 Validation library |
| Output escaping | ✅ All views | `esc()` function used |
| File permissions | ⚠️ Required | `writable/` must be 755/775 |
| Display errors disabled | ⚠️ Required | Set `CI_ENVIRONMENT = production` |
| Debug toolbar disabled | ⚠️ Required | Auto-disabled in production |

---

*End of Section 7 — System Administration Guide*