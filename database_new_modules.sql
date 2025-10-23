-- New Modules Database Schema for Life Atlas Organizer
-- Created for Next-Phase Features

-- ============================================
-- 1. LIFE AUTOMATION & SMART ACTIONS MODULE
-- ============================================

CREATE TABLE IF NOT EXISTS automation_rules (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    rule_name VARCHAR(255) NOT NULL,
    description TEXT,
    trigger_type VARCHAR(100) NOT NULL, -- 'schedule', 'event', 'condition', 'manual'
    trigger_config JSONB NOT NULL, -- Stores trigger configuration
    action_type VARCHAR(100) NOT NULL, -- 'finance', 'goal', 'notification', 'task', etc.
    action_config JSONB NOT NULL, -- Stores action configuration
    conditions JSONB, -- Additional conditions for rule execution
    is_active BOOLEAN DEFAULT true,
    last_triggered_at TIMESTAMP,
    execution_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS automation_logs (
    id SERIAL PRIMARY KEY,
    rule_id INTEGER REFERENCES automation_rules(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    execution_status VARCHAR(50) NOT NULL, -- 'success', 'failed', 'skipped'
    trigger_data JSONB,
    action_result JSONB,
    error_message TEXT,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 2. LIFE ANALYTICS & INSIGHT ENGINE MODULE
-- ============================================

CREATE TABLE IF NOT EXISTS life_reports (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    report_type VARCHAR(50) NOT NULL, -- 'daily', 'weekly', 'monthly', 'annual'
    report_period DATE NOT NULL,
    life_balance_score DECIMAL(5,2), -- Overall life balance score
    health_score DECIMAL(5,2),
    finance_score DECIMAL(5,2),
    productivity_score DECIMAL(5,2),
    mood_score DECIMAL(5,2),
    insights JSONB, -- AI-generated insights and commentary
    correlations JSONB, -- Data correlations (sleep vs productivity, etc.)
    recommendations JSONB, -- AI recommendations
    metrics_data JSONB, -- Raw metrics data for the period
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS life_analytics_data (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    metric_date DATE NOT NULL,
    metric_type VARCHAR(100) NOT NULL, -- 'sleep', 'spending', 'productivity', 'mood', etc.
    metric_value DECIMAL(10,2) NOT NULL,
    metric_unit VARCHAR(50), -- 'hours', 'dollars', 'score', etc.
    source_module VARCHAR(100), -- 'health', 'finance', 'tasks', etc.
    metadata JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, metric_date, metric_type)
);

-- ============================================
-- 3. COLLABORATION & SHARED SPACES MODULE
-- ============================================

CREATE TABLE IF NOT EXISTS shared_spaces (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    owner_user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    space_type VARCHAR(50) NOT NULL, -- 'family', 'team', 'project', 'friends'
    settings JSONB, -- Space-specific settings
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS shared_space_members (
    id SERIAL PRIMARY KEY,
    space_id INTEGER REFERENCES shared_spaces(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    role VARCHAR(50) NOT NULL, -- 'owner', 'admin', 'member', 'viewer'
    permissions JSONB, -- Specific permissions for this member
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    invited_by INTEGER REFERENCES users(id),
    UNIQUE(space_id, user_id)
);

CREATE TABLE IF NOT EXISTS shared_tasks (
    id SERIAL PRIMARY KEY,
    space_id INTEGER REFERENCES shared_spaces(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    assigned_to INTEGER REFERENCES users(id),
    created_by INTEGER REFERENCES users(id),
    priority VARCHAR(50) DEFAULT 'medium',
    status VARCHAR(50) DEFAULT 'pending',
    due_date DATE,
    completed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS shared_notes (
    id SERIAL PRIMARY KEY,
    space_id INTEGER REFERENCES shared_spaces(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    created_by INTEGER REFERENCES users(id),
    is_pinned BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 4. SMART FINANCE & BUSINESS SUITE MODULE
-- ============================================

CREATE TABLE IF NOT EXISTS business_profiles (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    business_name VARCHAR(255) NOT NULL,
    business_type VARCHAR(100), -- 'freelance', 'startup', 'small_business'
    registration_number VARCHAR(100),
    tax_id VARCHAR(100),
    address TEXT,
    currency VARCHAR(10) DEFAULT 'USD',
    settings JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS business_invoices (
    id SERIAL PRIMARY KEY,
    business_id INTEGER REFERENCES business_profiles(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    invoice_number VARCHAR(100) NOT NULL UNIQUE,
    client_name VARCHAR(255) NOT NULL,
    client_email VARCHAR(255),
    client_address TEXT,
    invoice_date DATE NOT NULL,
    due_date DATE,
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(15,2) DEFAULT 0,
    discount_amount DECIMAL(15,2) DEFAULT 0,
    total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    currency VARCHAR(10) DEFAULT 'USD',
    status VARCHAR(50) DEFAULT 'draft', -- 'draft', 'sent', 'paid', 'overdue', 'cancelled'
    paid_at TIMESTAMP,
    notes TEXT,
    items JSONB, -- Array of invoice items
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS business_expenses (
    id SERIAL PRIMARY KEY,
    business_id INTEGER REFERENCES business_profiles(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    expense_date DATE NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    amount DECIMAL(15,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    payment_method VARCHAR(100),
    is_billable BOOLEAN DEFAULT false,
    receipt_url TEXT,
    tax_deductible BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS currency_wallets (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    currency_code VARCHAR(10) NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0,
    wallet_name VARCHAR(255),
    is_primary BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, currency_code)
);

-- ============================================
-- 5. LIFE NAVIGATION & PLANNING (ROADMAP) MODULE
-- ============================================

CREATE TABLE IF NOT EXISTS life_roadmap (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    roadmap_type VARCHAR(50) NOT NULL, -- '1year', '5year', '10year', 'lifetime'
    title VARCHAR(255) NOT NULL,
    description TEXT,
    target_date DATE,
    category VARCHAR(100), -- 'career', 'finance', 'health', 'relationships', 'personal'
    milestones JSONB, -- Array of milestones with dates and descriptions
    progress_percentage DECIMAL(5,2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'active', -- 'active', 'achieved', 'abandoned', 'on_hold'
    vision_statement TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS roadmap_milestones (
    id SERIAL PRIMARY KEY,
    roadmap_id INTEGER REFERENCES life_roadmap(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    target_date DATE,
    completed_date DATE,
    is_completed BOOLEAN DEFAULT false,
    progress_percentage DECIMAL(5,2) DEFAULT 0,
    linked_goals JSONB, -- Links to existing goals
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 6. KNOWLEDGE GRAPH & MEMORY CENTER MODULE
-- ============================================

CREATE TABLE IF NOT EXISTS knowledge_nodes (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    node_type VARCHAR(100) NOT NULL, -- 'note', 'document', 'activity', 'concept', 'person', 'event'
    title VARCHAR(255) NOT NULL,
    content TEXT,
    tags JSONB, -- Array of tags
    metadata JSONB, -- Additional metadata
    source_module VARCHAR(100), -- Module that created this node
    source_id INTEGER, -- ID in the source module
    embedding_vector TEXT, -- For semantic search (can be enhanced with actual vector type)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS knowledge_relationships (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    from_node_id INTEGER REFERENCES knowledge_nodes(id) ON DELETE CASCADE,
    to_node_id INTEGER REFERENCES knowledge_nodes(id) ON DELETE CASCADE,
    relationship_type VARCHAR(100) NOT NULL, -- 'related_to', 'part_of', 'caused_by', 'similar_to', etc.
    strength DECIMAL(3,2) DEFAULT 1.0, -- Relationship strength (0.0 to 1.0)
    metadata JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(from_node_id, to_node_id, relationship_type)
);

-- ============================================
-- 7. EVENT & REMINDER SYSTEM 2.0 MODULE
-- ============================================

CREATE TABLE IF NOT EXISTS smart_reminders (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    reminder_type VARCHAR(50) NOT NULL, -- 'time_based', 'location_based', 'context_based'
    trigger_config JSONB NOT NULL, -- Configuration for when to trigger
    recurrence_pattern VARCHAR(100), -- 'daily', 'weekly', 'monthly', 'custom'
    recurrence_config JSONB, -- Detailed recurrence settings
    smart_snooze_enabled BOOLEAN DEFAULT false,
    snooze_conditions JSONB, -- Conditions for smart snooze
    priority VARCHAR(50) DEFAULT 'medium',
    is_active BOOLEAN DEFAULT true,
    next_trigger_at TIMESTAMP,
    last_triggered_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS external_calendar_sync (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    calendar_type VARCHAR(50) NOT NULL, -- 'google', 'outlook', 'apple', 'caldav'
    calendar_id VARCHAR(255) NOT NULL,
    access_token TEXT,
    refresh_token TEXT,
    sync_enabled BOOLEAN DEFAULT true,
    last_sync_at TIMESTAMP,
    sync_direction VARCHAR(50) DEFAULT 'bidirectional', -- 'import', 'export', 'bidirectional'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 8. EXTERNAL INTEGRATIONS LAYER MODULE
-- ============================================

CREATE TABLE IF NOT EXISTS integration_connections (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    integration_type VARCHAR(100) NOT NULL, -- 'google_fit', 'notion', 'drive', 'telegram', 'stripe', etc.
    connection_name VARCHAR(255),
    api_credentials JSONB, -- Encrypted API keys, tokens, etc.
    webhook_url TEXT,
    is_active BOOLEAN DEFAULT true,
    last_sync_at TIMESTAMP,
    sync_frequency VARCHAR(50), -- 'realtime', 'hourly', 'daily'
    settings JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS webhook_events (
    id SERIAL PRIMARY KEY,
    integration_id INTEGER REFERENCES integration_connections(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    event_type VARCHAR(100) NOT NULL,
    payload JSONB NOT NULL,
    processed BOOLEAN DEFAULT false,
    processed_at TIMESTAMP,
    result JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 9. AI DIGITAL TWIN MODULE
-- ============================================

CREATE TABLE IF NOT EXISTS digital_twin_models (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    model_version VARCHAR(50) NOT NULL,
    training_data JSONB, -- User patterns and behaviors
    prediction_accuracy DECIMAL(5,2), -- Model accuracy percentage
    last_trained_at TIMESTAMP,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS digital_twin_predictions (
    id SERIAL PRIMARY KEY,
    model_id INTEGER REFERENCES digital_twin_models(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    prediction_type VARCHAR(100) NOT NULL, -- 'stress_level', 'productivity', 'spending', etc.
    scenario_input JSONB, -- Input variables for prediction
    predicted_outcome JSONB, -- Predicted results
    confidence_score DECIMAL(5,2),
    actual_outcome JSONB, -- Actual result (for training)
    feedback VARCHAR(50), -- 'accurate', 'inaccurate', 'partially_accurate'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_behavior_patterns (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    pattern_type VARCHAR(100) NOT NULL, -- 'sleep', 'work', 'exercise', 'spending', etc.
    pattern_data JSONB NOT NULL, -- Detected patterns
    frequency VARCHAR(50), -- 'daily', 'weekly', 'monthly'
    confidence DECIMAL(5,2),
    detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT true
);

-- ============================================
-- 10. ENERGY & FOCUS MANAGER MODULE
-- ============================================

CREATE TABLE IF NOT EXISTS energy_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    log_date DATE NOT NULL,
    log_time TIME NOT NULL,
    energy_level INTEGER NOT NULL CHECK (energy_level BETWEEN 1 AND 10),
    focus_level INTEGER CHECK (focus_level BETWEEN 1 AND 10),
    activity_type VARCHAR(100), -- 'work', 'exercise', 'rest', 'social', etc.
    mood VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS focus_sessions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    session_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME,
    duration_minutes INTEGER,
    session_type VARCHAR(100), -- 'deep_work', 'shallow_work', 'creative', 'administrative'
    productivity_score INTEGER CHECK (productivity_score BETWEEN 1 AND 10),
    interruptions INTEGER DEFAULT 0,
    tasks_completed INTEGER DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS work_rhythm_insights (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    insight_date DATE NOT NULL,
    peak_energy_hours JSONB, -- Array of hour ranges when user has peak energy
    best_focus_times JSONB, -- Optimal times for deep work
    recommended_breaks JSONB, -- Break schedule recommendations
    pattern_analysis JSONB, -- Detected patterns
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 11. ASSET & SUBSCRIPTION MANAGER MODULE
-- ============================================

CREATE TABLE IF NOT EXISTS owned_assets (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    asset_name VARCHAR(255) NOT NULL,
    asset_type VARCHAR(100) NOT NULL, -- 'device', 'furniture', 'vehicle', 'equipment', 'electronics'
    purchase_date DATE,
    purchase_price DECIMAL(15,2),
    current_value DECIMAL(15,2),
    currency VARCHAR(10) DEFAULT 'USD',
    depreciation_method VARCHAR(50), -- 'straight_line', 'declining_balance', 'none'
    useful_life_years INTEGER,
    serial_number VARCHAR(100),
    warranty_until DATE,
    maintenance_schedule JSONB, -- Maintenance reminders and history
    location VARCHAR(255),
    notes TEXT,
    images JSONB, -- Array of image URLs
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS asset_maintenance (
    id SERIAL PRIMARY KEY,
    asset_id INTEGER REFERENCES owned_assets(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    maintenance_date DATE NOT NULL,
    maintenance_type VARCHAR(100), -- 'repair', 'service', 'inspection', 'cleaning'
    description TEXT,
    cost DECIMAL(15,2),
    performed_by VARCHAR(255),
    next_maintenance_due DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS subscriptions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    service_name VARCHAR(255) NOT NULL,
    category VARCHAR(100), -- 'entertainment', 'productivity', 'utilities', 'fitness', etc.
    cost DECIMAL(15,2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'USD',
    billing_cycle VARCHAR(50) NOT NULL, -- 'monthly', 'quarterly', 'annual'
    billing_date INTEGER, -- Day of month for billing (1-31)
    start_date DATE NOT NULL,
    end_date DATE,
    is_active BOOLEAN DEFAULT true,
    auto_renewal BOOLEAN DEFAULT true,
    payment_method VARCHAR(100),
    usage_level VARCHAR(50), -- 'high', 'medium', 'low', 'unused'
    optimization_suggestion TEXT,
    potential_savings DECIMAL(15,2),
    website_url TEXT,
    login_username VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 12. COMMUNICATION & JOURNALING SUITE MODULE
-- ============================================

CREATE TABLE IF NOT EXISTS journal_entries (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    entry_date DATE NOT NULL,
    entry_time TIME DEFAULT CURRENT_TIME,
    entry_type VARCHAR(50) DEFAULT 'text', -- 'text', 'voice', 'mixed'
    title VARCHAR(255),
    content TEXT,
    voice_recording_url TEXT,
    transcription TEXT,
    mood VARCHAR(100),
    emotions JSONB, -- Array of detected emotions
    ai_summary TEXT, -- AI-generated summary
    ai_insights JSONB, -- AI insights and patterns
    tags JSONB,
    is_private BOOLEAN DEFAULT true,
    media_attachments JSONB, -- Photos, videos, etc.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS journal_memories (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    memory_date DATE NOT NULL,
    memory_type VARCHAR(50), -- 'quote', 'achievement', 'milestone', 'learning'
    content TEXT NOT NULL,
    context TEXT,
    sentiment VARCHAR(50), -- 'positive', 'negative', 'neutral', 'mixed'
    importance INTEGER CHECK (importance BETWEEN 1 AND 5),
    related_entries JSONB, -- Links to related journal entries
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 13. SUSTAINABILITY & ECO TRACKER MODULE
-- ============================================

CREATE TABLE IF NOT EXISTS eco_impact_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    log_date DATE NOT NULL,
    impact_category VARCHAR(100) NOT NULL, -- 'transport', 'energy', 'waste', 'water', 'food', 'purchases'
    activity_description VARCHAR(255),
    carbon_footprint DECIMAL(10,2), -- kg of CO2
    water_usage DECIMAL(10,2), -- liters
    energy_usage DECIMAL(10,2), -- kWh
    waste_generated DECIMAL(10,2), -- kg
    transportation_distance DECIMAL(10,2), -- km
    transportation_mode VARCHAR(100), -- 'car', 'public_transport', 'bike', 'walk', 'plane'
    eco_score INTEGER CHECK (eco_score BETWEEN 0 AND 100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS eco_goals (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    goal_type VARCHAR(100) NOT NULL, -- 'reduce_carbon', 'reduce_waste', 'save_water', 'save_energy'
    target_value DECIMAL(10,2) NOT NULL,
    current_value DECIMAL(10,2) DEFAULT 0,
    target_date DATE,
    frequency VARCHAR(50), -- 'daily', 'weekly', 'monthly', 'annual'
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS eco_tips (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    tip_date DATE NOT NULL,
    tip_category VARCHAR(100),
    tip_content TEXT NOT NULL,
    potential_impact DECIMAL(10,2), -- Estimated impact if followed
    is_completed BOOLEAN DEFAULT false,
    feedback VARCHAR(50), -- 'helpful', 'not_applicable', 'already_doing'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 14. AI SCENARIO SIMULATOR MODULE
-- ============================================

CREATE TABLE IF NOT EXISTS scenario_simulations (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    simulation_name VARCHAR(255) NOT NULL,
    simulation_type VARCHAR(100) NOT NULL, -- 'financial', 'health', 'career', 'lifestyle', 'mixed'
    what_if_question TEXT NOT NULL,
    input_parameters JSONB NOT NULL, -- The "what if" variables
    simulation_period INTEGER, -- Number of days/months/years to simulate
    simulation_results JSONB, -- Predicted outcomes
    impact_graphs JSONB, -- Data for impact visualizations
    confidence_level DECIMAL(5,2),
    recommendation TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS scenario_comparisons (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    comparison_name VARCHAR(255) NOT NULL,
    base_scenario_id INTEGER REFERENCES scenario_simulations(id),
    alternative_scenarios JSONB, -- Array of scenario IDs to compare
    comparison_metrics JSONB, -- Side-by-side comparison data
    winner_scenario_id INTEGER REFERENCES scenario_simulations(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- CREATE INDEXES FOR PERFORMANCE
-- ============================================

-- Automation indexes
CREATE INDEX idx_automation_rules_user_id ON automation_rules(user_id);
CREATE INDEX idx_automation_rules_is_active ON automation_rules(is_active);
CREATE INDEX idx_automation_logs_rule_id ON automation_logs(rule_id);

-- Analytics indexes
CREATE INDEX idx_life_reports_user_id ON life_reports(user_id);
CREATE INDEX idx_life_reports_period ON life_reports(report_period);
CREATE INDEX idx_life_analytics_data_user_id ON life_analytics_data(user_id);
CREATE INDEX idx_life_analytics_data_date ON life_analytics_data(metric_date);

-- Collaboration indexes
CREATE INDEX idx_shared_spaces_owner ON shared_spaces(owner_user_id);
CREATE INDEX idx_shared_space_members_space ON shared_space_members(space_id);
CREATE INDEX idx_shared_space_members_user ON shared_space_members(user_id);
CREATE INDEX idx_shared_tasks_space ON shared_tasks(space_id);

-- Business indexes
CREATE INDEX idx_business_invoices_user_id ON business_invoices(user_id);
CREATE INDEX idx_business_invoices_status ON business_invoices(status);
CREATE INDEX idx_business_expenses_user_id ON business_expenses(user_id);

-- Roadmap indexes
CREATE INDEX idx_life_roadmap_user_id ON life_roadmap(user_id);
CREATE INDEX idx_roadmap_milestones_roadmap ON roadmap_milestones(roadmap_id);

-- Knowledge Graph indexes
CREATE INDEX idx_knowledge_nodes_user_id ON knowledge_nodes(user_id);
CREATE INDEX idx_knowledge_nodes_type ON knowledge_nodes(node_type);
CREATE INDEX idx_knowledge_relationships_from ON knowledge_relationships(from_node_id);
CREATE INDEX idx_knowledge_relationships_to ON knowledge_relationships(to_node_id);

-- Smart Reminders indexes
CREATE INDEX idx_smart_reminders_user_id ON smart_reminders(user_id);
CREATE INDEX idx_smart_reminders_next_trigger ON smart_reminders(next_trigger_at);
CREATE INDEX idx_smart_reminders_active ON smart_reminders(is_active);

-- Integrations indexes
CREATE INDEX idx_integration_connections_user_id ON integration_connections(user_id);
CREATE INDEX idx_webhook_events_integration ON webhook_events(integration_id);
CREATE INDEX idx_webhook_events_processed ON webhook_events(processed);

-- Digital Twin indexes
CREATE INDEX idx_digital_twin_models_user_id ON digital_twin_models(user_id);
CREATE INDEX idx_digital_twin_predictions_model ON digital_twin_predictions(model_id);

-- Energy & Focus indexes
CREATE INDEX idx_energy_logs_user_id ON energy_logs(user_id);
CREATE INDEX idx_energy_logs_date ON energy_logs(log_date);
CREATE INDEX idx_focus_sessions_user_id ON focus_sessions(user_id);

-- Assets & Subscriptions indexes
CREATE INDEX idx_owned_assets_user_id ON owned_assets(user_id);
CREATE INDEX idx_asset_maintenance_asset ON asset_maintenance(asset_id);
CREATE INDEX idx_subscriptions_user_id ON subscriptions(user_id);
CREATE INDEX idx_subscriptions_active ON subscriptions(is_active);

-- Journal indexes
CREATE INDEX idx_journal_entries_user_id ON journal_entries(user_id);
CREATE INDEX idx_journal_entries_date ON journal_entries(entry_date);
CREATE INDEX idx_journal_memories_user_id ON journal_memories(user_id);

-- Eco Tracker indexes
CREATE INDEX idx_eco_impact_logs_user_id ON eco_impact_logs(user_id);
CREATE INDEX idx_eco_impact_logs_date ON eco_impact_logs(log_date);
CREATE INDEX idx_eco_goals_user_id ON eco_goals(user_id);

-- Scenario Simulator indexes
CREATE INDEX idx_scenario_simulations_user_id ON scenario_simulations(user_id);
CREATE INDEX idx_scenario_comparisons_user_id ON scenario_comparisons(user_id);
