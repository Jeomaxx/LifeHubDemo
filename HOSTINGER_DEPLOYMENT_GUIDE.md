# Hostinger Deployment Guide for Life Atlas Organizer

## Prerequisites

Before deploying to Hostinger, ensure you have:
- Hostinger hosting account (Business or Premium plan recommended)
- PHP 8.2+ support enabled
- PostgreSQL database access
- FTP/SFTP credentials
- Domain name configured

## Step 1: Prepare Your Database

### Create PostgreSQL Database on Hostinger

1. Log into Hostinger Control Panel (hPanel)
2. Navigate to **Databases** → **PostgreSQL Databases**
3. Click **Create New Database**
4. Database Details:
   - Database Name: `life_atlas`
   - Username: Create a strong username
   - Password: Generate a secure password
5. Save the credentials securely

### Import Database Schema

1. Access phpPgAdmin from hPanel or use SSH
2. Connect to your database
3. Import the database schema:
   ```bash
   psql -h your-host -U your-username -d life_atlas < database_complete.sql
   ```
4. Verify all 24+ tables are created successfully

## Step 2: Upload Files via FTP

### Using FileZilla or Hostinger File Manager

1. **Connect via FTP/SFTP:**
   - Host: `ftp.yourdomain.com`
   - Username: Your Hostinger FTP username
   - Password: Your FTP password
   - Port: 21 (FTP) or 22 (SFTP)

2. **Upload Files:**
   - Upload all project files to `/public_html/` directory
   - Ensure all folders maintain their structure:
     ```
     /public_html/
     ├── includes/
     ├── api/
     ├── assets/
     ├── cron/
     ├── lang/
     ├── uploads/
     ├── *.php files
     └── .htaccess
     ```

3. **Set Permissions:**
   ```bash
   chmod 755 /public_html/
   chmod 644 /public_html/*.php
   chmod 755 /public_html/uploads/
   chmod 755 /public_html/uploads/backups/
   ```

## Step 3: Configure Environment

### Update Database Configuration

1. Edit `includes/config.php`:
   ```php
   <?php
   // Database Configuration (PostgreSQL)
   define('DB_HOST', 'your-hostinger-db-host');
   define('DB_PORT', '5432');
   define('DB_NAME', 'life_atlas');
   define('DB_USER', 'your-db-username');
   define('DB_PASS', 'your-db-password');
   
   // Application Settings
   define('SITE_NAME', 'Life Atlas Organizer');
   define('SITE_URL', 'https://yourdomain.com');
   
   // Security
   define('SESSION_LIFETIME', 86400);
   define('CSRF_TOKEN_EXPIRY', 3600);
   
   // For production, disable error display
   error_reporting(E_ALL);
   ini_set('display_errors', 0);
   ini_set('log_errors', 1);
   ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
   ?>
   ```

2. Test database connection:
   - Visit: `https://yourdomain.com/health_check.php`
   - Should show "Database: Connected"

## Step 4: Configure .htaccess

Create or update `.htaccess` in `/public_html/`:

```apache
# Rewrite Engine
RewriteEngine On

# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# PHP Settings
php_value upload_max_filesize 50M
php_value post_max_size 50M
php_value max_execution_time 300
php_value memory_limit 256M

# Prevent Directory Listing
Options -Indexes

# Protect sensitive files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

## Step 5: Setup Cron Jobs

### Configure Automated Tasks in Hostinger

1. Go to **Advanced** → **Cron Jobs** in hPanel
2. Create the following cron jobs:

#### Daily Backup (2 AM)
```
0 2 * * * /usr/bin/php /home/username/public_html/cron/backup.php
```

#### Bill Reminders (9 AM)
```
0 9 * * * /usr/bin/php /home/username/public_html/cron/cron_reminders.php
```

#### Crypto Price Update (Every hour)
```
0 * * * * /usr/bin/php /home/username/public_html/cron/cron_fetch_crypto.php
```

#### Bill Worker (Every 15 minutes)
```
*/15 * * * * /usr/bin/php /home/username/public_html/cron/bill_worker.php
```

**Note:** Replace `/home/username/public_html/` with your actual server path.

## Step 6: SSL Certificate

### Enable HTTPS

1. In hPanel, go to **Security** → **SSL/TLS**
2. Select your domain
3. Click **Install SSL Certificate**
4. Choose **Let's Encrypt** (free) or upload custom certificate
5. Wait for activation (usually 5-15 minutes)
6. Verify HTTPS works: `https://yourdomain.com`

