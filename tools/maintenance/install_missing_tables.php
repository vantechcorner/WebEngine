<?php
/**
 * Install Missing WebEngine Tables
 */

echo "<h1>Install Missing WebEngine Tables</h1>\n";
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
    
    // Check which tables already exist
    echo "<h2>🔍 Checking Existing Tables</h2>\n";
    $query = "SELECT table_name FROM information_schema.tables 
              WHERE table_schema = 'data' 
              AND table_name LIKE 'webengine_%'";
    $stmt = $pdo->query($query);
    $existing_tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Existing WebEngine tables: " . implode(', ', $existing_tables) . "<br><br>\n";
    
    // Create missing tables
    $tables_to_create = [
        'webengine_fla' => "
            CREATE TABLE IF NOT EXISTS data.webengine_fla (
                id SERIAL PRIMARY KEY,
                ip_address INET NOT NULL,
                attempts INTEGER DEFAULT 1,
                last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                blocked BOOLEAN DEFAULT FALSE
            )",
        'webengine_news' => "
            CREATE TABLE IF NOT EXISTS data.webengine_news (
                id SERIAL PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                content TEXT,
                author VARCHAR(100),
                date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                visible BOOLEAN DEFAULT TRUE
            )",
        'webengine_downloads' => "
            CREATE TABLE IF NOT EXISTS data.webengine_downloads (
                id SERIAL PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                file_path VARCHAR(500),
                file_size BIGINT,
                downloads INTEGER DEFAULT 0,
                date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                visible BOOLEAN DEFAULT TRUE
            )",
        'webengine_vote_sites' => "
            CREATE TABLE IF NOT EXISTS data.webengine_vote_sites (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                url VARCHAR(500) NOT NULL,
                image_url VARCHAR(500),
                credits_reward INTEGER DEFAULT 1,
                cooldown_hours INTEGER DEFAULT 12,
                visible BOOLEAN DEFAULT TRUE
            )",
        'webengine_vote_logs' => "
            CREATE TABLE IF NOT EXISTS data.webengine_vote_logs (
                id SERIAL PRIMARY KEY,
                account_id UUID REFERENCES data.\"Account\"(Id),
                site_id INTEGER REFERENCES data.webengine_vote_sites(id),
                ip_address INET,
                vote_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
        'webengine_credits_logs' => "
            CREATE TABLE IF NOT EXISTS data.webengine_credits_logs (
                id SERIAL PRIMARY KEY,
                account_id UUID REFERENCES data.\"Account\"(Id),
                amount INTEGER NOT NULL,
                reason VARCHAR(255),
                date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
        'webengine_bans' => "
            CREATE TABLE IF NOT EXISTS data.webengine_bans (
                id SERIAL PRIMARY KEY,
                account_id UUID REFERENCES data.\"Account\"(Id),
                reason TEXT,
                banned_by VARCHAR(100),
                ban_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                unban_date TIMESTAMP,
                active BOOLEAN DEFAULT TRUE
            )",
        'webengine_blocked_ips' => "
            CREATE TABLE IF NOT EXISTS data.webengine_blocked_ips (
                id SERIAL PRIMARY KEY,
                ip_address INET NOT NULL,
                reason TEXT,
                blocked_by VARCHAR(100),
                block_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                unblock_date TIMESTAMP,
                active BOOLEAN DEFAULT TRUE
            )",
        'webengine_paypal_transactions' => "
            CREATE TABLE IF NOT EXISTS data.webengine_paypal_transactions (
                id SERIAL PRIMARY KEY,
                account_id UUID REFERENCES data.\"Account\"(Id),
                transaction_id VARCHAR(255) UNIQUE,
                amount DECIMAL(10,2),
                credits INTEGER,
                status VARCHAR(50),
                date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
        'webengine_password_requests' => "
            CREATE TABLE IF NOT EXISTS data.webengine_password_requests (
                id SERIAL PRIMARY KEY,
                account_id UUID REFERENCES data.\"Account\"(Id),
                token VARCHAR(255) UNIQUE,
                request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                used BOOLEAN DEFAULT FALSE
            )",
        'webengine_email_verification' => "
            CREATE TABLE IF NOT EXISTS data.webengine_email_verification (
                id SERIAL PRIMARY KEY,
                account_id UUID REFERENCES data.\"Account\"(Id),
                email VARCHAR(255),
                token VARCHAR(255) UNIQUE,
                request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                verified BOOLEAN DEFAULT FALSE
            )",
        'webengine_account_country' => "
            CREATE TABLE IF NOT EXISTS data.webengine_account_country (
                id SERIAL PRIMARY KEY,
                account_id UUID REFERENCES data.\"Account\"(Id),
                country_code VARCHAR(2),
                country_name VARCHAR(100),
                date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
        'webengine_cron_logs' => "
            CREATE TABLE IF NOT EXISTS data.webengine_cron_logs (
                id SERIAL PRIMARY KEY,
                cron_name VARCHAR(100),
                status VARCHAR(50),
                message TEXT,
                execution_time DECIMAL(10,4),
                date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
        'webengine_plugins' => "
            CREATE TABLE IF NOT EXISTS data.webengine_plugins (
                id SERIAL PRIMARY KEY,
                name VARCHAR(100) UNIQUE,
                version VARCHAR(20),
                enabled BOOLEAN DEFAULT FALSE,
                config TEXT,
                install_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
    ];
    
    echo "<h2>🔧 Creating Missing Tables</h2>\n";
    $created_count = 0;
    $skipped_count = 0;
    
    foreach ($tables_to_create as $table_name => $sql) {
        if (in_array($table_name, $existing_tables)) {
            echo "⏭️ Table $table_name already exists, skipping<br>\n";
            $skipped_count++;
        } else {
            try {
                $pdo->exec($sql);
                echo "✅ Created table: $table_name<br>\n";
                $created_count++;
            } catch (PDOException $e) {
                echo "❌ Error creating table $table_name: " . $e->getMessage() . "<br>\n";
            }
        }
    }
    
    echo "<br><h2>📊 Installation Summary</h2>\n";
    echo "✅ Tables created: $created_count<br>\n";
    echo "⏭️ Tables skipped: $skipped_count<br><br>\n";
    
    // Insert some sample data
    echo "<h2>📝 Inserting Sample Data</h2>\n";
    
    // Insert sample news
    try {
        $pdo->exec("INSERT INTO data.webengine_news (title, content, author) VALUES 
            ('Welcome to OpenMU Server!', 'Welcome to our OpenMU server! This is a modern MU Online server built with .NET and PostgreSQL.', 'Admin'),
            ('Server Information', 'Our server features Season 6 Episode 3 with 100x experience rates and 50% drop rates.', 'Admin')
            ON CONFLICT DO NOTHING");
        echo "✅ Inserted sample news<br>\n";
    } catch (PDOException $e) {
        echo "⏭️ Sample news already exists or error: " . $e->getMessage() . "<br>\n";
    }
    
    // Insert sample vote sites
    try {
        $pdo->exec("INSERT INTO data.webengine_vote_sites (name, url, credits_reward) VALUES 
            ('TopG', 'https://topg.org/', 1),
            ('GTop100', 'https://gtop100.com/', 1)
            ON CONFLICT DO NOTHING");
        echo "✅ Inserted sample vote sites<br>\n";
    } catch (PDOException $e) {
        echo "⏭️ Sample vote sites already exist or error: " . $e->getMessage() . "<br>\n";
    }
    
    // Insert sample downloads
    try {
        $pdo->exec("INSERT INTO data.webengine_downloads (title, description, file_path) VALUES 
            ('OpenMU Client', 'Download the OpenMU client to connect to our server.', '/downloads/openmu_client.zip'),
            ('Game Guide', 'Complete guide for new players.', '/downloads/game_guide.pdf')
            ON CONFLICT DO NOTHING");
        echo "✅ Inserted sample downloads<br>\n";
    } catch (PDOException $e) {
        echo "⏭️ Sample downloads already exist or error: " . $e->getMessage() . "<br>\n";
    }
    
    echo "<br><h2>🎉 Installation Complete!</h2>\n";
    echo "<p>All missing WebEngine tables have been created successfully.</p>\n";
    echo "<p><strong>Next Steps:</strong></p>\n";
    echo "<ol>\n";
    echo "<li><a href='test_complete_integration.php'>Run complete integration test</a></li>\n";
    echo "<li><a href='index.php'>Test the main website</a></li>\n";
    echo "<li><a href='admincp/'>Access admin panel</a></li>\n";
    echo "</ol>\n";
    
} catch (PDOException $e) {
    echo "<h2>❌ Database Connection Failed</h2>\n";
    echo "Error: " . $e->getMessage() . "<br>\n";
}
?>




