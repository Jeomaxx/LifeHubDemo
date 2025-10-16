# Complete Setup & Deployment Guide

Comprehensive guide for setting up and deploying LifeHub on Replit and Hostinger.

## Table of Contents
1. [Quick Start (Replit)](#quick-start-replit)
2. [Production Deployment (Hostinger)](#production-deployment-hostinger)
3. [Feature Configuration](#feature-configuration)
4. [Testing](#testing)
5. [Troubleshooting](#troubleshooting)

---

## Quick Start (Replit)

### Prerequisites
- Replit account
- Basic understanding of PHP and databases

### Step 1: Initial Setup

1. **Database is already configured!** ✓
   - PostgreSQL database is provisioned automatically
   - Environment variables are set

2. **Install Dependencies** (if needed)
   ```bash
   composer install
   ```

3. **Import Database Schema** (if not already done)
   ```bash
   psql $DATABASE_URL < database.sql
   ```

### Step 2: Create Admin User

1. Visit `/register.php`
2. Create your first account
3. Make yourself admin via SQL:
   ```bash
   psql $DATABASE_URL -c "UPDATE users SET is_admin = true WHERE email = 'your-email@example.com';"
   ```

4. Assign admin role:
   ```bash
   psql $DATABASE_URL -c "INSERT INTO user_roles (user_id, role_id) SELECT id, (SELECT id FROM roles WHERE name = 'admin') FROM users WHERE email = 'your-email@example.com';"
   ```

### Step 3: Configure Integrations (Optional)

See [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md) for detailed setup:
- SMTP Email
- Telegram Bot
- Google OAuth
- CoinGecko API

### Step 4: Set Up Cron Jobs

Add secrets for cron job worker:
```bash
# Add to Replit Secrets or create .replit file
[unitTest]
language = "bash"

[[ports]]
localPort = 5000
externalPort = 80
```

Configure workflows to run job worker:
```bash
*/5 * * * * php cron/job_worker.php 10
*/15 * * * * php cron/reminders.php
0 2 * * * php cron/backup.php
```

### Step 5: Test the System

Run automated tests:
```bash
php tests/system_test.php
```

Check health endpoint:
```bash
curl https://your-repl.repl.co/health_check.php
```

---

## Production Deployment (Hostinger)

### Prerequisites
- Hostinger hosting account with PHP 8.2+ support
- PostgreSQL database access
- SSH access (recommended)
- Domain name

### Step 1: Prepare Files

1. **Download your Repl or clone from Git**
   ```bash
   git clone your-repo-url
   cd your-project
   ```

2. **Remove development files**
   ```bash
   rm -rf .replit replit.nix
   ```

3. **Update `.gitignore`** (ensure these are included)
   ```
   vendor/
   .env
   uploads/backups/*.json
   ```

### Step 2: Database Setup

1. **Create PostgreSQL Database**
   - Log into Hostinger cPanel
   - Go to "PostgreSQL Databases"
   - Create new database
   - Note credentials

2. **Import Schema**
   ```bash
   psql -h hostname -U username -d database_name < database.sql
   ```

3. **Update Configuration**
   - Edit `includes/config.php` with your database credentials
   - Or create `.env` file

### Step 3: Upload Files

1. **Via FTP/SFTP**
   - Upload all files to `public_html` or subdomain folder
   - Ensure proper file permissions (644 for files, 755 for directories)

2. **Via Git (Recommended)**
   ```bash
   cd /home/username/public_html
   git clone your-repo-url .
   composer install --no-dev
   ```

3. **Set Permissions**
   ```bash
   chmod 755 uploads
   chmod 755 uploads/backups
   chmod 755 uploads/csv
   ```

### Step 4: Configure Environment

1. **Create `.env` file** (if not using config.php)
   ```bash
   DB_HOST=your-db-host
   DB_PORT=5432
   DB_NAME=your-db-name
   DB_USER=your-db-user
   DB_PASS=your-db-password
   
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=587
   SMTP_USERNAME=your-email@gmail.com
   SMTP_PASSWORD=your-app-password
   
   TELEGRAM_BOT_TOKEN=your-bot-token
   
   GOOGLE_CLIENT_ID=your-client-id
   GOOGLE_CLIENT_SECRET=your-client-secret
   ```

2. **Update `includes/config.php`**
   - Set `SITE_URL` to your domain
   - Enable session security (set cookie_secure to 1)
   - Disable error display for production

### Step 5: Configure Cron Jobs

1. **Access cPanel → Cron Jobs**

2. **Add these cron jobs:**

   **Job Worker (Every 5 minutes)**
   ```bash
   */5 * * * * /usr/bin/php /home/username/public_html/cron/job_worker.php 10 >> /home/username/logs/job_worker.log 2>&1
   ```

   **Crypto Price Fetcher (Every 5 minutes)**
   ```bash
   */5 * * * * /usr/bin/php /home/username/public_html/cron/cron_fetch_crypto.php >> /home/username/logs/crypto.log 2>&1
   ```

   **Reminders (Every 15 minutes)**
   ```bash
   */15 * * * * /usr/bin/php /home/username/public_html/cron/reminders.php >> /home/username/logs/reminders.log 2>&1
   ```

   **Daily Backup (2 AM)**
   ```bash
   0 2 * * * /usr/bin/php /home/username/public_html/cron/backup.php >> /home/username/logs/backup.log 2>&1
   ```

   **Note:** Replace `/home/username/public_html` with your actual path!

### Step 6: SSL Certificate

1. **Enable HTTPS**
   - Hostinger provides free SSL (Let's Encrypt)
   - Go to cPanel → SSL/TLS
   - Enable AutoSSL

2. **Force HTTPS**
   - Add to `.htaccess`:
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

### Step 7: Create Admin Account

1. Visit `https://yourdomain.com/register.php`
2. Create account
3. Via SSH or cPanel phpPgAdmin:
   ```sql
   UPDATE users SET is_admin = true WHERE email = 'your-email@example.com';
   
   INSERT INTO user_roles (user_id, role_id)
   SELECT id, (SELECT id FROM roles WHERE name = 'admin')
   FROM users WHERE email = 'your-email@example.com';
   ```

### Step 8: Verify Deployment

1. **Access your site:** `https://yourdomain.com`
2. **Check health:** `https://yourdomain.com/health_check.php`
3. **Test features:**
   - Login/Register
   - Google OAuth (if configured)
   - Email notifications
   - Telegram notifications
   - CSV import
   - Job queue

---

## Feature Configuration

### Enable Google OAuth

1. Follow [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md)
2. Update OAuth redirect URI to your production domain
3. Add credentials to `.env` or Replit Secrets

### Configure Background Jobs

The job queue system handles:
- CSV imports with progress tracking
- Email sending
- Report generation
- Data backups

**Monitor jobs:**
- Via API: `/api/jobs.php?action=list`
- System Management → Job Queue (coming soon)

### Set Up Notifications

**Email:**
- Configure SMTP settings
- Test: System Management → Configuration → Email

**Telegram:**
- Create bot via @BotFather
- Get chat ID
- Configure in profile settings
- Test: System Management → Configuration → Telegram

### Role-Based Access Control

**Default Roles:**
- `admin` - Full access
- `user` - Standard access
- `editor` - Content management
- `viewer` - Read-only

**Assign roles via SQL:**
```sql
INSERT INTO user_roles (user_id, role_id)
SELECT user_id, role_id FROM ...
```

---

## Testing

### Automated Tests

Run the test suite:
```bash
php tests/system_test.php
```

Expected output:
```
=================================================
LIFEHUB SYSTEM TESTS
=================================================

Testing Database Connection...
  ✓ Database query executed
  ✓ Database tables exist
  ✓ Table 'users' exists
  ...

RESULTS: 20 passed, 0 failed
=================================================
```

### Manual Testing Checklist

- [ ] User registration and login
- [ ] Google OAuth login (if configured)
- [ ] Dashboard loads correctly
- [ ] Create finance transaction
- [ ] Create task
- [ ] Create goal
- [ ] CSV import works
- [ ] Job queue processes jobs
- [ ] Email notifications work
- [ ] Telegram notifications work
- [ ] Pagination works on lists
- [ ] Backup creation works
- [ ] Data export works

### Health Check

Access `/health_check.php` to verify:
- Database connection
- File system permissions
- PHP extensions
- Integration configurations

---

## Troubleshooting

### Database Connection Errors

**Error:** "Connection refused"
- Check database credentials in `config.php`
- Verify PostgreSQL is running
- Check firewall allows connection

**Error:** "Table not found"
- Import schema: `psql $DATABASE_URL < database.sql`
- Verify tables exist: `psql $DATABASE_URL -c "\dt"`

### 500 Internal Server Error

1. **Check PHP error logs:**
   ```bash
   tail -f /var/log/apache2/error.log
   # or
   tail -f ~/logs/error.log
   ```

2. **Enable error display temporarily:**
   ```php
   // In includes/config.php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

3. **Common causes:**
   - Missing PHP extensions
   - File permission issues
   - Syntax errors
   - Missing dependencies

### Cron Jobs Not Running

1. **Check cron is enabled:**
   ```bash
   crontab -l
   ```

2. **Check cron logs:**
   ```bash
   tail -f ~/logs/job_worker.log
   ```

3. **Test manually:**
   ```bash
   php /path/to/cron/job_worker.php
   ```

### OAuth Not Working

**Google OAuth:**
- Verify redirect URI matches exactly
- Check Client ID and Secret
- Ensure Google+ API is enabled
- Clear browser cookies

### Upload Errors

1. **Check directory permissions:**
   ```bash
   chmod 755 uploads
   chmod 755 uploads/backups
   chmod 755 uploads/csv
   ```

2. **Check PHP settings:**
   ```php
   // php.ini
   upload_max_filesize = 50M
   post_max_size = 50M
   ```

### Performance Issues

1. **Enable opcache:**
   ```ini
   ; php.ini
   opcache.enable=1
   opcache.memory_consumption=128
   ```

2. **Add database indexes** (if needed):
   ```sql
   CREATE INDEX idx_user_id ON finance(user_id);
   CREATE INDEX idx_created_at ON jobs(created_at);
   ```

3. **Use pagination:**
   - All data tables support pagination
   - Adjust `per_page` parameter

---

## Maintenance

### Regular Tasks

**Daily:**
- Check health endpoint
- Review error logs
- Monitor backup completion

**Weekly:**
- Review job queue status
- Check disk space usage
- Update security patches

**Monthly:**
- Rotate API keys
- Clean old backups
- Review user access roles

### Backup Strategy

**Automatic Backups:**
- Run daily at 2 AM via cron
- Stored in `uploads/backups/`
- Retention: 30 days

**Manual Backup:**
```bash
php cron/backup.php
```

**Database Backup:**
```bash
pg_dump -h hostname -U username database_name > backup_$(date +%Y%m%d).sql
```

### Updates

1. **Backup first:**
   ```bash
   php cron/backup.php
   pg_dump database > backup.sql
   ```

2. **Pull latest code:**
   ```bash
   git pull origin main
   composer install --no-dev
   ```

3. **Run migrations if needed:**
   ```bash
   psql $DATABASE_URL < migrations/latest.sql
   ```

4. **Test thoroughly**

---

## Security Checklist

- [ ] HTTPS enabled with valid SSL
- [ ] All secrets in environment variables
- [ ] Error display disabled in production
- [ ] Session cookies set to httponly and secure
- [ ] File upload validation enabled
- [ ] Rate limiting configured
- [ ] CSRF protection enabled
- [ ] SQL injection prevention (prepared statements)
- [ ] XSS prevention (input sanitization)
- [ ] Regular backups automated
- [ ] Access logs monitored

---

## Support Resources

- **Documentation:** [README.md](README.md)
- **Integration Guide:** [INTEGRATION_GUIDE.md](INTEGRATION_GUIDE.md)
- **Security Guide:** [SECURITY.md](SECURITY.md)
- **Health Check:** `/health_check.php`
- **System Tests:** `php tests/system_test.php`

---

## Performance Optimization

### Database

1. **Add indexes for frequently queried columns:**
   ```sql
   CREATE INDEX IF NOT EXISTS idx_finance_user_date ON finance(user_id, date DESC);
   CREATE INDEX IF NOT EXISTS idx_tasks_user_status ON tasks(user_id, status);
   ```

2. **Use pagination for large datasets**

3. **Enable query caching** (if supported)

### PHP

1. **Enable OPcache:**
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   opcache.interned_strings_buffer=8
   opcache.max_accelerated_files=10000
   ```

2. **Use APCu for data caching** (optional)

### Frontend

1. **Enable Gzip compression:**
   ```apache
   # .htaccess
   <IfModule mod_deflate.c>
     AddOutputFilterByType DEFLATE text/html text/plain text/css text/javascript application/javascript
   </IfModule>
   ```

2. **Browser caching:**
   ```apache
   <IfModule mod_expires.c>
     ExpiresActive On
     ExpiresByType image/jpg "access plus 1 year"
     ExpiresByType text/css "access plus 1 month"
   </IfModule>
   ```

---

## Conclusion

Your LifeHub installation is now complete! 🎉

For additional help:
1. Check the documentation files
2. Review logs in `/logs` or `~/logs`
3. Use the health check endpoint
4. Run automated tests

Happy organizing! 📊
