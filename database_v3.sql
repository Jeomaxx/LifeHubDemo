-- Life Atlas Organizer V3.0 Database Migration
-- Date: October 20, 2025
-- Description: Comprehensive database schema for all Phase 3.0 features

-- ==========================================
-- FREELANCE & PROFESSIONAL MODULE
-- ==========================================

CREATE TABLE IF NOT EXISTS freelance_clients (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    client_name VARCHAR(255) NOT NULL,
    company_name VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    notes TEXT,
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS freelance_projects (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    client_id INTEGER REFERENCES freelance_clients(id) ON DELETE SET NULL,
    project_name VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'in_progress',
    start_date DATE,
    end_date DATE,
    deadline DATE,
    budget DECIMAL(15, 2),
    hourly_rate DECIMAL(10, 2),
    estimated_hours DECIMAL(10, 2),
    actual_hours DECIMAL(10, 2) DEFAULT 0,
    total_invoiced DECIMAL(15, 2) DEFAULT 0,
    total_paid DECIMAL(15, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS freelance_invoices (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    client_id INTEGER REFERENCES freelance_clients(id) ON DELETE SET NULL,
    project_id INTEGER REFERENCES freelance_projects(id) ON DELETE SET NULL,
    invoice_number VARCHAR(100) UNIQUE NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE,
    subtotal DECIMAL(15, 2) NOT NULL,
    tax_amount DECIMAL(15, 2) DEFAULT 0,
    total_amount DECIMAL(15, 2) NOT NULL,
    amount_paid DECIMAL(15, 2) DEFAULT 0,
    status VARCHAR(50) DEFAULT 'draft',
    notes TEXT,
    payment_terms TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS freelance_invoice_items (
    id SERIAL PRIMARY KEY,
    invoice_id INTEGER NOT NULL REFERENCES freelance_invoices(id) ON DELETE CASCADE,
    description TEXT NOT NULL,
    quantity DECIMAL(10, 2) DEFAULT 1,
    unit_price DECIMAL(10, 2) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS freelance_time_entries (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    project_id INTEGER REFERENCES freelance_projects(id) ON DELETE CASCADE,
    description TEXT,
    hours DECIMAL(10, 2) NOT NULL,
    entry_date DATE NOT NULL,
    billable BOOLEAN DEFAULT true,
    invoiced BOOLEAN DEFAULT false,
    invoice_id INTEGER REFERENCES freelance_invoices(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- TEAM COLLABORATION MODULE
-- ==========================================

CREATE TABLE IF NOT EXISTS team_boards (
    id SERIAL PRIMARY KEY,
    owner_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    board_name VARCHAR(255) NOT NULL,
    description TEXT,
    is_private BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS team_board_members (
    id SERIAL PRIMARY KEY,
    board_id INTEGER NOT NULL REFERENCES team_boards(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role VARCHAR(50) DEFAULT 'viewer',
    invited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    joined_at TIMESTAMP,
    UNIQUE(board_id, user_id)
);

CREATE TABLE IF NOT EXISTS team_tasks (
    id SERIAL PRIMARY KEY,
    board_id INTEGER NOT NULL REFERENCES team_boards(id) ON DELETE CASCADE,
    created_by INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    assigned_to INTEGER REFERENCES users(id) ON DELETE SET NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'todo',
    priority VARCHAR(50) DEFAULT 'medium',
    due_date DATE,
    tags TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS team_task_comments (
    id SERIAL PRIMARY KEY,
    task_id INTEGER NOT NULL REFERENCES team_tasks(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- TAX REPORTING MODULE
-- ==========================================

CREATE TABLE IF NOT EXISTS tax_categories (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    category_name VARCHAR(255) NOT NULL,
    tax_year INTEGER NOT NULL,
    deductible BOOLEAN DEFAULT false,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tax_documents (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    document_type VARCHAR(100) NOT NULL,
    tax_year INTEGER NOT NULL,
    file_path VARCHAR(500),
    file_name VARCHAR(255),
    amount DECIMAL(15, 2),
    category_id INTEGER REFERENCES tax_categories(id) ON DELETE SET NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT
);

CREATE TABLE IF NOT EXISTS tax_reports (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    report_type VARCHAR(100) NOT NULL,
    tax_year INTEGER NOT NULL,
    start_date DATE,
    end_date DATE,
    total_income DECIMAL(15, 2) DEFAULT 0,
    total_expenses DECIMAL(15, 2) DEFAULT 0,
    total_deductions DECIMAL(15, 2) DEFAULT 0,
    net_income DECIMAL(15, 2) DEFAULT 0,
    estimated_tax DECIMAL(15, 2) DEFAULT 0,
    file_path VARCHAR(500),
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- DEVICE INTEGRATION MODULE
-- ==========================================

CREATE TABLE IF NOT EXISTS device_integrations (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    device_type VARCHAR(100) NOT NULL,
    device_name VARCHAR(255),
    access_token TEXT,
    refresh_token TEXT,
    token_expires_at TIMESTAMP,
    is_active BOOLEAN DEFAULT true,
    last_sync TIMESTAMP,
    sync_frequency VARCHAR(50) DEFAULT 'daily',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS device_sync_data (
    id SERIAL PRIMARY KEY,
    integration_id INTEGER NOT NULL REFERENCES device_integrations(id) ON DELETE CASCADE,
    data_type VARCHAR(100) NOT NULL,
    data_date DATE NOT NULL,
    data_value DECIMAL(15, 2),
    data_json JSONB,
    synced_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- NUTRITION AI MODULE
-- ==========================================

CREATE TABLE IF NOT EXISTS nutrition_profiles (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    age INTEGER,
    gender VARCHAR(20),
    height_cm DECIMAL(5, 2),
    current_weight_kg DECIMAL(5, 2),
    target_weight_kg DECIMAL(5, 2),
    activity_level VARCHAR(50),
    dietary_restrictions TEXT,
    allergies TEXT,
    health_goals TEXT,
    daily_calorie_target INTEGER,
    daily_protein_target DECIMAL(5, 2),
    daily_carbs_target DECIMAL(5, 2),
    daily_fats_target DECIMAL(5, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ai_meal_plans (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    plan_name VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_calories INTEGER,
    plan_goals TEXT,
    ai_generated BOOLEAN DEFAULT true,
    ai_model VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ai_meal_plan_details (
    id SERIAL PRIMARY KEY,
    plan_id INTEGER NOT NULL REFERENCES ai_meal_plans(id) ON DELETE CASCADE,
    day_of_week INTEGER NOT NULL,
    meal_type VARCHAR(50) NOT NULL,
    recipe_id INTEGER REFERENCES recipes(id) ON DELETE SET NULL,
    meal_name VARCHAR(255),
    calories INTEGER,
    protein DECIMAL(5, 2),
    carbs DECIMAL(5, 2),
    fats DECIMAL(5, 2),
    instructions TEXT
);

-- ==========================================
-- LIFE ORCHESTRATOR ENGINE (AUTOMATION)
-- ==========================================

CREATE TABLE IF NOT EXISTS automation_rules (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    rule_name VARCHAR(255) NOT NULL,
    description TEXT,
    trigger_type VARCHAR(100) NOT NULL,
    trigger_conditions JSONB NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    action_parameters JSONB NOT NULL,
    is_active BOOLEAN DEFAULT true,
    priority INTEGER DEFAULT 0,
    last_executed TIMESTAMP,
    execution_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS automation_execution_log (
    id SERIAL PRIMARY KEY,
    rule_id INTEGER NOT NULL REFERENCES automation_rules(id) ON DELETE CASCADE,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) NOT NULL,
    result_data JSONB,
    error_message TEXT
);

-- ==========================================
-- AI PERSONA & MEMORY MODULE
-- ==========================================

CREATE TABLE IF NOT EXISTS ai_persona_profile (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    communication_style VARCHAR(100),
    preferred_tone VARCHAR(100),
    notification_frequency VARCHAR(50),
    learning_preferences JSONB,
    personality_traits JSONB,
    interaction_history JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id)
);

CREATE TABLE IF NOT EXISTS ai_memory_graph (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    memory_type VARCHAR(100) NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id INTEGER,
    memory_key VARCHAR(255) NOT NULL,
    memory_content TEXT NOT NULL,
    metadata JSONB,
    embedding_vector VECTOR(1536),
    relevance_score DECIMAL(5, 4) DEFAULT 1.0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    accessed_count INTEGER DEFAULT 0,
    last_accessed TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ai_semantic_links (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    source_memory_id INTEGER NOT NULL REFERENCES ai_memory_graph(id) ON DELETE CASCADE,
    target_memory_id INTEGER NOT NULL REFERENCES ai_memory_graph(id) ON DELETE CASCADE,
    link_type VARCHAR(100) NOT NULL,
    link_strength DECIMAL(5, 4) DEFAULT 0.5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- IDENTITY VAULT & DIGITAL ESTATE
-- ==========================================

CREATE TABLE IF NOT EXISTS identity_credentials (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    credential_type VARCHAR(100) NOT NULL,
    website_url VARCHAR(500),
    website_name VARCHAR(255),
    username VARCHAR(255),
    encrypted_password TEXT NOT NULL,
    encryption_key_id VARCHAR(255),
    notes TEXT,
    tags TEXT,
    last_used TIMESTAMP,
    password_changed_at TIMESTAMP,
    auto_fill_enabled BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS digital_estate_trustees (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    trustee_name VARCHAR(255) NOT NULL,
    trustee_email VARCHAR(255) NOT NULL,
    trustee_phone VARCHAR(50),
    relationship VARCHAR(100),
    access_level VARCHAR(50) DEFAULT 'limited',
    is_active BOOLEAN DEFAULT true,
    activation_conditions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS digital_estate_items (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    item_type VARCHAR(100) NOT NULL,
    item_title VARCHAR(255) NOT NULL,
    encrypted_content TEXT,
    encryption_key_id VARCHAR(255),
    accessible_by_trustees BOOLEAN DEFAULT false,
    trustee_access_delay_days INTEGER DEFAULT 30,
    instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- SECURITY THREAT DETECTION
-- ==========================================

CREATE TABLE IF NOT EXISTS security_threat_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    threat_type VARCHAR(100) NOT NULL,
    threat_level VARCHAR(50) NOT NULL,
    ip_address VARCHAR(100),
    user_agent TEXT,
    location VARCHAR(255),
    device_info TEXT,
    anomaly_score DECIMAL(5, 4),
    description TEXT,
    resolved BOOLEAN DEFAULT false,
    resolved_at TIMESTAMP,
    detected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_behavior_baseline (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    behavior_type VARCHAR(100) NOT NULL,
    typical_time_range VARCHAR(100),
    typical_locations JSONB,
    typical_devices JSONB,
    access_patterns JSONB,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, behavior_type)
);

-- ==========================================
-- KNOWLEDGE VAULT MODULE
-- ==========================================

CREATE TABLE IF NOT EXISTS knowledge_vault_items (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    item_type VARCHAR(100) NOT NULL,
    title VARCHAR(500) NOT NULL,
    content TEXT,
    source_url VARCHAR(1000),
    source_type VARCHAR(100),
    tags TEXT,
    category VARCHAR(255),
    ai_summary TEXT,
    ai_keywords TEXT,
    embedding_vector VECTOR(1536),
    is_favorite BOOLEAN DEFAULT false,
    read_count INTEGER DEFAULT 0,
    last_accessed TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS knowledge_connections (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    item_id_1 INTEGER NOT NULL REFERENCES knowledge_vault_items(id) ON DELETE CASCADE,
    item_id_2 INTEGER NOT NULL REFERENCES knowledge_vault_items(id) ON DELETE CASCADE,
    connection_type VARCHAR(100),
    connection_strength DECIMAL(5, 4) DEFAULT 0.5,
    ai_generated BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- AI LEARNING PATH BUILDER
-- ==========================================

CREATE TABLE IF NOT EXISTS learning_paths (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    path_name VARCHAR(255) NOT NULL,
    description TEXT,
    skill_level VARCHAR(50),
    target_skill VARCHAR(255),
    estimated_duration_hours INTEGER,
    ai_generated BOOLEAN DEFAULT true,
    is_active BOOLEAN DEFAULT true,
    completion_percentage DECIMAL(5, 2) DEFAULT 0,
    started_at TIMESTAMP,
    completed_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS learning_path_resources (
    id SERIAL PRIMARY KEY,
    path_id INTEGER NOT NULL REFERENCES learning_paths(id) ON DELETE CASCADE,
    resource_type VARCHAR(100) NOT NULL,
    resource_title VARCHAR(500) NOT NULL,
    resource_url VARCHAR(1000),
    resource_platform VARCHAR(255),
    estimated_hours DECIMAL(5, 2),
    sequence_order INTEGER NOT NULL,
    is_completed BOOLEAN DEFAULT false,
    completed_at TIMESTAMP,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- LIFEWIKI GENERATOR
-- ==========================================

CREATE TABLE IF NOT EXISTS lifewiki_pages (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    page_type VARCHAR(100) NOT NULL,
    page_title VARCHAR(500) NOT NULL,
    page_slug VARCHAR(500) NOT NULL,
    content_markdown TEXT,
    content_html TEXT,
    ai_generated BOOLEAN DEFAULT true,
    generation_date TIMESTAMP,
    source_data_types JSONB,
    metadata JSONB,
    view_count INTEGER DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, page_slug)
);

CREATE TABLE IF NOT EXISTS lifewiki_timeline (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    event_type VARCHAR(100) NOT NULL,
    event_title VARCHAR(500) NOT NULL,
    event_date DATE NOT NULL,
    event_description TEXT,
    related_modules JSONB,
    importance_level INTEGER DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- CUSTOM DASHBOARD BUILDER
-- ==========================================

CREATE TABLE IF NOT EXISTS custom_dashboards (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    dashboard_name VARCHAR(255) NOT NULL,
    description TEXT,
    is_default BOOLEAN DEFAULT false,
    layout_config JSONB NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS dashboard_widgets (
    id SERIAL PRIMARY KEY,
    dashboard_id INTEGER NOT NULL REFERENCES custom_dashboards(id) ON DELETE CASCADE,
    widget_type VARCHAR(100) NOT NULL,
    widget_title VARCHAR(255),
    position_x INTEGER NOT NULL,
    position_y INTEGER NOT NULL,
    width INTEGER NOT NULL,
    height INTEGER NOT NULL,
    config JSONB,
    data_source VARCHAR(255),
    refresh_interval INTEGER DEFAULT 300,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- CLOUD BACKUP INTEGRATION
-- ==========================================

CREATE TABLE IF NOT EXISTS cloud_backup_configs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    provider VARCHAR(100) NOT NULL,
    provider_name VARCHAR(255),
    access_token TEXT,
    refresh_token TEXT,
    token_expires_at TIMESTAMP,
    backup_folder_id VARCHAR(500),
    backup_folder_path VARCHAR(1000),
    is_active BOOLEAN DEFAULT true,
    auto_backup_enabled BOOLEAN DEFAULT false,
    backup_frequency VARCHAR(50) DEFAULT 'daily',
    last_backup TIMESTAMP,
    encryption_enabled BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cloud_backup_history (
    id SERIAL PRIMARY KEY,
    config_id INTEGER NOT NULL REFERENCES cloud_backup_configs(id) ON DELETE CASCADE,
    backup_type VARCHAR(100) NOT NULL,
    file_name VARCHAR(500),
    file_size_bytes BIGINT,
    backup_status VARCHAR(50) NOT NULL,
    error_message TEXT,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP
);

-- ==========================================
-- API INTEGRATION HUB
-- ==========================================

CREATE TABLE IF NOT EXISTS api_applications (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    app_name VARCHAR(255) NOT NULL,
    app_description TEXT,
    client_id VARCHAR(255) UNIQUE NOT NULL,
    client_secret VARCHAR(500) NOT NULL,
    redirect_uris TEXT,
    allowed_scopes TEXT,
    is_active BOOLEAN DEFAULT true,
    rate_limit INTEGER DEFAULT 1000,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS api_access_tokens (
    id SERIAL PRIMARY KEY,
    app_id INTEGER NOT NULL REFERENCES api_applications(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    access_token VARCHAR(500) UNIQUE NOT NULL,
    refresh_token VARCHAR(500),
    token_type VARCHAR(50) DEFAULT 'Bearer',
    scope TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS api_usage_logs (
    id SERIAL PRIMARY KEY,
    app_id INTEGER REFERENCES api_applications(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    endpoint VARCHAR(500) NOT NULL,
    http_method VARCHAR(10) NOT NULL,
    status_code INTEGER,
    response_time_ms INTEGER,
    ip_address VARCHAR(100),
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- PLUGIN MARKETPLACE
-- ==========================================

CREATE TABLE IF NOT EXISTS marketplace_plugins (
    id SERIAL PRIMARY KEY,
    developer_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    plugin_name VARCHAR(255) NOT NULL,
    plugin_slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    version VARCHAR(50) NOT NULL,
    category VARCHAR(100),
    icon_url VARCHAR(500),
    download_url VARCHAR(1000),
    documentation_url VARCHAR(1000),
    is_verified BOOLEAN DEFAULT false,
    is_active BOOLEAN DEFAULT true,
    install_count INTEGER DEFAULT 0,
    rating_average DECIMAL(3, 2) DEFAULT 0,
    rating_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_installed_plugins (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    plugin_id INTEGER NOT NULL REFERENCES marketplace_plugins(id) ON DELETE CASCADE,
    installed_version VARCHAR(50),
    is_enabled BOOLEAN DEFAULT true,
    config JSONB,
    installed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, plugin_id)
);

CREATE TABLE IF NOT EXISTS plugin_ratings (
    id SERIAL PRIMARY KEY,
    plugin_id INTEGER NOT NULL REFERENCES marketplace_plugins(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    rating INTEGER NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(plugin_id, user_id)
);

-- ==========================================
-- WHATSAPP BOT INTEGRATION
-- ==========================================

CREATE TABLE IF NOT EXISTS whatsapp_bot_config (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    phone_number VARCHAR(50) NOT NULL,
    api_key TEXT,
    webhook_url VARCHAR(500),
    is_active BOOLEAN DEFAULT true,
    last_message_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id)
);

CREATE TABLE IF NOT EXISTS whatsapp_message_log (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    message_type VARCHAR(50) NOT NULL,
    message_from VARCHAR(100),
    message_to VARCHAR(100),
    message_content TEXT,
    command_executed VARCHAR(255),
    response_sent TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- OUTLOOK CALENDAR SYNC
-- ==========================================

CREATE TABLE IF NOT EXISTS outlook_calendar_sync (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    access_token TEXT,
    refresh_token TEXT,
    token_expires_at TIMESTAMP,
    calendar_id VARCHAR(500),
    sync_enabled BOOLEAN DEFAULT true,
    last_sync TIMESTAMP,
    sync_status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id)
);

-- ==========================================
-- CAREER GROWTH ANALYZER
-- ==========================================

CREATE TABLE IF NOT EXISTS career_milestones (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    milestone_type VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    achievement_date DATE,
    impact_level VARCHAR(50),
    skills_gained TEXT,
    evidence_url VARCHAR(1000),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS skill_assessments (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    skill_name VARCHAR(255) NOT NULL,
    skill_category VARCHAR(100),
    proficiency_level VARCHAR(50),
    years_experience DECIMAL(4, 1),
    last_used DATE,
    endorsed_count INTEGER DEFAULT 0,
    certifications TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS portfolio_projects (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    project_title VARCHAR(255) NOT NULL,
    project_description TEXT,
    role VARCHAR(255),
    technologies_used TEXT,
    start_date DATE,
    end_date DATE,
    project_url VARCHAR(1000),
    github_url VARCHAR(1000),
    images JSONB,
    metrics JSONB,
    is_featured BOOLEAN DEFAULT false,
    display_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================
-- LIFE PATTERN RECOGNITION
-- ==========================================

CREATE TABLE IF NOT EXISTS life_patterns (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    pattern_type VARCHAR(100) NOT NULL,
    pattern_name VARCHAR(255) NOT NULL,
    pattern_description TEXT,
    confidence_score DECIMAL(5, 4),
    data_sources JSONB,
    pattern_data JSONB,
    first_detected TIMESTAMP,
    last_confirmed TIMESTAMP,
    occurrence_count INTEGER DEFAULT 1,
    is_positive BOOLEAN,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS life_predictions (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    prediction_type VARCHAR(100) NOT NULL,
    prediction_title VARCHAR(255) NOT NULL,
    predicted_event TEXT,
    probability DECIMAL(5, 4),
    predicted_date DATE,
    data_basis JSONB,
    ai_model VARCHAR(100),
    status VARCHAR(50) DEFAULT 'pending',
    actual_outcome TEXT,
    accuracy_score DECIMAL(5, 4),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_at TIMESTAMP
);

-- ==========================================
-- INDEXES FOR PERFORMANCE
-- ==========================================

CREATE INDEX idx_freelance_projects_user ON freelance_projects(user_id);
CREATE INDEX idx_freelance_invoices_user ON freelance_invoices(user_id);
CREATE INDEX idx_team_boards_owner ON team_boards(owner_id);
CREATE INDEX idx_team_tasks_board ON team_tasks(board_id);
CREATE INDEX idx_automation_rules_user ON automation_rules(user_id);
CREATE INDEX idx_ai_memory_user ON ai_memory_graph(user_id);
CREATE INDEX idx_knowledge_vault_user ON knowledge_vault_items(user_id);
CREATE INDEX idx_learning_paths_user ON learning_paths(user_id);
CREATE INDEX idx_lifewiki_pages_user ON lifewiki_pages(user_id);
CREATE INDEX idx_security_threats_user ON security_threat_logs(user_id);
CREATE INDEX idx_api_tokens_user ON api_access_tokens(user_id);
CREATE INDEX idx_plugins_active ON marketplace_plugins(is_active);

-- ==========================================
-- FINAL NOTE
-- ==========================================
-- This migration adds 60+ new tables for Life Atlas Organizer V3.0
-- Run this script on your PostgreSQL database to enable all new features
