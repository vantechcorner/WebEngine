<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define access constant to bypass WebEngine security
define('access', 'cron');

/**
 * Update Rankings Cache
 * This script manually updates the rankings cache files
 */

// Load WebEngine
require_once('includes/webengine.php');

echo "<h2>Update Rankings Cache</h2>";

try {
    // Load module configs
    loadModuleConfigs('rankings');

    // Ensure cache files exist and are writable
    $ensureFiles = array(
        'rankings_level.cache',
        'rankings_master.cache',
        'rankings_pk.cache',
        'rankings_guilds.cache',
        'rankings_online.cache',
        'rankings_resets.cache',
        'rankings_grandresets.cache',
        'rankings_votes.cache',
        'rankings_gens.cache'
    );
    foreach($ensureFiles as $fname) {
        $fpath = __PATH_CACHE__.$fname;
        if(!file_exists($fpath)) {
            file_put_contents($fpath, "");
        }
        if(!is_writable($fpath)) {
            @chmod($fpath, 0666);
        }
    }

    // Use OpenMU rankings if available
    $useOpenMU = class_exists('RankingsOpenMU');
    if($useOpenMU) {
        $rank = new RankingsOpenMU();
        echo "<p>✅ Using RankingsOpenMU</p>";
    } else {
        $rank = new Rankings();
        echo "<p>ℹ️ Using base Rankings</p>";
    }

    // Build and write caches we care about
    echo "<p>Building level rankings...</p>";
    $levels = $useOpenMU ? $rank->loadRankings('level') : null;

    if(is_array($levels) && count($levels)>0) {
        // Convert to legacy cache row format: [name, classId, level, mapId]
        $levelsCache = array();
        foreach($levels as $row) {
            $name = isset($row['character_name']) ? $row['character_name'] : (isset($row[0]) ? $row[0] : '');
            $classId = isset($row['class_id']) ? $row['class_id'] : (isset($row[1]) ? $row[1] : 0);
            $level = isset($row['level']) ? $row['level'] : (isset($row[2]) ? $row[2] : 0);
            $mapId = isset($row['map']) ? $row['map'] : (isset($row[3]) ? $row[3] : 0);
            $levelsCache[] = array($name, $classId, (int)$level, (int)$mapId);
        }
        UpdateCache('rankings_level.cache', BuildCacheData($levelsCache));
        echo "<p>✅ Level cache updated (".count($levelsCache)." rows)</p>";
    } else {
        echo "<p>⚠️ No level data found</p>";
    }

    echo "<p>Building killers rankings...</p>";
    $killers = $useOpenMU ? $rank->loadRankings('killers') : null;
    if(is_array($killers) && count($killers)>0) {
        // Expected format: [name, classId, kills, level, mapId, pkLevel]
        $killersCache = array();
        foreach($killers as $row) {
            $name = isset($row['character_name']) ? $row['character_name'] : (isset($row[0]) ? $row[0] : '');
            $classId = isset($row['class_id']) ? $row['class_id'] : (isset($row[1]) ? $row[1] : 0);
            $kills = isset($row['pk_count']) ? $row['pk_count'] : (isset($row[2]) ? $row[2] : 0);
            $level = isset($row['level']) ? $row['level'] : (isset($row[3]) ? $row[3] : 0);
            $mapId = isset($row['map']) ? $row['map'] : (isset($row[4]) ? $row[4] : 0);
            $pkLevel = isset($row['pk_level']) ? $row['pk_level'] : (isset($row[5]) ? $row[5] : 0);
            $killersCache[] = array($name, (int)$classId, (int)$kills, (int)$level, (int)$mapId, (int)$pkLevel);
        }
        UpdateCache('rankings_pk.cache', BuildCacheData($killersCache));
        echo "<p>✅ Killers cache updated (".count($killersCache)." rows)</p>";
    } else {
        echo "<p>ℹ️ No killers data</p>";
    }

    echo "<p>Building master level rankings...</p>";
    $master = $useOpenMU ? $rank->loadRankings('grandresets') : null; // placeholder mapping
    if(is_array($master) && count($master)>0) {
        // Expected format: [name, classId, masterLevel, mapId, resets]
        $masterCache = array();
        foreach($master as $row) {
            $name = isset($row['character_name']) ? $row['character_name'] : (isset($row[0]) ? $row[0] : '');
            $classId = isset($row['class_id']) ? $row['class_id'] : (isset($row[1]) ? $row[1] : 0);
            $mlevel = isset($row['master_level']) ? $row['master_level'] : 0;
            $mapId = 0;
            $resets = isset($row['resets']) ? $row['resets'] : 0;
            $masterCache[] = array($name, $classId, (int)$mlevel, (int)$mapId, (int)$resets);
        }
        UpdateCache('rankings_master.cache', BuildCacheData($masterCache));
        echo "<p>✅ Master cache updated (".count($masterCache)." rows)</p>";
    } else {
        echo "<p>ℹ️ No master data</p>";
    }

    // Report cache files
    echo "<h3>Cache Files Check</h3>";
    foreach ($ensureFiles as $cacheFile) {
        $cachePath = __PATH_CACHE__ . $cacheFile;
        if (file_exists($cachePath)) {
            $size = filesize($cachePath);
            echo "<p>✅ $cacheFile exists (" . number_format($size) . " bytes)</p>";
        } else {
            echo "<p>❌ $cacheFile not found</p>";
        }
    }

    echo "<p><strong>Done. Go to <a href='rankings/level/'>Rankings</a></strong></p>";

} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>File: " . $e->getFile() . " Line: " . $e->getLine() . "</p>";
}
?>
