<?php
/**
 * Debug Login Issues
 */

echo "<h1>Debug Login Issues</h1>\n";
echo "<hr>\n";

// Include WebEngine core
define('access', true);
require_once('includes/webengine.php');

echo "<h2>🔧 Testing Login Process Step by Step</h2>\n";

// Test 1: Check if we can connect to database
echo "<h3>Step 1: Database Connection</h3>\n";
try {
    $mu = Connection::Database('MuOnline');
    echo "✅ Database connection successful<br>\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>\n";
    exit;
}

// Test 2: Check if we can query accounts
echo "<h3>Step 2: Account Query</h3>\n";
try {
    $query = 'SELECT "Id", "LoginName", "EMail", "PasswordHash" FROM data."Account" LIMIT 1';
    $result = $mu->query_fetch_single($query);
    if ($result) {
        echo "✅ Account query successful<br>\n";
        echo "Account: {$result['LoginName']}<br>\n";
        echo "Email: {$result['EMail']}<br>\n";
        echo "Password Hash: " . substr($result['PasswordHash'], 0, 20) . "...<br>\n";
    } else {
        echo "❌ No accounts found<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Account query failed: " . $e->getMessage() . "<br>\n";
}

// Test 3: Check if OpenMU classes are loaded
echo "<h3>Step 3: Class Loading</h3>\n";
if (class_exists('LoginOpenMU')) {
    echo "✅ LoginOpenMU class loaded<br>\n";
} else {
    echo "❌ LoginOpenMU class not loaded<br>\n";
}

if (class_exists('RankingsOpenMU')) {
    echo "✅ RankingsOpenMU class loaded<br>\n";
} else {
    echo "❌ RankingsOpenMU class not loaded<br>\n";
}

// Test 4: Check configuration
echo "<h3>Step 4: Configuration</h3>\n";
global $config;
echo "Server Files: " . ($config['server_files'] ?? 'Not set') . "<br>\n";
echo "Database: " . ($config['SQL_DB_NAME'] ?? 'Not set') . "<br>\n";
echo "PDO Driver: " . ($config['SQL_PDO_DRIVER'] ?? 'Not set') . "<br>\n";

// Test 5: Test login class methods
echo "<h3>Step 5: Login Class Methods</h3>\n";
try {
    $login = new LoginOpenMU();
    echo "✅ LoginOpenMU instantiated<br>\n";
    
    // Test if we can call the private method
    $reflection = new ReflectionClass($login);
    $method = $reflection->getMethod('openMUUserExists');
    $method->setAccessible(true);
    
    // Test with a sample username
    $query = 'SELECT "LoginName" FROM data."Account" LIMIT 1';
    $account = $mu->query_fetch_single($query);
    
    if ($account) {
        $exists = $method->invoke($login, $account['LoginName']);
        echo "✅ User exists check: " . ($exists ? 'YES' : 'NO') . "<br>\n";
    } else {
        echo "❌ No accounts found for testing<br>\n";
    }
    
} catch (Exception $e) {
    echo "❌ Login class test failed: " . $e->getMessage() . "<br>\n";
}

// Test 6: Test rankings
echo "<h3>Step 6: Rankings Test</h3>\n";
try {
    $rankings = new RankingsOpenMU();
    echo "✅ RankingsOpenMU instantiated<br>\n";
    
    $levelRankings = $rankings->getLevelRankings();
    if ($levelRankings && count($levelRankings) > 0) {
        echo "✅ Level rankings successful: " . count($levelRankings) . " characters<br>\n";
    } else {
        echo "❌ Level rankings failed or no data<br>\n";
    }
    
} catch (Exception $e) {
    echo "❌ Rankings test failed: " . $e->getMessage() . "<br>\n";
}

// Test 7: Check if WebEngine is using OpenMU classes
echo "<h3>Step 7: WebEngine Integration</h3>\n";
try {
    // Check if the main login class is using OpenMU
    $mainLogin = new login();
    echo "✅ Main login class instantiated<br>\n";
    
    // Check if rankings class is using OpenMU
    $mainRankings = new Rankings();
    echo "✅ Main rankings class instantiated<br>\n";
    
} catch (Exception $e) {
    echo "❌ Main class test failed: " . $e->getMessage() . "<br>\n";
}

echo "<h2>🎯 Debug Summary</h2>\n";
echo "<p>This debug script helps identify where the login and rankings issues are occurring.</p>\n";
echo "<p><strong>Common Issues:</strong></p>\n";
echo "<ul>\n";
echo "<li>Database connection problems</li>\n";
echo "<li>Column name case sensitivity</li>\n";
echo "<li>Class loading issues</li>\n";
echo "<li>Configuration problems</li>\n";
echo "<li>Missing data in database</li>\n";
echo "</ul>\n";
?>




