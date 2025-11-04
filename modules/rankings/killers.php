<?php
/**
 * WebEngine CMS
 * https://webenginecms.org/
 * 
 * @version 1.2.5
 * @author Lautaro Angelico <http://lautaroangelico.com/>
 * @copyright (c) 2013-2023 Lautaro Angelico, All Rights Reserved
 * 
 * Licensed under the MIT license
 * http://opensource.org/licenses/MIT
 */

try {
	
	echo '<div class="page-title"><span>'.lang('module_titles_txt_10',true).'</span></div>';
	
	// Use OpenMU rankings class if available
	$Rankings = class_exists('RankingsOpenMU') ? new RankingsOpenMU() : new Rankings();
	$Rankings->rankingsMenu();
	loadModuleConfigs('rankings');
	
	if(!mconfig('rankings_enable_pk')) throw new Exception(lang('error_44',true));
	if(!mconfig('active')) throw new Exception(lang('error_44',true));
	
	$ranking_data = LoadCacheData('rankings_pk.cache');
	$useDynamic = false; $dynamicRows = array();
	if(!is_array($ranking_data) || count($ranking_data) <= 1) {
		$RankObj = class_exists('RankingsOpenMU') ? new RankingsOpenMU() : new Rankings();
		$rows = $RankObj->loadRankings('killers');
		if(is_array($rows) && count($rows)>0) {
			foreach($rows as $row) {
				$name = isset($row['character_name']) ? $row['character_name'] : (isset($row[0]) ? $row[0] : '');
				$classId = isset($row['class_id']) ? (int)$row['class_id'] : (isset($row[1]) ? (int)$row[1] : 0);
				$kills = isset($row['pk_count']) ? (int)$row['pk_count'] : (isset($row[2]) ? (int)$row[2] : 0);
				$level = isset($row['level']) ? (int)$row['level'] : (isset($row[3]) ? (int)$row[3] : 0);
				$mapId = isset($row['map']) ? (int)$row['map'] : (isset($row[4]) ? (int)$row[4] : 0);
				$pkLevel = isset($row['pk_level']) ? (int)$row['pk_level'] : (isset($row[5]) ? (int)$row[5] : 0);
				$dynamicRows[] = array($name, $classId, $kills, $level, $mapId, $pkLevel);
			}
			UpdateCache('rankings_pk.cache', BuildCacheData($dynamicRows));
			$useDynamic = true;
		}
		if(!$useDynamic && !is_array($ranking_data)) throw new Exception(lang('error_58',true));
	}
	
	$showPlayerCountry = mconfig('show_country_flags') ? true : false;
	$charactersCountry = loadCache('character_country.cache');
	if(!is_array($charactersCountry)) $showPlayerCountry = false;
	
	if(mconfig('show_online_status')) $onlineCharacters = loadCache('online_characters.cache');
	if(!is_array($onlineCharacters)) $onlineCharacters = array();
	
	if(mconfig('rankings_class_filter')) $Rankings->rankingsFilterMenu();
	
	echo '<table class="rankings-table">';
	echo '<tr>';
	if(mconfig('rankings_show_place_number')) {
		echo '<td style="font-weight:bold;"></td>';
	}
	if($showPlayerCountry) echo '<td style="font-weight:bold;">'.lang('rankings_txt_33').'</td>';
	echo '<td style="font-weight:bold;">'.lang('rankings_txt_11').'</td>';
	echo '<td style="font-weight:bold;">'.lang('rankings_txt_10').'</td>';
	echo '<td style="font-weight:bold;">'.lang('rankings_txt_12').'</td>';
	echo '<td style="font-weight:bold;">'.lang('rankings_txt_35').'</td>';
	echo '<td style="font-weight:bold;">'.lang('rankings_txt_14').'</td>';
	if(mconfig('show_location')) echo '<td style="font-weight:bold;">'.lang('rankings_txt_34').'</td>';
	echo '</tr>';
	$i = 0;
	$source = $useDynamic ? $dynamicRows : $ranking_data;
	foreach($source as $idx => $rdata) {
		// skip timestamp row from cache
		if(!$useDynamic && $idx === 0) { continue; }
		// Normalize cache line if needed
		if(!is_array($rdata)) {
			if(is_string($rdata)) {
				$tmp = explode("¦", $rdata);
				if(count($tmp) < 2) $tmp = explode("Â¦", $rdata);
				if(count($tmp) >= 6) { $rdata = $tmp; } else { continue; }
			} else { continue; }
		}
		// Ensure row has required columns
		if(count($rdata) < 6) { continue; }
		$i++;
		$characterIMG = getPlayerClassAvatar($rdata[1], true, true, 'rankings-class-image');
		$onlineStatus = '';
		if(mconfig('show_online_status')) {
			$state = null;
			if(function_exists('openMuApiIsCharacterOnlineByName')) { $state = openMuApiIsCharacterOnlineByName($rdata[0]); }
			if($state === true) {
				$onlineStatus = '<img src="'.__PATH_ONLINE_STATUS__.'" class="online-status-indicator"/>';
			} elseif($state === false) {
				$onlineStatus = '<img src="'.__PATH_OFFLINE_STATUS__.'" class="online-status-indicator"/>';
			} else {
				$onlineStatus = in_array($rdata[0], $onlineCharacters) ? '<img src="'.__PATH_ONLINE_STATUS__.'" class="online-status-indicator"/>' : '<img src="'.__PATH_OFFLINE_STATUS__.'" class="online-status-indicator"/>';
			}
		}
		echo '<tr data-class-id="'.$rdata[1].'">';
		if(mconfig('rankings_show_place_number')) {
			echo '<td class="rankings-table-place">'.$i.'</td>';
		}
		if($showPlayerCountry) echo '<td><img src="'.getCountryFlag(array_key_exists($rdata[0], $charactersCountry) ? $charactersCountry[$rdata[0]] : 'default').'" /></td>';
		echo '<td>'.$characterIMG.'</td>';
		echo '<td>'.playerProfile($rdata[0]).$onlineStatus.'</td>';
		echo '<td>'.number_format((int)$rdata[3]).'</td>';
		echo '<td>'.returnPkLevel((int)$rdata[5]).'</td>';
		echo '<td>'.number_format((int)$rdata[2]).'</td>';
		if(mconfig('show_location')) echo '<td>'.returnMapName((int)$rdata[4]).'</td>';
		echo '</tr>';
	}
	echo '</table>';
	if(mconfig('rankings_show_date')) {
		echo '<div class="rankings-update-time">';
		echo ''.lang('rankings_txt_20',true).' ' . date("m/d/Y - h:i A",$ranking_data[0][0]);
		echo '</div>';
	}
	
} catch(Exception $ex) {
	message('error', $ex->getMessage());
}