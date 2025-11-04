<?php
/**
 * Test URL Generation
 */

echo "<h1>URL Generation Test</h1>\n";
echo "<hr>\n";

// Test basic URL generation
echo "<h2>Basic URL Test</h2>\n";
echo "Current URL: " . $_SERVER['REQUEST_URI'] . "<br>\n";
echo "Server Name: " . $_SERVER['SERVER_NAME'] . "<br>\n";
echo "Server Port: " . $_SERVER['SERVER_PORT'] . "<br>\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>\n";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>\n";
echo "Script Filename: " . $_SERVER['SCRIPT_FILENAME'] . "<br><br>\n";

// Test WebEngine path detection
echo "<h2>WebEngine Path Detection Test</h2>\n";

// Simulate WebEngine's path detection
$script_name = $_SERVER['SCRIPT_NAME'];
$script_filename = $_SERVER['SCRIPT_FILENAME'];
$document_root = $_SERVER['DOCUMENT_ROOT'];

echo "Script Name: $script_name<br>\n";
echo "Script Filename: $script_filename<br>\n";
echo "Document Root: $document_root<br><br>\n";

// Calculate paths like WebEngine does
$root_dir = str_replace('\\', '/', dirname(dirname(__FILE__))) . '/';
$relative_root = (!empty($_SERVER['SCRIPT_NAME'])) ? 
    str_ireplace(rtrim(str_replace('\\', '/', realpath(str_replace($_SERVER['SCRIPT_NAME'], '', $_SERVER['SCRIPT_FILENAME']))), '/'), '', $root_dir) : '/';

echo "Calculated Root Dir: $root_dir<br>\n";
echo "Calculated Relative Root: $relative_root<br><br>\n";

// Test base URL generation
$http_host = $_SERVER['HTTP_HOST'] ?? 'CLI';
$server_protocol = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? 'https://' : 'http://';
$base_url = $server_protocol . $http_host . $relative_root;

echo "HTTP Host: $http_host<br>\n";
echo "Server Protocol: $server_protocol<br>\n";
echo "Calculated Base URL: $base_url<br><br>\n";

// Test if this matches what we expect
$expected_base = 'http://localhost:8000/';
echo "Expected Base URL: $expected_base<br>\n";
echo "Match: " . ($base_url === $expected_base ? "✅ YES" : "❌ NO") . "<br><br>\n";

// Test WebEngine CMS loading
echo "<h2>WebEngine CMS Test</h2>\n";
if (file_exists('includes/webengine.php')) {
    echo "✅ WebEngine core file exists<br>\n";
    
    // Test if we can load the configuration
    if (file_exists('includes/config/webengine.json')) {
        $config = json_decode(file_get_contents('includes/config/webengine.json'), true);
        if ($config) {
            echo "✅ Configuration loaded successfully<br>\n";
            echo "Server Files: " . ($config['server_files'] ?? 'Not set') . "<br>\n";
        } else {
            echo "❌ Configuration file is invalid JSON<br>\n";
        }
    } else {
        echo "❌ Configuration file not found<br>\n";
    }
} else {
    echo "❌ WebEngine core file not found<br>\n";
}

echo "<h2>💡 Recommendations</h2>\n";
if ($base_url !== $expected_base) {
    echo "<p><strong>URL Issue Detected:</strong></p>\n";
    echo "<p>The calculated base URL ($base_url) doesn't match the expected URL ($expected_base).</p>\n";
    echo "<p><strong>Solutions:</strong></p>\n";
    echo "<ol>\n";
    echo "<li><strong>Use XAMPP Apache:</strong> Copy files to C:\\xampp\\htdocs\\webengine\\ and use http://localhost/webengine/</li>\n";
    echo "<li><strong>Use different port:</strong> Try php -S localhost:8080</li>\n";
    echo "<li><strong>Use different directory:</strong> Run from a different location</li>\n";
    echo "</ol>\n";
} else {
    echo "<p>✅ URL generation looks correct!</p>\n";
    echo "<p>The issue might be elsewhere. Try accessing the main page again.</p>\n";
}
?>




