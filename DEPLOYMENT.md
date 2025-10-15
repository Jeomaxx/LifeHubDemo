# Life Atlas Organizer - Production Deployment Guide

Comprehensive guide for deploying Life Atlas Organizer to Hostinger or any PHP hosting platform with PostgreSQL support.

## ✅ Pre-Deployment Checklist

### Security Configuration
✅ CSRF protection implemented across all forms and API endpoints  
✅ Password hashing with bcrypt  
✅ Prepared SQL statements to prevent SQL injection  
✅ Input sanitization for all user inputs  
✅ Session security configured (httponly cookies)  
✅ CSRF token auto-refresh on expiry  

### Database Setup
✅ PostgreSQL schema created with 20+ tables  
✅ Indexes added for performance  
✅ Foreign key constraints for data integrity  
✅ Cascade deletes configured  

### Features Implemented
✅ User authentication (register, login, logout)  
✅ 16 core modules (Assets, Bills, Birthdays, Finance, Goals, Habits, Health, Hobbies, Investments, Journal, Learning, Media, Subscriptions, Tasks, Cryptocurrency)  
✅ Dashboard with statistics and widgets  
✅ Crypto portfolio with live prices and alerts  
✅ System Management admin panel  
✅ Profile settings and password change  
✅ Backup and restore functionality  
✅ Email notification system (SMTP)  
✅ Telegram notification system (Bot API)  
✅ Dark/Light theme toggle  
✅ Global search functionality  
✅ Responsive mobile design  
✅ Chart.js integration for analytics  
✅ Cron job scripts for automation  

## 🚀 Hostinger Deployment Steps

### 1. Upload Files
Upload all files to your Hostinger account:
- **Via FTP**: Upload to `public_html/` directory
- **Via File Manager**: Upload and extract ZIP file
- **Via Git** (Recommended): Clone repository directly

```bash
cd public_html
git clone https://github.com/yourusername/life-atlas-organizer.git .
```

### 2. Database Configuration

#### Create PostgreSQL Database
1. Go to Hostinger Control Panel → Databases → PostgreSQL
2. Click "Create Database"
3. Note down: database name, username, password, host, port

#### Import Schema
```bash
psql -U your_username -d your_database -h your_host -p 5432 -f database.sql
```

Or use phpPgAdmin in Hostinger control panel.

#### Verify Tables
```bash
psql -U your_username -d your_database -c "\dt"
```

Expected tables: users, assets, bills, finance, crypto_portfolio, crypto_alerts, etc.

### 3. Update Configuration

Edit `includes/config.php`:

```php
define('DB_HOST', 'your-postgres-host');
define('DB_PORT', '5432');
define('DB_NAME', 'your-database-name');
define('DB_USER', 'your-username');
define('DB_PASS', 'your-password');
```

### 4. Set File Permissions

```bash
# Directories: 755
find /path/to/public_html -type d -exec chmod 755 {} \;

# PHP Files: 644
find /path/to/public_html -type f -name "*.php" -exec chmod 644 {} \;

# Cron Scripts: 755
chmod 755 /path/to/public_html/cron/*.php

# Uploads Directory: 755 (writable)
chmod 755 /path/to/public_html/uploads
chmod 755 /path/to/public_html/uploads/backups
```

### 5. Configure Cron Jobs

In Hostinger Control Panel → Advanced → Cron Jobs, add:

#### Crypto Price Fetcher (Every 5 minutes)
```
*/5 * * * * /usr/bin/php /home/username/public_html/cron/cron_fetch_crypto.php >> /home/username/logs/crypto.log 2>&1
```

#### Reminders & Alerts (Every 15 minutes)
```
*/15 * * * * /usr/bin/php /home/username/public_html/cron/reminders.php >> /home/username/logs/reminders.log 2>&1
```

#### Daily Backup (2 AM)
```
0 2 * * * /usr/bin/php /home/username/public_html/cron/backup.php >> /home/username/logs/backup.log 2>&1
```

**Important:** Replace `/home/username/public_html` with your actual path!

### 6. Optional: Email Setup (SMTP)

For email notifications, configure SMTP in `includes/config.php`:

**Gmail:**
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password'); // Not regular password!
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
```

**Hostinger Email:**
```php
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@yourdomain.com');
define('SMTP_PASSWORD', 'your-email-password');
```

### 7. Optional: Telegram Setup

For Telegram notifications:

1. **Create Bot**
   - Open Telegram, search for `@BotFather`
   - Send `/newbot` and follow instructions
   - Save your bot token

2. **Get Chat ID**
   - Start chat with your bot
   - Send any message
   - Visit: `https://api.telegram.org/bot<YOUR_TOKEN>/getUpdates`
   - Find `chat_id` in response

