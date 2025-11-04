<?php
/**
 * Fix FLA Table Structure
 */

// Database connection
$host = 'localhost';
$port = '5432';
$dbname = 'openmu';
$username = 'postgres';
$password = 'Muahe2025~';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;options=--search_path=data,guild,friend,config,public", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Fixing FLA Table Structure</h2>";
    
    // Check if table exists
    $checkTable = "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_schema = 'data' AND table_name = 'webengine_fla')";
    $tableExists = $pdo->query($checkTable)->fetchColumn();
    
    if ($tableExists) {
        echo "<p>✅ Table data.webengine_fla exists</p>";
        
        // Check current structure
        $columns = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'data' AND table_name = 'webengine_fla' ORDER BY ordinal_position")->fetchAll();
        
        echo "<p>Current columns:</p><ul>";
        foreach ($columns as $col) {
            echo "<li>" . $col['column_name'] . " (" . $col['data_type'] . ")</li>";
        }
        echo "</ul>";
        
        // Check if username column exists
        $hasUsername = false;
        foreach ($columns as $col) {
            if ($col['column_name'] == 'username') {
                $hasUsername = true;
                break;
            }
        }
        
        if (!$hasUsername) {
            echo "<p>❌ Missing 'username' column. Adding it...</p>";
            $pdo->exec("ALTER TABLE data.webengine_fla ADD COLUMN username VARCHAR(50)");
            echo "<p>✅ Added 'username' column</p>";
        } else {
            echo "<p>✅ 'username' column already exists</p>";
        }
        
    } else {
        echo "<p>❌ Table data.webengine_fla does not exist. Creating it...</p>";
        $createTable = "
        CREATE TABLE data.webengine_fla (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            ip_address INET NOT NULL,
            unlock_time TIMESTAMP NOT NULL,
            attempts INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($createTable);
        echo "<p>✅ Created table data.webengine_fla with correct structure</p>";
    }
    
    // Test the table
    $testQuery = "SELECT COUNT(*) as count FROM data.webengine_fla";
    $count = $pdo->query($testQuery)->fetchColumn();
    echo "<p>✅ Table test successful. Current rows: $count</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>



