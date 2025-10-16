# Hostinger Deployment Guide - Life Atlas Organizer

## Prerequisites
- Hostinger web hosting account (Premium or Business plan recommended)
- PostgreSQL database access
- SSH access (for command line operations)
- Domain name configured

## Step 1: Prepare Your Local Environment

### 1.1 Export Database
```bash
# From your development environment
pg_dump -h $PGHOST -U $PGUSER -d $PGDATABASE -f database_backup.sql
```

### 1.2 Prepare Files
Create a production-ready package:
```bash
# Create deployment package
tar -czf life-atlas-prod.tar.gz \
  --exclude='node_modules' \
  --exclude='.git' \
  --exclude='*.log' \
  --exclude='database*.sql' \
  .
```

## Step 2: Hostinger Setup

### 2.1 Create PostgreSQL Database
1. Login to Hostinger hPanel
2. Navigate to "Databases" → "PostgreSQL"
3. Click "Create Database"
4. Note down:
   - Database name
   - Username
   - Password
   - Host
   - Port (usually 5432)

### 2.2 Import Database Schema
1. In hPanel, go to "PostgreSQL Databases"
2. Click "phpPgAdmin" for your database
3. Select your database → SQL tab
4. Paste contents of `database_complete.sql`
5. Click "Execute"

**OR** via SSH:
```bash
psql -h your_host -U your_user -d your_database -f database_complete.sql
```

## Step 3: File Upload

### 3.1 Via File Manager (GUI Method)
1. Login to hPanel → File Manager
2. Navigate to `public_html` folder
3. Upload `life-atlas-prod.tar.gz`
4. Right-click → Extract

### 3.2 Via SSH (Recommended)
```bash
# Connect to your server
ssh your_username@your_server_ip

# Navigate to public_html
cd public_html

# Upload via SCP (from local machine)
scp life-atlas-prod.tar.gz your_username@your_server:/home/your_username/public_html/

# Extract
tar -xzf life-atlas-prod.tar.gz
rm life-atlas-prod.tar.gz
```

## Step 4: Configuration

### 4.1 Update includes/config.php
```php
<?php
// Database Configuration
define('DB_HOST', 'your_postgresql_host');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASSWORD', 'your_database_password');
define('DB_PORT', '5432');

// App Configuration
define('APP_NAME', 'Life Atlas');
define('SITE_NAME', 'Life Atlas Organizer');
define('APP_URL', 'https://yourdomain.com');

// Security
define('CSRF_TOKEN_EXPIRY', 3600);
define('SESSION_LIFETIME', 86400);

// Email Configuration (SMTP)
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@yourdomain.com');
define('SMTP_PASSWORD', 'your_email_password');
define('SMTP_FROM_EMAIL', 'noreply@yourdomain.com');
define('SMTP_FROM_NAME', 'Life Atlas');

// Telegram (Optional)
define('TELEGRAM_BOT_TOKEN', 'your_bot_token');
define('TELEGRAM_API_URL', 'https://api.telegram.org/bot');

// Paths
define('BACKUP_PATH', __DIR__ . '/../backups/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// File Upload Limits
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);
```

### 4.2 Set Proper Permissions
```bash
# Set directory permissions
chmod 755 public_html
chmod 755 public_html/includes
chmod 755 public_html/api

# Make uploads and backups writable
mkdir -p backups uploads
chmod 777 backups
chmod 777 uploads

# Secure config file
chmod 600 includes/config.php
```

### 4.3 Create .htaccess (if not exists)
Create `public_html/.htaccess`:
```apache
# Enable PHP 8.2
AddHandler application/x-httpd-php82 .php

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</IfModule>

# Prevent directory browsing
Options -Indexes

# Error pages
ErrorDocument 404 /404.php
ErrorDocument 500 /500.php

# URL Rewriting (if needed)
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>

# Block access to sensitive files
<FilesMatch "^(config\.php|\.env|\.git|database.*\.sql)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

## Step 5: Install Composer Dependencies

```bash
# SSH into your server
cd public_html

# Install composer if not available
curl -sS https://getcomposer.org/installer | php

# Install dependencies
php composer.phar install --no-dev --optimize-autoloader

