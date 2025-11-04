<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define access constant to bypass WebEngine security
define('access', 'cron');

echo "<h2>Check OpenMU Config Tables</h2>";

try {
    require_once('includes/webengine.php');

    $mu = Connection::Database('MuOnline');

    echo "<p>Checking CharacterClass table...</p>";
    $result = $mu->query_fetch("SELECT * FROM config.\"CharacterClass\" LIMIT 5");
    if (is_array($result)) {
        echo "✅ config.CharacterClass exists, found " . count($result) . " classes:<br>";
        foreach ($result as $row) {
            echo "- " . $row['Name'] . " (ID: " . $row['Id'] . ", Number: " . $row['Number'] . ")<br>";
        }
    } else {
        echo "❌ config.CharacterClass not found or empty<br>";
    }

    echo "<p>Checking GameMapDefinition table...</p>";
    $result = $mu->query_fetch("SELECT * FROM config.\"GameMapDefinition\" LIMIT 5");
    if (is_array($result)) {
        echo "✅ config.GameMapDefinition exists, found " . count($result) . " maps:<br>";
        foreach ($result as $row) {
            echo "- " . $row['Name'] . " (ID: " . $row['Id'] . ", Number: " . $row['Number'] . ")<br>";
        }
    } else {
        echo "❌ config.GameMapDefinition not found or empty<br>";
    }

} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

