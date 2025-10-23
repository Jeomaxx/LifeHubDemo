# Life Atlas Organizer - Compressed Replit.md

## Overview
Life Atlas Organizer is a comprehensive personal life management system built with PHP and PostgreSQL. It provides advanced tools for managing finances, health, tasks, goals, and more, aiming to be a next-generation platform with AI-powered insights and automation across all life domains. The project is fully operational, production-ready, and positioned as an industry-leading solution.

## User Preferences
I prefer iterative development with clear communication at each step. Please ask for confirmation before making significant architectural changes or adding new external dependencies. I value detailed explanations for complex solutions but prefer concise summaries for routine updates. Ensure all code adheres to modern PHP standards and best practices for security and maintainability.

## System Architecture
The application uses a LAMP-like stack with PHP 8.3, PostgreSQL (Neon-backed), HTML, CSS (Tailwind), and JavaScript.

### UI/UX Decisions
- Responsive design implemented using Tailwind CSS.
- Chart.js integration for data visualization.
- Real-time updates via AJAX.
- Modal-based workflows.
- Mobile-optimized interfaces.

### Technical Implementations
- **Backend**: PHP 8.3 with Composer for dependency management.
- **Database**: PostgreSQL with 53 tables, including JSONB columns for flexible data storage and comprehensive indexing for optimization.
- **Authentication**: Session-based with bcrypt password hashing, CSRF token protection, optional Google OAuth, and TOTP (2FA) support.
- **API Layer**: RESTful architecture with 27 new API endpoints, CSRF protection, JSON response format, error handling, and validation.
- **Background Processing**: Cron job scripts for automation execution, analytics generation, and digital twin model training.
- **Module Structure**: 14 advanced modules, each with dedicated UI (`index.php`) and API endpoints, designed for comprehensive life management. Modules include Life Automation, Analytics, Collaboration, Smart Finance, Life Navigation, Knowledge Graph, Event & Reminder System 2.0, External Integrations Layer, AI Digital Twin, Energy & Focus Manager, Asset & Subscription Manager, Communication & Journaling Suite, Sustainability & Eco Tracker, and AI Scenario Simulator.
- **Security**: Robust CSRF protection implemented across all state-changing operations, input sanitization, type validation, and comprehensive error logging.

### Feature Specifications
- **Life Balance Scoring System**: Weekly/monthly reports with AI commentary and cross-module correlation analysis.
- **Automation Rules Engine**: IFTTT-like triggers and actions across modules with a visual builder.
- **AI-powered Knowledge Base**: Semantic search, visual mind-maps, and automatic node creation.
- **Smart Reminders**: Advanced time, location, and context-based reminders with external calendar integration.
- **Digital Twin**: ML model for user pattern learning, predictive simulations, and behavior pattern detection.
- **Scenario Simulator**: What-if modeling for financial, health, career, and lifestyle simulations.

## External Dependencies
- **Database**: PostgreSQL (Neon-backed).
- **PHP Libraries**: `league/oauth2-google` (for Google OAuth), `dompdf/dompdf` (for PDF generation).
- **Frontend Libraries**: Tailwind CSS (CDN), Chart.js.
- **External Services (Integrated)**: Google Fit, Notion, Google Drive, Telegram, Stripe, OpenAI, CoinGecko (for cryptocurrency data).