# Life Atlas Organizer

## Overview

Life Atlas Organizer is a comprehensive personal life management web application that centralizes 16+ core life modules into a single secure dashboard. Built with PHP and PostgreSQL, it provides users with tools to track assets, manage finances, monitor health, maintain journals, organize tasks, and track cryptocurrency investments. The application emphasizes security, user experience, and data integrity through features like CSRF protection, automated backups, multi-channel notifications (email and Telegram), comprehensive analytics, and data export/import capabilities.

## Recent Changes (October 2025)

### Major Updates - October 16, 2025

**Security & Authentication:**
- ✅ Implemented TOTP 2FA (Two-Factor Authentication) with Google Authenticator support
- ✅ Added secure backup codes with bcrypt hashing and JSON storage in users table
- ✅ Built complete 2FA flow: setup, verification, backup code recovery, regeneration
- ✅ Enhanced rate limiting and CSRF protection across all endpoints
- ✅ Created SECURITY.md comprehensive hardening guide

**Advanced Task Management:**
- ✅ Implemented task dependencies (task blocking/prerequisite system)
- ✅ Built recurring tasks engine with flexible patterns (daily, weekly, monthly, yearly)
- ✅ Added Pomodoro timer integration with focus/break cycles
- ✅ Created smart task scheduler with availability-based suggestions
- ✅ Enhanced task module with parent-child relationships

**Encrypted Notes Module:**
- ✅ Built AES-256 encryption for sensitive notes with user-specific keys
- ✅ Implemented secure note creation, viewing, editing, deletion
- ✅ Added encrypted_notes table with proper security measures

**REST API & Mobile Integration:**
- ✅ Developed complete REST API with token-based authentication
- ✅ Implemented API token generation, management, and expiration
- ✅ Built API endpoints for all major modules (tasks, finance, health, etc.)
- ✅ Added rate limiting and security headers for API requests

**PWA & Offline Support:**
- ✅ Created Progressive Web App manifest for installable experience
- ✅ Built service worker for offline functionality and caching
- ✅ Implemented offline data access and sync capabilities

**Multi-Language Support:**
- ✅ Implemented i18n system with English and Arabic support
- ✅ Created language switcher with localStorage persistence
- ✅ Built translation files for all UI elements

**Activity Tracking:**
- ✅ Developed activity logs/timeline for all modules
- ✅ Implemented user action tracking (create, update, delete)
- ✅ Built activity_logs table with complete audit trail

**Calendar Integration:**
- ✅ Created smart calendar merging tasks, goals, bills, birthdays
- ✅ Built unified event view with color-coded categories
- ✅ Implemented calendar API for event management

**Project Management:**
- ✅ Developed project & milestone tracker with hierarchical structure
- ✅ Built project progress monitoring and milestone tracking
- ✅ Added projects table with complete lifecycle management

**Wellbeing Module:**
- ✅ Created mindfulness & wellbeing tracking module
- ✅ Implemented meditation logging, mood tracking, stress monitoring
- ✅ Built wellbeing_logs table with comprehensive mental health tracking

**Document Management:**
- ✅ Added OCR integration hooks with Tesseract fallback support
- ✅ Implemented receipt/document text extraction capabilities

**Setup & Onboarding:**
- ✅ Built first-time setup wizard for admin configuration
- ✅ Created guided SMTP and Telegram integration setup
- ✅ Implemented system configuration validation

**Cryptocurrency Enhancements:**
- ✅ Added CSV import for bulk crypto transactions
- ✅ Built coin detail pages with sparkline charts
- ✅ Enhanced crypto module with transaction history tables

**Production Deployment:**
- ✅ Created portable database.sql without environment-specific commands
- ✅ Updated README.md with complete Hostinger deployment guide
- ✅ Fixed all critical bugs and passed architect review for production
- ✅ Ensured all 22 tasks are production-ready

### Earlier Updates (October 2025)

**Cryptocurrency Module:**
- Added crypto portfolio tracking with live price updates via CoinGecko API
- Implemented price alert system with customizable triggers
- Created crypto-specific database tables (crypto_portfolio, crypto_alerts, crypto_price_history)
- Built automatic price fetching and portfolio valuation
- Added top cryptocurrencies market overview

**Analytics Enhancements:**
- Created comprehensive analytics dashboard with 10+ chart types
- Added health tracking visualization (weight, exercise, water intake)
- Implemented investment portfolio performance charts
- Enhanced finance charts with 12-month income/expense trends
- Added journal mood distribution analytics
- Created learning progress visualization
- Implemented goals and habits completion tracking charts

**Data Management:**
- Built data export functionality (JSON and CSV formats)
- Implemented data import with validation
- Added system-wide data portability features
- Created backup and restore capabilities

**Bug Fixes:**
- Fixed footer undefined variable errors
- Corrected database method implementations (fetchColumn, execute)
- Fixed crypto price parsing for formatted numbers with thousand separators
- Fixed 2FA backup code verification to use JSON storage properly
- Improved admin panel functionality

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Backend Architecture

**Technology Stack:**
- PHP 8.2+ as the primary server-side language
- Session-based authentication with secure cookie handling (httponly)
- CSRF token protection implemented across all forms and API endpoints
- Bcrypt password hashing for user credentials
- Prepared SQL statements to prevent SQL injection attacks
- Input sanitization layer for all user-submitted data

