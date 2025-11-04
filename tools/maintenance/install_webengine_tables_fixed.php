<?php
/**
 * Install WebEngine Tables in OpenMU Database (Fixed Version)
 */

echo "<h1>Install WebEngine Tables in OpenMU Database (Fixed)</h1>\n";
echo "<hr>\n";

// Database connection parameters
$host = 'localhost';
$port = '5432';
$dbname = 'openmu';
$username = 'postgres';
$password = 'Muahe2025~';

try {
    // Connect to OpenMU database
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>✅ Connected to OpenMU Database</h2>\n";
    echo "Database: $dbname<br>\n";
    echo "Host: $host:$port<br><br>\n";
    
    // Define tables to create in order
    $tables = [
        'webengine_news' => "
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
            )",
        
        'webengine_downloads' => "
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
            )",
        
        'webengine_vote_sites' => "
            CREATE TABLE IF NOT EXISTS data.webengine_vote_sites (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                url VARCHAR(500) NOT NULL,
                reward_credits INTEGER DEFAULT 0,
                cooldown_hours INTEGER DEFAULT 12,
                active BOOLEAN DEFAULT true,
                image_url VARCHAR(500),
                description TEXT
            )",
        
        'webengine_vote_logs' => "
            CREATE TABLE IF NOT EXISTS data.webengine_vote_logs (
                id SERIAL PRIMARY KEY,
                account_id UUID REFERENCES data.\"Account\"(id),
                vote_site_id INTEGER REFERENCES data.webengine_vote_sites(id),
                ip_address INET,
                voted_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                reward_given BOOLEAN DEFAULT false
            )",
        
        'webengine_credits_logs' => "
            CREATE TABLE IF NOT EXISTS data.webengine_credits_logs (
                id SERIAL PRIMARY KEY,
                account_id UUID REFERENCES data.\"Account\"(id),
                character_id UUID REFERENCES data.\"Character\"(id),
                amount INTEGER NOT NULL,
                transaction_type VARCHAR(50) NOT NULL,
                description TEXT,
                created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
            )",
        
        'webengine_bans' => "
            CREATE TABLE IF NOT EXISTS data.webengine_bans (
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
        
        'webengine_blocked_ips' => "
            CREATE TABLE IF NOT EXISTS data.webengine_blocked_ips (
                id SERIAL PRIMARY KEY,
                ip_address INET NOT NULL UNIQUE,
                reason TEXT,
                blocked_by VARCHAR(50) NOT NULL,
                blocked_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                active BOOLEAN DEFAULT true
            )",
        
        'webengine_paypal_transactions' => "
            CREATE TABLE IF NOT EXISTS data.webengine_paypal_transactions (
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
        
        'webengine_password_requests' => "
            CREATE TABLE IF NOT EXISTS data.webengine_password_requests (
                id SERIAL PRIMARY KEY,
                account_id UUID REFERENCES data.\"Account\"(id),
                verification_key VARCHAR(100) NOT NULL UNIQUE,
                email VARCHAR(255) NOT NULL,
                requested_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP WITH TIME ZONE DEFAULT (CURRENT_TIMESTAMP + INTERVAL '24 hours'),
                used BOOLEAN DEFAULT false
            )",
        
        'webengine_email_verification' => "
            CREATE TABLE IF NOT EXISTS data.webengine_email_verification (
                id SERIAL PRIMARY KEY,
                account_id UUID REFERENCES data.\"Account\"(id),
                verification_key VARCHAR(100) NOT NULL UNIQUE,
                email VARCHAR(255) NOT NULL,
                created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP WITH TIME ZONE DEFAULT (CURRENT_TIMESTAMP + INTERVAL '24 hours'),
                verified BOOLEAN DEFAULT false
            )",
        
        'webengine_account_country' => "
            CREATE TABLE IF NOT EXISTS data.webengine_account_country (
                id SERIAL PRIMARY KEY,
                account_id UUID REFERENCES data.\"Account\"(id),
                country_code VARCHAR(2) NOT NULL,
                country_name VARCHAR(100) NOT NULL,
                detected_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
            )",
        
        'webengine_cron_logs' => "
            CREATE TABLE IF NOT EXISTS data.webengine_cron_logs (
                id SERIAL PRIMARY KEY,
                cron_name VARCHAR(100) NOT NULL,
                status VARCHAR(20) NOT NULL,
                message TEXT,
                execution_time INTEGER,
                executed_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
            )",
        
        'webengine_plugins' => "
            CREATE TABLE IF NOT EXISTS data.webengine_plugins (
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
    $success_count = 0;
    $error_count = 0;
    
    foreach ($tables as $table_name => $sql) {
        try {
            $pdo->exec($sql);
            $success_count++;
            echo "✅ Created table: data.$table_name<br>\n";
        } catch (PDOException $e) {
            $error_count++;
            echo "❌ Error creating table $table_name: " . $e->getMessage() . "<br>\n";
        }
    }
    
    echo "<br><h2>🔧 Creating Indexes</h2>\n";
    
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_webengine_news_published ON data.webengine_news(published, created_at)",
        "CREATE INDEX IF NOT EXISTS idx_webengine_downloads_active ON data.webengine_downloads(active, created_at)",
        "CREATE INDEX IF NOT EXISTS idx_webengine_vote_logs_account ON data.webengine_vote_logs(account_id, voted_at)",
        "CREATE INDEX IF NOT EXISTS idx_webengine_credits_logs_account ON data.webengine_credits_logs(account_id, created_at)",
        "CREATE INDEX IF NOT EXISTS idx_webengine_bans_active ON data.webengine_bans(active, banned_at)",
        "CREATE INDEX IF NOT EXISTS idx_webengine_blocked_ips_active ON data.webengine_blocked_ips(active, blocked_at)",
        "CREATE INDEX IF NOT EXISTS idx_webengine_paypal_transactions_status ON data.webengine_paypal_transactions(status, created_at)",
        "CREATE INDEX IF NOT EXISTS idx_webengine_password_requests_key ON data.webengine_password_requests(verification_key, expires_at)",
        "CREATE INDEX IF NOT EXISTS idx_webengine_email_verification_key ON data.webengine_email_verification(verification_key, expires_at)",
        "CREATE INDEX IF NOT EXISTS idx_webengine_account_country_account ON data.webengine_account_country(account_id)",
        "CREATE INDEX IF NOT EXISTS idx_webengine_cron_logs_name ON data.webengine_cron_logs(cron_name, executed_at)",
        "CREATE INDEX IF NOT EXISTS idx_webengine_plugins_active ON data.webengine_plugins(active, name)"
    ];
    
    foreach ($indexes as $index_sql) {
        try {
            $pdo->exec($index_sql);
            echo "✅ Created index<br>\n";
        } catch (PDOException $e) {
            echo "❌ Error creating index: " . $e->getMessage() . "<br>\n";
        }
    }
    
    echo "<br><h2>🔧 Inserting Default Data</h2>\n";
    
    // Insert default vote sites
    try {
        $pdo->exec("INSERT INTO data.webengine_vote_sites (name, url, reward_credits, cooldown_hours, active) VALUES
            ('TopG', 'https://topg.org/vote/your-server', 100, 12, true),
            ('GTop100', 'https://gtop100.com/vote/your-server', 100, 12, true),
            ('Top100Arena', 'https://top100arena.com/vote/your-server', 100, 12, true)
            ON CONFLICT DO NOTHING");
        echo "✅ Inserted default vote sites<br>\n";
    } catch (PDOException $e) {
        echo "❌ Error inserting vote sites: " . $e->getMessage() . "<br>\n";
    }
    
    // Insert default news
    try {
        $pdo->exec("INSERT INTO data.webengine_news (title, author, content, published) VALUES
            ('Welcome to OpenMU Server!', 'Admin', 'Welcome to our OpenMU server! This is a modern MU Online server built with .NET and PostgreSQL.', true),
            ('Server Rules', 'Admin', 'Please read and follow our server rules to ensure a great gaming experience for everyone.', true)
            ON CONFLICT DO NOTHING");
        echo "✅ Inserted default news<br>\n";
    } catch (PDOException $e) {
        echo "❌ Error inserting news: " . $e->getMessage() . "<br>\n";
    }
    
    // Insert default downloads
    try {
        $pdo->exec("INSERT INTO data.webengine_downloads (title, description, file_path, file_size, active) VALUES
            ('OpenMU Client', 'Download the official OpenMU client', '/downloads/openmu-client.zip', 104857600, true),
            ('Game Guide', 'Complete game guide for new players', '/downloads/game-guide.pdf', 5242880, true)
            ON CONFLICT DO NOTHING");
        echo "✅ Inserted default downloads<br>\n";
    } catch (PDOException $e) {
        echo "❌ Error inserting downloads: " . $e->getMessage() . "<br>\n";
    }
    
    echo "<br><h2>📊 Installation Summary</h2>\n";
    echo "✅ Successful table creations: $success_count<br>\n";
    echo "❌ Failed table creations: $error_count<br><br>\n";
    
    if ($error_count == 0) {
        echo "<h2>🎉 Installation Complete!</h2>\n";
        echo "<p>All WebEngine tables have been successfully installed in your OpenMU database.</p>\n";
        echo "<p><strong>Next Steps:</strong></p>\n";
        echo "<ol>\n";
        echo "<li><a href='test_openmu_integration.php'>Run integration tests</a></li>\n";
        echo "<li><a href='index.php'>Start WebEngine CMS</a></li>\n";
        echo "<li><a href='admincp/'>Access Admin Panel</a></li>\n";
        echo "</ol>\n";
    } else {
        echo "<h2>⚠️ Installation Completed with Errors</h2>\n";
        echo "<p>Some tables failed to create. Please check the errors above.</p>\n";
    }
    
    // Verify tables were created
    echo "<br><h2>🔍 Verifying Tables</h2>\n";
    $table_names = array_keys($tables);
    
    foreach ($table_names as $table_name) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM data.$table_name");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "✅ Table data.$table_name exists (rows: {$result['count']})<br>\n";
        } catch (PDOException $e) {
            echo "❌ Table data.$table_name not found or error: " . $e->getMessage() . "<br>\n";
        }
    }
    
} catch (PDOException $e) {
    echo "<h2>❌ Database Connection Failed</h2>\n";
    echo "Error: " . $e->getMessage() . "<br><br>\n";
    echo "<p>Please check:</p>\n";
    echo "<ul>\n";
    echo "<li>PostgreSQL is running</li>\n";
    echo "<li>Database 'openmu' exists</li>\n";
    echo "<li>User 'postgres' has access</li>\n";
    echo "<li>Password is correct</li>\n";
    echo "</ul>\n";
} catch (Exception $e) {
    echo "<h2>❌ Error</h2>\n";
    echo "Error: " . $e->getMessage() . "<br>\n";
}
?>




