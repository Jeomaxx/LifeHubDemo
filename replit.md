# Life Atlas Organizer

## Overview
Life Atlas Organizer is a comprehensive personal life management web application designed to centralize over 25+ core life modules into a single, secure dashboard. Built with PHP and PostgreSQL, it empowers users to efficiently manage finances, track assets, monitor health, maintain journals, organize tasks, track cryptocurrency investments, and much more. 

The application now features **AI-powered insights using Google Gemini** for intelligent financial forecasting, emotional wellness tracking, SMART goal achievement analysis, life event predictions, and relationship insights. It prioritizes security, user experience, and data integrity, incorporating features like CSRF protection, automated backups, multi-channel notifications (email and Telegram), extensive analytics, AI-driven predictions, and robust data export/import capabilities. Its purpose is to provide a holistic, intelligent solution for personal organization and productivity.

## Recent Changes (October 17, 2025)

### PRODUCTION-READY STATUS ✅
All core features implemented and tested. System ready for Hostinger deployment.

### Latest Updates (Session 5 - October 17, 2025)
### UI/UX Enhancements & Advanced Animations ✅
- **15+ Advanced Animation Effects**: Glass morphism, gradient animations, glow effects, particles, floating animations, shine effects, wiggle, flip, bounce, and more
- **Dashboard Enhancements**: Applied hover-lift, icon-hover, shine-effect, and counter animations to stat cards for interactive UX
- **Auto-Categorization Expansion**: Enhanced expense categorization from 13 to 16 categories with 100+ additional keywords
  - **New Categories**: Pets, Gifts & Donations, Bills & Fees
  - **Enhanced Coverage**: Education, Entertainment, Health & Medical, Personal Care, Transportation, Food & Dining, Shopping, Home & Utilities, Insurance, Savings & Investments, Debt Payments, Business, Charity, Miscellaneous
- **Critical Fix**: Resolved PHP syntax error in life_analytics.php (corrupted closing tag `%>` replaced with `?>` using surgical sed replacement)
- **Performance**: All 146 PHP files validated, charts working with Chart.js, animations optimized with CSS-only approach

### Previous Updates (Session 4 - October 17, 2025)
### AI-Powered Insights with Google Gemini ✅
- **AI Integration**: Complete Google Gemini API integration via AIConfig class
- **5 AI Modules Created**:
  1. **Financial Forecasting**: AI-powered predictions for cash flow, expenses, and savings recommendations
  2. **Mood Tracker & Emotional Insights**: Sentiment analysis, emotion detection, and personalized wellness suggestions
  3. **SMART Goals Engine**: AI-driven progress tracking with actionable feedback and milestone analysis
  4. **Life Event Predictor**: Cross-module data correlation to predict upcoming life events (budget issues, wellness changes)
  5. **Relationship Insights**: Interaction tracking with AI analysis of communication patterns and health scores

### AI Features Implemented ✅
- **7 New Database Tables**: financial_forecasts, mood_entries, mood_insights, smart_goals, goal_milestones, life_event_predictions, relationship_insights
- **6 New API Endpoints**: financial_forecast.php, mood_tracker.php, smart_goals.php, life_events.php, relationships.php, ai_insights.php
- **5 Frontend Pages**: Complete UI for all AI modules with interactive dashboards and data visualization
- **Unified AI Dashboard**: Real-time AI insights aggregator on main dashboard showing predictions across all modules
- **Sidebar Integration**: All AI modules added to "AI & Productivity" category with Mood Tracker in "Health & Wellness"

### Technical Implementation ✅
- **AIConfig Class**: Centralized Gemini API management with helper methods for all AI operations
- **Structured Prompts**: JSON-based responses for reliable data parsing across all AI features
- **Security**: GEMINI_API_KEY stored in environment variables with proper validation
- **Error Handling**: Graceful fallbacks and user-friendly error messages throughout AI pipeline

### Previous Updates (Session 3 - October 16, 2025)
### Universal Import/Export System ✅
- **Created comprehensive CSV/Excel import/export system** supporting 9+ modules
- **Frontend UI**: import_export.php with template download and bulk import capabilities
- **Export API**: api/universal_export.php with template generation for all modules
- **Import API**: api/universal_import.php with validation, error reporting, and safe data insertion
- **Supported Modules**: Gifts, Bills, Tasks, Finance, Goals, Habits, Gym Routines, Diet Plans, Water Intake
- **Features**: Download CSV templates, bulk import validation, error handling, data portability

### Production Documentation Complete ✅
- **README.md**: Enhanced with 25+ modules documentation, import/export usage guide, troubleshooting
- **HOSTINGER_DEPLOYMENT.md**: 10-step comprehensive deployment guide with SSL, cron, SMTP, security
- **.htaccess**: Production-ready Apache configuration with security headers (CSP, X-Frame-Options, etc.)
- **Import/Export Guide**: Step-by-step instructions with pro tips and best practices

### Critical Function Fixes ✅
- **Helper Functions Added**: requireLogin() and t() wrappers in includes/functions.php and includes/i18n.php
- **Fixed Undefined Function Errors**: Resolved across all files requiring authentication and translation
- **Backwards Compatibility**: All helper functions wrapped with function_exists() to prevent conflicts

### Previous Updates (Session 2)
### Critical Security Fixes
- **SQL Injection Vulnerabilities Fixed**: Patched 3 critical SQL injection issues in calendar.php, crypto.php, and bills.php by implementing proper input validation and parameter binding

