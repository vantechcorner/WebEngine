<?php
// Define access to bypass template rendering
define('access', 'cron');

require_once(__DIR__ . '/includes/webengine.php');

echo "<h2>Upgrade WEBENGINE_FLA Table</h2>";

try {
    $db = Connection::Database('MuOnline');

    // Ensure table exists in data schema
    $exists = $db->query_fetch_single("SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema='data' AND table_name='webengine_fla') as exists");
    if(!$exists || !$exists['exists']) {
        throw new Exception("Table data.webengine_fla does not exist. Run install_webengine_tables or fix_fla_table_complete.php first.");
    }

    $cols = $db->query_fetch("SELECT column_name FROM information_schema.columns WHERE table_schema='data' AND table_name='webengine_fla'");
    $colSet = array();
    if(is_array($cols)) { foreach($cols as $c){ $colSet[strtolower($c['column_name'])] = true; } }

    // Add missing columns expected by core login class
    if(!isset($colSet['username'])) {
        $db->query("ALTER TABLE data.webengine_fla ADD COLUMN username VARCHAR(50)");
        echo "<p>Added column: username</p>";
    }
    if(!isset($colSet['ip_address'])) {
        $db->query("ALTER TABLE data.webengine_fla ADD COLUMN ip_address INET");
        echo "<p>Added column: ip_address</p>";
    }
    if(!isset($colSet['unlock_timestamp'])) {
        $db->query("ALTER TABLE data.webengine_fla ADD COLUMN unlock_timestamp BIGINT DEFAULT 0 NOT NULL");
        echo "<p>Added column: unlock_timestamp (BIGINT)</p>";
    } else {
        // if it's timestamp, convert to epoch bigint
        $typeRow = $db->query_fetch_single("SELECT data_type FROM information_schema.columns WHERE table_schema='data' AND table_name='webengine_fla' AND column_name='unlock_timestamp'");
        if(is_array($typeRow) && strtolower($typeRow['data_type']) != 'bigint') {
            $db->query("ALTER TABLE data.webengine_fla ALTER COLUMN unlock_timestamp TYPE BIGINT USING COALESCE(EXTRACT(EPOCH FROM unlock_timestamp),0)::bigint");
            echo "<p>Converted unlock_timestamp to BIGINT epoch.</p>";
        }
    }
    // Core expects failed_attempts and timestamp INT
    $addedFailed = false;
    if(!isset($colSet['failed_attempts'])) {
        $db->query("ALTER TABLE data.webengine_fla ADD COLUMN failed_attempts INTEGER DEFAULT 0 NOT NULL");
        $addedFailed = true;
        echo "<p>Added column: failed_attempts</p>";
    }
    if(!isset($colSet['timestamp'])) {
        $db->query("ALTER TABLE data.webengine_fla ADD COLUMN timestamp BIGINT DEFAULT 0 NOT NULL");
        echo "<p>Added column: timestamp</p>";
    }

    // Migrate data from attempts -> failed_attempts if present
    if(isset($colSet['attempts'])) {
        $db->query("UPDATE data.webengine_fla SET failed_attempts = COALESCE(failed_attempts, 0) + COALESCE(attempts, 0)");
        echo "<p>Migrated existing attempts to failed_attempts.</p>";
    } elseif($addedFailed) {
        // initialize counts
        $db->query("UPDATE data.webengine_fla SET failed_attempts = 0");
    }

    echo "<p><strong>Done.</strong> Try logging in again.</p>";
} catch(Exception $e) {
    echo "<p style='color:red'>Error: ".$e->getMessage()."</p>";
}

?>


