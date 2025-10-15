# Life Atlas Organizer - Production Deployment Guide

A comprehensive personal life management platform that centralizes 16+ core life modules into a single secure dashboard with cryptocurrency tracking, analytics, automated backups, and real-time notifications.

## 🚀 Quick Start

### Prerequisites
- PHP 8.2 or higher
- PostgreSQL database
- Web server (Apache/Nginx) or PHP built-in server
- Cron job access (for automation)

### Installation Steps

#### 1. Upload Files to Hostinger

Upload all files to your `public_html` directory (or subdirectory):

```bash
/public_html/
├── includes/
├── api/
├── assets/
├── cron/
├── index.php
├── login.php
├── dashboard.php
└── [other files]
```

#### 2. Database Setup

**Using Hostinger phpPgAdmin:**

1. Go to Hostinger Control Panel → Databases → PostgreSQL
2. Create a new database (e.g., `life_atlas_db`)
3. Note your database credentials
4. Import `database.sql` through phpPgAdmin or command line:

```bash
psql -U your_username -d life_atlas_db -h your_host -p 5432 -f database.sql
```

#### 3. Configure Database Connection

Edit `includes/config.php` and update these constants:

```php
define('DB_HOST', 'your-postgres-host');
define('DB_PORT', '5432');
define('DB_NAME', 'life_atlas_db');
define('DB_USER', 'your-username');
define('DB_PASS', 'your-password');
```

#### 4. Set File Permissions

```bash
chmod 755 /path/to/public_html
chmod 644 *.php
chmod 755 cron/*.php
chmod 755 uploads/backups
```

#### 5. Access Your Application

Navigate to: `https://yourdomain.com`

Create your admin account through the registration page, then promote to admin:

```sql
UPDATE users SET is_admin = TRUE WHERE email = 'your-email@example.com';
```

## ⚙️ Cron Jobs Configuration

### Required Cron Jobs for Hostinger

Add these cron jobs in Hostinger Control Panel → Advanced → Cron Jobs:

#### 1. Cryptocurrency Price Fetcher (Every 5 minutes)
```bash
*/5 * * * * /usr/bin/php /home/username/public_html/cron/cron_fetch_crypto.php >> /home/username/logs/crypto.log 2>&1
```

#### 2. Reminders & Alerts (Every 15 minutes)
```bash
*/15 * * * * /usr/bin/php /home/username/public_html/cron/reminders.php >> /home/username/logs/reminders.log 2>&1
```

#### 3. Automated Backups (Daily at 2 AM)
```bash
0 2 * * * /usr/bin/php /home/username/public_html/cron/backup.php >> /home/username/logs/backup.log 2>&1
```

**Important:** Replace `/home/username/public_html` with your actual path!

### Testing Cron Jobs

Test cron jobs manually before scheduling:

```bash
php /path/to/public_html/cron/cron_fetch_crypto.php
php /path/to/public_html/cron/reminders.php
php /path/to/public_html/cron/backup.php
```

## 📧 Email Configuration (SMTP)

### Gmail Setup

