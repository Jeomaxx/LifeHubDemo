# Deployment Guide for Life Atlas Organizer

## Pre-Deployment Checklist

### Security Configuration
✅ CSRF protection implemented across all forms and API endpoints
✅ Password hashing with bcrypt
✅ Prepared SQL statements to prevent SQL injection
✅ Input sanitization for all user inputs
✅ Session security configured (httponly cookies)
✅ CSRF token auto-refresh on expiry

### Database Setup
✅ PostgreSQL schema created with all 20+ tables
✅ Indexes added for performance
✅ Foreign key constraints for data integrity
✅ Cascade deletes configured

### Features Implemented
✅ User authentication (register, login, logout)
✅ 15 core modules (Assets, Bills, Birthdays, Finance, Goals, Habits, Health, Hobbies, Investments, Journal, Learning, Media, Subscriptions, Tasks)
✅ Dashboard with statistics and widgets
✅ Profile settings and password change
✅ Backup and restore functionality
✅ Email notification system (SMTP)
✅ Telegram notification system (Bot API)
✅ Dark/Light theme toggle
✅ Global search functionality
✅ Responsive mobile design
✅ Chart.js integration for analytics
✅ Cron job scripts for automation

## Hostinger Deployment Steps

### 1. Upload Files
Upload all files to your Hostinger account:
- Via FTP: Upload to `public_html/` directory
- Via File Manager: Upload and extract ZIP file

### 2. Database Configuration
Create a PostgreSQL database in Hostinger control panel:
1. Go to Databases → PostgreSQL
2. Create new database
3. Import `database.sql` file
4. Note down: database name, username, password, host

### 3. Update Configuration
Edit `includes/config.php`:
```php
define('DB_HOST', 'your-postgres-host');
define('DB_PORT', '5432');
define('DB_NAME', 'your-database-name');
define('DB_USER', 'your-username');
define('DB_PASS', 'your-password');
```

### 4. Optional: Email Setup
For email notifications, configure SMTP in `includes/config.php`:
```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

### 5. Optional: Telegram Setup
For Telegram notifications:
1. Create bot with @BotFather on Telegram
2. Get bot token
3. Add token to `includes/config.php`
4. Users add their chat_id in Profile Settings

### 6. Set File Permissions
```bash
chmod 755 uploads/backups
chmod 644 includes/config.php
```

### 7. Configure Cron Jobs
In Hostinger control panel:

**Daily Backup (2 AM)**:
```
0 2 * * * /usr/bin/php /home/username/public_html/cron/backup.php
```

**Daily Reminders (9 AM)**:
```
0 9 * * * /usr/bin/php /home/username/public_html/cron/reminders.php
```

### 8. Production Settings
In `includes/config.php`, set:
```php
error_reporting(0);
ini_set('display_errors', 0);
```

### 9. Create Admin User
After first user registration, run SQL:
```sql
UPDATE users SET is_admin = TRUE WHERE email = 'your-email@example.com';
```

## Testing After Deployment

1. **Authentication**: Register, login, logout
2. **CSRF Protection**: Try forms without token (should fail)
3. **Modules**: Create, update, delete items in each module
4. **Backup**: Create manual backup, verify file download
5. **Profile**: Update profile, change password
6. **Notifications**: Test with Telegram/Email if configured
7. **Theme**: Toggle dark/light theme
8. **Charts**: Verify Chart.js displays correctly
9. **Responsive**: Test on mobile devices

## Troubleshooting

### Database Connection Error
- Verify PostgreSQL credentials in config.php
- Ensure PostgreSQL service is running
- Check database user has proper permissions

### CSRF Token Error
- Clear browser cache and cookies
- Check session storage is enabled
- Verify PHP session configuration

### Email Not Sending
- Verify SMTP credentials
- Check port 587 is not blocked
- Use app-specific password for Gmail

### Cron Jobs Not Running
- Check cron syntax is correct
- Verify PHP path: `which php`
- Check cron logs for errors
- Ensure script paths are absolute

## Security Best Practices

1. **HTTPS**: Always use SSL certificate
2. **Passwords**: Never share database credentials
3. **Backups**: Store backups securely offline
4. **Updates**: Keep PHP and PostgreSQL updated
5. **Monitoring**: Check error logs regularly
6. **Access**: Limit database access to localhost only

## Performance Optimization

1. **Database**: Add indexes to frequently queried columns
2. **Caching**: Enable PHP OPcache
3. **CDN**: Use CDN for Font Awesome and Chart.js
4. **Images**: Optimize uploaded images
5. **Gzip**: Enable compression in .htaccess

## Maintenance

### Weekly
- Review backup files
- Check disk space usage
- Monitor error logs

### Monthly
- Update dependencies if needed
- Review database performance
- Test all critical features

### Quarterly
- Security audit
- Performance review
- User feedback analysis

## Support Contacts

For Hostinger-specific issues:
- Hostinger Support: support@hostinger.com
- Documentation: https://support.hostinger.com

For application issues:
- Check README.md for common solutions
- Review error logs in Hostinger panel

## Version History

**v1.0.0** (October 2025)
- Initial release
- Complete CRUD for all 15 modules
- CSRF protection implemented
- Email & Telegram notifications
- Backup/restore functionality
- Responsive design with theme toggle