3. **Configure**
   ```php
   define('TELEGRAM_BOT_TOKEN', 'your-bot-token');
   ```
   
4. Users add their chat_id in Profile Settings

### 8. Security Hardening

#### Enable HTTPS

Create/Edit `.htaccess` in root:

```apache
# Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security Headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Disable Directory Listing
Options -Indexes
```

#### Protect Configuration Files

Create `.htaccess` in `includes/` directory:

```apache
Order deny,allow
Deny from all
```

#### Production Settings

In `includes/config.php`:

```php
// Disable error display in production
ini_set('display_errors', 0);
error_reporting(0);

// Secure sessions
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
```

### 9. Post-Deployment Testing

**Test Core Features:**
- [ ] User registration works
- [ ] User login works
- [ ] Dashboard loads correctly
- [ ] All 16 modules accessible
- [ ] Data CRUD operations working
- [ ] Crypto prices updating
- [ ] Alerts triggering correctly

**Test Automation:**
- [ ] Cron jobs running (check logs)
- [ ] Email notifications sending
- [ ] Telegram notifications sending
- [ ] Backups created successfully

**Test Security:**
- [ ] HTTPS enforced
- [ ] Config files protected (try accessing directly)
- [ ] Session secure
- [ ] CSRF protection working

### 10. Create Admin Account

1. Register a regular account
2. Promote to admin via database:

```sql
UPDATE users SET is_admin = TRUE WHERE email = 'your-email@example.com';
```

3. Access System Management at `/system.php`

## 🔧 Troubleshooting

### Database Connection Failed
**Solution:**
- Verify credentials in `includes/config.php`
- Check PostgreSQL service is running
- Verify user has database permissions
- Test connection: `psql -U username -d database -h host`

### Email Not Sending
**Solution:**
- Verify SMTP credentials
- Check port 587 is open
- For Gmail: Use App Password
- Test in System Management panel

### Telegram Not Working
**Solution:**
- Verify bot token is correct
- Ensure user sent message to bot first
- Check chat_id is set in profile
- Test with: `https://api.telegram.org/bot<TOKEN>/getMe`

### Cron Jobs Not Running
**Solution:**
- Verify paths are absolute
- Check PHP path: `which php`
- Review cron logs
- Test scripts manually first
- Ensure execute permissions (755)

### 500 Internal Server Error
**Solution:**
- Check PHP error logs
- Verify file permissions
- Check `.htaccess` syntax
- Enable error display temporarily for debugging

## 📊 Performance Optimization

### PHP Configuration

Add to `.user.ini` or `php.ini`:

```ini
memory_limit = 256M
max_execution_time = 60
upload_max_filesize = 20M
post_max_size = 25M

; Opcache
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
```

### Asset Optimization

Add to `.htaccess`:

```apache
# Gzip Compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>

# Browser Caching
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### Database Optimization

```sql
-- Vacuum and analyze
VACUUM ANALYZE;

-- Verify indexes exist
SELECT tablename, indexname FROM pg_indexes WHERE schemaname = 'public';
```

## 🔒 Security Best Practices

1. **Use HTTPS** - Always enforce SSL/TLS
2. **Strong Passwords** - Use complex, unique passwords
3. **Regular Updates** - Keep PHP and PostgreSQL updated
4. **Backup Regularly** - Automated daily backups
5. **Monitor Logs** - Review error and access logs
6. **Limit Access** - Use firewall rules
7. **Secure Config** - Protect configuration files
8. **Input Validation** - Already implemented in code

## 📦 Backup Strategy

### Automated Backups

Backups run automatically at 2 AM daily via cron.

### Manual Backup

1. Go to System Management → Backups
2. Click "Create Backup Now"
3. Download JSON file
4. Store securely off-server

### Restore Procedure

1. Navigate to Backup section
2. Upload backup JSON file
3. Confirm restore (overwrites data!)
4. Verify data integrity

## 🚨 Production Checklist

Before going live:

- [ ] SSL certificate configured
- [ ] All credentials updated
- [ ] File permissions set
- [ ] Cron jobs configured
- [ ] Email tested
- [ ] Telegram tested (if used)
- [ ] Backups tested
- [ ] Admin account created
- [ ] All modules tested
- [ ] Security headers added
- [ ] Error display disabled
- [ ] Performance optimized
- [ ] Monitoring configured
- [ ] Documentation reviewed

## 📞 Support

For issues:
1. Check this deployment guide
2. Review `README.md`
3. Check System Management logs
4. Review troubleshooting section
5. Verify all configuration settings

---

**Successfully deployed? Access your Life Atlas at `https://yourdomain.com`**

For development guidelines, see `DEVELOPER_NOTE.md`
