<?php
/**
 * Simple Login Test
 */

echo "<h1>Simple Login Test</h1>\n";
echo "<hr>\n";

// Include WebEngine core
define('access', true);
require_once('includes/webengine.php');

echo "<h2>🔧 Testing Login Process</h2>\n";

// Test 1: Check configuration
echo "<h3>Step 1: Configuration Check</h3>\n";
global $config;
echo "Server Files: " . ($config['server_files'] ?? 'Not set') . "<br>\n";
echo "Database: " . ($config['SQL_DB_NAME'] ?? 'Not set') . "<br>\n";
echo "PDO Driver: " . ($config['SQL_PDO_DRIVER'] ?? 'Not set') . "<br>\n";

if (strtolower($config['server_files']) == 'openmu') {
    echo "✅ OpenMU configuration detected<br>\n";
} else {
    echo "❌ OpenMU configuration not detected<br>\n";
    exit;
}

// Test 2: Check if OpenMU classes are loaded
echo "<h3>Step 2: Class Loading Check</h3>\n";
if (class_exists('LoginOpenMU')) {
    echo "✅ LoginOpenMU class loaded<br>\n";
} else {
    echo "❌ LoginOpenMU class not loaded<br>\n";
}

// Test 3: Test database connection
echo "<h3>Step 3: Database Connection</h3>\n";
try {
    $mu = Connection::Database('MuOnline');
    echo "✅ Database connection successful<br>\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>\n";
    exit;
}

// Test 4: Test account query
echo "<h3>Step 4: Account Query</h3>\n";
try {
    $query = 'SELECT "Id", "LoginName", "PasswordHash" FROM data."Account" LIMIT 1';
    $result = $mu->query_fetch_single($query);
    if ($result) {
        echo "✅ Account query successful<br>\n";
        echo "Sample account: {$result['LoginName']}<br>\n";
        echo "Password hash: " . substr($result['PasswordHash'], 0, 20) . "...<br>\n";
    } else {
        echo "❌ No accounts found<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Account query failed: " . $e->getMessage() . "<br>\n";
}

// Test 5: Test login class
echo "<h3>Step 5: Login Class Test</h3>\n";
try {
    $login = new LoginOpenMU();
    echo "✅ LoginOpenMU instantiated<br>\n";
    
    // Test if we can call the validateOpenMULogin method
    $reflection = new ReflectionClass($login);
    $method = $reflection->getMethod('validateOpenMULogin');
    $method->setAccessible(true);
    
    // Test with sample credentials
    $testUsername = 'test';
    $testPassword = 'test';
    
    echo "Testing login with: $testUsername / $testPassword<br>\n";
    
    try {
        $result = $method->invoke($login, $testUsername, $testPassword);
        if ($result) {
            echo "✅ Login validation successful<br>\n";
        } else {
            echo "❌ Login validation failed<br>\n";
        }
    } catch (Exception $e) {
        echo "❌ Login validation error: " . $e->getMessage() . "<br>\n";
    }
    
} catch (Exception $e) {
    echo "❌ Login class test failed: " . $e->getMessage() . "<br>\n";
}

// Test 6: Test rankings
echo "<h3>Step 6: Rankings Test</h3>\n";
try {
    if (class_exists('RankingsOpenMU')) {
        $rankings = new RankingsOpenMU();
        echo "✅ RankingsOpenMU instantiated<br>\n";
        
        $levelRankings = $rankings->getLevelRankings();
        if ($levelRankings && count($levelRankings) > 0) {
            echo "✅ Level rankings successful: " . count($levelRankings) . " characters<br>\n";
        } else {
            echo "❌ Level rankings failed or no data<br>\n";
        }
    } else {
        echo "❌ RankingsOpenMU class not found<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Rankings test failed: " . $e->getMessage() . "<br>\n";
}

echo "<h2>🎯 Test Summary</h2>\n";
echo "<p>This test verifies that the OpenMU integration is working correctly.</p>\n";
echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ol>\n";
echo "<li>Check the password hash format with: <a href='check_password_hashes.php'>check_password_hashes.php</a></li>\n";
echo "<li>Test the actual website login</li>\n";
echo "<li>Test the rankings page</li>\n";
echo "</ol>\n";
?>




