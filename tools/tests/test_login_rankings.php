<?php
/**
 * Test Login and Rankings Functionality
 */

echo "<h1>Test Login and Rankings Functionality</h1>\n";
echo "<hr>\n";

// Include WebEngine core
define('access', true);
require_once('includes/webengine.php');

echo "<h2>🔧 Testing Database Connection</h2>\n";
try {
    $mu = Connection::Database('MuOnline');
    echo "✅ Database connection successful<br>\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>\n";
    exit;
}

echo "<h2>🧪 Test 1: Account Query</h2>\n";
try {
    $query = 'SELECT "Id", "LoginName", "EMail" FROM data."Account" LIMIT 1';
    $result = $mu->query_fetch_single($query);
    if ($result) {
        echo "✅ Account query successful<br>\n";
        echo "Sample account: " . json_encode($result) . "<br>\n";
    } else {
        echo "❌ No accounts found<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Account query failed: " . $e->getMessage() . "<br>\n";
}

echo "<h2>🧪 Test 2: Character Query</h2>\n";
try {
    $query = 'SELECT "Id", "Name", "Experience", "PlayerKillCount" FROM data."Character" WHERE "Experience" > 0 ORDER BY "Experience" DESC LIMIT 5';
    $results = $mu->query_fetch($query);
    if ($results && count($results) > 0) {
        echo "✅ Character query successful<br>\n";
        echo "Found " . count($results) . " characters with experience<br>\n";
        
        echo "<h3>Top Characters:</h3>\n";
        echo "<table border='1' style='border-collapse: collapse;'>\n";
        echo "<tr><th>Name</th><th>Experience</th><th>PK Count</th></tr>\n";
        foreach ($results as $char) {
            echo "<tr><td>{$char['Name']}</td><td>{$char['Experience']}</td><td>{$char['PlayerKillCount']}</td></tr>\n";
        }
        echo "</table><br>\n";
    } else {
        echo "❌ No characters found with experience<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Character query failed: " . $e->getMessage() . "<br>\n";
}

echo "<h2>🧪 Test 3: Login Class Test</h2>\n";
try {
    // Check if OpenMU login class is loaded
    if (class_exists('LoginOpenMU')) {
        echo "✅ LoginOpenMU class exists<br>\n";
        
        // Test login class instantiation
        $login = new LoginOpenMU();
        echo "✅ LoginOpenMU class instantiated<br>\n";
        
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
                echo "Account: " . json_encode($accountData) . "<br>\n";
            } else {
                echo "❌ Account data retrieval failed<br>\n";
            }
        } else {
            echo "❌ No accounts found for testing<br>\n";
        }
        
    } else {
        echo "❌ LoginOpenMU class not found<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Login class test failed: " . $e->getMessage() . "<br>\n";
}

echo "<h2>🧪 Test 4: Rankings Class Test</h2>\n";
try {
    // Check if OpenMU rankings class is loaded
    if (class_exists('RankingsOpenMU')) {
        echo "✅ RankingsOpenMU class exists<br>\n";
        
        // Test rankings class instantiation
        $rankings = new RankingsOpenMU();
        echo "✅ RankingsOpenMU class instantiated<br>\n";
        
        // Test level rankings
        $levelRankings = $rankings->getLevelRankings();
        if ($levelRankings && count($levelRankings) > 0) {
            echo "✅ Level rankings successful<br>\n";
            echo "Found " . count($levelRankings) . " characters in level rankings<br>\n";
            
            echo "<h3>Level Rankings:</h3>\n";
            echo "<table border='1' style='border-collapse: collapse;'>\n";
            echo "<tr><th>Rank</th><th>Character</th><th>Account</th><th>Experience</th></tr>\n";
            foreach ($levelRankings as $index => $char) {
                $rank = $index + 1;
                echo "<tr><td>$rank</td><td>{$char['character_name']}</td><td>{$char['account_name']}</td><td>{$char['experience']}</td></tr>\n";
            }
            echo "</table><br>\n";
        } else {
            echo "❌ Level rankings failed or no data<br>\n";
        }
        
        // Test PK rankings
        $pkRankings = $rankings->getPvPRankings();
        if ($pkRankings && count($pkRankings) > 0) {
            echo "✅ PK rankings successful<br>\n";
            echo "Found " . count($pkRankings) . " characters in PK rankings<br>\n";
        } else {
            echo "❌ PK rankings failed or no data<br>\n";
        }
        
    } else {
        echo "❌ RankingsOpenMU class not found<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Rankings class test failed: " . $e->getMessage() . "<br>\n";
}

echo "<h2>🧪 Test 5: WebEngine Configuration</h2>\n";
try {
    global $config;
    echo "Server Files: " . ($config['server_files'] ?? 'Not set') . "<br>\n";
    echo "Database: " . ($config['SQL_DB_NAME'] ?? 'Not set') . "<br>\n";
    echo "PDO Driver: " . ($config['SQL_PDO_DRIVER'] ?? 'Not set') . "<br>\n";
    echo "Password Encryption: " . ($config['SQL_PASSWORD_ENCRYPTION'] ?? 'Not set') . "<br>\n";
    
    if (strtolower($config['server_files']) == 'openmu') {
        echo "✅ OpenMU configuration detected<br>\n";
    } else {
        echo "❌ OpenMU configuration not detected<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Configuration test failed: " . $e->getMessage() . "<br>\n";
}

echo "<h2>🎯 Summary</h2>\n";
echo "<p>If all tests pass, the login and rankings should work on the website.</p>\n";
echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ol>\n";
echo "<li><a href='index.php'>Test the main website</a></li>\n";
echo "<li><a href='login/'>Test user login</a></li>\n";
echo "<li><a href='rankings/'>Test rankings page</a></li>\n";
echo "</ol>\n";
?>




