-- WebEngine CMS Tables for OpenMU Integration
-- These tables extend OpenMU's existing schema

-- WebEngine News
CREATE TABLE IF NOT EXISTS data.webengine_news (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(50) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    allow_comments BOOLEAN DEFAULT true,
    published BOOLEAN DEFAULT true,
    views INTEGER DEFAULT 0
);

-- WebEngine Downloads
CREATE TABLE IF NOT EXISTS data.webengine_downloads (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(500) NOT NULL,
    file_size BIGINT,
    download_count INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    active BOOLEAN DEFAULT true,
    category VARCHAR(50) DEFAULT 'general'
);

-- WebEngine Vote Sites
CREATE TABLE IF NOT EXISTS data.webengine_vote_sites (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    url VARCHAR(500) NOT NULL,
    reward_credits INTEGER DEFAULT 0,
    cooldown_hours INTEGER DEFAULT 12,
    active BOOLEAN DEFAULT true,
    image_url VARCHAR(500),
    description TEXT
);

-- WebEngine Vote Logs
CREATE TABLE IF NOT EXISTS data.webengine_vote_logs (
    id SERIAL PRIMARY KEY,
    account_id UUID REFERENCES data."Account"(id),
    vote_site_id INTEGER REFERENCES data.webengine_vote_sites(id),
    ip_address INET,
    voted_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    reward_given BOOLEAN DEFAULT false
);

