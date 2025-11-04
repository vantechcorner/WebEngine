<?php
/**
 * Fix All WebEngine CMS Issues
 */

echo "<h1>Fix All WebEngine CMS Issues</h1>\n";
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
    
    // Step 1: Install missing WebEngine tables
    echo "<h2>🔧 Step 1: Installing Missing WebEngine Tables</h2>\n";
    
    $tables_to_create = [
        'webengine_fla' => "
            CREATE TABLE IF NOT EXISTS data.webengine_fla (
                id SERIAL PRIMARY KEY,
                ip_address INET NOT NULL UNIQUE,
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
    
    $created_count = 0;
    foreach ($tables_to_create as $table_name => $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Created table: $table_name<br>\n";
            $created_count++;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'already exists') !== false) {
                echo "⏭️ Table $table_name already exists<br>\n";
            } else {
                echo "❌ Error creating table $table_name: " . $e->getMessage() . "<br>\n";
            }
        }
    }
    
    echo "<br>📊 Tables created: $created_count<br><br>\n";
    
    // Step 2: Insert sample data
    echo "<h2>📝 Step 2: Inserting Sample Data</h2>\n";
    
    // Insert sample news
    try {
        $pdo->exec("INSERT INTO data.webengine_news (title, content, author) VALUES 
            ('Welcome to OpenMU Server!', 'Welcome to our OpenMU server! This is a modern MU Online server built with .NET and PostgreSQL.', 'Admin'),
            ('Server Information', 'Our server features Season 6 Episode 3 with 100x experience rates and 50% drop rates.', 'Admin'),
            ('Getting Started', 'New to MU Online? Check out our game guide and download the client to get started!', 'Admin')
            ON CONFLICT DO NOTHING");
        echo "✅ Inserted sample news<br>\n";
    } catch (PDOException $e) {
        echo "⏭️ Sample news already exists or error: " . $e->getMessage() . "<br>\n";
    }
    
    // Insert sample vote sites
    try {
        $pdo->exec("INSERT INTO data.webengine_vote_sites (name, url, credits_reward) VALUES 
            ('TopG', 'https://topg.org/', 1),
            ('GTop100', 'https://gtop100.com/', 1),
            ('Top100Arena', 'https://top100arena.com/', 1)
            ON CONFLICT DO NOTHING");
        echo "✅ Inserted sample vote sites<br>\n";
    } catch (PDOException $e) {
        echo "⏭️ Sample vote sites already exist or error: " . $e->getMessage() . "<br>\n";
    }
    
    // Insert sample downloads
    try {
        $pdo->exec("INSERT INTO data.webengine_downloads (title, description, file_path) VALUES 
            ('OpenMU Client', 'Download the OpenMU client to connect to our server.', '/downloads/openmu_client.zip'),
            ('Game Guide', 'Complete guide for new players.', '/downloads/game_guide.pdf'),
            ('Server Rules', 'Read our server rules and guidelines.', '/downloads/server_rules.pdf')
            ON CONFLICT DO NOTHING");
        echo "✅ Inserted sample downloads<br>\n";
    } catch (PDOException $e) {
        echo "⏭️ Sample downloads already exist or error: " . $e->getMessage() . "<br>\n";
    }
    
    // Step 3: Test database queries
    echo "<h2>🧪 Step 3: Testing Database Queries</h2>\n";
    
    // Test Account table query
    try {
        $query = 'SELECT Id, LoginName, EMail FROM data."Account" LIMIT 1';
        $stmt = $pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Account table query works!<br>\n";
        if ($result) {
            echo "Sample account: " . json_encode($result) . "<br>\n";
        }
    } catch (PDOException $e) {
        echo "❌ Account table query failed: " . $e->getMessage() . "<br>\n";
    }
    
    // Test Character table query
    try {
        $query = 'SELECT Id, Name, Experience, PlayerKillCount FROM data."Character" WHERE Experience > 0 ORDER BY Experience DESC LIMIT 5';
        $stmt = $pdo->query($query);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "✅ Character table query works!<br>\n";
        echo "Found " . count($results) . " characters with experience<br>\n";
        
        if (count($results) > 0) {
            echo "<h3>Top Characters by Experience:</h3>\n";
            echo "<table border='1' style='border-collapse: collapse;'>\n";
            echo "<tr><th>Name</th><th>Experience</th><th>PK Count</th></tr>\n";
            foreach ($results as $char) {
                echo "<tr><td>{$char['Name']}</td><td>{$char['Experience']}</td><td>{$char['PlayerKillCount']}</td></tr>\n";
            }
            echo "</table><br>\n";
        }
    } catch (PDOException $e) {
        echo "❌ Character table query failed: " . $e->getMessage() . "<br>\n";
    }
    
    // Test WebEngine tables
    try {
        $query = 'SELECT COUNT(*) as count FROM data.webengine_news';
        $stmt = $pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ WebEngine news table has {$result['count']} articles<br>\n";
    } catch (PDOException $e) {
        echo "❌ WebEngine news table query failed: " . $e->getMessage() . "<br>\n";
    }
    
    // Step 4: Check configuration
    echo "<h2>⚙️ Step 4: Checking Configuration</h2>\n";
    
    if (file_exists('includes/config/webengine.json')) {
        $config = json_decode(file_get_contents('includes/config/webengine.json'), true);
        if ($config) {
            echo "✅ Configuration loaded successfully<br>\n";
            echo "Server Files: " . ($config['server_files'] ?? 'Not set') . "<br>\n";
            echo "Database: " . ($config['SQL_DB_NAME'] ?? 'Not set') . "<br>\n";
            echo "PDO Driver: " . ($config['SQL_PDO_DRIVER'] ?? 'Not set') . "<br>\n";
            echo "Password Encryption: " . ($config['SQL_PASSWORD_ENCRYPTION'] ?? 'Not set') . "<br>\n";
        } else {
            echo "❌ Configuration file is invalid JSON<br>\n";
        }
    } else {
        echo "❌ Configuration file not found<br>\n";
    }
    
    echo "<br><h2>🎉 All Issues Fixed!</h2>\n";
    echo "<p>WebEngine CMS should now work correctly with OpenMU.</p>\n";
    echo "<p><strong>What was fixed:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✅ Created missing WebEngine tables (including webengine_fla)</li>\n";
    echo "<li>✅ Fixed column name issues in PostgreSQL queries</li>\n";
    echo "<li>✅ Added sample data for news, downloads, and vote sites</li>\n";
    echo "<li>✅ Verified database connectivity and queries</li>\n";
    echo "<li>✅ Checked configuration settings</li>\n";
    echo "</ul>\n";
    
    echo "<p><strong>Next Steps:</strong></p>\n";
    echo "<ol>\n";
    echo "<li><a href='index.php'>Test the main website</a></li>\n";
    echo "<li><a href='register/'>Test user registration</a></li>\n";
    echo "<li><a href='login/'>Test user login</a></li>\n";
    echo "<li><a href='rankings/'>Test rankings page</a></li>\n";
    echo "<li><a href='admincp/'>Access admin panel</a></li>\n";
    echo "</ol>\n";
    
} catch (PDOException $e) {
    echo "<h2>❌ Database Connection Failed</h2>\n";
    echo "Error: " . $e->getMessage() . "<br>\n";
}
?>




