<?php
/**
 * Simple Rankings Test
 */

// Database connection
$host = 'localhost';
$port = '5432';
$dbname = 'openmu';
$username = 'postgres';
$password = 'Muahe2025~';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;options=--search_path=data,guild,friend,config,public", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Simple Rankings Test</h2>";
    
    // Load OpenMU functions
    require_once('includes/functions/openmu.php');
    
    // Test the exact query from RankingsOpenMU
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
    
    echo "<p>Testing query:</p>";
    echo "<pre>" . $query . "</pre>";
    
    $results = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Query result: " . count($results) . " rows</p>";
    
    if (count($results) > 0) {
        echo "<p>✅ Query successful! Processing results...</p>";
        
        // Process results like RankingsOpenMU does
        foreach ($results as &$character) {
            $character['level'] = calculateOpenMULevel($character['experience']);
            $character['master_level'] = calculateOpenMUMasterLevel($character['master_experience']);
            $character['resets'] = 0; // getOpenMUCharacterResets($character['character_name']);
            $character['grand_resets'] = 0; // getOpenMUCharacterGrandResets($character['character_name']);
        }
        
        // Sort by calculated level
        usort($results, function($a, $b) {
            return $b['level'] - $a['level'];
        });
        
        echo "<table border='1'>";
        echo "<tr><th>Character</th><th>Level</th><th>Experience</th><th>Account</th><th>Class ID</th></tr>";
        
        foreach ($results as $char) {
            echo "<tr>";
            echo "<td>" . $char['character_name'] . "</td>";
            echo "<td>" . $char['level'] . "</td>";
            echo "<td>" . number_format($char['experience']) . "</td>";
            echo "<td>" . $char['account_name'] . "</td>";
            echo "<td>" . $char['class_id'] . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
    } else {
        echo "<p>❌ No results returned</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>



