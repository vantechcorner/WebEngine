<?php
/**
 * Fix Main Classes to Use OpenMU Versions
 */

echo "<h1>Fix Main Classes to Use OpenMU Versions</h1>\n";
echo "<hr>\n";

// Include WebEngine core
define('access', true);
require_once('includes/webengine.php');

echo "<h2>🔧 Checking Current Configuration</h2>\n";
global $config;
echo "Server Files: " . ($config['server_files'] ?? 'Not set') . "<br>\n";
echo "Database: " . ($config['SQL_DB_NAME'] ?? 'Not set') . "<br>\n";
echo "PDO Driver: " . ($config['SQL_PDO_DRIVER'] ?? 'Not set') . "<br>\n";

if (strtolower($config['server_files']) == 'openmu') {
    echo "✅ OpenMU configuration detected<br>\n";
} else {
    echo "❌ OpenMU configuration not detected<br>\n";
    echo "<p>Please make sure your webengine.json has 'server_files': 'openmu'</p>\n";
    exit;
}

echo "<h2>🧪 Testing OpenMU Classes</h2>\n";

// Test 1: Check if OpenMU classes exist
if (class_exists('LoginOpenMU')) {
    echo "✅ LoginOpenMU class exists<br>\n";
} else {
    echo "❌ LoginOpenMU class not found<br>\n";
}

if (class_exists('RankingsOpenMU')) {
    echo "✅ RankingsOpenMU class exists<br>\n";
} else {
    echo "❌ RankingsOpenMU class not found<br>\n";
}

// Test 2: Test database connection
try {
    $mu = Connection::Database('MuOnline');
    echo "✅ Database connection successful<br>\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>\n";
    exit;
}

// Test 3: Test account query
try {
    $query = 'SELECT "Id", "LoginName", "EMail" FROM data."Account" LIMIT 1';
    $result = $mu->query_fetch_single($query);
    if ($result) {
        echo "✅ Account query successful<br>\n";
        echo "Sample account: {$result['LoginName']}<br>\n";
    } else {
        echo "❌ No accounts found<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Account query failed: " . $e->getMessage() . "<br>\n";
}

// Test 4: Test character query
try {
    $query = 'SELECT "Id", "Name", "Experience" FROM data."Character" WHERE "Experience" > 0 ORDER BY "Experience" DESC LIMIT 5';
    $results = $mu->query_fetch($query);
    if ($results && count($results) > 0) {
        echo "✅ Character query successful<br>\n";
        echo "Found " . count($results) . " characters with experience<br>\n";
    } else {
        echo "❌ No characters found with experience<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Character query failed: " . $e->getMessage() . "<br>\n";
}

// Test 5: Test OpenMU login class
echo "<h2>🧪 Testing OpenMU Login Class</h2>\n";
try {
    $login = new LoginOpenMU();
    echo "✅ LoginOpenMU instantiated<br>\n";
    
    // Test if we can get account data
    $reflection = new ReflectionClass($login);
    $method = $reflection->getMethod('getOpenMUAccountData');
    $method->setAccessible(true);
    
    // Get first account for testing
    $query = 'SELECT "LoginName" FROM data."Account" LIMIT 1';
    $account = $mu->query_fetch_single($query);
    
    if ($account) {
        $accountData = $method->invoke($login, $account['LoginName']);
        if ($accountData) {
            echo "✅ Account data retrieval successful<br>\n";
        } else {
            echo "❌ Account data retrieval failed<br>\n";
        }
    } else {
        echo "❌ No accounts found for testing<br>\n";
    }
    
} catch (Exception $e) {
    echo "❌ LoginOpenMU test failed: " . $e->getMessage() . "<br>\n";
}

// Test 6: Test OpenMU rankings class
echo "<h2>🧪 Testing OpenMU Rankings Class</h2>\n";
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
    echo "❌ RankingsOpenMU test failed: " . $e->getMessage() . "<br>\n";
}

echo "<h2>🎯 Summary</h2>\n";
echo "<p>If all tests pass, the OpenMU classes are working correctly.</p>\n";
echo "<p><strong>The issue might be that WebEngine's main classes are not using the OpenMU versions.</strong></p>\n";
echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ol>\n";
echo "<li>Check if the main login/rankings pages are using the OpenMU classes</li>\n";
echo "<li>Test the actual website login and rankings pages</li>\n";
echo "<li>Check for any error messages in the browser console</li>\n";
echo "</ol>\n";
?>