1. Enable 2-Factor Authentication in your Google account
2. Generate an App Password: https://myaccount.google.com/apppasswords
3. Update `includes/config.php`:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'Life Atlas Organizer');
```

### Other SMTP Providers

**Hostinger Email:**
```php
define('SMTP_HOST', 'smtp.hostinger.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@yourdomain.com');
define('SMTP_PASSWORD', 'your-email-password');
```

**SendGrid:**
```php
define('SMTP_HOST', 'smtp.sendgrid.net');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'apikey');
define('SMTP_PASSWORD', 'your-sendgrid-api-key');
```

### Testing Email

Navigate to System Management → Configuration → Test Email

## 📱 Telegram Integration

### Setup Telegram Bot

1. **Create a Bot**
   - Open Telegram and search for `@BotFather`
   - Send `/newbot` command
   - Follow instructions and note your **Bot Token**

2. **Get Your Chat ID**
   - Start a chat with your bot
   - Send any message to the bot
   - Visit: `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates`
   - Find your `chat_id` in the JSON response (inside `message.from.id`)

3. **Configure in Life Atlas**
   
   Edit `includes/config.php`:
   ```php
   define('TELEGRAM_BOT_TOKEN', '123456789:ABCdefGHIjklMNOpqrsTUVwxyz');
   ```
   
   Then add your Chat ID in Profile Settings → Telegram Chat ID

### Testing Telegram

Navigate to System Management → Configuration → Test Telegram

## 🔐 Security Best Practices

### Production Configuration

1. **Disable Debug Mode**
   ```php
   // In includes/config.php
   ini_set('display_errors', 0);
   error_reporting(0);
   ```

2. **Enable HTTPS**
   - Get SSL certificate from Hostinger (free with Let's Encrypt)
   - Force HTTPS redirect in `.htaccess`:
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

3. **Secure Sensitive Files**
   
   Create `.htaccess` in `includes/` directory:
   ```apache
   Order deny,allow
   Deny from all
   ```

4. **Change Default Credentials**
   - Update all default database passwords
   - Use strong, unique passwords
   - Enable 2FA for admin accounts (when implemented)

5. **Regular Backups**
   - Enable auto-backups in System Management
   - Download backups regularly
   - Test restore procedures

### File Permissions Checklist

```bash
Directories: 755
PHP Files: 644
Cron Scripts: 755
Config Files: 644
Backup Directory: 755 (writable)
```

## 🎨 Features Overview

### Core Modules (16+)

1. **Dashboard** - Centralized overview with statistics
2. **Assets** - Item tracking with values and categories
3. **Bills** - Recurring bill management
4. **Birthdays** - Never forget important dates
5. **Finance** - Income/expense tracking with analytics
6. **Goals** - Personal goal setting and tracking
7. **Habits** - Daily habit tracking with streaks
8. **Health** - Medical records, exercise, vitals
9. **Hobbies** - Hobby time and resource management
10. **Investments** - Portfolio tracking with ROI
11. **Journal** - Daily journaling with mood tracking
12. **Learning** - Course and book progress
13. **Media** - Watchlist management
14. **Subscriptions** - Subscription tracking with alerts
15. **Tasks** - To-do list with priorities and due dates
16. **Cryptocurrency** - Live price tracking, alerts, portfolio

### Advanced Features

- **Real-time Crypto Prices** - CoinGecko API integration
- **Price Alerts** - Email & Telegram notifications
- **Automated Backups** - Daily scheduled backups
- **Multi-channel Notifications** - Email, Telegram, in-app
- **CSV Import/Export** - Data portability
- **System Management** - Admin dashboard for monitoring
- **Analytics & Charts** - Visual insights with Chart.js
- **Dark/Light Theme** - User preference toggle
- **Mobile Responsive** - Works on all devices

## 🔧 Troubleshooting

### Database Connection Issues

**Error:** "Could not connect to database"

**Solution:**
1. Verify credentials in `includes/config.php`
2. Check if PostgreSQL service is running
3. Ensure database user has proper permissions
4. Test connection:
   ```php
   php -r "new PDO('pgsql:host=HOST;dbname=DB', 'USER', 'PASS');"
   ```

### Email Not Sending

**Error:** "Failed to send email"

**Solution:**
1. Verify SMTP credentials
2. Check if port 587 is open on your server
3. For Gmail: Use App Password, not regular password
4. Check email logs in System Management

### Telegram Notifications Not Working

**Error:** "Telegram message failed"

**Solution:**
1. Verify bot token is correct
2. Ensure you've sent a message to your bot first
3. Check chat_id is properly set in profile
4. Test with: `https://api.telegram.org/bot<TOKEN>/getMe`

### Cron Jobs Not Running

**Error:** "Prices not updating / Alerts not sent"

**Solution:**
1. Verify cron job paths are absolute
2. Check PHP path with: `which php`
3. Review cron logs in `/home/username/logs/`
4. Test cron scripts manually first
5. Ensure scripts have execute permissions (755)

### Permission Denied Errors

**Error:** "Permission denied on backup directory"

**Solution:**
```bash
chmod 755 uploads/backups
chown username:username uploads/backups
```

## 📊 System Management

Access admin panel at: `/system.php` (requires admin privileges)

### Features

- **System Diagnostics** - PHP version, disk usage, memory
- **Backup Management** - Create, download, restore backups
- **Cron Monitoring** - Test and verify cron jobs
- **Configuration** - SMTP & Telegram setup
- **System Logs** - View application logs
- **Maintenance Mode** - Disable access temporarily

## 🔄 Backup & Restore

### Manual Backup

1. Navigate to System Management → Backups
2. Click "Create Backup Now"
3. Download the JSON backup file
4. Store securely off-server

### Automated Backups

- Configured in System Management
- Runs daily at 2 AM via cron
- Retains last 30 days (configurable)
- Stored in `uploads/backups/`

### Restore from Backup

1. Go to Backup section
2. Upload backup JSON file
3. Confirm restore (overwrites current data!)
4. System will rebuild from backup

## 📝 Changelog

### Version 1.0.0 (October 2025)

**Initial Release:**
- 16 core life management modules
- Cryptocurrency portfolio tracking
- Real-time price alerts
- Multi-channel notifications
- Automated backup system
- Analytics dashboard
- System management panel
- CSV import/export
- Dark/light themes
- Mobile responsive design

## 🆘 Support

For issues and questions:

1. Check this README thoroughly
2. Review troubleshooting section
3. Check system logs in admin panel
4. Verify all configuration settings
5. Test components individually

## 📄 License

This project is open-source and available for personal and commercial use.

## 🙏 Credits

Built with:
- PHP 8.2
- PostgreSQL
- Chart.js for analytics
- Font Awesome for icons
- CoinGecko API for crypto prices
- Vanilla JavaScript (no frameworks)

---

**Made with ❤️ for better life organization**

For deployment support and updates, refer to `DEPLOYMENT.md` and `DEVELOPER_NOTE.md`.
