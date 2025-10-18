-- Database Upgrade V2 - New Features Schema
-- Life Atlas Organizer - Advanced Features

-- Security Analytics Tables
CREATE TABLE IF NOT EXISTS security_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    event_type VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    details JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_event (user_id, event_type),
    INDEX idx_created_at (created_at)
);

-- Emergency Mode Tables
CREATE TABLE IF NOT EXISTS emergency_contacts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(255),
    relationship VARCHAR(50),
    priority INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_priority (user_id, priority)
);

CREATE TABLE IF NOT EXISTS health_profiles (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE UNIQUE,
    blood_type VARCHAR(5),
    medical_id VARCHAR(50),
    allergies TEXT,
    conditions TEXT,
    medications TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS emergency_log (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    contacts_notified INTEGER DEFAULT 0,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_timestamp (user_id, timestamp)
);

-- Sleep and Meditation Tables
CREATE TABLE IF NOT EXISTS sleep_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    sleep_date DATE NOT NULL,
    hours DECIMAL(4, 2) NOT NULL,
    quality_rating INTEGER CHECK (quality_rating BETWEEN 1 AND 10),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_date (user_id, sleep_date)
);

CREATE TABLE IF NOT EXISTS meditation_sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    session_date DATE NOT NULL,
    duration_minutes INTEGER NOT NULL,
    meditation_type VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_date (user_id, session_date)
);

-- Voice Command History
CREATE TABLE IF NOT EXISTS voice_commands (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    command_text TEXT NOT NULL,
    action_taken VARCHAR(100),
    success BOOLEAN DEFAULT TRUE,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_timestamp (user_id, timestamp)
);

-- Receipt Scans
CREATE TABLE IF NOT EXISTS receipt_scans (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    image_path VARCHAR(255),
    ocr_text TEXT,
    extracted_amount DECIMAL(12, 2),
    extracted_date DATE,
    extracted_merchant VARCHAR(255),
    category VARCHAR(100),
    processed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_date (user_id, created_at)
);

-- Multi-Currency Support
ALTER TABLE users ADD COLUMN IF NOT EXISTS currency_preference VARCHAR(3) DEFAULT 'USD';

CREATE TABLE IF NOT EXISTS currency_rates (
    id SERIAL PRIMARY KEY,
    from_currency VARCHAR(3) NOT NULL,
    to_currency VARCHAR(3) NOT NULL,
    rate DECIMAL(18, 8) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (from_currency, to_currency),
    INDEX idx_currencies (from_currency, to_currency)
);

