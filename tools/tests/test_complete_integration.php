<?php
/**
 * Complete OpenMU Integration Test
 */

echo "<h1>Complete OpenMU Integration Test</h1>\n";
echo "<hr>\n";

// Database connection parameters
$host = 'localhost';
$port = '5432';
$dbname = 'openmu';
$username = 'postgres';
$password = 'Muahe2025~';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>✅ Connected to OpenMU Database</h2>\n";
    
    // Test 1: Check WebEngine tables exist
    echo "<h2>1. WebEngine Tables Check</h2>\n";
    $webengine_tables = [
        'webengine_news',
        'webengine_downloads', 
        'webengine_vote_sites',
        'webengine_vote_logs',
        'webengine_credits_logs',
        'webengine_bans',
        'webengine_blocked_ips',
        'webengine_paypal_transactions',
        'webengine_password_requests',
        'webengine_email_verification',
        'webengine_account_country',
        'webengine_cron_logs',
        'webengine_plugins'
    ];
    
    $tables_exist = 0;
    foreach ($webengine_tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM data.$table");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "✅ Table data.$table exists (rows: {$result['count']})<br>\n";
            $tables_exist++;
        } catch (PDOException $e) {
            echo "❌ Table data.$table not found<br>\n";
        }
    }
    echo "Tables found: $tables_exist/" . count($webengine_tables) . "<br><br>\n";
    
    // Test 2: Check OpenMU data
    echo "<h2>2. OpenMU Data Check</h2>\n";
    
    // Check accounts
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM data.\"Account\"");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ OpenMU Accounts: {$result['count']}<br>\n";
    
    // Check characters
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM data.\"Character\"");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ OpenMU Characters: {$result['count']}<br>\n";
    
    // Check guilds (if they exist)
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM guild.\"Guild\"");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ OpenMU Guilds: {$result['count']}<br>\n";
    } catch (PDOException $e) {
        echo "⚠️ Guild tables not found (this is normal if no guilds exist)<br>\n";
    }
    
    echo "<br>\n";
    
    // Test 3: Test sample queries
    echo "<h2>3. Sample Data Queries</h2>\n";
    
    // Get sample account
    $stmt = $pdo->query("SELECT \"Id\", \"LoginName\", \"EMail\" FROM data.\"Account\" LIMIT 1");
    $account = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($account) {
        echo "✅ Sample Account: {$account['LoginName']} ({$account['EMail']})<br>\n";
    }
    
    // Get sample character
    $stmt = $pdo->query("SELECT \"Id\", \"Name\", \"Experience\", \"State\" FROM data.\"Character\" LIMIT 1");
    $character = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($character) {
        echo "✅ Sample Character: {$character['Name']} (Exp: {$character['Experience']}, State: {$character['State']})<br>\n";
    }
    
    // Get sample news
    $stmt = $pdo->query("SELECT title, author FROM data.webengine_news LIMIT 1");
    $news = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($news) {
        echo "✅ Sample News: {$news['title']} by {$news['author']}<br>\n";
    }
    
    echo "<br>\n";
    
    // Test 4: Test WebEngine CMS files
    echo "<h2>4. WebEngine CMS Files Check</h2>\n";
    $required_files = [
        'includes/config/webengine.json',
        'includes/config/openmu.tables.php',
        'includes/functions/openmu.php',
        'includes/classes/class.login.openmu.php',
        'includes/classes/class.rankings.openmu.php',
        'index.php'
    ];
    
    $files_exist = 0;
    foreach ($required_files as $file) {
        if (file_exists($file)) {
            echo "✅ $file exists<br>\n";
            $files_exist++;
        } else {
            echo "❌ $file not found<br>\n";
        }
    }
    echo "Files found: $files_exist/" . count($required_files) . "<br><br>\n";
    
    // Test 5: Test configuration
    echo "<h2>5. Configuration Check</h2>\n";
    if (file_exists('includes/config/webengine.json')) {
        $config = json_decode(file_get_contents('includes/config/webengine.json'), true);
        if ($config) {
            echo "✅ Configuration loaded successfully<br>\n";
            echo "Server Files: " . ($config['server_files'] ?? 'Not set') . "<br>\n";
            echo "Database: " . ($config['SQL_DB_NAME'] ?? 'Not set') . "<br>\n";
            echo "PDO Driver: " . ($config['SQL_PDO_DRIVER'] ?? 'Not set') . "<br>\n";
        } else {
            echo "❌ Configuration file is invalid JSON<br>\n";
        }
    } else {
        echo "❌ Configuration file not found<br>\n";
    }
    
    echo "<br>\n";
    
    // Test 6: Test OpenMU functions
    echo "<h2>6. OpenMU Functions Test</h2>\n";
    if (file_exists('includes/functions/openmu.php')) {
        include_once('includes/functions/openmu.php');
        echo "✅ OpenMU functions loaded<br>\n";
        
        // Test level calculation
        $test_exp = 1000000;
        if (function_exists('calculateOpenMULevel')) {
            $level = calculateOpenMULevel($test_exp);
            echo "✅ Level calculation test: $test_exp exp = Level $level<br>\n";
        } else {
            echo "❌ calculateOpenMULevel function not found<br>\n";
        }
    } else {
        echo "❌ OpenMU functions file not found<br>\n";
    }
    
    echo "<br>\n";
    
    // Final summary
    echo "<h2>🎯 Integration Summary</h2>\n";
    echo "<p><strong>Status:</strong> ";
    if ($tables_exist == count($webengine_tables) && $files_exist == count($required_files)) {
        echo "✅ <strong>READY TO USE!</strong></p>\n";
        echo "<p>Your WebEngine CMS is fully integrated with OpenMU and ready to use.</p>\n";
        echo "<p><strong>Next Steps:</strong></p>\n";
        echo "<ol>\n";
        echo "<li><a href='index.php'>Start WebEngine CMS</a></li>\n";
        echo "<li><a href='admincp/'>Access Admin Panel</a></li>\n";
        echo "<li>Test login with your OpenMU accounts</li>\n";
        echo "<li>Check rankings and character profiles</li>\n";
        echo "</ol>\n";
    } else {
        echo "⚠️ <strong>NEEDS ATTENTION</strong></p>\n";
        echo "<p>Some components are missing. Please check the errors above.</p>\n";
    }
    
} catch (PDOException $e) {
    echo "<h2>❌ Database Connection Failed</h2>\n";
    echo "Error: " . $e->getMessage() . "<br>\n";
}
?>




