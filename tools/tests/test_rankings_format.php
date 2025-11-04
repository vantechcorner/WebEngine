<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define access constant to bypass WebEngine security
define('access', 'cron');

echo "<h2>Rankings Data Format Test</h2>";

try {
    require_once('includes/webengine.php');

    echo "<p>Testing RankingsOpenMU getLevelRankings()...</p>";
    $rank = new RankingsOpenMU();
    $levelData = $rank->getLevelRankings();

    echo "<p>getLevelRankings() returned: " . (is_array($levelData) ? count($levelData) . " rows" : "not an array") . "</p>";
    if(is_array($levelData) && count($levelData)>0) {
        echo "<p>First row keys: " . implode(", ", array_keys($levelData[0])) . "</p>";
        echo "<p>First row data: <pre>" . print_r($levelData[0], true) . "</pre></p>";
    }

    echo "<p>Testing RankingsOpenMU loadRankings('level')...</p>";
    $loadData = $rank->loadRankings('level');
    echo "<p>loadRankings() returned: " . (is_array($loadData) ? count($loadData) . " rows" : "not an array") . "</p>";
    if(is_array($loadData) && count($loadData)>0) {
        echo "<p>First row keys: " . implode(", ", array_keys($loadData[0])) . "</p>";
        echo "<p>First row data: <pre>" . print_r($loadData[0], true) . "</pre></p>";
    }

    echo "<p>Testing parent Rankings loadRankings('level')...</p>";
    $parentRank = new Rankings();
    $parentData = $parentRank->loadRankings('level');
    echo "<p>Parent loadRankings() returned: " . (is_array($parentData) ? count($parentData) . " rows" : "not an array") . "</p>";
    if(is_array($parentData) && count($parentData)>0) {
        echo "<p>Parent first row keys: " . implode(", ", array_keys($parentData[0])) . "</p>";
        echo "<p>Parent first row data: <pre>" . print_r($parentData[0], true) . "</pre></p>";
    }

} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . "</p>";
    echo "<p>Line: " . $e->getLine() . "</p>";
}

