<?php
/**
 * Test Level Calculation
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
    
    echo "<h2>Level Calculation Test</h2>";
    
    // Load OpenMU functions
    require_once('includes/functions/openmu.php');
    
    // Get characters with experience
    $query = "
        SELECT c.\"Name\", c.\"Experience\", c.\"MasterExperience\", a.\"LoginName\" 
        FROM data.\"Character\" c 
        INNER JOIN data.\"Account\" a ON c.\"AccountId\" = a.\"Id\" 
        WHERE c.\"Experience\" > 0 
        ORDER BY c.\"Experience\" DESC 
        LIMIT 10
    ";
    
    $characters = $pdo->query($query)->fetchAll();
    
    echo "<table border='1'>";
    echo "<tr><th>Character</th><th>Experience</th><th>Calculated Level</th><th>Master Exp</th><th>Master Level</th><th>Account</th></tr>";
    
    foreach ($characters as $char) {
        $level = calculateOpenMULevel($char['Experience']);
        $masterLevel = calculateOpenMUMasterLevel($char['MasterExperience']);
        
        echo "<tr>";
        echo "<td>" . $char['Name'] . "</td>";
        echo "<td>" . number_format($char['Experience']) . "</td>";
        echo "<td>" . $level . "</td>";
        echo "<td>" . number_format($char['MasterExperience']) . "</td>";
        echo "<td>" . $masterLevel . "</td>";
        echo "<td>" . $char['LoginName'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<p>✅ Level calculation test completed. If levels look reasonable, rankings should work.</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>