-- Team Collaboration Tables
CREATE TABLE IF NOT EXISTS team_boards (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    owner_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    visibility VARCHAR(20) DEFAULT 'private',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS team_board_members (
    id SERIAL PRIMARY KEY,
    board_id INTEGER NOT NULL REFERENCES team_boards(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role VARCHAR(20) DEFAULT 'viewer',
    invited_by INTEGER REFERENCES users(id),
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (board_id, user_id)
);

CREATE TABLE IF NOT EXISTS team_tasks (
    id SERIAL PRIMARY KEY,
    board_id INTEGER NOT NULL REFERENCES team_boards(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    assigned_to INTEGER REFERENCES users(id),
    status VARCHAR(50) DEFAULT 'todo',
    priority VARCHAR(20) DEFAULT 'medium',
    due_date DATE,
    created_by INTEGER NOT NULL REFERENCES users(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_board_status (board_id, status)
);

-- AI Report Generation
CREATE TABLE IF NOT EXISTS ai_reports (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    report_type VARCHAR(50) NOT NULL,
    report_period VARCHAR(50) NOT NULL,
    content TEXT NOT NULL,
    metrics JSONB,
    sent_via VARCHAR(20),
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_type (user_id, report_type)
);

-- Goal Progress Tracking
CREATE TABLE IF NOT EXISTS goal_milestones (
    id SERIAL PRIMARY KEY,
    goal_id INTEGER NOT NULL REFERENCES goals(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    target_value DECIMAL(12, 2),
    current_value DECIMAL(12, 2) DEFAULT 0,
    target_date DATE,
    completed BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS goal_progress_history (
    id SERIAL PRIMARY KEY,
    goal_id INTEGER NOT NULL REFERENCES goals(id) ON DELETE CASCADE,
    progress_value DECIMAL(12, 2) NOT NULL,
    notes TEXT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_goal_date (goal_id, recorded_at)
);

-- Health Device Integration
CREATE TABLE IF NOT EXISTS health_device_sync (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    device_type VARCHAR(50) NOT NULL,
    device_id VARCHAR(255),
    last_sync TIMESTAMP,
    sync_status VARCHAR(20),
    data JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_device (user_id, device_type)
);

-- Nutrition Planning
CREATE TABLE IF NOT EXISTS meal_plans (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    plan_name VARCHAR(255) NOT NULL,
    plan_type VARCHAR(50),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    daily_calories INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_dates (user_id, start_date, end_date)
);

CREATE TABLE IF NOT EXISTS meal_plan_items (
    id SERIAL PRIMARY KEY,
    meal_plan_id INTEGER NOT NULL REFERENCES meal_plans(id) ON DELETE CASCADE,
    day_of_week INTEGER CHECK (day_of_week BETWEEN 1 AND 7),
    meal_type VARCHAR(20),
    recipe_id INTEGER,
    description TEXT,
    calories INTEGER,
    protein DECIMAL(6, 2),
    carbs DECIMAL(6, 2),
    fats DECIMAL(6, 2)
);

-- Tax Reports
CREATE TABLE IF NOT EXISTS tax_reports (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    report_year INTEGER NOT NULL,
    report_type VARCHAR(50) NOT NULL,
    total_income DECIMAL(12, 2),
    total_expenses DECIMAL(12, 2),
    deductions DECIMAL(12, 2),
    tax_estimate DECIMAL(12, 2),
    report_file VARCHAR(255),
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_year (user_id, report_year)
);

-- Analytics Correlation Data
CREATE TABLE IF NOT EXISTS life_analytics_snapshots (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    snapshot_date DATE NOT NULL,
    productivity_score INTEGER,
    finance_score INTEGER,
    health_score INTEGER,
    overall_score INTEGER,
    metrics JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_date (user_id, snapshot_date)
);

-- Indexes for Performance
CREATE INDEX IF NOT EXISTS idx_tasks_user_status ON tasks(user_id, status);
CREATE INDEX IF NOT EXISTS idx_finance_user_date ON finance(user_id, date);
CREATE INDEX IF NOT EXISTS idx_health_user_date ON health(user_id, date);
CREATE INDEX IF NOT EXISTS idx_goals_user_status ON goals(user_id, status);
CREATE INDEX IF NOT EXISTS idx_habits_user ON habits(user_id);
CREATE INDEX IF NOT EXISTS idx_bills_user_due ON bills(user_id, due_date);

-- Update existing tables
ALTER TABLE users ADD COLUMN IF NOT EXISTS telegram_chat_id VARCHAR(50);
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_active_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Function to update last_active timestamp
CREATE OR REPLACE FUNCTION update_last_active()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE users SET last_active_at = CURRENT_TIMESTAMP WHERE id = NEW.user_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create triggers for activity tracking (examples)
CREATE TRIGGER update_user_activity_tasks
    AFTER INSERT OR UPDATE ON tasks
    FOR EACH ROW
    EXECUTE FUNCTION update_last_active();

CREATE TRIGGER update_user_activity_finance
    AFTER INSERT OR UPDATE ON finance
    FOR EACH ROW
    EXECUTE FUNCTION update_last_active();

-- Comments
COMMENT ON TABLE security_logs IS 'Tracks all security-related events including login attempts';
COMMENT ON TABLE emergency_contacts IS 'Emergency contacts for emergency mode feature';
COMMENT ON TABLE health_profiles IS 'Medical information for emergency sharing';
COMMENT ON TABLE team_boards IS 'Collaborative Kanban boards for team productivity';
COMMENT ON TABLE ai_reports IS 'AI-generated reports sent to users via email/telegram';
COMMENT ON TABLE life_analytics_snapshots IS 'Daily snapshots for unified analytics correlations';
