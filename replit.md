# Life Atlas Organizer

## Overview
Life Atlas Organizer is a comprehensive personal life management system built with PHP, PostgreSQL, and modern web technologies. It provides 60+ modules for managing finances, health, career, tasks, goals, and much more, with AI-powered features for insights and automation.

## Current State
- **Status**: Fully functional with 41 database tables
- **Environment**: Replit deployment with PostgreSQL database
- **PHP Version**: 8.2.23
- **Database**: PostgreSQL (Neon-backed)
- **Framework**: Custom PHP with modern CSS/JS

## Recent Changes (October 20, 2025)
### Database
- Migrated to Replit environment with 41 tables
- Created `database_MASTER_COMPLETE.sql` as the single source of truth
- Database includes all core and V3 feature tables
- Added rate_limit_log table for API rate limiting

### Features Developed
- **PDF Resume Generation**: Implemented using Dompdf library
- **AI Assistant Integration**: Connected to Gemini API via AIConfig class
- **Rate Limiting**: Fully implemented for API security
- **Sidebar Navigation**: Enhanced with defensive variable checks

### Libraries Installed
- `league/oauth2-google` for Google OAuth
- `dompdf/dompdf` for PDF generation

## Project Architecture

### Directory Structure
```
/
├── api/                    # Backend API endpoints (60+ files)
├── assets/                 # Static assets (CSS, JS, images)
│   ├── css/               # Style sheets (style, animations, enhanced-ui)
│   └── js/                # JavaScript files
├── attached_assets/        # Generated/uploaded assets
├── cron/                   # Scheduled tasks
├── docs/                   # Documentation
├── exports/                # Generated files (PDFs, CSVs)
├── includes/               # Core PHP includes
│   ├── auth.php           # Authentication system
│   ├── db.php             # Database class (singleton)
│   ├── config.php         # Configuration
│   ├── functions.php      # Helper functions
│   ├── sidebar.php        # Navigation sidebar
│   ├── header.php         # Page header
│   ├── footer.php         # Page footer
│   ├── ai_config.php      # AI/Gemini integration
│   └── rate_limiter.php   # API rate limiting
├── lang/                   # Internationalization files
├── tests/                  # Test files
├── vendor/                 # Composer dependencies
├── database_MASTER_COMPLETE.sql  # Complete database schema
└── *.php                   # Frontend modules (60+ files)
```

### Database Schema
The system uses PostgreSQL with 41 tables organized into categories:

**Core Tables:**
- users, api_tokens, backups, notifications

**Finance Modules:**
- finance, accounts, bills, budgets, debts, subscriptions, investments, crypto_portfolio, crypto_alerts, tax_documents

**Tasks & Productivity:**
- tasks, team_tasks, goals, smart_goals, habits, kanban_boards

**Health & Wellness:**
- health, gym_routines, gym_sessions, gym_exercises, diet_plans, mood_entries, medications, symptoms, water_intake, sleep_tracking

**Career & Professional:**
- career_skills, portfolio_projects, career_milestones, freelance_clients, freelance_projects, freelance_invoices

**Life Management:**
- calendar_events, contacts, birthdays, gifts, documents, recipes, vehicles, home_assets

**AI & Analytics:**
- ai_conversations, ai_messages, ai_briefings, automation_rules, automation_execution_log

## User Preferences
- System uses Tailwind CSS via CDN (note: should migrate to PostCSS for production)
- Dark/light theme toggle supported
- Mobile-responsive design
- PWA capabilities

## Module Categories

### 1. Finances (11 modules)
- Transactions, Accounts, Budgets
- Bills & Payments, Subscriptions
- Debt Payoff Planner, Investments
- Cryptocurrency, Finance Advanced
- Financial Forecast, Tax Reports & PDF

### 2. Tasks & Projects (3 modules)
- All Tasks, Kanban Board
- Pomodoro Timer

### 3. Goals & Habits (3 modules)
- Goals, SMART Goals
- Habits

### 4. Health & Wellness (9 modules)
- Health Dashboard, Health Records
- Gym Routines, Diet Plans, Nutrition AI
- Water Tracker, Mood Tracker
- Mindfulness Hub, Sleep Tracker
- Medications, Symptom Tracker

### 5. Professional & Career (4 modules)
- Career Center, Freelance Tracker
- Portfolio Generator, Team Collaboration

### 6. Learning & Knowledge (3 modules)
- Learning, Learning Hub
- Knowledge Vault

### 7. Life Management (9 modules)
- Journal & Mood, Hobbies
- Media & Entertainment, Relationships
- Family Manager, Personal CRM
- Gift Management, Life Events

### 8. Travel & Lifestyle (2 modules)
- Travel Planner, Travel Journal

### 9. Calendar & Events (3 modules)
- Calendar View, Event Planner
- Birthdays

### 10. Home & Assets (6 modules)
- Home Assets, Personal Assets
- Vehicle Maintenance, Maintenance Logs
- Documents, Recipe Book

### 11. AI & Productivity (7 modules)
- AI Assistant (Gemini-powered)
- Daily Briefing, Life Advisor
- AI Life Map, Life Orchestrator
- Custom Dashboards, Unified Search

### 12. Analytics & Reports (2 modules)
- Analytics, Life Analytics

### 13. Security & Privacy (3 modules)
- Secure Vault, Device Management
- 2FA Security

### 14. Settings (4 modules)
- Profile, Import/Export
- Backup & Restore, Preferences

## API Integration

### Gemini AI (Google)
- **Status**: Configured via AIConfig class
- **Environment Variable**: `GEMINI_API_KEY`
- **Features**: AI Assistant, Mood Analysis, Financial Predictions, Goal Progress Analysis

### Google OAuth
- **Status**: Configured for authentication
- **Library**: league/oauth2-google
- **Features**: Login with Google

## Security Features
- CSRF protection on all forms
- Rate limiting on API endpoints
- Password hashing (bcrypt)
- Prepared statements (SQL injection prevention)
- Session management
- 2FA support
- Secure vault for sensitive data

## Known Limitations
1. **Tailwind CSS**: Using CDN in development (should use PostCSS for production)
2. **Google Calendar Sync**: Placeholder - needs full API implementation
3. **WhatsApp Integration**: Planned but not yet implemented

## Development Guidelines

### Database Changes
1. Always update `database_MASTER_COMPLETE.sql`
2. Use PostgreSQL-compatible SQL
3. Include proper indexes for performance
4. Never run destructive SQL without backups

### Adding New Modules
1. Create frontend PHP file in root
2. Create corresponding API in `api/` folder
3. Add to sidebar in `includes/sidebar.php`
4. Update database schema if needed
5. Add JavaScript handlers if needed

### Code Conventions
- Use prepared statements for all database queries
- Sanitize all user input with `sanitize()` function
- Use `Auth` class for authentication
- Use `Database::getInstance()` for DB access
- Follow existing naming conventions
- Add error handling and logging

## Deployment
- **Development**: PHP built-in server on port 5000
- **Workflow**: Configured via `workflows_set_run_config_tool`
- **Command**: `php -S 0.0.0.0:5000 -t .`

## Testing
- Test user created: test@test.com / test123
- 41 database tables verified and operational
- All major modules functional
- PDF generation working (Dompdf)
- AI integration working (Gemini API)
- Rate limiting implemented on all API endpoints

## Support & Documentation
- See `README.md` for feature list
- See `DATABASE_SETUP_GUIDE.md` for database info
- See `DEPLOYMENT.md` for deployment instructions
- See individual module docs in `docs/` folder
