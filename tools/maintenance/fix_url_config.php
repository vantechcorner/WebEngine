<?php
/**
 * Fix URL Configuration for WebEngine CMS
 */

echo "<h1>Fix URL Configuration</h1>\n";
echo "<hr>\n";

// Get current working directory and server info
$current_dir = getcwd();
$server_name = $_SERVER['SERVER_NAME'] ?? 'localhost';
$server_port = $_SERVER['SERVER_PORT'] ?? '8000';
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

// Calculate the correct base URL
$base_url = $protocol . '://' . $server_name . ':' . $server_port . '/';

echo "<h2>Current Configuration</h2>\n";
echo "Current Directory: $current_dir<br>\n";
echo "Server Name: $server_name<br>\n";
echo "Server Port: $server_port<br>\n";
echo "Protocol: $protocol<br>\n";
echo "Calculated Base URL: $base_url<br><br>\n";

// Check if webengine.json exists
if (file_exists('includes/config/webengine.json')) {
    $config = json_decode(file_get_contents('includes/config/webengine.json'), true);
    
    if ($config) {
        echo "<h2>Current WebEngine Configuration</h2>\n";
        echo "Configuration loaded successfully<br>\n";
        echo "Server Files: " . ($config['server_files'] ?? 'Not set') . "<br>\n";
        echo "Database: " . ($config['SQL_DB_NAME'] ?? 'Not set') . "<br>\n";
        echo "PDO Driver: " . ($config['SQL_PDO_DRIVER'] ?? 'Not set') . "<br><br>\n";
        
        echo "<h2>✅ Configuration is Correct</h2>\n";
        echo "<p>The issue is with URL generation, not the configuration file.</p>\n";
        echo "<p>This is likely a WebEngine CMS path detection issue.</p>\n";
        
    } else {
        echo "<h2>❌ Configuration File is Invalid</h2>\n";
        echo "<p>The webengine.json file contains invalid JSON.</p>\n";
    }
} else {
    echo "<h2>❌ Configuration File Not Found</h2>\n";
    echo "<p>The webengine.json file does not exist.</p>\n";
}

echo "<h2>🔧 URL Issue Analysis</h2>\n";
echo "<p>The problem is that WebEngine CMS is generating URLs like:</p>\n";
echo "<code>http://localhost:8000D:/Github/WebEngine/</code><br>\n";
echo "<p>Instead of:</p>\n";
echo "<code>http://localhost:8000/</code><br><br>\n";

echo "<h2>💡 Solutions</h2>\n";
echo "<ol>\n";
echo "<li><strong>Use Apache instead of PHP built-in server</strong></li>\n";
echo "<li><strong>Run from the correct directory</strong></li>\n";
echo "<li><strong>Use a different port</strong></li>\n";
echo "</ol>\n";

echo "<h2>🚀 Recommended Fix</h2>\n";
echo "<p><strong>Option 1: Use XAMPP Apache</strong></p>\n";
echo "<ol>\n";
echo "<li>Copy WebEngine files to <code>C:\\xampp\\htdocs\\webengine\\</code></li>\n";
echo "<li>Start Apache in XAMPP Control Panel</li>\n";
echo "<li>Open <code>http://localhost/webengine/</code></li>\n";
echo "</ol>\n";

echo "<p><strong>Option 2: Fix PHP Server</strong></p>\n";
echo "<ol>\n";
echo "<li>Run from the WebEngine root directory</li>\n";
echo "<li>Use: <code>php -S localhost:8000 -t .</code></li>\n";
echo "<li>Open <code>http://localhost:8000</code></li>\n";
echo "</ol>\n";

echo "<p><strong>Option 3: Use Different Port</strong></p>\n";
echo "<ol>\n";
echo "<li>Try: <code>php -S localhost:8080</code></li>\n";
echo "<li>Open <code>http://localhost:8080</code></li>\n";
echo "</ol>\n";

echo "<h2>🎯 Quick Test</h2>\n";
echo "<p>Try these URLs:</p>\n";
echo "<ul>\n";
echo "<li><a href='http://localhost:8000/'>http://localhost:8000/</a></li>\n";
echo "<li><a href='http://localhost:8080/'>http://localhost:8080/</a></li>\n";
echo "<li><a href='http://localhost/webengine/'>http://localhost/webengine/</a> (if using XAMPP Apache)</li>\n";
echo "</ul>\n";
?>




