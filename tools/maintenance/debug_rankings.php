<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define access constant to bypass WebEngine security
define('access', 'cron');

/**
 * Debug Rankings System
 */

// Load WebEngine
require_once('includes/webengine.php');

echo "<h2>Rankings Debug</h2>";

try {
    // Check if OpenMU classes are loaded
    echo "<h3>1. Class Loading Check</h3>";
    
    if (class_exists('LoginOpenMU')) {
        echo "<p>✅ LoginOpenMU class exists</p>";
    } else {
        echo "<p>❌ LoginOpenMU class not found</p>";
    }
    
    if (class_exists('RankingsOpenMU')) {
        echo "<p>✅ RankingsOpenMU class exists</p>";
    } else {
        echo "<p>❌ RankingsOpenMU class not found</p>";
    }
    
    // Check if aliases are working
    if (class_exists('login')) {
        $loginClass = new ReflectionClass('login');
        echo "<p>✅ 'login' class exists: " . $loginClass->getName() . "</p>";
    } else {
        echo "<p>❌ 'login' class not found</p>";
    }
    
    if (class_exists('Rankings')) {
        $rankingsClass = new ReflectionClass('Rankings');
        echo "<p>✅ 'Rankings' class exists: " . $rankingsClass->getName() . "</p>";
    } else {
        echo "<p>❌ 'Rankings' class not found</p>";
    }
    
    // Test rankings directly
    echo "<h3>2. Direct Rankings Test</h3>";
    
    $rankings = new Rankings();
    echo "<p>✅ Rankings object created</p>";
    
    // Test level rankings
    $levelRankings = $rankings->loadRankings('level');
    echo "<p>Level rankings result: " . (is_array($levelRankings) ? count($levelRankings) . " results" : "Failed") . "</p>";
    
    if (is_array($levelRankings) && count($levelRankings) > 0) {
        echo "<p>✅ Level rankings working! First result:</p>";
        echo "<pre>" . print_r($levelRankings[0], true) . "</pre>";
    } else {
        echo "<p>❌ Level rankings failed or empty</p>";
        
        // Test the query directly
        echo "<h3>3. Direct Query Test</h3>";
        
        $query = "SELECT 
                    c.\"Name\" as character_name,
                    c.\"Experience\" as experience,
                    c.\"MasterExperience\" as master_experience,
                    c.\"LevelUpPoints\" as level_up_points,
                    c.\"MasterLevelUpPoints\" as master_level_up_points,
                    c.\"CharacterClassId\" as class_id,
                    a.\"LoginName\" as account_name
                  FROM data.\"Character\" c
                  INNER JOIN data.\"Account\" a ON c.\"AccountId\" = a.\"Id\"
                  WHERE c.\"Experience\" > 0
                  ORDER BY c.\"Experience\" DESC
                  LIMIT 10";
        
        $results = $mu->query_fetch($query);
        echo "<p>Direct query result: " . (is_array($results) ? count($results) . " results" : "Failed") . "</p>";
        
        if (is_array($results) && count($results) > 0) {
            echo "<p>✅ Direct query working! First result:</p>";
            echo "<pre>" . print_r($results[0], true) . "</pre>";
        } else {
            echo "<p>❌ Direct query failed</p>";
        }
    }
    
    // Test other ranking types
    echo "<h3>4. Other Ranking Types</h3>";
    
    $killersRankings = $rankings->loadRankings('killers');
    echo "<p>Killers rankings: " . (is_array($killersRankings) ? count($killersRankings) . " results" : "Failed") . "</p>";
    
    $guildsRankings = $rankings->loadRankings('guilds');
    echo "<p>Guilds rankings: " . (is_array($guildsRankings) ? count($guildsRankings) . " results" : "Failed") . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace:</p><pre>" . $e->getTraceAsString() . "</pre>";
}
?>

