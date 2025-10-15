# Life Atlas Organizer

## Overview

Life Atlas Organizer is a comprehensive personal life management web application that centralizes 15+ core life modules into a single secure dashboard. Built with PHP and PostgreSQL, it provides users with tools to track assets, manage finances, monitor health, maintain journals, and organize tasks among other features. The application emphasizes security, user experience, and data integrity through features like CSRF protection, automated backups, and multi-channel notifications (email and Telegram).

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

**15 Core Modules:**
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