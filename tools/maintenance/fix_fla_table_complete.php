<?php
/**
 * Complete FLA Table Fix
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
    
    echo "<h2>Complete FLA Table Fix</h2>";
    
    // Check current structure
    $columns = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'data' AND table_name = 'webengine_fla' ORDER BY ordinal_position")->fetchAll();
    
    echo "<p>Current columns:</p><ul>";
    foreach ($columns as $col) {
        echo "<li>" . $col['column_name'] . " (" . $col['data_type'] . ")</li>";
    }
    echo "</ul>";
    
    // Check what columns are missing
    $requiredColumns = ['username', 'ip_address', 'unlock_timestamp', 'attempts'];
    $existingColumns = array_column($columns, 'column_name');
    
    foreach ($requiredColumns as $reqCol) {
        if (!in_array($reqCol, $existingColumns)) {
            echo "<p>❌ Missing column: $reqCol</p>";
            
            switch ($reqCol) {
                case 'username':
                    $pdo->exec("ALTER TABLE data.webengine_fla ADD COLUMN username VARCHAR(50)");
                    echo "<p>✅ Added username column</p>";
                    break;
                case 'ip_address':
                    $pdo->exec("ALTER TABLE data.webengine_fla ADD COLUMN ip_address INET");
                    echo "<p>✅ Added ip_address column</p>";
                    break;
                case 'unlock_timestamp':
                    $pdo->exec("ALTER TABLE data.webengine_fla ADD COLUMN unlock_timestamp TIMESTAMP");
                    echo "<p>✅ Added unlock_timestamp column</p>";
                    break;
                case 'attempts':
                    $pdo->exec("ALTER TABLE data.webengine_fla ADD COLUMN attempts INTEGER DEFAULT 0");
                    echo "<p>✅ Added attempts column</p>";
                    break;
            }
        } else {
            echo "<p>✅ Column $reqCol already exists</p>";
        }
    }
    
    // Test the table
    $testQuery = "SELECT COUNT(*) as count FROM data.webengine_fla";
    $count = $pdo->query($testQuery)->fetchColumn();
    echo "<p>✅ Table test successful. Current rows: $count</p>";
    
    // Show final structure
    $finalColumns = $pdo->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'data' AND table_name = 'webengine_fla' ORDER BY ordinal_position")->fetchAll();
    echo "<p><strong>Final table structure:</strong></p><ul>";
    foreach ($finalColumns as $col) {
        echo "<li>" . $col['column_name'] . " (" . $col['data_type'] . ")</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>



