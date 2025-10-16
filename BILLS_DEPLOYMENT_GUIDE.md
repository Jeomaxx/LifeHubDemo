# Bills Module - Production Deployment Guide for Hostinger

## Prerequisites

- Hostinger shared hosting or VPS plan
- PHP 8.2+ support
- PostgreSQL database
- SSH access (recommended)
- cPanel or similar control panel access

---

## Step 1: Database Setup

### 1.1 Create PostgreSQL Database

Via cPanel:
1. Navigate to **PostgreSQL Databases**
2. Create a new database: `lifeatlas_db`
3. Create a database user with secure password
4. Assign user to database with ALL PRIVILEGES

### 1.2 Import Database Schema

Upload `database.sql` to your server and import:

```bash
# Via SSH
psql -h localhost -U your_db_user -d lifeatlas_db -f database.sql

# Via phpPgAdmin or cPanel
# Use the import feature to upload database.sql
```

### 1.3 Verify Bills Tables

Check that these tables exist:
```sql
SELECT table_name FROM information_schema.tables 
WHERE table_schema = 'public' 
AND table_name IN ('bills', 'bill_payments');
```

---

## Step 2: File Upload

### 2.1 Upload Application Files

Using FTP/SFTP client (FileZilla, etc.):

1. Upload all PHP files to `public_html/` or your domain root
2. Ensure correct file structure:
```
public_html/
├── api/
│   ├── bills.php
│   ├── bills_import.php
│   ├── bills_calendar.php
│   ├── bills_budget_impact.php
│   └── bills_export.php
├── cron/
│   └── bill_worker.php
├── includes/
├── bills.php
└── ...other files
```

### 2.2 Set File Permissions

```bash
# Directories: 755
find . -type d -exec chmod 755 {} \;

# PHP files: 644
find . -type f -name "*.php" -exec chmod 644 {} \;

# Cron scripts: 755 (executable)
chmod 755 cron/bill_worker.php
```

---

## Step 3: Configuration

### 3.1 Database Configuration

Edit `includes/config.php`:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'lifeatlas_db');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_secure_password');
define('DB_PORT', '5432');

// Site URL
define('SITE_URL', 'https://yourdomain.com');

// Email Settings (for notifications)
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@yourdomain.com');
define('SMTP_PASS', 'your_email_password');
define('SMTP_FROM', 'noreply@yourdomain.com');
define('SMTP_FROM_NAME', 'Life Atlas Organizer');

// Telegram Bot (optional)
define('TELEGRAM_BOT_TOKEN', 'your_bot_token');
```

### 3.2 Environment Variables (Alternative)

For better security, use .env file (not in public_html):

```bash
# Create .env file outside web root
nano /home/username/.env
```

```env
DB_HOST=localhost
DB_NAME=lifeatlas_db
DB_USER=your_db_user
DB_PASS=your_secure_password
SMTP_HOST=smtp.hostinger.com
SMTP_USER=noreply@yourdomain.com
SMTP_PASS=your_email_password
TELEGRAM_BOT_TOKEN=your_bot_token
```

Update config.php to load from .env:
```php
<?php
$envFile = '/home/username/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && $line[0] !== '#') {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME'));
// ...etc
```

---

## Step 4: Cron Job Setup

### 4.1 Configure Cron via cPanel

1. Navigate to **Advanced** → **Cron Jobs**
2. Add new cron job:

**Frequency:** Every 15 minutes
```
*/15 * * * *
```

**Command:**
```bash
/usr/bin/php /home/username/public_html/cron/bill_worker.php >> /home/username/logs/bill_worker.log 2>&1
```

Alternative commands:
```bash
# If php binary is in different location
/usr/local/bin/php8.2 /home/username/public_html/cron/bill_worker.php

# With explicit path
php -f /home/username/public_html/cron/bill_worker.php
```

### 4.2 Create Log Directory

```bash
mkdir -p /home/username/logs
chmod 755 /home/username/logs
```

### 4.3 Test Cron Job Manually

```bash
# SSH into server
ssh username@your-server.com

# Run manually
php /home/username/public_html/cron/bill_worker.php

