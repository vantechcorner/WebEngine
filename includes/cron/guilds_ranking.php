<?php
/**
 * WebEngine CMS
 * https://webenginecms.org/
 * 
 * @version 1.0.9.8
 * @author Lautaro Angelico <http://lautaroangelico.com/>
 * @copyright (c) 2013-2017 Lautaro Angelico, All Rights Reserved
 * 
 * Licensed under the MIT license
 * http://opensource.org/licenses/MIT
 */

// File Name
$file_name = basename(__FILE__);

// Load Rankings Class (use OpenMU if available)
$useOpenMU = class_exists('RankingsOpenMU');
$Rankings = $useOpenMU ? new RankingsOpenMU() : new Rankings();

// Load Ranking Configs
loadModuleConfigs('rankings');

if(mconfig('active') && mconfig('rankings_enable_guilds')) {
    if($useOpenMU) {
        // Build guilds cache using OpenMU schema
        $data = $Rankings->loadRankings('guilds');
        if(is_array($data) && count($data) > 0) {
            $rows = array();
            foreach($data as $g) {
                // Expected order: [guild_name, master_name, score, logo_hex]
                $guildName = isset($g['guild_name']) ? $g['guild_name'] : (isset($g[0]) ? $g[0] : '');
                $masterName = isset($g['master_name']) ? $g['master_name'] : (isset($g[1]) ? $g[1] : '');
                $score = isset($g['score']) ? (int)$g['score'] : (isset($g[2]) ? (int)$g[2] : 0);
                $logoHex = isset($g['logo_hex']) ? $g['logo_hex'] : (isset($g['logo']) ? convertOpenMUGuildLogoToHex($g['logo']) : (isset($g[3]) ? $g[3] : ''));
                $rows[] = array($guildName, $masterName, $score, $logoHex);
            }
            if(!empty($rows)) {
                UpdateCache('rankings_guilds.cache', BuildCacheData($rows));
            }
        }
    } else {
        // Legacy/other servers path
        $Rankings->UpdateRankingCache('guilds');
    }
}

// UPDATE CRON
updateCronLastRun($file_name);