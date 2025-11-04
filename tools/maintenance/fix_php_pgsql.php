<?php
/**
 * Fix PHP PostgreSQL Extensions
 */

echo "<h1>PHP PostgreSQL Extension Fixer</h1>\n";
echo "<hr>\n";

// Get PHP info
$php_ini_file = php_ini_loaded_file();
$php_ini_dir = dirname($php_ini_file);

echo "<h2>Current PHP Configuration</h2>\n";
echo "PHP Version: " . phpversion() . "\n";
echo "PHP.ini file: $php_ini_file\n";
echo "PHP.ini directory: $php_ini_dir\n";
echo "<br><br>\n";

// Check if extensions are loaded
$pdo_loaded = extension_loaded('pdo');
$pdo_pgsql_loaded = extension_loaded('pdo_pgsql');
$pgsql_loaded = extension_loaded('pgsql');

echo "<h2>Extension Status</h2>\n";
echo "PDO: " . ($pdo_loaded ? "✅ Loaded" : "❌ Not loaded") . "\n";
echo "PDO PostgreSQL: " . ($pdo_pgsql_loaded ? "✅ Loaded" : "❌ Not loaded") . "\n";
echo "PostgreSQL: " . ($pgsql_loaded ? "✅ Loaded" : "❌ Not loaded") . "\n";
echo "<br><br>\n";

if ($pdo_pgsql_loaded && $pgsql_loaded) {
    echo "<h2>✅ All Required Extensions Are Loaded!</h2>\n";
    echo "<p>You can now proceed with the database installation.</p>\n";
    echo "<p><a href='install_webengine_tables.php'>Install WebEngine Tables</a></p>\n";
} else {
    echo "<h2>❌ Extensions Missing - Fix Required</h2>\n";
    
    // Check if php.ini is writable
    if (is_writable($php_ini_file)) {
        echo "<h3>Option 1: Auto-Fix (Recommended)</h3>\n";
        echo "<p>I can automatically enable the required extensions in your php.ini file.</p>\n";
        echo "<p><a href='?action=auto_fix'>Auto-Fix Extensions</a></p>\n";
    } else {
        echo "<h3>⚠️ Manual Fix Required</h3>\n";
        echo "<p>Your php.ini file is not writable. Please follow the manual steps below:</p>\n";
    }
    
    echo "<h3>Manual Fix Steps:</h3>\n";
    echo "<ol>\n";
    echo "<li>Open your php.ini file: <code>$php_ini_file</code></li>\n";
    echo "<li>Find the following lines and uncomment them (remove the semicolon):</li>\n";
    echo "<pre>\n";
    echo ";extension=pdo_pgsql\n";
    echo ";extension=pgsql\n";
    echo "</pre>\n";
    echo "<li>Change them to:</li>\n";
    echo "<pre>\n";
    echo "extension=pdo_pgsql\n";
    echo "extension=pgsql\n";
    echo "</pre>\n";
    echo "<li>Save the file and restart your web server</li>\n";
    echo "<li>Refresh this page to check if extensions are loaded</li>\n";
    echo "</ol>\n";
    
    echo "<h3>Alternative: XAMPP Control Panel</h3>\n";
    echo "<ol>\n";
    echo "<li>Open XAMPP Control Panel</li>\n";
    echo "<li>Click 'Config' next to Apache</li>\n";
    echo "<li>Select 'PHP (php.ini)'</li>\n";
    echo "<li>Search for 'pdo_pgsql' and 'pgsql'</li>\n";
    echo "<li>Remove the semicolon (;) from the beginning of these lines:</li>\n";
    echo "<pre>\n";
    echo "extension=pdo_pgsql\n";
    echo "extension=pgsql\n";
    echo "</pre>\n";
    echo "<li>Save and restart Apache</li>\n";
    echo "</ol>\n";
}

// Handle auto-fix
if (isset($_GET['action']) && $_GET['action'] == 'auto_fix') {
    echo "<h2>🔧 Auto-Fixing Extensions...</h2>\n";
    
    try {
        // Read current php.ini
        $php_ini_content = file_get_contents($php_ini_file);
        
        // Enable pdo_pgsql
        $php_ini_content = preg_replace('/^;extension=pdo_pgsql$/m', 'extension=pdo_pgsql', $php_ini_content);
        
        // Enable pgsql
        $php_ini_content = preg_replace('/^;extension=pgsql$/m', 'extension=pgsql', $php_ini_content);
        
        // Write back to file
        if (file_put_contents($php_ini_file, $php_ini_content)) {
            echo "✅ Successfully updated php.ini file\n";
            echo "<p><strong>Important:</strong> You need to restart your web server for changes to take effect.</p>\n";
            echo "<p>After restarting, <a href='check_php_extensions.php'>check extensions again</a>.</p>\n";
        } else {
            echo "❌ Failed to write to php.ini file\n";
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}
?>




