<?php
/**
 * Check Rankings Data
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
    
    echo "<h2>Rankings Data Check</h2>";
    
    // Check accounts
    $accountCount = $pdo->query("SELECT COUNT(*) FROM data.\"Account\"")->fetchColumn();
    echo "<p>📊 Total Accounts: $accountCount</p>";
    
    // Check characters
    $charCount = $pdo->query("SELECT COUNT(*) FROM data.\"Character\"")->fetchColumn();
    echo "<p>📊 Total Characters: $charCount</p>";
    
    // Check characters with experience
    $charWithExp = $pdo->query("SELECT COUNT(*) FROM data.\"Character\" WHERE \"Experience\" > 0")->fetchColumn();
    echo "<p>📊 Characters with Experience > 0: $charWithExp</p>";
    
    // Check top characters by experience
    $topChars = $pdo->query("
        SELECT c.\"Name\", c.\"Experience\", a.\"LoginName\" 
        FROM data.\"Character\" c 
        INNER JOIN data.\"Account\" a ON c.\"AccountId\" = a.\"Id\" 
        WHERE c.\"Experience\" > 0 
        ORDER BY c.\"Experience\" DESC 
        LIMIT 5
    ")->fetchAll();
    
    echo "<p><strong>Top 5 Characters by Experience:</strong></p>";
    echo "<table border='1'><tr><th>Character</th><th>Experience</th><th>Account</th></tr>";
    foreach ($topChars as $char) {
        echo "<tr><td>" . $char['Name'] . "</td><td>" . number_format($char['Experience']) . "</td><td>" . $char['LoginName'] . "</td></tr>";
    }
    echo "</table>";
    
    // Check if there are any characters at all
    if ($charCount == 0) {
        echo "<p>⚠️ No characters found. You may need to create some test characters in OpenMU.</p>";
    } elseif ($charWithExp == 0) {
        echo "<p>⚠️ No characters with experience found. Characters may need to gain some experience in the game.</p>";
    } else {
        echo "<p>✅ Rankings should work with $charWithExp characters having experience.</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>



