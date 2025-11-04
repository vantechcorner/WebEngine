<?php
/**
 * Simple Table Creation Script
 * Creates WebEngine tables one by one
 */

echo "<h1>Simple WebEngine Table Creation</h1>\n";
echo "<hr>\n";

// Database connection
$host = 'localhost';
$port = '5432';
$dbname = 'openmu';
$username = 'postgres';
$password = 'Muahe2025~';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>✅ Connected to OpenMU Database</h2>\n";
    
    // Create tables one by one
    $tables = [
        'webengine_news' => "CREATE TABLE IF NOT EXISTS data.webengine_news (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            author VARCHAR(50) NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
            allow_comments BOOLEAN DEFAULT true,
            published BOOLEAN DEFAULT true,
            views INTEGER DEFAULT 0
        )",
        
        'webengine_downloads' => "CREATE TABLE IF NOT EXISTS data.webengine_downloads (
            id SERIAL PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            file_path VARCHAR(500) NOT NULL,
            file_size BIGINT,
            download_count INTEGER DEFAULT 0,
            created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
            active BOOLEAN DEFAULT true,
            category VARCHAR(50) DEFAULT 'general'
        )",
        
        'webengine_vote_sites' => "CREATE TABLE IF NOT EXISTS data.webengine_vote_sites (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            url VARCHAR(500) NOT NULL,
            reward_credits INTEGER DEFAULT 0,
            cooldown_hours INTEGER DEFAULT 12,
            active BOOLEAN DEFAULT true,
            image_url VARCHAR(500),
            description TEXT
        )",
        
        'webengine_vote_logs' => "CREATE TABLE IF NOT EXISTS data.webengine_vote_logs (
            id SERIAL PRIMARY KEY,
            account_id UUID REFERENCES data.\"Account\"(id),
            vote_site_id INTEGER REFERENCES data.webengine_vote_sites(id),
            ip_address INET,
            voted_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
            reward_given BOOLEAN DEFAULT false
        )",
        
        'webengine_credits_logs' => "CREATE TABLE IF NOT EXISTS data.webengine_credits_logs (
            id SERIAL PRIMARY KEY,
            account_id UUID REFERENCES data.\"Account\"(id),
            character_id UUID REFERENCES data.\"Character\"(id),
            amount INTEGER NOT NULL,
            transaction_type VARCHAR(50) NOT NULL,
            description TEXT,
            created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
        )",
        
        'webengine_bans' => "CREATE TABLE IF NOT EXISTS data.webengine_bans (
            id SERIAL PRIMARY KEY,
            account_id UUID REFERENCES data.\"Account\"(id),
            character_id UUID REFERENCES data.\"Character\"(id),
            banned_by VARCHAR(50) NOT NULL,
            ban_reason TEXT NOT NULL,
            ban_type VARCHAR(20) NOT NULL,
            banned_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP WITH TIME ZONE,
            active BOOLEAN DEFAULT true
        )",
        
        'webengine_blocked_ips' => "CREATE TABLE IF NOT EXISTS data.webengine_blocked_ips (
            id SERIAL PRIMARY KEY,
            ip_address INET NOT NULL UNIQUE,
            reason TEXT,
            blocked_by VARCHAR(50) NOT NULL,
            blocked_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
            active BOOLEAN DEFAULT true
        )",
        
        'webengine_paypal_transactions' => "CREATE TABLE IF NOT EXISTS data.webengine_paypal_transactions (
            id SERIAL PRIMARY KEY,
            account_id UUID REFERENCES data.\"Account\"(id),
            character_id UUID REFERENCES data.\"Character\"(id),
            transaction_id VARCHAR(100) NOT NULL UNIQUE,
            amount DECIMAL(10,2) NOT NULL,
            currency VARCHAR(3) DEFAULT 'USD',
            credits_given INTEGER NOT NULL,
            status VARCHAR(20) DEFAULT 'pending',
            created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
            completed_at TIMESTAMP WITH TIME ZONE
        )",
        
        'webengine_password_requests' => "CREATE TABLE IF NOT EXISTS data.webengine_password_requests (
            id SERIAL PRIMARY KEY,
            account_id UUID REFERENCES data.\"Account\"(id),
            verification_key VARCHAR(100) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL,
            requested_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP WITH TIME ZONE DEFAULT (CURRENT_TIMESTAMP + INTERVAL '24 hours'),
            used BOOLEAN DEFAULT false
        )",
        
        'webengine_email_verification' => "CREATE TABLE IF NOT EXISTS data.webengine_email_verification (
            id SERIAL PRIMARY KEY,
            account_id UUID REFERENCES data.\"Account\"(id),
            verification_key VARCHAR(100) NOT NULL UNIQUE,
            email VARCHAR(255) NOT NULL,
            created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP WITH TIME ZONE DEFAULT (CURRENT_TIMESTAMP + INTERVAL '24 hours'),
            verified BOOLEAN DEFAULT false
        )",
        
        'webengine_account_country' => "CREATE TABLE IF NOT EXISTS data.webengine_account_country (
            id SERIAL PRIMARY KEY,
            account_id UUID REFERENCES data.\"Account\"(id),
            country_code VARCHAR(2) NOT NULL,
            country_name VARCHAR(100) NOT NULL,
            detected_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
        )",
        
        'webengine_cron_logs' => "CREATE TABLE IF NOT EXISTS data.webengine_cron_logs (
            id SERIAL PRIMARY KEY,
            cron_name VARCHAR(100) NOT NULL,
            status VARCHAR(20) NOT NULL,
            message TEXT,
            execution_time INTEGER,
            executed_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
        )",
        
        'webengine_plugins' => "CREATE TABLE IF NOT EXISTS data.webengine_plugins (
            id SERIAL PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            version VARCHAR(20) NOT NULL,
            author VARCHAR(100),
            description TEXT,
            active BOOLEAN DEFAULT false,
            installed_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
        )"
    ];
    
    echo "<h2>🔧 Creating Tables</h2>\n";
    
    foreach ($tables as $table_name => $sql) {
        echo "<h3>Creating table: $table_name</h3>\n";
        try {
            $pdo->exec($sql);
            echo "✅ Successfully created data.$table_name<br>\n";
        } catch (PDOException $e) {
            echo "❌ Error creating $table_name: " . $e->getMessage() . "<br>\n";
        }
        echo "<br>\n";
    }
    
    echo "<h2>🔧 Inserting Default Data</h2>\n";
    
    // Insert default vote sites
    try {
        $pdo->exec("INSERT INTO data.webengine_vote_sites (name, url, reward_credits, cooldown_hours, active) VALUES
            ('TopG', 'https://topg.org/vote/your-server', 100, 12, true),
            ('GTop100', 'https://gtop100.com/vote/your-server', 100, 12, true),
            ('Top100Arena', 'https://top100arena.com/vote/your-server', 100, 12, true)");
        echo "✅ Inserted default vote sites<br>\n";
    } catch (PDOException $e) {
        echo "❌ Error inserting vote sites: " . $e->getMessage() . "<br>\n";
    }
    
    // Insert default news
    try {
        $pdo->exec("INSERT INTO data.webengine_news (title, author, content, published) VALUES
            ('Welcome to OpenMU Server!', 'Admin', 'Welcome to our OpenMU server! This is a modern MU Online server built with .NET and PostgreSQL.', true),
            ('Server Rules', 'Admin', 'Please read and follow our server rules to ensure a great gaming experience for everyone.', true)");
        echo "✅ Inserted default news<br>\n";
    } catch (PDOException $e) {
        echo "❌ Error inserting news: " . $e->getMessage() . "<br>\n";
    }
    
    // Insert default downloads
    try {
        $pdo->exec("INSERT INTO data.webengine_downloads (title, description, file_path, file_size, active) VALUES
            ('OpenMU Client', 'Download the official OpenMU client', '/downloads/openmu-client.zip', 104857600, true),
            ('Game Guide', 'Complete game guide for new players', '/downloads/game-guide.pdf', 5242880, true)");
        echo "✅ Inserted default downloads<br>\n";
    } catch (PDOException $e) {
        echo "❌ Error inserting downloads: " . $e->getMessage() . "<br>\n";
    }
    
    echo "<h2>🎉 Installation Complete!</h2>\n";
    echo "<p>All WebEngine tables have been created successfully.</p>\n";
    echo "<p><a href='test_openmu_integration.php'>Run integration tests</a></p>\n";
    echo "<p><a href='index.php'>Start WebEngine CMS</a></p>\n";
    
} catch (PDOException $e) {
    echo "<h2>❌ Database Connection Failed</h2>\n";
    echo "Error: " . $e->getMessage() . "<br>\n";
}
?>




