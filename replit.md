# Life Atlas Organizer

## Overview
Life Atlas Organizer is a comprehensive personal life management web application designed to centralize over 25+ core life modules into a single, secure dashboard. Built with PHP and PostgreSQL, it empowers users to efficiently manage finances, track assets, monitor health, maintain journals, organize tasks, track cryptocurrency investments, and much more. The application prioritizes security, user experience, and data integrity, incorporating features like CSRF protection, automated backups, multi-channel notifications (email and Telegram), extensive analytics, AI assistant, and robust data export/import capabilities. Its purpose is to provide a holistic solution for personal organization and productivity.

## Recent Changes (October 16, 2025)
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

### Bug Fixes & Improvements
- **Core Functions**: Fixed t() translation and requireLogin() helper function errors
- **Enhanced Tables**: Added categories, vendors, attachments to Bills; Added transaction tracking for Accounts
- **Performance**: Added indexes for all user-related queries across all tables

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
The application integrates 16 core modules: Dashboard, Assets, Bills, Birthdays, Finance, Goals, Habits, Health, Hobbies, Investments, Journal, Learning, Media, Subscriptions, Tasks, and Cryptocurrency. Each module operates as a self-contained feature, sharing authentication, authorization, UI components, and a centralized notification system.

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