## Step 7: Email Configuration (Optional)

### Setup SMTP for Notifications

1. Update `includes/config.php`:
   ```php
   // Email Configuration (SMTP)
   define('SMTP_HOST', 'smtp.hostinger.com');
   define('SMTP_PORT', 587);
   define('SMTP_USERNAME', 'noreply@yourdomain.com');
   define('SMTP_PASSWORD', 'your-email-password');
   define('SMTP_FROM_EMAIL', 'noreply@yourdomain.com');
   define('SMTP_FROM_NAME', SITE_NAME);
   ```

2. Test email functionality in `/settings.php`

## Step 8: Create First Admin User

1. Visit: `https://yourdomain.com/register.php`
2. Create your admin account
3. Login to verify everything works
4. Go to `/admin.php` (if admin features exist)

## Step 9: Production Optimizations

### Install Composer Dependencies

If your hosting supports SSH:
```bash
cd /home/username/public_html
composer install --no-dev --optimize-autoloader
```

### Enable OPcache (Performance)

Add to `.htaccess` or `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### Setup Error Logging

Create logs directory:
```bash
mkdir /home/username/public_html/logs
chmod 755 /home/username/public_html/logs
```

## Step 10: Security Checklist

- [ ] Database credentials secured in config file
- [ ] `.htaccess` properly configured
- [ ] SSL certificate installed and working
- [ ] Error display disabled in production
- [ ] File permissions set correctly (755 for directories, 644 for files)
- [ ] Sensitive directories protected (includes/, cron/)
- [ ] Regular backups scheduled via cron
- [ ] 2FA enabled for admin accounts
- [ ] CSRF protection active on all forms
- [ ] SQL injection prevention (prepared statements)
- [ ] Session security configured

## Step 11: Post-Deployment Testing

### Test All Features

1. **Authentication:**
   - [ ] Register new account
   - [ ] Login/logout
   - [ ] Password reset
   - [ ] 2FA (if enabled)

2. **Core Modules:**
   - [ ] Dashboard loads
   - [ ] Create/edit tasks
   - [ ] Add bills and payments
   - [ ] Track finances
   - [ ] Health & wellness features
   - [ ] Documents upload
   - [ ] AI assistant (if configured)

3. **Notifications:**
   - [ ] Email notifications work
   - [ ] Telegram notifications (if configured)
   - [ ] In-app notifications display

4. **Data Export/Import:**
   - [ ] CSV export works
   - [ ] Excel import works
   - [ ] Backup/restore functions

5. **Performance:**
   - [ ] Page load times < 2 seconds
   - [ ] Charts and analytics render
   - [ ] Mobile responsiveness

## Troubleshooting

### Common Issues

**Issue: White screen / 500 error**
- Check PHP error logs: `/logs/php_errors.log`
- Verify PHP version is 8.2+
- Ensure all required PHP extensions are enabled

**Issue: Database connection failed**
- Verify database credentials in `config.php`
- Check if PostgreSQL service is running
- Confirm database name and user permissions

**Issue: Permission denied errors**
- Set correct file permissions:
  ```bash
  find /public_html -type d -exec chmod 755 {} \;
  find /public_html -type f -exec chmod 644 {} \;
  ```

**Issue: Cron jobs not running**
- Check cron job syntax in Hostinger panel
- Verify PHP path: `which php` via SSH
- Check cron job logs in Hostinger

**Issue: Email not sending**
- Verify SMTP credentials
- Check if port 587 is open
- Test with Hostinger's SMTP server

## Maintenance

### Regular Tasks

**Daily:**
- Monitor error logs
- Check backup status

**Weekly:**
- Review database performance
- Update dependencies (if needed)
- Check disk space usage

**Monthly:**
- Security updates
- Database optimization
- Performance review

## Support Resources

- Hostinger Knowledge Base: https://support.hostinger.com
- PHP Documentation: https://www.php.net/docs.php
- PostgreSQL Docs: https://www.postgresql.org/docs/

## Production Checklist

Before going live:
- [ ] All database tables created
- [ ] Files uploaded and permissions set
- [ ] .htaccess configured
- [ ] SSL certificate active
- [ ] Cron jobs scheduled
- [ ] Email notifications working
- [ ] Error logging enabled
- [ ] Backups configured
- [ ] Admin account created
- [ ] All features tested
- [ ] Security measures in place
- [ ] Performance optimized

---

**Deployment Complete!** Your Life Atlas Organizer is now live on Hostinger.

For support, contact your system administrator or refer to the technical documentation.
