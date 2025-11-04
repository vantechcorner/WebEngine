<?php
/**
 * Check PHP Extensions for PostgreSQL Support
 */

echo "<h1>PHP Extensions Check</h1>\n";
echo "<hr>\n";

echo "<h2>PHP Version</h2>\n";
echo "PHP Version: " . phpversion() . "\n";
echo "PHP SAPI: " . php_sapi_name() . "\n";
echo "PHP Binary: " . PHP_BINARY . "\n";
echo "<br><br>\n";

echo "<h2>Required Extensions for PostgreSQL</h2>\n";

// Check PDO
echo "<h3>PDO Extension</h3>\n";
if (extension_loaded('pdo')) {
    echo "✅ PDO extension is loaded\n";
    echo "Available PDO drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";
} else {
    echo "❌ PDO extension is NOT loaded\n";
}
echo "<br>\n";

// Check PDO PostgreSQL
echo "<h3>PDO PostgreSQL Extension</h3>\n";
if (extension_loaded('pdo_pgsql')) {
    echo "✅ PDO PostgreSQL extension is loaded\n";
} else {
    echo "❌ PDO PostgreSQL extension is NOT loaded\n";
}
echo "<br>\n";

// Check PostgreSQL
echo "<h3>PostgreSQL Extension</h3>\n";
if (extension_loaded('pgsql')) {
    echo "✅ PostgreSQL extension is loaded\n";
} else {
    echo "❌ PostgreSQL extension is NOT loaded\n";
}
echo "<br>\n";

echo "<h2>All Loaded Extensions</h2>\n";
$extensions = get_loaded_extensions();
sort($extensions);
echo "<pre>" . implode("\n", $extensions) . "</pre>\n";

echo "<h2>PHP Configuration File</h2>\n";
echo "php.ini location: " . php_ini_loaded_file() . "\n";
echo "Additional ini files: " . php_ini_scanned_files() . "\n";
?>




