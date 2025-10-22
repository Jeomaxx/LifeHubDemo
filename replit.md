# Life Atlas Organizer - Replit Environment Setup

## Project Overview
Life Atlas Organizer is a comprehensive personal life management system built with PHP and PostgreSQL. It provides tools for managing finances, health, tasks, goals, and more.

## Technology Stack
- **Backend**: PHP 8.3
- **Database**: PostgreSQL (Neon-backed)
- **Frontend**: HTML, CSS (Tailwind), JavaScript
- **Dependencies**: Composer (league/oauth2-google, dompdf)
- **Server**: PHP Built-in Development Server

## Environment Setup (Completed)

### ✅ Installation Steps Completed
1. **PHP 8.3** installed with language module
2. **Composer dependencies** installed:
   - league/oauth2-google ^4.0
   - dompdf/dompdf ^3.1
3. **PostgreSQL database** created and configured
4. **Database schema** imported from `database_COMPLETE_MASTER.sql`
5. **Database fixes** applied:
   - Added `timezone` column to users table
   - Added `theme_preference` column to users table

### ✅ Bug Fixes Applied
**Sidebar Navigation Issue (Fixed)**
- **Problem**: Clicking sidebar category toggles did nothing; submenus wouldn't expand
- **Root Cause**: Duplicate event listeners in both `includes/sidebar.php` and `assets/js/main.js` were firing simultaneously, causing toggles to cancel each other out
- **Solution**: Removed duplicate category-toggle event listeners from `assets/js/main.js` (lines 79-96), keeping only the implementation in `sidebar.php`
- **Status**: ✅ Fixed - Sidebar now expands/collapses properly

## Project Structure
```
/
├── includes/          # Core PHP includes (auth, db, config, sidebar)
├── api/              # API endpoints
├── assets/           # Static assets (CSS, JS, images)
│   ├── css/
│   └── js/
├── cron/             # Cron job scripts
├── docs/             # Documentation
├── *.php             # Main application pages
├── composer.json     # PHP dependencies
└── database_*.sql    # Database schemas
```

## Key Files
- `includes/config.php` - Configuration (DB connection, security settings)
- `includes/db.php` - Database connection handler (PDO wrapper)
- `includes/auth.php` - Authentication and session management
- `includes/sidebar.php` - Navigation sidebar with dynamic menus
- `assets/js/main.js` - Main JavaScript functionality
- `dashboard.php` - Main dashboard page

## Database Configuration
The application uses PostgreSQL with the following environment variables:
- `DATABASE_URL` - Full connection string
- `PGHOST` - Database host
- `PGPORT` - Database port (5432)
- `PGDATABASE` - Database name
- `PGUSER` - Database user
- `PGPASSWORD` - Database password

Connection is handled through the `Database` class in `includes/db.php`.

## Authentication
- Session-based authentication
- Password hashing with bcrypt
- CSRF token protection
- Optional Google OAuth integration
- Optional TOTP (2FA) support

## Running the Application
The PHP development server is configured to run on port 5000:
```bash
php -S 0.0.0.0:5000 -t .
```

Access the application at: `http://localhost:5000`

## Test User Credentials
- Email: `test@example.com`
- Password: `password`
- ID: 1

## Current Status
✅ **Project is fully operational**
- Server running on port 5000
- Database connected and populated
- Dependencies installed
- All critical bugs fixed
- Ready for development and testing

## Recent Changes (October 22, 2025)
- Migrated from external environment to Replit
- Fixed sidebar navigation toggle functionality
- Added missing database columns for user preferences
- Created test user for development
- Verified all core functionality working

## Known Notes
- Tailwind CSS is loaded from CDN (consider installing locally for production)
- Google OAuth requires `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` environment variables
- Session cookies are set to non-secure mode for development (change in production)

## Next Steps for Development
The project is ready for feature development and improvements. All import and setup tasks are complete.
