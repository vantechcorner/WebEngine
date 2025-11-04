<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define access constant to bypass WebEngine security
define('access', 'cron');

/**
 * Check Cache Directory
 */

// Load WebEngine
require_once('includes/webengine.php');

echo "<h2>Cache Directory Check</h2>";

try {
    echo "<p>Cache directory: " . __PATH_CACHE__ . "</p>";
    
    if (is_dir(__PATH_CACHE__)) {
        echo "<p>✅ Cache directory exists</p>";
        
        if (is_writable(__PATH_CACHE__)) {
            echo "<p>✅ Cache directory is writable</p>";
        } else {
            echo "<p>❌ Cache directory is not writable</p>";
        }
        
        // List existing cache files
        $cacheFiles = glob(__PATH_CACHE__ . "*.cache");
        echo "<p>Existing cache files (" . count($cacheFiles) . "):</p>";
        echo "<ul>";
        foreach ($cacheFiles as $file) {
            $filename = basename($file);
            $size = filesize($file);
            echo "<li>$filename (" . number_format($size) . " bytes)</li>";
        }
        echo "</ul>";
        
    } else {
        echo "<p>❌ Cache directory does not exist</p>";
        
        // Try to create it
        if (mkdir(__PATH_CACHE__, 0755, true)) {
            echo "<p>✅ Created cache directory</p>";
        } else {
            echo "<p>❌ Failed to create cache directory</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>


