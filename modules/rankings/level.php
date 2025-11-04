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
	
	if(!mconfig('rankings_enable_level')) throw new Exception(lang('error_44',true));
	if(!mconfig('active')) throw new Exception(lang('error_44',true));
	
	$ranking_data = LoadCacheData('rankings_level.cache');
	$useDynamic = false; $dynamicRows = array();
	$needsRebuild = false;
	if(is_array($ranking_data) && count($ranking_data) > 1) {
		// If parsed row doesn't have 4 columns, rebuild dynamically
		if(!is_array($ranking_data[1]) || count($ranking_data[1]) < 4) {
			$needsRebuild = true;
		}
	} else {
		$needsRebuild = true;
	}
	if($needsRebuild) {
		// Fallback: build rankings on the fly if cache is empty
		$RankObj = class_exists('RankingsOpenMU') ? new RankingsOpenMU() : new Rankings();
		$rows = $RankObj->loadRankings('level');
		if(is_array($rows) && count($rows)>0) {
			// Prepare dynamic rows for immediate render
			foreach($rows as $row) {
				$name = isset($row['character_name']) ? $row['character_name'] : (isset($row[0]) ? $row[0] : '');
				$classId = isset($row['class_id']) ? $row['class_id'] : (isset($row[1]) ? $row[1] : 0);
				$level = isset($row['level']) ? $row['level'] : (isset($row[2]) ? $row[2] : 0);
				$mapId = isset($row['map']) ? $row['map'] : (isset($row[3]) ? $row[3] : 0);
				$dynamicRows[] = array($name, (int)$classId, (int)$level, (int)$mapId);
			}
			// Persist cache for future requests
			UpdateCache('rankings_level.cache', BuildCacheData($dynamicRows));
			$useDynamic = true;
		}
	}
	if(!$useDynamic && !is_array($ranking_data)) throw new Exception(lang('error_58',true));
	
	$showPlayerCountry = mconfig('show_country_flags') ? true : false;
	$charactersCountry = loadCache('character_country.cache');
	if(!is_array($charactersCountry)) $showPlayerCountry = false;
	
	if(mconfig('show_online_status')) $onlineCharacters = loadCache('online_characters.cache');
	if(!is_array($onlineCharacters)) $onlineCharacters = array();
	
	if(mconfig('rankings_class_filter')) $Rankings->rankingsFilterMenu();
	
	echo '<table class="table rankings-table">';
	echo '<tr>';
	if(mconfig('rankings_show_place_number')) {
		echo '<td style="font-weight:bold;"></td>';
	}
	if($showPlayerCountry) echo '<td style="font-weight:bold;">'.lang('rankings_txt_33').'</td>';
	echo '<td style="font-weight:bold;">'.lang('rankings_txt_11').'</td>';
	echo '<td style="font-weight:bold;">'.lang('rankings_txt_10').'</td>';
	echo '<td style="font-weight:bold;">'.lang('rankings_txt_12').'</td>';
	if(mconfig('show_location')) echo '<td style="font-weight:bold;">'.lang('rankings_txt_34').'</td>';
	echo '</tr>';
	$i = 0;
	$source = $useDynamic ? $dynamicRows : $ranking_data;
	foreach($source as $rdata) {
		// Normalize cache row: handle wrong delimiter or single-cell arrays
		if(!is_array($rdata)) {
			if(is_string($rdata)) {
				$tmp = explode("¦", $rdata);
				if(count($tmp) < 2) $tmp = explode("Â¦", $rdata);
				if(count($tmp) >= 4) { $rdata = $tmp; } else { $i++; continue; }
			} else { $i++; continue; }
		} else if(count($rdata) == 1 && is_string($rdata[0])) {
			$tmp = explode("¦", $rdata[0]);
			if(count($tmp) < 2) $tmp = explode("Â¦", $rdata[0]);
			if(count($tmp) >= 4) { $rdata = $tmp; } else { $i++; continue; }
		}
		// Skip timestamp row when using cached data
		if(!$useDynamic && $i==0) { $i++; continue; }
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
			if($showPlayerCountry) echo '<td><img src="'.getCountryFlag((is_array($charactersCountry) && array_key_exists($rdata[0], $charactersCountry)) ? $charactersCountry[$rdata[0]] : 'default').'" /></td>';
			echo '<td>'.$characterIMG.'</td>';
			echo '<td>'.playerProfile($rdata[0]).$onlineStatus.'</td>';
			echo '<td>'.number_format($rdata[2]).'</td>';
			if(mconfig('show_location')) echo '<td>'.returnMapName($rdata[3]).'</td>';
			echo '</tr>';
		$i++;
	}
	echo '</table>';
	if(mconfig('rankings_show_date')) {
		echo '<div class="rankings-update-time">';
		$ts = time();
		if(!$useDynamic && is_array($ranking_data) && isset($ranking_data[0]) && is_array($ranking_data[0]) && isset($ranking_data[0][0])) {
			$ts = (int)$ranking_data[0][0];
		}
		echo ''.lang('rankings_txt_20',true).' ' . date("m/d/Y - h:i A", $ts);
		echo '</div>';
	}
	
} catch(Exception $ex) {
	message('error', $ex->getMessage());
}