# Or if composer is globally installed
composer install --no-dev --optimize-autoloader
```

## Step 6: Setup Cron Jobs

### 6.1 Access Cron Jobs in hPanel
1. Login to hPanel
2. Navigate to "Advanced" → "Cron Jobs"

### 6.2 Add Cron Jobs

**Daily Backup (Every day at 2 AM)**
```
0 2 * * * /usr/bin/php /home/your_username/public_html/cron/backup.php
```

**Bill Reminders (Every day at 9 AM)**
```
0 9 * * * /usr/bin/php /home/your_username/public_html/cron/bill_reminders.php
```

**Birthday Reminders (Every day at 9 AM)**
```
0 9 * * * /usr/bin/php /home/your_username/public_html/cron/birthday_reminders.php
```

**Subscription Renewals (Every day at 10 AM)**
```
0 10 * * * /usr/bin/php /home/your_username/public_html/cron/subscription_reminders.php
```

**Process Job Queue (Every 5 minutes)**
```
*/5 * * * * /usr/bin/php /home/your_username/public_html/cron/process_jobs.php
```

### 6.3 Verify PHP Path
```bash
which php
# Use the returned path in cron jobs
```

## Step 7: SSL Certificate

### 7.1 Enable SSL in Hostinger
1. Login to hPanel
2. Navigate to "Security" → "SSL"
3. Select your domain
4. Click "Install SSL" (Free Let's Encrypt)

### 7.2 Force HTTPS (Already in .htaccess)
The `.htaccess` file already includes HTTPS redirection.

## Step 8: Testing

### 8.1 Access Your Site
```
https://yourdomain.com
```

### 8.2 Create Admin Account
1. Register first user
2. Manually set as admin in database:
```sql
UPDATE users SET is_admin = TRUE WHERE email = 'your_email@example.com';
```

### 8.3 Test Key Features
- [ ] Login/Registration
- [ ] Add tasks, bills, notes
- [ ] Import/Export functionality
- [ ] Notifications
- [ ] File uploads
- [ ] Cron jobs (check backup folder)

## Step 9: Performance Optimization

### 9.1 Enable OPcache
Add to `php.ini` (via hPanel → PHP Configuration):
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### 9.2 Database Optimization
```sql
-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_tasks_user_status ON tasks(user_id, status);
CREATE INDEX IF NOT EXISTS idx_bills_user_status ON bills(user_id, payment_status);
CREATE INDEX IF NOT EXISTS idx_finance_user_date ON finance(user_id, date);
CREATE INDEX IF NOT EXISTS idx_notifications_user_read ON notifications(user_id, is_read);
```

### 9.3 Enable Gzip Compression
Add to `.htaccess`:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css application/javascript application/json
</IfModule>
```

## Step 10: Monitoring & Maintenance

### 10.1 Setup Error Logging
Create `includes/error_handler.php`:
```php
<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php_errors.log');

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno]: $errstr in $errfile on line $errline");
    return true;
});
```

### 10.2 Monitor Disk Space
```bash
# Check disk usage
du -sh /home/your_username/public_html/*
df -h
```

### 10.3 Database Backups
Automate daily backups via cron (already configured above).

## Troubleshooting

### Issue: 500 Internal Server Error
**Solution:**
1. Check error logs: `tail -f logs/php_errors.log`
2. Verify file permissions
3. Check `.htaccess` syntax

### Issue: Database Connection Failed
**Solution:**
1. Verify credentials in `config.php`
2. Test connection:
```php
pg_connect("host=your_host dbname=your_db user=your_user password=your_pass");
```

### Issue: Composer Dependencies Missing
**Solution:**
```bash
composer install --no-dev
```

### Issue: Cron Jobs Not Running
**Solution:**
1. Check cron logs in hPanel
2. Verify PHP path: `which php`
3. Test manually: `php /path/to/cron/script.php`

## Security Checklist

- [ ] SSL certificate installed and forced
- [ ] Config file permissions set to 600
- [ ] Database credentials secured
- [ ] Directory browsing disabled
- [ ] Error display disabled in production
- [ ] CSRF protection enabled
- [ ] SQL injection prevention (prepared statements)
- [ ] XSS protection (htmlspecialchars)
- [ ] File upload validation
- [ ] Session security configured

## Post-Deployment

1. **Monitor Performance:**
   - Check page load times
   - Monitor database queries
   - Review error logs daily

2. **Regular Updates:**
   - Update dependencies monthly
   - Review security patches
   - Backup before updates

3. **User Training:**
   - Document key features
   - Create user guide
   - Setup support channels

## Support Resources

- Hostinger Support: https://www.hostinger.com/support
- PHP Documentation: https://www.php.net/docs.php
- PostgreSQL Docs: https://www.postgresql.org/docs/

---

**Deployment Complete! 🎉**

Your Life Atlas Organizer is now live and production-ready on Hostinger.