**Architectural Pattern:**
- Traditional MVC-style structure with separation of concerns
- Module-based organization where each feature (Assets, Bills, Finance, etc.) operates independently
- Centralized configuration management through `includes/config.php`
- Shared utility functions and security helpers

**Security Design Decisions:**
- Session security configured with httponly cookies to prevent XSS attacks
- CSRF tokens with auto-refresh mechanism on expiry
- All database queries use prepared statements
- Multi-layer input validation and sanitization

### Frontend Architecture

**Technology Choices:**
- Vanilla JavaScript (no framework dependencies) for maximum performance and simplicity
- Modular JS structure with separate files for charts, main application logic
- CSS custom properties (CSS variables) for theming system
- Chart.js library for data visualization and analytics

**Theme System:**
- Dark/Light mode toggle with localStorage persistence
- CSS custom properties for dynamic color switching
- Theme state managed client-side with `data-theme` attribute

**UI/UX Patterns:**
- Responsive design with mobile-first approach
- Sidebar navigation (280px width) with mobile toggle
- Fixed topbar (70px height) for consistent navigation
- Modal-based interactions for forms and confirmations
- Toast notification system for user feedback
- Global search functionality across all modules

### Data Storage

**Database: PostgreSQL**

**Schema Design:**
- 20+ tables representing different life modules
- Foreign key constraints for referential integrity
- Cascade deletes configured for data cleanup
- Indexes added on frequently queried columns for performance optimization

**Key Tables:**
- User authentication and profiles
- Module-specific tables (assets, bills, birthdays, finance, goals, habits, health, hobbies, investments, journal, learning, media, subscriptions, tasks)
- System tables for backups and notifications

**Data Integrity:**
- Foreign key relationships enforce data consistency
- Cascade delete rules prevent orphaned records
- Transaction support for complex operations

### Module Architecture

**16 Core Modules:**
1. **Dashboard** - Aggregated statistics and summary visualizations
2. **Assets** - Item tracking with categories, values, acquisition dates
3. **Bills** - Recurring bill management with payment status
4. **Birthdays** - Birthday tracking with reminder system
5. **Finance** - Income/expense tracking with budgeting and analytics
6. **Goals** - Personal goal setting with progress monitoring
7. **Habits** - Habit tracking with streaks and completion rates
8. **Health** - Medical records, exercise logs, vitals tracking
9. **Hobbies** - Hobby tracking with resources and time management
10. **Investments** - Portfolio management with return calculations
11. **Journal** - Daily journaling with mood tracking
12. **Learning** - Course and book progress tracking
13. **Media** - Watchlist and media consumption tracking
14. **Subscriptions** - Subscription management with renewal alerts
15. **Tasks** - Todo list with categories, priorities, due dates
16. **Cryptocurrency** - Crypto portfolio tracking with live prices and alerts

**Module Independence:**
- Each module operates as a self-contained feature
- Shared authentication and authorization layer
- Common UI components and patterns
- Centralized notification system

### Notification System

**Multi-Channel Architecture:**
- Email notifications via SMTP integration
- Telegram notifications via Bot API
- User preference management for notification channels
- Event-based triggers (bill due dates, birthday reminders, subscription renewals)

**Automation:**
- Cron job scripts for scheduled notifications
- Automatic backup system
- Recurring event processing

### Backup and Recovery

**Backup Strategy:**
- Manual backup triggering from user interface
- Automatic scheduled backups via cron jobs
- Full database export functionality
- Restore capability from backup files

**Data Portability:**
- Export formats for data migration
- Import validation and error handling

### Performance Optimizations

**Database:**
- Strategic indexing on frequently queried columns
- Query optimization with prepared statements
- Connection pooling considerations

**Frontend:**
- CSS custom properties for efficient theming
- Lazy loading patterns where applicable
- Chart.js for hardware-accelerated visualizations
- localStorage for client-side state management

**Caching Strategy:**
- Session-based caching for user data
- Theme preferences cached in localStorage
- Static asset optimization

## External Dependencies

### Third-Party Libraries

**Chart.js**
- Purpose: Data visualization and analytics charts
- Usage: Dashboard statistics, financial analytics, progress tracking
- Integration: Client-side rendering of dynamic charts

**Font Awesome 6**
- Purpose: Icon library for UI elements
- Usage: Navigation icons, action buttons, status indicators

### External Services

**SMTP Email Service**
- Purpose: Transactional email notifications
- Configuration: Host, port, username, password in config.php
- Features: Bill reminders, birthday notifications, subscription alerts

**Telegram Bot API**
- Purpose: Instant push notifications
- Integration: Bot token configuration
- Features: Real-time alerts and reminders

### Database Service

**PostgreSQL**
- Version: Compatible with modern PostgreSQL versions
- Connection: Standard PostgreSQL driver via PHP PDO/pg_* functions
- Configuration: Host, database name, username, password in config.php

### Hosting Requirements

**Hostinger Deployment Specifications:**
- PHP 8.2+ support required
- PostgreSQL database access
- Web server (Apache/Nginx) or PHP built-in server
- File upload capability for backups
- Cron job support for automation
- SMTP/external API access for notifications

### Development Dependencies

**Runtime Requirements:**
- PHP 8.2 or higher
- PostgreSQL database server
- Web server with PHP support
- SSL/TLS for secure sessions (production)

**Optional Services:**
- Email SMTP server (for notifications)
- Telegram Bot (for instant alerts)
- Backup storage location (local or remote)