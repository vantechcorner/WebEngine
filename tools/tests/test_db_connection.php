<?php
/**
 * Test Database Connection with Multiple Methods
 */

echo "<h1>Database Connection Test</h1>\n";
echo "<hr>\n";

// Test different connection methods
$host = 'localhost';
$port = '5432';
$dbname = 'openmu';
$username = 'postgres';
$password = 'Muahe2025~';

echo "<h2>Testing Connection Methods</h2>\n";

// Method 1: PDO PostgreSQL
echo "<h3>Method 1: PDO PostgreSQL</h3>\n";
try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ PDO PostgreSQL connection successful!\n";
    
    // Test query
    $stmt = $pdo->query("SELECT version()");
    $version = $stmt->fetchColumn();
    echo "PostgreSQL Version: $version\n";
    
} catch (PDOException $e) {
    echo "❌ PDO PostgreSQL failed: " . $e->getMessage() . "\n";
}
echo "<br>\n";

// Method 2: PostgreSQL extension
echo "<h3>Method 2: PostgreSQL Extension</h3>\n";
if (extension_loaded('pgsql')) {
    try {
        $connection = pg_connect("host=$host port=$port dbname=$dbname user=$username password=$password");
        if ($connection) {
            echo "✅ PostgreSQL extension connection successful!\n";
            
            // Test query
            $result = pg_query($connection, "SELECT version()");
            $version = pg_fetch_result($result, 0, 0);
            echo "PostgreSQL Version: $version\n";
            
            pg_close($connection);
        } else {
            echo "❌ PostgreSQL extension connection failed\n";
        }
    } catch (Exception $e) {
        echo "❌ PostgreSQL extension error: " . $e->getMessage() . "\n";
    }
} else {
    echo "❌ PostgreSQL extension not loaded\n";
}
echo "<br>\n";

// Method 3: Check if PostgreSQL is running
echo "<h3>Method 3: Check PostgreSQL Service</h3>\n";
$connection_string = "host=$host port=$port dbname=$dbname user=$username password=$password";
echo "Connection string: $connection_string\n";

// Try to connect with error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    echo "✅ Connection successful!\n";
    
    // Test OpenMU specific tables
    echo "<h3>Testing OpenMU Tables</h3>\n";
    
    $tables = [
        'data."Account"',
        'data."Character"',
        'guild."Guild"',
        'guild."GuildMember"'
    ];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "✅ Table $table: {$result['count']} rows\n";
        } catch (PDOException $e) {
            echo "❌ Table $table: " . $e->getMessage() . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
    echo "<br>\n";
    echo "<h3>Troubleshooting Steps:</h3>\n";
    echo "<ol>\n";
    echo "<li>Make sure PostgreSQL is running</li>\n";
    echo "<li>Check if the 'openmu' database exists</li>\n";
    echo "<li>Verify username and password</li>\n";
    echo "<li>Check if PostgreSQL is listening on port 5432</li>\n";
    echo "<li>Enable PostgreSQL extensions in PHP</li>\n";
    echo "</ol>\n";
}
?>




