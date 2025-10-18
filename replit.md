# Life Atlas Organizer

## Overview
Life Atlas Organizer is a comprehensive personal life management web application designed to centralize over 30+ core life modules into a single, secure dashboard. Built with PHP 8.2+ and PostgreSQL, it empowers users to efficiently manage finances, track assets, monitor health, maintain journals, organize tasks, track cryptocurrency investments, and much more. The application features AI-powered insights using Google Gemini for intelligent financial forecasting, emotional wellness tracking, SMART goal achievement analysis, life event predictions, and relationship insights. It prioritizes security, user experience, and data integrity, offering a holistic, intelligent solution for personal organization and productivity.

## Recent Changes (October 2025)
### New Modules Added
1. **Debt Payoff Planner** (`debts.php`, `api/debts.php`) - Full debt management with payment tracking, snowball/avalanche strategies, and payoff projections
2. **Recipe Book & Meal Planner** (`recipes.php`, `api/recipes.php`) - Recipe storage, meal planning, and grocery list integration
3. **Vehicle Maintenance** (`vehicles.php`, `api/vehicles.php`) - Vehicle tracking with maintenance logs, service reminders, and mileage tracking
4. **Medication & Supplement Tracker** (`medications.php`, `api/medications.php`) - Medication management with intake logging and refill reminders
5. **Symptom Tracker** (`symptoms.php`, `api/symptoms.php`) - Health symptom logging with severity tracking and pattern analysis
6. **Personal CRM - Contacts** (`contacts.php`, `api/contacts.php`) - Contact management with relationship tracking and interaction history
7. **Event Planner** (`events.php`, `api/events.php`) - Event planning with guest lists, checklists, and budget tracking

### Technical Updates
- Created consolidated JavaScript module (`assets/js/new-modules.js`) for efficient frontend management
- All new modules implement full CRUD operations with authentication and validation
- Updated navigation sidebar with all new modules organized by category
- Enhanced calendar module with improved views and functionality
- Database expanded with 60+ new tables supporting all new features
- All API endpoints follow RESTful patterns with proper error handling

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