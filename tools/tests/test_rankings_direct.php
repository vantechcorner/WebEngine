<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define access constant to bypass WebEngine security
define('access', 'cron');

echo "<h2>Direct Rankings Test</h2>";

try {
    echo "<p>Loading WebEngine...</p>";
    require_once('includes/webengine.php');
    echo "✅ WebEngine loaded<br>";
    
    echo "<p>Testing Rankings class...</p>";
    if (class_exists('RankingsOpenMU')) {
        echo "✅ RankingsOpenMU class exists<br>";
    } else {
        echo "❌ RankingsOpenMU class not found<br>";
    }
    
    if (class_exists('Rankings')) {
        echo "✅ Rankings class exists<br>";
    } else {
        echo "❌ Rankings class not found<br>";
    }
    
    echo "<p>Testing database connection...</p>";
    $mu = Connection::Database('MuOnline');
    echo "✅ Database connection established<br>";
    
    echo "<p>Testing direct query...</p>";
    $result = $mu->query_fetch("SELECT \"Name\", \"CharacterClassId\", \"Experience\", \"CurrentMapId\" FROM data.\"Character\" LIMIT 5");
    if (is_array($result)) {
        echo "✅ Query successful, found " . count($result) . " characters<br>";
        foreach ($result as $row) {
            echo "- " . $row['Name'] . " (Class: " . $row['CharacterClassId'] . ", Exp: " . $row['Experience'] . ")<br>";
        }
    } else {
        echo "❌ Query failed or no results<br>";
    }
    
    echo "<p>Testing RankingsOpenMU methods...</p>";
    $rank = new RankingsOpenMU();
    echo "✅ RankingsOpenMU instantiated<br>";
    
    $levelData = $rank->loadRankings('level');
    if (is_array($levelData)) {
        echo "✅ Level rankings loaded: " . count($levelData) . " entries<br>";
        foreach (array_slice($levelData, 0, 3) as $entry) {
            echo "- " . (isset($entry['character_name']) ? $entry['character_name'] : 'Unknown') . "<br>";
        }
    } else {
        echo "❌ Level rankings failed<br>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}

