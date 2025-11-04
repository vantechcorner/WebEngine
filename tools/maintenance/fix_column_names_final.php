<?php
/**
 * Fix Column Name Issues - Final Version
 */

echo "<h1>Fix Column Name Issues - Final Version</h1>\n";
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
    
    // Check the actual column names in the Account table
    echo "<h2>🔍 Checking Account Table Columns</h2>\n";
    $query = "SELECT column_name, data_type 
              FROM information_schema.columns 
              WHERE table_schema = 'data' 
              AND table_name = 'Account' 
              ORDER BY ordinal_position";
    
    $stmt = $pdo->query($query);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>\n";
    echo "<tr><th>Column Name</th><th>Data Type</th></tr>\n";
    foreach ($columns as $column) {
        echo "<tr><td>{$column['column_name']}</td><td>{$column['data_type']}</td></tr>\n";
    }
    echo "</table><br>\n";
    
    // Check the actual column names in the Character table
    echo "<h2>🔍 Checking Character Table Columns</h2>\n";
    $query = "SELECT column_name, data_type 
              FROM information_schema.columns 
              WHERE table_schema = 'data' 
              AND table_name = 'Character' 
              ORDER BY ordinal_position";
    
    $stmt = $pdo->query($query);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse;'>\n";
    echo "<tr><th>Column Name</th><th>Data Type</th></tr>\n";
    foreach ($columns as $column) {
        echo "<tr><td>{$column['column_name']}</td><td>{$column['data_type']}</td></tr>\n";
    }
    echo "</table><br>\n";
    
    // Test different query approaches
    echo "<h2>🧪 Testing Different Query Approaches</h2>\n";
    
    // Test 1: Fully quoted column names
    echo "<h3>Test 1: Fully Quoted Column Names</h3>\n";
    try {
        $query = 'SELECT "Id", "LoginName", "EMail" FROM data."Account" LIMIT 1';
        $stmt = $pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Fully quoted columns work!<br>\n";
        if ($result) {
            echo "Sample data: " . json_encode($result) . "<br>\n";
        }
    } catch (PDOException $e) {
        echo "❌ Fully quoted columns failed: " . $e->getMessage() . "<br>\n";
    }
    
    // Test 2: Unquoted column names
    echo "<h3>Test 2: Unquoted Column Names</h3>\n";
    try {
        $query = 'SELECT Id, LoginName, EMail FROM data."Account" LIMIT 1';
        $stmt = $pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Unquoted columns work!<br>\n";
        if ($result) {
            echo "Sample data: " . json_encode($result) . "<br>\n";
        }
    } catch (PDOException $e) {
        echo "❌ Unquoted columns failed: " . $e->getMessage() . "<br>\n";
    }
    
    // Test 3: Mixed approach
    echo "<h3>Test 3: Mixed Approach</h3>\n";
    try {
        $query = 'SELECT "Account"."Id", "Account"."LoginName", "Account"."EMail" FROM data."Account" LIMIT 1';
        $stmt = $pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Mixed approach works!<br>\n";
        if ($result) {
            echo "Sample data: " . json_encode($result) . "<br>\n";
        }
    } catch (PDOException $e) {
        echo "❌ Mixed approach failed: " . $e->getMessage() . "<br>\n";
    }
    
    // Test Character table
    echo "<h3>Test 4: Character Table Query</h3>\n";
    try {
        $query = 'SELECT "Id", "Name", "Experience", "PlayerKillCount" FROM data."Character" WHERE "Experience" > 0 ORDER BY "Experience" DESC LIMIT 5';
        $stmt = $pdo->query($query);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "✅ Character table query works!<br>\n";
        echo "Found " . count($results) . " characters with experience<br>\n";
        
        if (count($results) > 0) {
            echo "<h4>Top Characters by Experience:</h4>\n";
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
    
    // Fix the vote sites table
    echo "<h2>🔧 Fixing Vote Sites Table</h2>\n";
    try {
        // Check if the table exists and what columns it has
        $query = "SELECT column_name FROM information_schema.columns 
                  WHERE table_schema = 'data' 
                  AND table_name = 'webengine_vote_sites'";
        $stmt = $pdo->query($query);
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "Current columns in webengine_vote_sites: " . implode(', ', $columns) . "<br>\n";
        
        // Add missing column if it doesn't exist
        if (!in_array('credits_reward', $columns)) {
            $pdo->exec("ALTER TABLE data.webengine_vote_sites ADD COLUMN credits_reward INTEGER DEFAULT 1");
            echo "✅ Added credits_reward column<br>\n";
        }
        
        // Insert sample data
        $pdo->exec("INSERT INTO data.webengine_vote_sites (name, url, credits_reward) VALUES 
            ('TopG', 'https://topg.org/', 1),
            ('GTop100', 'https://gtop100.com/', 1),
            ('Top100Arena', 'https://top100arena.com/', 1)
            ON CONFLICT DO NOTHING");
        echo "✅ Inserted sample vote sites<br>\n";
        
    } catch (PDOException $e) {
        echo "❌ Error fixing vote sites table: " . $e->getMessage() . "<br>\n";
    }
    
    echo "<h2>💡 Solution Summary</h2>\n";
    echo "<p><strong>The issue is PostgreSQL case sensitivity with quoted table names.</strong></p>\n";
    echo "<p>When using quoted table names like <code>data.\"Account\"</code>, column names must also be quoted.</p>\n";
    echo "<p><strong>Correct approach:</strong> Use fully quoted column names: <code>\"Id\"</code>, <code>\"LoginName\"</code>, etc.</p>\n";
    
    echo "<h2>🎯 Next Steps</h2>\n";
    echo "<ol>\n";
    echo "<li>Update the OpenMU table definitions to use quoted column names</li>\n";
    echo "<li>Update the login and rankings classes to use quoted column names</li>\n";
    echo "<li>Test the website functionality</li>\n";
    echo "</ol>\n";
    
} catch (PDOException $e) {
    echo "<h2>❌ Database Connection Failed</h2>\n";
    echo "Error: " . $e->getMessage() . "<br>\n";
}
?>




