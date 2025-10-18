# Life Atlas Organizer

## Overview
Life Atlas Organizer is a comprehensive personal life management web application designed to centralize over 30+ core life modules into a single, secure dashboard. Built with PHP 8.2+ and PostgreSQL, it empowers users to efficiently manage finances, track assets, monitor health, maintain journals, organize tasks, track cryptocurrency investments, and much more. The application features AI-powered insights using Google Gemini for intelligent financial forecasting, emotional wellness tracking, SMART goal achievement analysis, life event predictions, and relationship insights. It prioritizes security, user experience, and data integrity, offering a holistic, intelligent solution for personal organization and productivity.

## Recent Changes (October 2025)

### System Migration & Verification (October 18, 2025)
**Status:** Complete - All systems operational

**Import & Setup:**
- Composer dependencies installed (OAuth2 Google library)
- PostgreSQL database provisioned and configured
- 134 database tables created and verified
- All migration scripts executed successfully

**Comprehensive Deep Check Results:**
- ✅ Database: 134 tables operational
- ✅ Authentication: Registration & login tested end-to-end
- ✅ UI/UX: 73 modules with icons, 44 with dark mode
- ✅ Responsive: 42 pages mobile-optimized (768px/480px breakpoints)
- ✅ Interactivity: 23 pages with modals & CRUD operations
- ✅ Performance: Scripts optimized with defer loading
- ✅ Animations: Comprehensive CSS animations across all modules

**Security Notes:**
- Created `DEPLOYMENT_CHECKLIST.md` for production deployment
- **CRITICAL:** `session.cookie_secure` set to 0 for development (no HTTPS)
- **MUST** be changed to 1 before production deployment with HTTPS
- All authentication uses Bcrypt password hashing
- CSRF protection active on all forms

## Recent Changes (October 2025)

### V2 Advanced Features (Latest)
**Status:** Production-ready, all features tested and integrated

1. **Voice Interaction System** (`voice_assistant.php`, `assets/js/voice-assistant.js`) - Speech recognition for hands-free commands, natural language processing, text-to-speech responses
2. **Security Analytics Dashboard** (`security_analytics.php`) - Login tracking, IP monitoring, anomaly detection, security scoring
3. **Expense OCR Scanner** (`expense_scanner.php`, `assets/js/receipt-scanner.js`) - Tesseract.js integration for receipt scanning and automated expense extraction
4. **Multi-Currency Support** (`api/currency.php`) - Real-time exchange rates, currency conversion, multi-currency transactions
5. **Enhanced PWA Offline Mode** (`assets/js/pwa-offline.js`) - IndexedDB storage for tasks/notes/calendar, automatic background sync, conflict resolution
6. **Mental Wellness Dashboard** (`mental_wellness_dashboard.php`) - Integrated mood, sleep, and mindfulness tracking with correlations
7. **Shared Family Dashboard** (`shared_family_dashboard.php`) - Aggregated family data view with health, tasks, and finance overview
8. **Emergency Mode** (`emergency_mode.php`, `api/emergency.php`) - GPS sharing, health data broadcast to emergency contacts
9. **Unified Life Analytics** (`unified_analytics.php`) - Cross-dimensional insights correlating productivity, finance, health, and relationships
10. **Goal Progress Visualizer** (`goal_visualizer.php`) - Heatmaps and timeline graphs for goal tracking
11. **Telegram Bot** (`telegram_bot.php`) - Command handling with secure webhook authentication, automated reports
12. **AI Report Generator** (`api/ai_report_generator.php`) - Weekly/monthly automated reports via Telegram or Email
13. **Database Upgrade V2** (`database_upgrade_v2.sql`) - 30+ new tables for all advanced features

**Security Enhancements:**
- Telegram webhook token verification (SHA-256 hash)
- Enhanced authentication and rate limiting
- Emergency data encryption at rest
- Security log analysis and anomaly detection

**Bug Fixes:**
- Fixed PWA IndexedDB initialization (calendarStore reference)
- Secured Telegram bot webhook endpoint
- PostgreSQL interval syntax verified

### New Modules Added
1. **Debt Payoff Planner** (`debts.php`, `api/debts.php`) - Full debt management with payment tracking, snowball/avalanche strategies, and payoff projections
2. **Recipe Book & Meal Planner** (`recipes.php`, `api/recipes.php`) - Recipe storage, meal planning, and grocery list integration
3. **Vehicle Maintenance** (`vehicles.php`, `api/vehicles.php`) - Vehicle tracking with maintenance logs, service reminders, and mileage tracking
4. **Medication & Supplement Tracker** (`medications.php`, `api/medications.php`) - Medication management with intake logging and refill reminders
5. **Symptom Tracker** (`symptoms.php`, `api/symptoms.php`) - Health symptom logging with severity tracking and pattern analysis
6. **Personal CRM - Contacts** (`contacts.php`, `api/contacts.php`) - Contact management with relationship tracking and interaction history
7. **Event Planner** (`events.php`, `api/events.php`) - Event planning with guest lists, checklists, and budget tracking
8. **Multi-User Family Sharing** (`family_manager.php`, `api/family.php`, `assets/js/family-manager.js`) - Complete family collaboration system with:
   - Family member management (contact details, relationships, birthdays)
   - Shared household task tracking with assignments and priorities
   - Expense sharing with split calculations and payment tracking
   - Collaborative grocery list management
   - Full CRUD operations with user-scoped authorization
