<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Simple Bootstrap Test</h2>";

try {
    echo "<p>Step 1: Testing basic PHP...</p>";
    
    echo "<p>Step 2: Testing file includes...</p>";
    if (file_exists('includes/webengine.php')) {
        echo "✅ includes/webengine.php exists<br>";
    } else {
        echo "❌ includes/webengine.php NOT found<br>";
        exit;
    }
    
    echo "<p>Step 3: Testing config loading...</p>";
    if (file_exists('includes/config/webengine.json')) {
        echo "✅ webengine.json exists<br>";
        $config = json_decode(file_get_contents('includes/config/webengine.json'), true);
        if ($config) {
            echo "✅ Config loaded successfully<br>";
            echo "Server files: " . ($config['server_files'] ?? 'not set') . "<br>";
        } else {
            echo "❌ Failed to parse config JSON<br>";
        }
    } else {
        echo "❌ webengine.json NOT found<br>";
    }
    
    echo "<p>Step 4: Testing database connection...</p>";
    try {
        $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=openmu', 'postgres', 'Muahe2025~');
        echo "✅ Database connection successful<br>";
    } catch (Exception $e) {
        echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    }
    
    echo "<p>Step 5: Testing WebEngine bootstrap...</p>";
    require_once('includes/webengine.php');
    echo "✅ WebEngine loaded successfully<br>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
    echo "<p>Stack trace:</p><pre>" . $e->getTraceAsString() . "</pre>";
}


