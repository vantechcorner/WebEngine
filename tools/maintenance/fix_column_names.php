<?php
/**
 * Fix Column Name Issues in OpenMU Integration
 */

echo "<h1>Fix Column Name Issues</h1>\n";
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
    
    // Test a simple query to see what works
    echo "<h2>🧪 Testing Queries</h2>\n";
    
    // Test 1: Try with quoted column names
    echo "<h3>Test 1: Quoted Column Names</h3>\n";
    try {
        $query = 'SELECT "Id", "LoginName", "EMail" FROM data."Account" LIMIT 1';
        $stmt = $pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Query with quoted columns works!<br>\n";
        if ($result) {
            echo "Sample data: " . json_encode($result) . "<br>\n";
        }
    } catch (PDOException $e) {
        echo "❌ Query failed: " . $e->getMessage() . "<br>\n";
    }
    
    // Test 2: Try with unquoted column names
    echo "<h3>Test 2: Unquoted Column Names</h3>\n";
    try {
        $query = 'SELECT Id, LoginName, EMail FROM data."Account" LIMIT 1';
        $stmt = $pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "✅ Query with unquoted columns works!<br>\n";
        if ($result) {
            echo "Sample data: " . json_encode($result) . "<br>\n";
        }
    } catch (PDOException $e) {
        echo "❌ Query failed: " . $e->getMessage() . "<br>\n";
    }
    
    // Test 3: Check if WEBENGINE_FLA table exists
    echo "<h3>Test 3: Check WEBENGINE_FLA Table</h3>\n";
    try {
        $query = "SELECT table_name FROM information_schema.tables 
                  WHERE table_schema = 'data' 
                  AND table_name LIKE '%webengine%'";
        $stmt = $pdo->query($query);
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (in_array('webengine_fla', $tables)) {
            echo "✅ WEBENGINE_FLA table exists<br>\n";
        } else {
            echo "❌ WEBENGINE_FLA table does not exist<br>\n";
            echo "Available WebEngine tables: " . implode(', ', $tables) . "<br>\n";
        }
    } catch (PDOException $e) {
        echo "❌ Error checking tables: " . $e->getMessage() . "<br>\n";
    }
    
    echo "<h2>💡 Solutions</h2>\n";
    echo "<ol>\n";
    echo "<li><strong>Column Names:</strong> Use unquoted column names in queries</li>\n";
    echo "<li><strong>Missing Tables:</strong> Run the WebEngine table installation</li>\n";
    echo "<li><strong>Rankings:</strong> Check if Character table has data</li>\n";
    echo "</ol>\n";
    
    // Check Character table for rankings
    echo "<h2>📊 Checking Character Data for Rankings</h2>\n";
    try {
        $query = 'SELECT COUNT(*) as total FROM data."Character"';
        $stmt = $pdo->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Total characters: {$result['total']}<br>\n";
        
        if ($result['total'] > 0) {
            $query = 'SELECT "Name", "Experience", "PlayerKillCount" 
                      FROM data."Character" 
                      WHERE "Experience" > 0 
                      ORDER BY "Experience" DESC 
                      LIMIT 5';
            $stmt = $pdo->query($query);
            $characters = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<h3>Top 5 Characters by Experience:</h3>\n";
            echo "<table border='1' style='border-collapse: collapse;'>\n";
            echo "<tr><th>Name</th><th>Experience</th><th>PK Count</th></tr>\n";
            foreach ($characters as $char) {
                echo "<tr><td>{$char['Name']}</td><td>{$char['Experience']}</td><td>{$char['PlayerKillCount']}</td></tr>\n";
            }
            echo "</table><br>\n";
        } else {
            echo "❌ No character data found for rankings<br>\n";
        }
    } catch (PDOException $e) {
        echo "❌ Error checking character data: " . $e->getMessage() . "<br>\n";
    }
    
} catch (PDOException $e) {
    echo "<h2>❌ Database Connection Failed</h2>\n";
    echo "Error: " . $e->getMessage() . "<br>\n";
}
?>




