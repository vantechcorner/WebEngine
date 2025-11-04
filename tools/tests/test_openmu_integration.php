<?php
/**
 * OpenMU Integration Test Script
 * Tests the WebEngine CMS integration with OpenMU database
 */

echo "<h1>WebEngine CMS + OpenMU Integration Test</h1>\n";
echo "<hr>\n";

// Test 1: Check PHP version
echo "<h2>1. PHP Version Check</h2>\n";
echo "PHP Version: " . phpversion() . "\n";
if (version_compare(phpversion(), '8.1.0', '>=')) {
    echo "✅ PHP version is compatible (8.1+)\n";
} else {
    echo "❌ PHP version is too old. Need 8.1+\n";
}
echo "<br><br>\n";

// Test 2: Check PostgreSQL extension
echo "<h2>2. PostgreSQL Extension Check</h2>\n";
if (extension_loaded('pgsql')) {
    echo "✅ PostgreSQL extension is loaded\n";
} else {
    echo "❌ PostgreSQL extension is not loaded\n";
}
echo "<br><br>\n";

// Test 3: Check PDO PostgreSQL
echo "<h2>3. PDO PostgreSQL Check</h2>\n";
if (extension_loaded('pdo_pgsql')) {
    echo "✅ PDO PostgreSQL extension is loaded\n";
} else {
    echo "❌ PDO PostgreSQL extension is not loaded\n";
}
echo "<br><br>\n";

// Test 4: Test database connection
echo "<h2>4. OpenMU Database Connection Test</h2>\n";
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=openmu', 'postgres', 'Muahe2025~');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Successfully connected to OpenMU database\n";
    
    // Test query to check if OpenMU tables exist
    $stmt = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = 'data'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Found {$result['table_count']} tables in 'data' schema\n";
    
    // Check specific OpenMU tables
    $tables = ['Account', 'Character', 'Guild', 'GuildMember'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = 'data' AND table_name = '$table'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] > 0) {
            echo "✅ Table 'data.$table' exists\n";
        } else {
            echo "❌ Table 'data.$table' not found\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
echo "<br><br>\n";

// Test 5: Test WebEngine CMS files
echo "<h2>5. WebEngine CMS Files Check</h2>\n";
$required_files = [
    'includes/config/openmu.tables.php',
    'includes/classes/class.database.php',
    'includes/functions/openmu.php',
    'includes/classes/class.login.openmu.php',
    'includes/classes/class.rankings.openmu.php',
    'includes/config/webengine.openmu.json'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists\n";
    } else {
        echo "❌ $file not found\n";
    }
}
echo "<br><br>\n";

// Test 6: Test OpenMU table definitions
echo "<h2>6. OpenMU Table Definitions Test</h2>\n";
if (file_exists('includes/config/openmu.tables.php')) {
    include_once('includes/config/openmu.tables.php');
    echo "✅ OpenMU table definitions loaded\n";
    echo "Account table: " . _TBL_ACCOUNT_ . "\n";
    echo "Character table: " . _TBL_CHARACTER_ . "\n";
    echo "Guild table: " . _TBL_GUILD_ . "\n";
} else {
    echo "❌ OpenMU table definitions not found\n";
}
echo "<br><br>\n";

// Test 7: Test OpenMU functions
echo "<h2>7. OpenMU Functions Test</h2>\n";
if (file_exists('includes/functions/openmu.php')) {
    include_once('includes/functions/openmu.php');
    echo "✅ OpenMU functions loaded\n";
    
    // Test level calculation
    $test_exp = 1000000;
    $level = calculateOpenMULevel($test_exp);
    echo "✅ Level calculation test: $test_exp exp = Level $level\n";
} else {
    echo "❌ OpenMU functions not found\n";
}
echo "<br><br>\n";

// Test 8: Test sample data query
echo "<h2>8. Sample Data Query Test</h2>\n";
try {
    $pdo = new PDO('pgsql:host=localhost;port=5432;dbname=openmu', 'postgres', 'Muahe2025~');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test account count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM data.\"Account\"");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Found {$result['count']} accounts in OpenMU database\n";
    
    // Test character count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM data.\"Character\"");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Found {$result['count']} characters in OpenMU database\n";
    
    // Test guild count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM guild.\"Guild\"");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Found {$result['count']} guilds in OpenMU database\n";
    
} catch (PDOException $e) {
    echo "❌ Sample data query failed: " . $e->getMessage() . "\n";
}
echo "<br><br>\n";

echo "<h2>Test Complete!</h2>\n";
echo "<p>If all tests passed, your OpenMU integration is ready to use!</p>\n";
echo "<p>Next steps:</p>\n";
echo "<ul>\n";
echo "<li>Copy webengine.openmu.json to webengine.json</li>\n";
echo "<li>Install WebEngine tables in your OpenMU database</li>\n";
echo "<li>Start the WebEngine CMS</li>\n";
echo "</ul>\n";
?>