### Database Expansion
- **Database Setup**: Migrated to Replit's managed PostgreSQL database with 42+ tables
- **18 New Tables Created**: 
  - Gift Management: `gifts` table
  - Health & Wellness: `gym_routines`, `diet_plans`, `water_intake` tables
  - Home & Assets: `home_assets`, `maintenance_logs`, `documents` tables
  - AI & Productivity: `ai_conversations`, `ai_messages`, `ai_briefings` tables
  - Security: `vault_items`, `user_devices`, `user_sessions` tables
  - Finance: `accounts`, `transactions`, `calendar_events` tables

### New Modules Developed (API + Frontend)
- **Gift Management**: Track gifts with provider links, prices, and occasion tagging
- **Gym Tracker**: Workout routine tracking with sets, reps, duration, muscle groups
- **Diet Planner**: Daily meal planning with calorie tracking and nutrition goals
- **Water Tracker**: Hydration monitoring with daily goals and progress visualization
- **Home & Assets**: Asset tracking with maintenance logs and warranty management
- **Documents Hub**: Document management with version control and tagging (API only)
- **AI Assistant**: Chat interface with daily briefings and contextual suggestions (API only)
- **Security Center**: Encrypted vault and device management (API only)

### API Development
- **11 New API Endpoints Created**: gifts.php, gym.php, diet.php, water.php, home_assets.php, documents.php, ai_assistant.php, transactions.php, calendar.php, vault.php, devices.php
- **Authentication**: All new endpoints properly secured with user authentication checks
- **RESTful Design**: Consistent GET/POST/DELETE patterns across all endpoints

### UI/UX Enhancements
- **Sidebar Update**: Added new categories - Health & Wellness, Home & Assets, AI & Productivity, Security & Privacy
- **4 Frontend Pages Created**: gifts.php, gym.php, diet.php, water.php with modern responsive design
- **Interactive Dashboards**: Real-time stats, progress tracking, and data visualization

### Global Features Implemented
- **Global Search**: Universal search across all modules (Tasks, Bills, Notes, Goals, Habits) with keyboard shortcut (Ctrl+K)
- **Advanced Analytics Dashboard**: Comprehensive productivity insights with interactive charts, habit streaks, financial summaries
- **Deployment Ready**: Complete Hostinger deployment guide with SSL, cron jobs, security configuration

### Bug Fixes & Improvements
- **Critical Fixes**: Fixed requireLogin(), sanitize(), and t() function calls across 10+ files
- **Core Functions**: Fixed t() translation and requireLogin() helper function errors  
- **Enhanced Tables**: Added categories, vendors, attachments to Bills; Added transaction tracking for Accounts
- **Performance**: Added indexes for all user-related queries across all tables
- **UI Integration**: Added global search bar to header, notification system, responsive top bar

## User Preferences
Preferred communication style: Simple, everyday language.

## System Architecture

### Backend Architecture
**Technology Stack:** PHP 8.2+, PostgreSQL. Employs session-based authentication with secure cookie handling, CSRF token protection, Bcrypt password hashing, and prepared SQL statements to prevent injections.
**Architectural Pattern:** Traditional MVC-style structure with a module-based organization, allowing each feature to operate independently. Centralized configuration and shared utility functions.
**Security Design Decisions:** httponly cookies, auto-refreshing CSRF tokens, prepared statements, and multi-layer input validation.

### Frontend Architecture
**Technology Choices:** Vanilla JavaScript for performance, CSS custom properties for theming, and Chart.js for data visualization. Tailwind CSS is integrated for a modern, responsive design.
**Theme System:** Dark/Light mode toggle with localStorage persistence and CSS custom properties.
**UI/UX Patterns:** Responsive design with a mobile-first approach, fixed topbar, sidebar navigation, modal-based interactions, and a toast notification system. Global search functionality is available.

### Data Storage
**Database:** PostgreSQL, with a schema comprising 20+ tables, foreign key constraints, cascade deletes, and indexing for performance. Key tables cover user authentication, profiles, and module-specific data.
**Data Integrity:** Enforced by foreign key relationships and transaction support.

### Module Architecture
The application integrates **25+ core modules**: Dashboard, Assets & Home Management, Bills, Birthdays, Finance, Goals, Habits, Health Dashboard, Gym Routines, Diet Planner, Water Tracker, Hobbies, Investments, Journal, Learning, Media, Subscriptions, Tasks, Cryptocurrency, Gift Ideas, Document Hub, AI Assistant, Security Vault, Device Management, and Advanced Analytics. Each module operates as a self-contained feature, sharing authentication, authorization, UI components, and a centralized notification system.

### Import/Export System
**Universal Data Portability**: CSV/Excel import/export system supporting 9+ core modules with template generation, validation, and bulk operations. Users can export all data for backup or migration, and import hundreds of records at once using pre-formatted templates.

### Notification System
**Multi-Channel Architecture:** Supports email (via SMTP) and Telegram (via Bot API) notifications. User preferences manage notification channels. Triggers include bill due dates, birthday reminders, and subscription renewals.
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
*   **Font Awesome 6:** Icon library for UI elements.

### External Services
*   **SMTP Email Service:** For transactional email notifications (e.g., bill reminders, birthday alerts).
*   **Telegram Bot API:** For instant push notifications and real-time alerts.
*   **CoinGecko API:** (Specific to Cryptocurrency module) for live price updates.

### Database Service
*   **PostgreSQL:** The primary database, compatible with modern versions, connected via PHP PDO.

### Hosting Requirements
*   **Hostinger Deployment Specifications:** Requires PHP 8.2+, PostgreSQL database access, a web server (Apache/Nginx), file upload capability, cron job support, and SMTP/external API access.