-- WebEngine Credits Logs
CREATE TABLE IF NOT EXISTS data.webengine_credits_logs (
    id SERIAL PRIMARY KEY,
    account_id UUID REFERENCES data."Account"(id),
    character_id UUID REFERENCES data."Character"(id),
    amount INTEGER NOT NULL,
    transaction_type VARCHAR(50) NOT NULL, -- 'earned', 'spent', 'admin_add', 'admin_remove'
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- WebEngine Bans
CREATE TABLE IF NOT EXISTS data.webengine_bans (
    id SERIAL PRIMARY KEY,
    account_id UUID REFERENCES data."Account"(id),
    character_id UUID REFERENCES data."Character"(id),
    banned_by VARCHAR(50) NOT NULL,
    ban_reason TEXT NOT NULL,
    ban_type VARCHAR(20) NOT NULL, -- 'account', 'character', 'ip'
    banned_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP WITH TIME ZONE,
    active BOOLEAN DEFAULT true
);

-- WebEngine Blocked IPs
CREATE TABLE IF NOT EXISTS data.webengine_blocked_ips (
    id SERIAL PRIMARY KEY,
    ip_address INET NOT NULL UNIQUE,
    reason TEXT,
    blocked_by VARCHAR(50) NOT NULL,
    blocked_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    active BOOLEAN DEFAULT true
);

-- WebEngine PayPal Transactions
CREATE TABLE IF NOT EXISTS data.webengine_paypal_transactions (
    id SERIAL PRIMARY KEY,
    account_id UUID REFERENCES data."Account"(id),
    character_id UUID REFERENCES data."Character"(id),
    transaction_id VARCHAR(100) NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    credits_given INTEGER NOT NULL,
    status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'completed', 'failed', 'cancelled'
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP WITH TIME ZONE
);

-- WebEngine Password Change Requests
CREATE TABLE IF NOT EXISTS data.webengine_password_requests (
    id SERIAL PRIMARY KEY,
    account_id UUID REFERENCES data."Account"(id),
    verification_key VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL,
    requested_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP WITH TIME ZONE DEFAULT (CURRENT_TIMESTAMP + INTERVAL '24 hours'),
    used BOOLEAN DEFAULT false
);

-- WebEngine Email Verification
CREATE TABLE IF NOT EXISTS data.webengine_email_verification (
    id SERIAL PRIMARY KEY,
    account_id UUID REFERENCES data."Account"(id),
    verification_key VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP WITH TIME ZONE DEFAULT (CURRENT_TIMESTAMP + INTERVAL '24 hours'),
    verified BOOLEAN DEFAULT false
);

-- WebEngine Account Country (for statistics)
CREATE TABLE IF NOT EXISTS data.webengine_account_country (
    id SERIAL PRIMARY KEY,
    account_id UUID REFERENCES data."Account"(id),
    country_code VARCHAR(2) NOT NULL,
    country_name VARCHAR(100) NOT NULL,
    detected_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- WebEngine Cron Logs
CREATE TABLE IF NOT EXISTS data.webengine_cron_logs (
    id SERIAL PRIMARY KEY,
    cron_name VARCHAR(100) NOT NULL,
    status VARCHAR(20) NOT NULL, -- 'success', 'error', 'warning'
    message TEXT,
    execution_time INTEGER, -- in milliseconds
    executed_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- WebEngine Plugins
CREATE TABLE IF NOT EXISTS data.webengine_plugins (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    version VARCHAR(20) NOT NULL,
    author VARCHAR(100),
    description TEXT,
    active BOOLEAN DEFAULT false,
    installed_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_webengine_news_published ON data.webengine_news(published, created_at);
CREATE INDEX IF NOT EXISTS idx_webengine_downloads_active ON data.webengine_downloads(active, created_at);
CREATE INDEX IF NOT EXISTS idx_webengine_vote_logs_account ON data.webengine_vote_logs(account_id, voted_at);
CREATE INDEX IF NOT EXISTS idx_webengine_credits_logs_account ON data.webengine_credits_logs(account_id, created_at);
CREATE INDEX IF NOT EXISTS idx_webengine_bans_active ON data.webengine_bans(active, banned_at);
CREATE INDEX IF NOT EXISTS idx_webengine_blocked_ips_active ON data.webengine_blocked_ips(active, blocked_at);
CREATE INDEX IF NOT EXISTS idx_webengine_paypal_transactions_status ON data.webengine_paypal_transactions(status, created_at);
CREATE INDEX IF NOT EXISTS idx_webengine_password_requests_key ON data.webengine_password_requests(verification_key, expires_at);
CREATE INDEX IF NOT EXISTS idx_webengine_email_verification_key ON data.webengine_email_verification(verification_key, expires_at);
CREATE INDEX IF NOT EXISTS idx_webengine_account_country_account ON data.webengine_account_country(account_id);
CREATE INDEX IF NOT EXISTS idx_webengine_cron_logs_name ON data.webengine_cron_logs(cron_name, executed_at);
CREATE INDEX IF NOT EXISTS idx_webengine_plugins_active ON data.webengine_plugins(active, name);

-- Insert default vote sites (example)
INSERT INTO data.webengine_vote_sites (name, url, reward_credits, cooldown_hours, active) VALUES
('TopG', 'https://topg.org/vote/your-server', 100, 12, true),
('GTop100', 'https://gtop100.com/vote/your-server', 100, 12, true),
('Top100Arena', 'https://top100arena.com/vote/your-server', 100, 12, true)
ON CONFLICT DO NOTHING;

-- Insert default news (example)
INSERT INTO data.webengine_news (title, author, content, published) VALUES
('Welcome to OpenMU Server!', 'Admin', 'Welcome to our OpenMU server! This is a modern MU Online server built with .NET and PostgreSQL.', true),
('Server Rules', 'Admin', 'Please read and follow our server rules to ensure a great gaming experience for everyone.', true)
ON CONFLICT DO NOTHING;

-- Insert default downloads (example)
INSERT INTO data.webengine_downloads (title, description, file_path, file_size, active) VALUES
('OpenMU Client', 'Download the official OpenMU client', '/downloads/openmu-client.zip', 104857600, true),
('Game Guide', 'Complete game guide for new players', '/downloads/game-guide.pdf', 5242880, true)
ON CONFLICT DO NOTHING;


