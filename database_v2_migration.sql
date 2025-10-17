-- Life Atlas Organizer v2 Database Migration
-- New Modules: Career, Learning, Family, Travel, Wellness, Finance Advanced, AI Life Map

-- ========================================
-- WORK & CAREER CENTER MODULE
-- ========================================

-- Job Applications Tracker
CREATE TABLE job_applications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    company_name VARCHAR(200) NOT NULL,
    position VARCHAR(200) NOT NULL,
    job_url TEXT,
    status VARCHAR(50) DEFAULT 'applied',
    application_date DATE NOT NULL,
    follow_up_date DATE,
    salary_range VARCHAR(100),
    location VARCHAR(200),
    job_type VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_job_applications_user ON job_applications(user_id);
CREATE INDEX idx_job_applications_status ON job_applications(status);

-- Interview Tracker
CREATE TABLE interviews (
    id SERIAL PRIMARY KEY,
    job_application_id INTEGER REFERENCES job_applications(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    interview_type VARCHAR(100),
    interview_date TIMESTAMP NOT NULL,
    interviewer_name VARCHAR(200),
    interview_notes TEXT,
    outcome VARCHAR(50),
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_interviews_user ON interviews(user_id);
CREATE INDEX idx_interviews_job ON interviews(job_application_id);

-- Career Certifications & Skills
CREATE TABLE career_certifications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    issuing_organization VARCHAR(200),
    issue_date DATE,
    expiry_date DATE,
    credential_id VARCHAR(100),
    credential_url TEXT,
    skills_gained TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_certifications_user ON career_certifications(user_id);

-- Resume Versions
CREATE TABLE resume_versions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    version_name VARCHAR(200) NOT NULL,
    file_path TEXT,
    content TEXT,
    ai_feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- LEARNING & KNOWLEDGE HUB MODULE
-- ========================================

-- Courses Tracker
CREATE TABLE courses (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(250) NOT NULL,
    platform VARCHAR(100),
    instructor VARCHAR(200),
    course_url TEXT,
    status VARCHAR(50) DEFAULT 'not_started',
    progress INTEGER DEFAULT 0,
    start_date DATE,
    completion_date DATE,
    certificate_url TEXT,
    rating INTEGER,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_courses_user ON courses(user_id);
CREATE INDEX idx_courses_status ON courses(status);

-- Learning Flashcards
CREATE TABLE flashcards (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    course_id INTEGER REFERENCES courses(id) ON DELETE CASCADE,
    front_text TEXT NOT NULL,
    back_text TEXT NOT NULL,
    category VARCHAR(100),
    difficulty VARCHAR(20),
    last_reviewed TIMESTAMP,
    review_count INTEGER DEFAULT 0,
    mastery_level INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_flashcards_user ON flashcards(user_id);

-- Books Tracker
CREATE TABLE books (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(250) NOT NULL,
    author VARCHAR(200),
    isbn VARCHAR(50),
    status VARCHAR(50) DEFAULT 'to_read',
    current_page INTEGER DEFAULT 0,
    total_pages INTEGER,
    rating INTEGER,
    notes TEXT,
    started_date DATE,
    completed_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_books_user ON books(user_id);
CREATE INDEX idx_books_status ON books(status);

-- AI Document Summaries
CREATE TABLE document_summaries (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(250) NOT NULL,
    original_content TEXT,
    ai_summary TEXT,
    key_points TEXT,
    document_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- HOUSEHOLD & FAMILY MANAGER MODULE
-- ========================================

-- Family Members
CREATE TABLE family_members (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    relationship VARCHAR(100),
    email VARCHAR(200),
    phone VARCHAR(50),
    birthday DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_family_members_user ON family_members(user_id);

-- Shared Household Tasks
CREATE TABLE household_tasks (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    assigned_to_member_id INTEGER REFERENCES family_members(id) ON DELETE SET NULL,
    title VARCHAR(250) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    priority VARCHAR(50) DEFAULT 'medium',
    due_date DATE,
    completed BOOLEAN DEFAULT FALSE,
    recurring BOOLEAN DEFAULT FALSE,
    frequency VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_household_tasks_user ON household_tasks(user_id);

-- Household Expense Split
CREATE TABLE household_expenses (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    description VARCHAR(250) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    paid_by_member_id INTEGER REFERENCES family_members(id) ON DELETE SET NULL,
    expense_date DATE NOT NULL,
    category VARCHAR(100),
    split_type VARCHAR(50) DEFAULT 'equal',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Household Expense Shares
CREATE TABLE household_expense_shares (
    id SERIAL PRIMARY KEY,
    household_expense_id INTEGER REFERENCES household_expenses(id) ON DELETE CASCADE,
    family_member_id INTEGER REFERENCES family_members(id) ON DELETE CASCADE,
    share_amount DECIMAL(10,2) NOT NULL,
    paid BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Grocery Planner
CREATE TABLE grocery_lists (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE grocery_items (
    id SERIAL PRIMARY KEY,
    grocery_list_id INTEGER REFERENCES grocery_lists(id) ON DELETE CASCADE,
    item_name VARCHAR(200) NOT NULL,
    quantity VARCHAR(50),
    category VARCHAR(100),
    purchased BOOLEAN DEFAULT FALSE,
    price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- TRAVEL PLANNER & JOURNAL MODULE
-- ========================================

-- Trips
CREATE TABLE trips (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    destination VARCHAR(250) NOT NULL,
    country VARCHAR(100),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    budget DECIMAL(12,2),
    actual_spent DECIMAL(12,2),
    trip_type VARCHAR(50),
    status VARCHAR(50) DEFAULT 'planned',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_trips_user ON trips(user_id);
CREATE INDEX idx_trips_dates ON trips(start_date, end_date);

-- Trip Itinerary
CREATE TABLE trip_itinerary (
    id SERIAL PRIMARY KEY,
    trip_id INTEGER REFERENCES trips(id) ON DELETE CASCADE,
    day_number INTEGER NOT NULL,
    date DATE NOT NULL,
    title VARCHAR(250),
    description TEXT,
    location VARCHAR(250),
    start_time TIME,
    end_time TIME,
    cost DECIMAL(10,2),
    booking_reference VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_itinerary_trip ON trip_itinerary(trip_id);

-- Packing Lists
CREATE TABLE packing_lists (
    id SERIAL PRIMARY KEY,
    trip_id INTEGER REFERENCES trips(id) ON DELETE CASCADE,
    item_name VARCHAR(200) NOT NULL,
    category VARCHAR(100),
    packed BOOLEAN DEFAULT FALSE,
    quantity INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Travel Journal Entries
CREATE TABLE travel_journal (
    id SERIAL PRIMARY KEY,
    trip_id INTEGER REFERENCES trips(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    entry_date DATE NOT NULL,
    title VARCHAR(250),
    content TEXT NOT NULL,
    location VARCHAR(250),
    mood VARCHAR(50),
    weather VARCHAR(50),
    photos_urls TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- WELLNESS & MINDFULNESS HUB MODULE
-- ========================================

-- Meditation Sessions
CREATE TABLE meditation_sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    session_date DATE NOT NULL,
    duration_minutes INTEGER NOT NULL,
    meditation_type VARCHAR(100),
    technique VARCHAR(100),
    mood_before VARCHAR(50),
    mood_after VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_meditation_user ON meditation_sessions(user_id);
CREATE INDEX idx_meditation_date ON meditation_sessions(session_date);

-- Breathing Exercises
CREATE TABLE breathing_exercises (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    exercise_date DATE NOT NULL,
    exercise_type VARCHAR(100),
    duration_minutes INTEGER NOT NULL,
    rounds_completed INTEGER,
    stress_level_before INTEGER,
    stress_level_after INTEGER,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_breathing_user ON breathing_exercises(user_id);

-- Sleep Tracking
CREATE TABLE sleep_tracking (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    sleep_date DATE NOT NULL,
    bedtime TIME,
    wake_time TIME,
    duration_hours DECIMAL(4,2),
    quality_rating INTEGER,
    sleep_notes TEXT,
    dreams TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_sleep_user ON sleep_tracking(user_id);
CREATE INDEX idx_sleep_date ON sleep_tracking(sleep_date);

-- Stress & Wellness Logs
CREATE TABLE stress_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    log_date DATE NOT NULL,
    stress_level INTEGER NOT NULL,
    triggers TEXT,
    coping_strategies TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- FINANCE ADVANCED MODULE
-- ========================================

-- Subscription Manager (Enhanced)
CREATE TABLE subscriptions_advanced (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    service_name VARCHAR(200) NOT NULL,
    category VARCHAR(100),
    amount DECIMAL(10,2) NOT NULL,
    billing_cycle VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    next_billing_date DATE NOT NULL,
    auto_renew BOOLEAN DEFAULT TRUE,
    payment_method VARCHAR(100),
    subscription_url TEXT,
    cancellation_reminder_days INTEGER DEFAULT 7,
    status VARCHAR(50) DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_subscriptions_advanced_user ON subscriptions_advanced(user_id);

-- Investment Portfolio (Enhanced)
CREATE TABLE investment_portfolio (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    asset_name VARCHAR(200) NOT NULL,
    asset_type VARCHAR(50) NOT NULL,
    symbol VARCHAR(20),
    quantity DECIMAL(20,8) NOT NULL,
    purchase_price DECIMAL(20,2) NOT NULL,
    current_price DECIMAL(20,2),
    purchase_date DATE NOT NULL,
    broker VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_investment_user ON investment_portfolio(user_id);

-- Tax Documents & Receipts
CREATE TABLE tax_documents (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    document_name VARCHAR(250) NOT NULL,
    tax_year INTEGER NOT NULL,
    category VARCHAR(100),
    amount DECIMAL(12,2),
    file_path TEXT,
    document_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_tax_documents_user ON tax_documents(user_id);
CREATE INDEX idx_tax_documents_year ON tax_documents(tax_year);

-- Financial Goals & Projections
CREATE TABLE financial_projections (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    projection_type VARCHAR(100) NOT NULL,
    target_amount DECIMAL(12,2) NOT NULL,
    current_amount DECIMAL(12,2),
    target_date DATE NOT NULL,
    monthly_contribution DECIMAL(12,2),
    ai_recommendation TEXT,
    status VARCHAR(50) DEFAULT 'in_progress',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- AI LIFE MAP DASHBOARD MODULE
-- ========================================

-- Life Area Metrics
CREATE TABLE life_area_metrics (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    metric_date DATE NOT NULL,
    area VARCHAR(100) NOT NULL,
    score INTEGER NOT NULL,
    trend VARCHAR(20),
    ai_insights TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_life_metrics_user ON life_area_metrics(user_id);
CREATE INDEX idx_life_metrics_date ON life_area_metrics(metric_date);

-- AI Weekly Summaries
CREATE TABLE ai_weekly_summaries (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    week_start DATE NOT NULL,
    week_end DATE NOT NULL,
    finance_summary TEXT,
    health_summary TEXT,
    productivity_summary TEXT,
    social_summary TEXT,
    overall_insights TEXT,
    improvement_suggestions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_weekly_summaries_user ON ai_weekly_summaries(user_id);

-- Life Balance Tracking
CREATE TABLE life_balance_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    log_date DATE NOT NULL,
    work_hours DECIMAL(4,2),
    health_hours DECIMAL(4,2),
    social_hours DECIMAL(4,2),
    learning_hours DECIMAL(4,2),
    personal_hours DECIMAL(4,2),
    balance_score INTEGER,
    ai_feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- PRIVACY & BACKUP CENTER ENHANCEMENTS
-- ========================================

-- Encrypted Cloud Backups
CREATE TABLE cloud_backups (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    backup_name VARCHAR(250) NOT NULL,
    backup_size BIGINT,
    encryption_key_hash VARCHAR(255),
    cloud_provider VARCHAR(100),
    backup_url TEXT,
    modules_included TEXT,
    backup_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_date TIMESTAMP,
    status VARCHAR(50) DEFAULT 'active'
);

CREATE INDEX idx_cloud_backups_user ON cloud_backups(user_id);

-- Data Export Logs
CREATE TABLE data_export_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    export_type VARCHAR(100) NOT NULL,
    modules_exported TEXT,
    file_format VARCHAR(50),
    file_size BIGINT,
    export_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ========================================
-- LIFEHUB AI ADVISOR 2.0
-- ========================================

-- AI Chat Contexts (Enhanced)
CREATE TABLE ai_chat_contexts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    context_type VARCHAR(100) NOT NULL,
    context_data JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- AI Daily Briefings (Enhanced)
CREATE TABLE ai_daily_briefings_v2 (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    briefing_date DATE NOT NULL,
    finance_insights TEXT,
    health_insights TEXT,
    productivity_insights TEXT,
    social_insights TEXT,
    action_items TEXT,
    priority_tasks TEXT,
    weather_mood_correlation TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_daily_briefings_v2_user ON ai_daily_briefings_v2(user_id);
CREATE INDEX idx_daily_briefings_v2_date ON ai_daily_briefings_v2(briefing_date);

-- AI Module Connections
CREATE TABLE ai_module_connections (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    module_from VARCHAR(100) NOT NULL,
    module_to VARCHAR(100) NOT NULL,
    connection_type VARCHAR(100),
    insight TEXT,
    strength_score INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Grant permissions
GRANT ALL ON ALL TABLES IN SCHEMA public TO CURRENT_USER;
GRANT ALL ON ALL SEQUENCES IN SCHEMA public TO CURRENT_USER;
