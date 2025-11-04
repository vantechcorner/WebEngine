<?php
/**
 * Install WebEngine Tables in OpenMU Database
 */

echo "<h1>Install WebEngine Tables in OpenMU Database</h1>\n";
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
    
    // Read the SQL file
    $sql_file = 'install/sql/openmu/webengine_tables.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found: $sql_file");
    }
    
    $sql = file_get_contents($sql_file);
    if (!$sql) {
        throw new Exception("Failed to read SQL file");
    }
    
    echo "<h2>📄 Reading SQL File</h2>\n";
    echo "File: $sql_file<br>\n";
    echo "Size: " . strlen($sql) . " bytes<br><br>\n";
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "<h2>🔧 Executing SQL Statements</h2>\n";
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $index => $statement) {
        if (empty($statement) || strpos($statement, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $success_count++;
            
            // Extract table name for display
            if (preg_match('/CREATE TABLE.*?(\w+\.\w+)/i', $statement, $matches)) {
                echo "✅ Created table: {$matches[1]}<br>\n";
            } elseif (preg_match('/CREATE INDEX.*?(\w+\.\w+)/i', $statement, $matches)) {
                echo "✅ Created index on: {$matches[1]}<br>\n";
            } elseif (preg_match('/INSERT INTO.*?(\w+\.\w+)/i', $statement, $matches)) {
                echo "✅ Inserted data into: {$matches[1]}<br>\n";
            } else {
                echo "✅ Executed statement " . ($index + 1) . "<br>\n";
            }
        } catch (PDOException $e) {
            $error_count++;
            echo "❌ Error in statement " . ($index + 1) . ": " . $e->getMessage() . "<br>\n";
        }
    }
    
    echo "<br><h2>📊 Installation Summary</h2>\n";
    echo "✅ Successful statements: $success_count<br>\n";
    echo "❌ Failed statements: $error_count<br><br>\n";
    
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
        echo "<p>Some statements failed. Please check the errors above and try again.</p>\n";
    }
    
    // Verify tables were created
    echo "<br><h2>🔍 Verifying Tables</h2>\n";
    $tables = [
        'data.webengine_news',
        'data.webengine_downloads',
        'data.webengine_vote_sites',
        'data.webengine_vote_logs',
        'data.webengine_credits_logs',
        'data.webengine_bans',
        'data.webengine_blocked_ips',
        'data.webengine_paypal_transactions',
        'data.webengine_password_requests',
        'data.webengine_email_verification',
        'data.webengine_account_country',
        'data.webengine_cron_logs',
        'data.webengine_plugins'
    ];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "✅ Table $table exists (rows: {$result['count']})<br>\n";
        } catch (PDOException $e) {
            echo "❌ Table $table not found or error: " . $e->getMessage() . "<br>\n";
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