# Check output
cat /home/username/logs/bill_worker.log
```

Expected output:
```
=== Bill Worker Started at 2025-01-14 10:00:00 ===
1. Checking for bills needing reminders...
   Sent 0 reminders
2. Marking overdue bills...
   Marked 0 bills as overdue
...
```

---

## Step 5: Email & Notification Setup

### 5.1 SMTP Configuration (Hostinger)

Hostinger SMTP Settings:
- **Server:** smtp.hostinger.com
- **Port:** 587 (TLS) or 465 (SSL)
- **Username:** Your email address
- **Password:** Your email password
- **Authentication:** Required

### 5.2 Test Email Sending

Create test file `test_email.php`:
```php
<?php
require_once 'includes/config.php';
require_once 'includes/notifications.php';

$result = sendEmailNotification(
    'youremail@example.com',
    'Test Email',
    'This is a test email from Life Atlas'
);

echo $result ? 'Email sent successfully' : 'Failed to send email';
```

Run via browser or CLI:
```bash
php test_email.php
```

### 5.3 Telegram Bot Setup (Optional)

1. Create bot via @BotFather on Telegram
2. Get bot token
3. Add token to config.php
4. Get chat ID by messaging bot and visiting:
   ```
   https://api.telegram.org/bot<YOUR_TOKEN>/getUpdates
   ```

---

## Step 6: Security Hardening

### 6.1 Protect Sensitive Directories

Create `.htaccess` in `/cron/`:
```apache
Order Deny,Allow
Deny from all
```

Create `.htaccess` in `/includes/`:
```apache
Order Deny,Allow
Deny from all
```

### 6.2 Disable Directory Listing

Add to root `.htaccess`:
```apache
Options -Indexes
```

### 6.3 Rate Limiting (if not using Cloudflare)

Add to `.htaccess`:
```apache
# Limit requests
<IfModule mod_ratelimit.c>
    SetOutputFilter RATE_LIMIT
    SetEnv rate-limit 400
</IfModule>
```

### 6.4 SSL/HTTPS

1. Enable SSL certificate in cPanel (free Let's Encrypt)
2. Force HTTPS redirect:

Add to `.htaccess`:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## Step 7: Testing & Verification

### 7.1 Access Checklist

- [ ] Website loads: `https://yourdomain.com`
- [ ] Login page works: `https://yourdomain.com/login.php`
- [ ] Bills page loads: `https://yourdomain.com/bills.php`

### 7.2 Functionality Tests

1. **Create Bill:**
   - Navigate to Bills
   - Click "Add Bill"
   - Fill form and submit
   - Verify bill appears in list

2. **Mark as Paid:**
   - Click mark paid icon
   - Verify bill status changes
   - Check payment recorded in database

3. **CSV Import:**
   - Prepare test CSV file
   - Use import function
   - Verify bills imported

4. **Calendar Export:**
   - Click export calendar
   - Verify .ics file downloads
   - Import to calendar app

5. **Budget Impact:**
   - Link bill to budget
   - Verify budget warning displays

### 7.3 Cron Job Verification

1. Wait for cron to run (15 minutes)
2. Check log file:
```bash
cat /home/username/logs/bill_worker.log
```

3. Verify reminders sent:
   - Check email inbox
   - Check Telegram (if configured)

### 7.4 Database Verification

```sql
-- Check bills created
SELECT COUNT(*) FROM bills;

-- Check payments recorded
SELECT COUNT(*) FROM bill_payments;

-- Check overdue bills
SELECT COUNT(*) FROM bills 
WHERE payment_status != 'paid' 
AND due_date < CURRENT_DATE;
```

---

## Step 8: Performance Optimization

### 8.1 Enable OPcache

Add to `php.ini` (or contact support):
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### 8.2 Database Indexing

Verify indexes exist:
```sql
SELECT indexname FROM pg_indexes 
WHERE tablename IN ('bills', 'bill_payments');
```

### 8.3 Caching (Optional)

If using Cloudflare:
1. Enable Cloudflare
2. Set cache rules for static assets
3. Exclude API endpoints from cache

---

## Step 9: Monitoring & Maintenance

### 9.1 Set Up Error Logging

