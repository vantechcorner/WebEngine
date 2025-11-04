<?php
define('access','cron');
require_once(__DIR__.'/includes/webengine.php');

try {
    $db = Connection::Database('Me_MuOnline');
    $ip = isset($_GET['ip']) ? $_GET['ip'] : $_SERVER['REMOTE_ADDR'];
    if(isset($_GET['all']) && $_GET['all'] === '1') {
        $db->query("DELETE FROM ".WEBENGINE_FLA);
        echo "<p>Cleared all lockouts.</p>";
    } else {
        $db->query("DELETE FROM ".WEBENGINE_FLA." WHERE ip_address = ?", array($ip));
        echo "<p>Cleared lockouts for IP: ".$ip."</p>";
    }
    echo '<p><a href="/">Back to site</a></p>';
} catch(Exception $e) {
    echo "<p style='color:red'>Error: ".$e->getMessage()."</p>";
}
?>


