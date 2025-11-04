<?php
/**
 * WebEngine CMS Configuration Setup for OpenMU
 */

echo "<h1>WebEngine CMS Configuration Setup</h1>\n";
echo "<hr>\n";

// Check if webengine.json already exists
if (file_exists('includes/config/webengine.json')) {
    echo "<h2>⚠️ Configuration Already Exists</h2>\n";
    echo "<p>webengine.json already exists. Do you want to:</p>\n";
    echo "<ul>\n";
    echo "<li><a href='?action=backup'>Backup existing config and use OpenMU config</a></li>\n";
    echo "<li><a href='?action=overwrite'>Overwrite with OpenMU config</a></li>\n";
    echo "<li><a href='?action=view'>View current config</a></li>\n";
    echo "</ul>\n";
} else {
    echo "<h2>✅ Setting up OpenMU Configuration</h2>\n";
    $action = 'setup';
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
}

switch ($action) {
    case 'setup':
        setupOpenMUConfig();
        break;
    case 'backup':
        backupAndSetupConfig();
        break;
    case 'overwrite':
        overwriteConfig();
        break;
    case 'view':
        viewCurrentConfig();
        break;
}

function setupOpenMUConfig() {
    echo "<h2>Setting up OpenMU Configuration</h2>\n";
    
    if (file_exists('includes/config/webengine.openmu.json')) {
        if (copy('includes/config/webengine.openmu.json', 'includes/config/webengine.json')) {
            echo "✅ Successfully copied OpenMU configuration to webengine.json\n";
            echo "<br><br>\n";
            echo "<h3>Configuration Summary:</h3>\n";
            echo "<ul>\n";
            echo "<li>Server Files: OpenMU</li>\n";
            echo "<li>Database: PostgreSQL (localhost:5432)</li>\n";
            echo "<li>Database Name: openmu</li>\n";
            echo "<li>PDO Driver: 3 (PostgreSQL)</li>\n";
            echo "</ul>\n";
            echo "<br>\n";
            echo "<p><strong>Next Steps:</strong></p>\n";
            echo "<ol>\n";
            echo "<li><a href='install_webengine_tables.php'>Install WebEngine tables in OpenMU database</a></li>\n";
            echo "<li><a href='test_openmu_integration.php'>Run integration tests</a></li>\n";
            echo "<li><a href='index.php'>Start WebEngine CMS</a></li>\n";
            echo "</ol>\n";
        } else {
            echo "❌ Failed to copy configuration file\n";
        }
    } else {
        echo "❌ OpenMU configuration file not found\n";
    }
}

function backupAndSetupConfig() {
    echo "<h2>Backing up and Setting up Configuration</h2>\n";
    
    $backup_name = 'includes/config/webengine.json.backup.' . date('Y-m-d-H-i-s');
    if (copy('includes/config/webengine.json', $backup_name)) {
        echo "✅ Backed up existing config to: $backup_name\n";
        setupOpenMUConfig();
    } else {
        echo "❌ Failed to backup existing configuration\n";
    }
}

function overwriteConfig() {
    echo "<h2>Overwriting Configuration</h2>\n";
    echo "<p>⚠️ This will overwrite your existing configuration!</p>\n";
    echo "<p><a href='?action=overwrite_confirm'>Confirm Overwrite</a> | <a href='?'>Cancel</a></p>\n";
}

function viewCurrentConfig() {
    echo "<h2>Current Configuration</h2>\n";
    if (file_exists('includes/config/webengine.json')) {
        $config = json_decode(file_get_contents('includes/config/webengine.json'), true);
        echo "<pre>" . json_encode($config, JSON_PRETTY_PRINT) . "</pre>\n";
    } else {
        echo "❌ No configuration file found\n";
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'overwrite_confirm') {
    setupOpenMUConfig();
}
?>




