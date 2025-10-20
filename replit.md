# Life Atlas Organizer

## Overview
Life Atlas Organizer is a comprehensive personal life management web application designed to centralize over 30+ core life modules into a single, secure dashboard. Built with PHP 8.2+ and PostgreSQL, it empowers users to efficiently manage finances, track assets, monitor health, maintain journals, organize tasks, and track cryptocurrency investments. The application features AI-powered insights using Google Gemini for intelligent financial forecasting, emotional wellness tracking, SMART goal achievement analysis, life event predictions, and relationship insights. It prioritizes security, user experience, and data integrity, offering a holistic, intelligent solution for personal organization and productivity. The latest version (V3.0) introduces professional and AI tools such as a Freelance & Side-Hustle Tracker, Tax Reports & PDF Export, Team Collaboration, Knowledge Vault, Unified Semantic Search, Life Orchestrator (automation engine), Custom Dashboard Builder, Career Portfolio & Resume Generator, and Nutrition AI.

## User Preferences
Preferred communication style: Simple, everyday language.

## System Architecture

### Backend Architecture
**Technology Stack:** PHP 8.2+, PostgreSQL. Employs session-based authentication with secure cookie handling, CSRF token protection, Bcrypt password hashing, and prepared SQL statements.
**Architectural Pattern:** Traditional MVC-style structure with a module-based organization, allowing each feature to operate independently. Centralized configuration and shared utility functions.
**Security Design Decisions:** httponly cookies, auto-refreshing CSRF tokens, prepared statements, multi-layer input validation, comprehensive authorization checks (including cross-entity and role-based), and user-scoped queries.

### Frontend Architecture
**Technology Choices:** Vanilla JavaScript, CSS custom properties, Chart.js for data visualization, and Tailwind CSS for modern, responsive design. Integrates Lucide Icons and a motion animation library.
**Theme System:** Dark/Light mode toggle with localStorage persistence.
**UI/UX Patterns:** Responsive design (mobile-first), fixed topbar, sidebar navigation, modal-based interactions, and a toast notification system. Global search functionality is available.

### Data Storage
**Database:** PostgreSQL, with a comprehensive schema (134+ tables) comprising foreign key constraints, cascade deletes, and indexing for performance. Key tables cover user authentication, profiles, and module-specific data.
**Data Integrity:** Enforced by foreign key relationships and transaction support.

### Module Architecture
The application integrates over 30+ core modules, including: Dashboard, Assets & Home Management, Bills, Finance, Goals, Habits, Health Dashboard, Gym Routines, Diet Planner, Water Tracker, Investments, Journal, Learning, Subscriptions, Tasks, Cryptocurrency, Gift Ideas, Document Hub, AI Assistant, Security Vault, Device Management, Advanced Analytics, Debt Payoff Planner, Recipe Book & Meal Planner, Vehicle Maintenance, Medication & Supplement Tracker, Symptom Tracker, Personal CRM, Event Planner, Multi-User Family Sharing, and Calendar Sync Integration. Each module operates as a self-contained feature, sharing authentication, authorization, UI components, and a centralized notification system. V3.0 additions include Freelance & Side-Hustle Tracker, Tax Reports, Team Collaboration, Knowledge Vault, Unified Semantic Search, Life Orchestrator, Custom Dashboard Builder, Career Portfolio, and Nutrition AI.

### AI Integration
Google Gemini API is integrated via an `AIConfig` class to power modules like Financial Forecasting, Mood Tracker & Emotional Insights, SMART Goals Engine, Life Event Predictor, and Relationship Insights, as well as Nutrition AI and AI Report Generator. These modules utilize structured prompts for reliable data parsing and store insights in dedicated database tables.

### Universal Import/Export System
A comprehensive CSV/Excel import/export system supports multiple core modules, offering template generation, validation, and bulk operations for data portability.

### Notification System
**Multi-Channel Architecture:** Supports email (via SMTP) and Telegram (via Bot API) notifications for scheduled alerts and automated reports.

### Backup and Recovery
Features manual and automatic scheduled backups, full database export, and restore capabilities. Data portability is supported through JSON and CSV export formats.

### Performance Optimizations
**Database:** Strategic indexing and query optimization using prepared statements.
**Frontend:** CSS custom properties, lazy loading, Chart.js for visualizations, and localStorage for client-side state management.

## External Dependencies

### Third-Party Libraries
*   **Chart.js:** For data visualization.
*   **Lucide Icons:** SVG icon library.
*   **Motion Animation Library:** For UI interactions.
*   **Tailwind CSS:** For styling and responsive design.
*   **Tesseract.js:** For OCR (Expense OCR Scanner).
*   **OAuth2 Google library (via Composer):** For Google Calendar integration.

### External Services
*   **Google Gemini API:** For AI-powered insights.
*   **SMTP Email Service:** For transactional email notifications.
*   **Telegram Bot API:** For instant push notifications and real-time alerts.
*   **CoinGecko API:** For live cryptocurrency price updates.
*   **Google Calendar API:** For calendar synchronization.

### Database Service
*   **PostgreSQL:** The primary database.

### Hosting Requirements
*   **Hostinger Deployment Specifications:** Requires PHP 8.2+, PostgreSQL database access, a web server (Apache/Nginx), file upload capability, cron job support, and SMTP/external API access.