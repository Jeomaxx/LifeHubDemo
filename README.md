# Life Atlas Organizer

A comprehensive personal life management dashboard that centralizes all areas of your life in one secure, fast, and modern web application.

## Features

### Core Modules
- **Dashboard** - Overview of all modules with quick stats and summary charts
- **Assets** - Track owned items with categories, values, and acquisition dates
- **Bills** - Manage recurring bills, due dates, and payment status
- **Birthdays** - Never forget important birthdays with reminders
- **Finance** - Income/expense tracking with budgeting and analytics
- **Goals** - Set and track personal goals with progress monitoring
- **Habits** - Build habits with streak tracking and completion rates
- **Health** - Medical records, exercise logs, water intake, weight tracking
- **Hobbies** - Track hobbies, resources, and time spent
- **Investments** - Manage investment portfolio and track returns
- **Journal** - Daily journal with date, title, content, and mood tracking
- **Learning** - Track courses, books, and study progress
- **Media** - Movies, music, and content watchlist
- **Subscriptions** - Manage subscriptions with renewal alerts
- **Tasks** - To-do list with categories, priorities, and due dates

### Additional Features
- **User Authentication** - Secure login/register with password hashing
- **Profile Settings** - Edit profile, change password, notification preferences
- **Data Backup & Restore** - Manual and automatic database backups
- **Email Notifications** - SMTP-based email alerts
- **Telegram Notifications** - Instant notifications via Telegram Bot
- **Analytics & Charts** - Dynamic visualizations using Chart.js
- **Dark/Light Theme** - Toggle between themes with localStorage
- **Global Search** - Search across all modules
- **Responsive Design** - Mobile-friendly interface
- **Security** - CSRF protection, prepared statements, input sanitization

## Tech Stack

- **Backend**: PHP 8.2
- **Database**: PostgreSQL
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Charts**: Chart.js
- **Icons**: Font Awesome 6

## Installation

### Prerequisites
- PHP 8.2 or higher
- PostgreSQL database
- Web server (Apache/Nginx) or PHP built-in server

### Setup Instructions

1. **Upload Files**
   ```bash
   # Upload all files to your web hosting directory
   # For Hostinger, upload to public_html
   ```

2. **Database Setup**
   ```bash
   # Import the database schema
   psql -U username -d database_name -f database.sql
   ```

3. **Configuration**
   - Edit `includes/config.php` with your database credentials
   - Set up SMTP settings for email notifications (optional)
   - Add Telegram Bot token for Telegram notifications (optional)

4. **Set Permissions**
   ```bash
   chmod 755 uploads/backups
   ```

5. **Access the Application**
   - Navigate to your domain: `https://yourdomain.com`
   - Register a new account
   - Start organizing your life!

## Hostinger Deployment

### File Structure for Hostinger
```
public_html/
├── includes/
├── api/
├── assets/
├── cron/
├── uploads/
├── index.php
├── login.php
├── dashboard.php
└── [other module files]
```

### Cron Jobs Setup on Hostinger

1. **Go to Hostinger Control Panel** → Advanced → Cron Jobs

2. **Daily Backup** (Run at 2 AM daily)
   ```
   0 2 * * * /usr/bin/php /home/username/public_html/cron/backup.php
   ```

3. **Reminder Notifications** (Run at 9 AM daily)
   ```
   0 9 * * * /usr/bin/php /home/username/public_html/cron/reminders.php
   ```

## Email Configuration (SMTP)

Edit `includes/config.php`:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
```

## Telegram Bot Setup

1. **Create a Telegram Bot**
   - Open Telegram and search for @BotFather
   - Send `/newbot` and follow the instructions
   - Copy the bot token

2. **Get Your Chat ID**
   - Start a chat with your bot
   - Visit: `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates`
   - Find your `chat_id` in the response

3. **Configure**
   - Edit `includes/config.php` and add your bot token
   - Add your chat ID in Profile Settings

## Default Admin Account

After installation, you can promote a user to admin:

```sql
UPDATE users SET is_admin = TRUE WHERE email = 'your-email@example.com';
```

## Security Recommendations

1. **Production Setup**
   - Set `display_errors = 0` in `includes/config.php`
   - Use HTTPS (SSL certificate)
   - Change default database credentials
   - Enable CSRF protection
   - Regular backups

2. **File Permissions**
   - Set directories to 755
   - Set PHP files to 644
   - Protect sensitive files with .htaccess

## Backup & Restore

### Manual Backup
1. Go to Backup section
2. Click "Create Backup Now"
3. Download the JSON file

### Restore from Backup
1. Import the JSON file into the database
2. Use the backup restoration tool (coming soon)

### Automatic Backups
- Configured via cron jobs
- Runs daily at 2 AM
- Retains backups for 30 days
- Stored in `uploads/backups/`

## Troubleshooting

### Database Connection Issues
- Verify database credentials in `includes/config.php`
- Ensure PostgreSQL service is running
- Check database user permissions

### Email Not Sending
- Verify SMTP configuration
- Check if port 587 is open
- Use app-specific password for Gmail

### Telegram Notifications Not Working
- Verify bot token is correct
- Ensure chat_id is properly set
- Check bot permissions

## Support

For issues and feature requests, please check the documentation or contact support.

## License

This project is open-source and available for personal and commercial use.

## Version

Current Version: 1.0.0
Release Date: October 2025

---

**Made with ❤️ for better life organization**