Create error log handler in `includes/config.php`:
```php
ini_set('log_errors', 1);
ini_set('error_log', '/home/username/logs/php_errors.log');
```

### 9.2 Monitor Cron Job

Add to cron:
```bash
# Send daily summary email
0 8 * * * /usr/bin/php /home/username/scripts/daily_summary.php
```

### 9.3 Database Backup

Automated backup cron:
```bash
# Daily backup at 2 AM
0 2 * * * pg_dump -h localhost -U db_user lifeatlas_db > /home/username/backups/db_$(date +\%Y\%m\%d).sql
```

### 9.4 Disk Space Monitoring

```bash
# Check disk usage weekly
0 0 * * 0 df -h | mail -s "Disk Space Report" admin@yourdomain.com
```

---

## Step 10: Troubleshooting

### Common Issues

#### Issue: Bills not showing
- **Check:** Database connection in config.php
- **Verify:** Run query: `SELECT * FROM bills LIMIT 1;`
- **Solution:** Check credentials, ensure PostgreSQL is running

#### Issue: Cron not running
- **Check:** Cron logs: `grep CRON /var/log/syslog`
- **Verify:** PHP path: `which php`
- **Solution:** Update cron command with correct PHP path

#### Issue: Reminders not sending
- **Check:** Email configuration in config.php
- **Test:** Run `test_email.php`
- **Solution:** Verify SMTP credentials, check spam folder

#### Issue: CSV import fails
- **Check:** File upload limits in php.ini
- **Verify:** `upload_max_filesize` and `post_max_size`
- **Solution:** Increase limits or split CSV into smaller files

#### Issue: Slow performance
- **Check:** Database indexes
- **Enable:** OPcache
- **Optimize:** Queries with EXPLAIN ANALYZE

---

## Support Contacts

### Hostinger Support
- Live Chat: Available 24/7 in cPanel
- Email: support@hostinger.com
- Knowledge Base: https://support.hostinger.com

### System Logs Locations
- PHP Errors: `/home/username/logs/php_errors.log`
- Cron Logs: `/home/username/logs/bill_worker.log`
- Apache Errors: `/home/username/logs/error_log`
- PostgreSQL: `/var/log/postgresql/`

---

## Security Checklist

- [ ] Database credentials secured
- [ ] .env file outside public_html
- [ ] Directory listing disabled
- [ ] HTTPS enforced
- [ ] Sensitive directories protected
- [ ] File permissions set correctly
- [ ] Rate limiting configured
- [ ] Error logging enabled (not displayed)
- [ ] Regular backups scheduled
- [ ] CSRF protection active
- [ ] SQL injection prevention (prepared statements)
- [ ] Input sanitization implemented

---

## Production Readiness Checklist

- [ ] Database migrated and verified
- [ ] All files uploaded with correct permissions
- [ ] Configuration completed (DB, SMTP, Telegram)
- [ ] Cron job configured and tested
- [ ] Email notifications working
- [ ] Bills CRUD operations tested
- [ ] CSV import/export tested
- [ ] Calendar export tested
- [ ] Budget impact warnings tested
- [ ] Security hardening applied
- [ ] SSL certificate installed
- [ ] Error logging configured
- [ ] Backups scheduled
- [ ] Performance optimized
- [ ] Documentation accessible to team

---

## Quick Reference Commands

### Database
```bash
# Connect to database
psql -h localhost -U db_user -d lifeatlas_db

# Backup database
pg_dump -h localhost -U db_user lifeatlas_db > backup.sql

# Restore database
psql -h localhost -U db_user -d lifeatlas_db < backup.sql
```

### Cron
```bash
# List cron jobs
crontab -l

# Edit cron jobs
crontab -e

# View cron logs
grep CRON /var/log/syslog
```

### File Management
```bash
# Set ownership
chown -R username:username /home/username/public_html

# Set permissions
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# Check disk usage
du -sh /home/username/*
```

---

## Version Information

- **Bills Module Version:** 1.0.0
- **PHP Requirement:** 8.2+
- **PostgreSQL Requirement:** 12+
- **Recommended Hostinger Plan:** Business or higher

Last Updated: January 2025