9. **Calendar Sync Integration** (`api/calendar_sync.php`, enhanced `calendar.php`) - Google Calendar integration with:
   - OAuth 2.0 authentication flow
   - Two-way sync status tracking
   - ICS export for manual calendar import
   - Connection management UI with status cards

### Technical Updates
- Created consolidated JavaScript module (`assets/js/new-modules.js`) for efficient frontend management
- All new modules implement full CRUD operations with authentication and validation
- Updated navigation sidebar with all new modules organized by category
- Enhanced calendar module with day/week/month views and Google Calendar sync
- Implemented family sharing system with complete REST API (`api/family.php`)
- Added Composer dependency management with OAuth2 library integration
- Database expanded with 60+ new tables supporting all new features
- All API endpoints follow RESTful patterns with proper error handling
- All CRUD operations use prepared statements with user_id authorization for security

## User Preferences
Preferred communication style: Simple, everyday language.

## System Architecture

### Backend Architecture
**Technology Stack:** PHP 8.2+, PostgreSQL. Employs session-based authentication with secure cookie handling, CSRF token protection, Bcrypt password hashing, and prepared SQL statements.
**Architectural Pattern:** Traditional MVC-style structure with a module-based organization, allowing each feature to operate independently. Centralized configuration and shared utility functions.
**Security Design Decisions:** httponly cookies, auto-refreshing CSRF tokens, prepared statements, and multi-layer input validation.

### Frontend Architecture
**Technology Choices:** Vanilla JavaScript, CSS custom properties, Chart.js for data visualization, and Tailwind CSS for modern, responsive design. Integrates Lucide Icons and a motion animation library for enhanced UI/UX.
**Theme System:** Dark/Light mode toggle with localStorage persistence.
**UI/UX Patterns:** Responsive design (mobile-first), fixed topbar, sidebar navigation, modal-based interactions, and a toast notification system. Global search functionality is available.

### Data Storage
**Database:** PostgreSQL, with a schema comprising 40+ tables, foreign key constraints, cascade deletes, and indexing for performance. Key tables cover user authentication, profiles, and module-specific data.
**Data Integrity:** Enforced by foreign key relationships and transaction support.

### Module Architecture
The application integrates over 25 core modules, including: Dashboard, Assets & Home Management, Bills, Finance, Goals, Habits, Health Dashboard, Gym Routines, Diet Planner, Water Tracker, Investments, Journal, Learning, Subscriptions, Tasks, Cryptocurrency, Gift Ideas, Document Hub, AI Assistant, Security Vault, Device Management, and Advanced Analytics. Each module operates as a self-contained feature, sharing authentication, authorization, UI components, and a centralized notification system. New modules like Work & Career Center, Learning & Knowledge Hub, Household & Family Manager, Travel Planner & Journal, Wellness & Mindfulness Hub, and AI Life Map Dashboard have been added in V2.

### AI Integration
Google Gemini API is integrated via an `AIConfig` class to power 5 AI modules: Financial Forecasting, Mood Tracker & Emotional Insights, SMART Goals Engine, Life Event Predictor, and Relationship Insights. These modules utilize structured prompts for reliable data parsing and store insights in dedicated database tables.

### Universal Import/Export System
A comprehensive CSV/Excel import/export system supports 9+ core modules, offering template generation, validation, and bulk operations for data portability.

### Notification System
**Multi-Channel Architecture:** Supports email (via SMTP) and Telegram (via Bot API) notifications. Triggers include bill due dates, birthday reminders, and subscription renewals.
**Automation:** Cron job scripts handle scheduled notifications, automated backups, and recurring event processing.

### Backup and Recovery
Features manual and automatic scheduled backups, full database export, and restore capabilities. Data portability is supported through JSON and CSV export formats.

### Performance Optimizations
**Database:** Strategic indexing and query optimization using prepared statements.
**Frontend:** CSS custom properties, lazy loading, Chart.js for visualizations, and localStorage for client-side state management.
**Caching Strategy:** Session-based caching for user data and localStorage for theme preferences.

## External Dependencies

### Third-Party Libraries
*   **Chart.js:** For data visualization and analytics charts.
*   **Lucide Icons:** Modern SVG icon library.
*   **Motion Animation Library:** For smooth UI interactions.
*   **Tailwind CSS:** For styling and responsive design.

### External Services
*   **Google Gemini API:** For AI-powered insights and predictions.
*   **SMTP Email Service:** For transactional email notifications.
*   **Telegram Bot API:** For instant push notifications and real-time alerts.
*   **CoinGecko API:** (Specific to Cryptocurrency module) for live price updates.

### Database Service
*   **PostgreSQL:** The primary database, connected via PHP PDO.

### Hosting Requirements
*   **Hostinger Deployment Specifications:** Requires PHP 8.2+, PostgreSQL database access, a web server (Apache/Nginx), file upload capability, cron job support, and SMTP/external API access.