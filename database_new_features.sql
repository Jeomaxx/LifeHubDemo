-- New Features Database Schema
-- Life Atlas Organizer - Additional Modules

-- 1. CALENDAR MODULE
CREATE TABLE IF NOT EXISTS calendar_events (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    start_datetime TIMESTAMP NOT NULL,
    end_datetime TIMESTAMP NOT NULL,
    event_type VARCHAR(50), -- 'custom', 'task', 'bill', 'birthday', 'gym', 'appointment'
    source_module VARCHAR(50), -- which module the event came from
    source_id INTEGER, -- ID from the source module
    location VARCHAR(255),
    reminder_minutes INTEGER DEFAULT 30,
    color VARCHAR(20),
    is_all_day BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_calendar_events_user_date ON calendar_events(user_id, start_datetime);
CREATE INDEX idx_calendar_events_source ON calendar_events(source_module, source_id);

-- 2. NOTES / KNOWLEDGE BASE MODULE
CREATE TABLE IF NOT EXISTS notes (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    content TEXT,
    category VARCHAR(100),
    tags VARCHAR(500), -- JSON array of tags
    is_favorite BOOLEAN DEFAULT FALSE,
    is_archived BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS note_categories (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_notes_user ON notes(user_id);
CREATE INDEX idx_notes_category ON notes(category);

-- 3. PROJECT MANAGEMENT (KANBAN) MODULE
CREATE TABLE IF NOT EXISTS projects (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'active', -- 'active', 'completed', 'archived'
    priority VARCHAR(50) DEFAULT 'medium',
    start_date DATE,
    target_date DATE,
    progress INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS project_tasks (
    id SERIAL PRIMARY KEY,
    project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'todo', -- 'todo', 'in_progress', 'done'
    priority VARCHAR(50) DEFAULT 'medium',
    assigned_to INTEGER REFERENCES users(id),
    due_date DATE,
    position INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS project_checklists (
    id SERIAL PRIMARY KEY,
    task_id INTEGER NOT NULL REFERENCES project_tasks(id) ON DELETE CASCADE,
    item_text VARCHAR(300) NOT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    position INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS project_attachments (
    id SERIAL PRIMARY KEY,
    project_id INTEGER REFERENCES projects(id) ON DELETE CASCADE,
    task_id INTEGER REFERENCES project_tasks(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INTEGER,
    file_type VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_projects_user ON projects(user_id);
CREATE INDEX idx_project_tasks_project ON project_tasks(project_id);

-- 4. POMODORO TIMER MODULE
CREATE TABLE IF NOT EXISTS pomodoro_sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    task_id INTEGER REFERENCES tasks(id) ON DELETE SET NULL,
    habit_id INTEGER REFERENCES habits(id) ON DELETE SET NULL,
    project_task_id INTEGER REFERENCES project_tasks(id) ON DELETE SET NULL,
    session_type VARCHAR(20) DEFAULT 'work', -- 'work', 'short_break', 'long_break'
    duration_minutes INTEGER NOT NULL,
    completed BOOLEAN DEFAULT FALSE,
    start_time TIMESTAMP,
    end_time TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_pomodoro_user_date ON pomodoro_sessions(user_id, created_at);

-- 5. DEDICATED BUDGETING MODULE (ENVELOPE BUDGETING)
CREATE TABLE IF NOT EXISTS budget_envelopes (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100),
    monthly_allocation DECIMAL(10,2) NOT NULL DEFAULT 0,
    current_balance DECIMAL(10,2) NOT NULL DEFAULT 0,
    color VARCHAR(20),
    icon VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS budget_transactions (
    id SERIAL PRIMARY KEY,
    envelope_id INTEGER NOT NULL REFERENCES budget_envelopes(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    amount DECIMAL(10,2) NOT NULL,
    transaction_type VARCHAR(20) NOT NULL, -- 'debit', 'credit', 'allocation'
    description TEXT,
    transaction_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_budget_envelopes_user ON budget_envelopes(user_id);
CREATE INDEX idx_budget_transactions_envelope ON budget_transactions(envelope_id);

-- 6. DEBT PAYOFF PLANNER MODULE
CREATE TABLE IF NOT EXISTS debts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(150) NOT NULL,
    debt_type VARCHAR(50), -- 'credit_card', 'student_loan', 'mortgage', 'personal_loan', 'car_loan'
    principal_amount DECIMAL(12,2) NOT NULL,
    current_balance DECIMAL(12,2) NOT NULL,
    interest_rate DECIMAL(5,2) NOT NULL,
    minimum_payment DECIMAL(10,2),
    payment_due_day INTEGER, -- day of month
    start_date DATE,
    payoff_strategy VARCHAR(50), -- 'snowball', 'avalanche', 'custom'
    priority_order INTEGER,
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS debt_payments (
    id SERIAL PRIMARY KEY,
    debt_id INTEGER NOT NULL REFERENCES debts(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    payment_amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    principal_paid DECIMAL(10,2),
    interest_paid DECIMAL(10,2),
    remaining_balance DECIMAL(12,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_debts_user ON debts(user_id);
CREATE INDEX idx_debt_payments_debt ON debt_payments(debt_id);

-- 7. RECIPE BOOK & MEAL PLANNER MODULE
CREATE TABLE IF NOT EXISTS recipes (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    category VARCHAR(100), -- 'breakfast', 'lunch', 'dinner', 'snack', 'dessert'
    cuisine VARCHAR(100),
    prep_time INTEGER, -- minutes
    cook_time INTEGER, -- minutes
    servings INTEGER,
    ingredients TEXT, -- JSON array
    instructions TEXT,
    image_url VARCHAR(500),
    source_url VARCHAR(500),
    nutrition_info TEXT, -- JSON object
    is_favorite BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS meal_plans (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    recipe_id INTEGER REFERENCES recipes(id) ON DELETE CASCADE,
    meal_date DATE NOT NULL,
    meal_type VARCHAR(50), -- 'breakfast', 'lunch', 'dinner', 'snack'
    servings INTEGER DEFAULT 1,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS shopping_lists (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(150) NOT NULL,
    week_start_date DATE,
    status VARCHAR(50) DEFAULT 'active', -- 'active', 'completed'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS shopping_list_items (
    id SERIAL PRIMARY KEY,
    shopping_list_id INTEGER NOT NULL REFERENCES shopping_lists(id) ON DELETE CASCADE,
    item_name VARCHAR(200) NOT NULL,
    quantity VARCHAR(50),
    category VARCHAR(100),
    is_purchased BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_recipes_user ON recipes(user_id);
CREATE INDEX idx_meal_plans_user_date ON meal_plans(user_id, meal_date);

-- 8. VEHICLE MAINTENANCE MODULE
CREATE TABLE IF NOT EXISTS vehicles (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    make VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    year INTEGER,
    vin VARCHAR(50),
    license_plate VARCHAR(50),
    current_mileage INTEGER,
    purchase_date DATE,
    color VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS vehicle_maintenance (
    id SERIAL PRIMARY KEY,
    vehicle_id INTEGER NOT NULL REFERENCES vehicles(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    service_type VARCHAR(100) NOT NULL, -- 'oil_change', 'tire_rotation', 'brake_service', 'inspection', etc.
    service_date DATE NOT NULL,
    mileage INTEGER,
    cost DECIMAL(10,2),
    service_provider VARCHAR(150),
    next_service_date DATE,
    next_service_mileage INTEGER,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_vehicles_user ON vehicles(user_id);
CREATE INDEX idx_vehicle_maintenance_vehicle ON vehicle_maintenance(vehicle_id);

-- 9. SLEEP TRACKER MODULE
CREATE TABLE IF NOT EXISTS sleep_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    sleep_date DATE NOT NULL,
    bedtime TIMESTAMP,
    wake_time TIMESTAMP,
    duration_hours DECIMAL(4,2),
    quality VARCHAR(50), -- 'poor', 'fair', 'good', 'excellent'
    sleep_score INTEGER, -- 1-100
    notes TEXT,
    mood_on_waking VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, sleep_date)
);

CREATE INDEX idx_sleep_logs_user_date ON sleep_logs(user_id, sleep_date);

-- 10. MEDICATION & SUPPLEMENT TRACKER MODULE
CREATE TABLE IF NOT EXISTS medications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    medication_type VARCHAR(50), -- 'medication', 'supplement', 'vitamin'
    dosage VARCHAR(100),
    frequency VARCHAR(100), -- 'daily', 'twice_daily', 'weekly', etc.
    time_of_day VARCHAR(100), -- 'morning', 'afternoon', 'evening', 'bedtime'
    start_date DATE,
    end_date DATE,
    prescribing_doctor VARCHAR(150),
    purpose TEXT,
    current_quantity INTEGER,
    refill_reminder_quantity INTEGER,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS medication_logs (
    id SERIAL PRIMARY KEY,
    medication_id INTEGER NOT NULL REFERENCES medications(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    log_date DATE NOT NULL,
    log_time TIME,
    taken BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_medications_user ON medications(user_id);
CREATE INDEX idx_medication_logs_user_date ON medication_logs(user_id, log_date);

-- 11. SYMPTOM TRACKER MODULE
CREATE TABLE IF NOT EXISTS symptoms (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(100), -- 'pain', 'digestive', 'respiratory', 'mental', etc.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS symptom_logs (
    id SERIAL PRIMARY KEY,
    symptom_id INTEGER NOT NULL REFERENCES symptoms(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    log_date DATE NOT NULL,
    log_time TIME,
    severity INTEGER, -- 1-10 scale
    duration_minutes INTEGER,
    triggers TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_symptoms_user ON symptoms(user_id);
CREATE INDEX idx_symptom_logs_user_date ON symptom_logs(user_id, log_date);

-- 12. PERSONAL CRM (CONTACTS) MODULE
CREATE TABLE IF NOT EXISTS contacts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    relationship VARCHAR(100), -- 'family', 'friend', 'professional', 'acquaintance'
    email VARCHAR(200),
    phone VARCHAR(50),
    birthday DATE,
    address TEXT,
    company VARCHAR(150),
    job_title VARCHAR(150),
    notes TEXT,
    importance VARCHAR(50) DEFAULT 'medium', -- 'low', 'medium', 'high', 'vip'
    tags VARCHAR(500), -- JSON array
    is_favorite BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contact_interactions (
    id SERIAL PRIMARY KEY,
    contact_id INTEGER NOT NULL REFERENCES contacts(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    interaction_date DATE NOT NULL,
    interaction_type VARCHAR(100), -- 'call', 'email', 'meeting', 'text', 'social'
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS contact_reminders (
    id SERIAL PRIMARY KEY,
    contact_id INTEGER NOT NULL REFERENCES contacts(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    reminder_type VARCHAR(50), -- 'keep_in_touch', 'birthday', 'anniversary', 'follow_up'
    frequency_days INTEGER, -- how often to remind (e.g., every 7 days)
    last_contact_date DATE,
    next_reminder_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_contacts_user ON contacts(user_id);
CREATE INDEX idx_contact_interactions_contact ON contact_interactions(contact_id);

-- 13. EVENT PLANNER MODULE
CREATE TABLE IF NOT EXISTS events (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(200) NOT NULL,
    event_type VARCHAR(100), -- 'party', 'wedding', 'meeting', 'trip', 'other'
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME,
    location VARCHAR(300),
    budget DECIMAL(10,2),
    actual_cost DECIMAL(10,2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'planning', -- 'planning', 'confirmed', 'completed', 'cancelled'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS event_checklists (
    id SERIAL PRIMARY KEY,
    event_id INTEGER NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    item_text VARCHAR(300) NOT NULL,
    is_completed BOOLEAN DEFAULT FALSE,
    due_date DATE,
    position INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS event_guests (
    id SERIAL PRIMARY KEY,
    event_id INTEGER NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    contact_id INTEGER REFERENCES contacts(id) ON DELETE SET NULL,
    name VARCHAR(200) NOT NULL,
    email VARCHAR(200),
    phone VARCHAR(50),
    rsvp_status VARCHAR(50) DEFAULT 'pending', -- 'pending', 'attending', 'declined', 'maybe'
    plus_one INTEGER DEFAULT 0,
    dietary_restrictions TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS event_budget_items (
    id SERIAL PRIMARY KEY,
    event_id INTEGER NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    category VARCHAR(100) NOT NULL,
    description VARCHAR(300),
    estimated_cost DECIMAL(10,2),
    actual_cost DECIMAL(10,2),
    is_paid BOOLEAN DEFAULT FALSE,
    payment_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_events_user ON events(user_id);
CREATE INDEX idx_event_guests_event ON event_guests(event_id);

-- 14. MULTI-USER / FAMILY SHARING MODULE
CREATE TABLE IF NOT EXISTS shared_modules (
    id SERIAL PRIMARY KEY,
    owner_user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    module_name VARCHAR(100) NOT NULL, -- 'calendar', 'shopping_lists', 'bills', 'meal_plans', etc.
    share_name VARCHAR(200), -- friendly name for this share (e.g., "Family Calendar")
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS shared_access (
    id SERIAL PRIMARY KEY,
    shared_module_id INTEGER NOT NULL REFERENCES shared_modules(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    permission_level VARCHAR(50) DEFAULT 'view', -- 'view', 'edit', 'admin'
    invited_email VARCHAR(200),
    invitation_status VARCHAR(50) DEFAULT 'pending', -- 'pending', 'accepted', 'declined'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    accepted_at TIMESTAMP
);

CREATE INDEX idx_shared_modules_owner ON shared_modules(owner_user_id);
CREATE INDEX idx_shared_access_user ON shared_access(user_id);

-- 15. CALENDAR SYNC (GOOGLE/OUTLOOK) MODULE
CREATE TABLE IF NOT EXISTS calendar_sync_settings (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    provider VARCHAR(50) NOT NULL, -- 'google', 'outlook', 'caldav'
    calendar_name VARCHAR(200),
    sync_enabled BOOLEAN DEFAULT TRUE,
    sync_direction VARCHAR(50) DEFAULT 'bidirectional', -- 'import_only', 'export_only', 'bidirectional'
    last_sync_at TIMESTAMP,
    access_token TEXT,
    refresh_token TEXT,
    token_expires_at TIMESTAMP,
    calendar_id VARCHAR(200), -- provider-specific calendar ID
    settings TEXT, -- JSON object for provider-specific settings
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, provider, calendar_id)
);

CREATE TABLE IF NOT EXISTS calendar_sync_logs (
    id SERIAL PRIMARY KEY,
    sync_setting_id INTEGER NOT NULL REFERENCES calendar_sync_settings(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    sync_direction VARCHAR(50), -- 'import', 'export'
    events_synced INTEGER DEFAULT 0,
    status VARCHAR(50), -- 'success', 'partial', 'failed'
    error_message TEXT,
    sync_started_at TIMESTAMP,
    sync_completed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_calendar_sync_settings_user ON calendar_sync_settings(user_id);
CREATE INDEX idx_calendar_sync_logs_setting ON calendar_sync_logs(sync_setting_id);
