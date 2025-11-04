<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define access constant to bypass WebEngine security
define('access', 'cron');

echo "<h2>Cache Read Test</h2>";

try {
    require_once('includes/webengine.php');

    echo "<p>Testing cache file reading...</p>";

    // Test level rankings cache
    echo "<p>Level rankings cache:</p>";
    $levelData = LoadCacheData('rankings_level.cache');
    if(is_array($levelData)) {
        echo "✅ Cache loaded successfully, " . count($levelData) . " entries<br>";
        if(count($levelData) > 0) {
            echo "First entry: " . implode(" | ", $levelData[0]) . "<br>";
        }
    } else {
        echo "❌ Cache not loaded or empty<br>";
    }

    // Test killers rankings cache
    echo "<p>Killers rankings cache:</p>";
    $killerData = LoadCacheData('rankings_pk.cache');
    if(is_array($killerData)) {
        echo "✅ Cache loaded successfully, " . count($killerData) . " entries<br>";
        if(count($killerData) > 0) {
            echo "First entry: " . implode(" | ", $killerData[0]) . "<br>";
        }
    } else {
        echo "❌ Cache not loaded or empty<br>";
    }

    // Check cache file sizes
    echo "<p>Cache file info:</p>";
    $cacheDir = __PATH_CACHE__;
    $cacheFiles = glob($cacheDir . "rankings_*.cache");
    foreach($cacheFiles as $file) {
        $size = filesize($file);
        echo basename($file) . ": " . $size . " bytes<br>";
    }

} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

