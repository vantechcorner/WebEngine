<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define access constant to bypass WebEngine security
define('access', 'cron');

echo "<h2>Check OpenMU Column Names</h2>";

try {
    require_once('includes/webengine.php');
    
    $mu = Connection::Database('MuOnline');
    
    echo "<p>Checking Character table columns...</p>";
    $result = $mu->query_fetch("SELECT column_name, data_type FROM information_schema.columns WHERE table_schema = 'data' AND table_name = 'Character' ORDER BY ordinal_position");
    
    if (is_array($result)) {
        echo "✅ Found " . count($result) . " columns in data.Character:<br>";
        foreach ($result as $col) {
            echo "- " . $col['column_name'] . " (" . $col['data_type'] . ")<br>";
        }
    } else {
        echo "❌ Could not get column information<br>";
    }
    
    echo "<p>Testing with correct column names...</p>";
    $result = $mu->query_fetch("SELECT \"Name\", \"CharacterClass\", \"Experience\", \"CurrentMap\" FROM data.\"Character\" LIMIT 5");
    if (is_array($result)) {
        echo "✅ Query successful with correct columns, found " . count($result) . " characters<br>";
        foreach ($result as $row) {
            echo "- " . $row['Name'] . " (Class: " . $row['CharacterClass'] . ", Exp: " . $row['Experience'] . ")<br>";
        }
    } else {
        echo "❌ Query still failed<br>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}