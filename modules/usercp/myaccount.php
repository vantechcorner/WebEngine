<?php
/**
 * WebEngine CMS
 * https://webenginecms.org/
 * 
 * @version 1.2.6
 * @author Lautaro Angelico <http://lautaroangelico.com/>
 * @copyright (c) 2013-2025 Lautaro Angelico, All Rights Reserved
 * 
 * Licensed under the MIT license
 * http://opensource.org/licenses/MIT
 */

if(!isLoggedIn()) redirect(1,'login');

echo '<div class="page-title"><span>'.lang('module_titles_txt_4').'</span></div>';

// module status
if(!mconfig('active')) throw new Exception(lang('error_47'));
	
// common class
$common = new common();

// Retrieve Account Information (OpenMU uses UUID account id)
$lookupId = isset($_SESSION['userid']) && check_value($_SESSION['userid']) ? $_SESSION['userid'] : $_SESSION['username'];
$accountInfo = $common->accountInformation($lookupId);
if(!is_array($accountInfo)) throw new Exception(lang('error_12'));
$accountInfo_l = array_change_key_case($accountInfo, CASE_LOWER);

# account online status (force login name so API check is accurate)
$loginForOnline = isset($accountInfo_l['loginname']) && check_value($accountInfo_l['loginname']) ? $accountInfo_l['loginname'] : (isset($_SESSION['username']) ? $_SESSION['username'] : $lookupId);
$__accOnline = $common->accountOnline($loginForOnline);
$onlineStatus = ($__accOnline ? '<span class="label label-success">'.lang('myaccount_txt_9').'</span>' : '<span class="label label-danger">'.lang('myaccount_txt_10').'</span>');

# account status
$accState = isset($accountInfo_l['state']) ? (int)$accountInfo_l['state'] : 0;
$accountStatus = ($accState == 1 ? '<span class="label label-danger">'.lang('myaccount_txt_8').'</span>' : '<span class="label label-default">'.lang('myaccount_txt_7').'</span>');

# characters info
$Character = new Character();
$AccountCharacters = $Character->AccountCharacter($_SESSION['userid']);

// Account Information
echo '<table class="table myaccount-table">';
	echo '<tr>';
		echo '<td>'.lang('myaccount_txt_1').'</td>';
		echo '<td>'.$accountStatus.'</td>';
	echo '</tr>';
	
    echo '<tr>';
        echo '<td>'.lang('myaccount_txt_2').'</td>';
        echo '<td>'.($accountInfo_l['loginname'] ?? '').'</td>';
    echo '</tr>';
	
    echo '<tr>';
        echo '<td>'.lang('myaccount_txt_3').'</td>';
        echo '<td>'.($accountInfo_l['email'] ?? '').' <a href="'.__BASE_URL__.'usercp/myemail/" class="btn btn-xs btn-primary pull-right">'.lang('myaccount_txt_6').'</a></td>';
    echo '</tr>';
	
	echo '<tr>';
		echo '<td>'.lang('myaccount_txt_4').'</td>';
		echo '<td>&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226; <a href="'.__BASE_URL__.'usercp/mypassword/" class="btn btn-xs btn-primary pull-right">'.lang('myaccount_txt_6').'</a></td>';
	echo '</tr>';
	
	echo '<tr>';
		echo '<td>'.lang('myaccount_txt_5').'</td>';
		echo '<td>'.$onlineStatus.'</td>';
	echo '</tr>';
	
	try {
		$creditSystem = new CreditSystem();
		$creditCofigList = $creditSystem->showConfigs();
		if(is_array($creditCofigList)) {
			foreach($creditCofigList as $myCredits) {
				if(!$myCredits['config_display']) continue;
				
				$creditSystem->setConfigId($myCredits['config_id']);
                switch($myCredits['config_user_col_id']) {
                    case 'userid':
                        $creditSystem->setIdentifier($accountInfo_l['id'] ?? null);
                        break;
                    case 'username':
                        $creditSystem->setIdentifier($accountInfo_l['loginname'] ?? null);
                        break;
                    case 'email':
                        $creditSystem->setIdentifier($accountInfo_l['email'] ?? null);
                        break;
					default:
						continue 2;
				}
				
				$configCredits = $creditSystem->getCredits();
				
				echo '<tr>';
					echo '<td>'.$myCredits['config_title'].'</td>';
					echo '<td>'.number_format($configCredits).'</td>';
				echo '</tr>';
			}
		}
	} catch(Exception $ex) {}
echo '</table>';

// Account Characters
echo '<div class="page-title"><span>'.lang('myaccount_txt_15').'</span></div>';
if(is_array($AccountCharacters)) {
    $onlineCharacters = loadCache('online_characters.cache') ? loadCache('online_characters.cache') : array();
	echo '<div class="row text-center">';
        foreach($AccountCharacters as $characterName) {
            $characterData = $Character->CharacterData($characterName);
            if(!is_array($characterData)) continue;
            $characterData_l = array_change_key_case($characterData, CASE_LOWER);
			
			// Compute master level separately (OpenMU stores master exp on Character)
			$masterLevel = 0;
			if(function_exists('calculateOpenMUMasterLevel') && method_exists($Character, 'getMasterLevelInfo')) {
				$characterMLData = $Character->getMasterLevelInfo($characterName);
				if(is_array($characterMLData) && isset($characterMLData[_CLMN_ML_LVL_])) {
					$masterLevel = (int)$characterMLData[_CLMN_ML_LVL_];
				}
			}
			
            // Resolve legacy class number for avatar
            $rawClass = isset($characterData[_CLMN_CHR_CLASS_]) ? $characterData[_CLMN_CHR_CLASS_] : ($characterData['CharacterClassId'] ?? ($characterData_l['characterclassid'] ?? 0));
            $legacyClassNum = 0;
            // If raw is GUID, resolve via config table; if number but not in custom map, still resolve by GUID using CharacterClassId
            if(is_numeric($rawClass)) {
                $legacyClassNum = (int)$rawClass;
                global $custom;
                if(!(is_array($custom) && isset($custom['character_class']) && array_key_exists($legacyClassNum, $custom['character_class']))) {
                    $guid = $characterData['CharacterClassId'] ?? ($characterData_l['characterclassid'] ?? null);
                    if($guid && function_exists('getOpenMUClassNumberById')) $legacyClassNum = getOpenMUClassNumberById($guid);
                }
            } else {
                if(function_exists('getOpenMUClassNumberById')) $legacyClassNum = getOpenMUClassNumberById($rawClass);
            }
            $characterClassAvatar = getPlayerClassAvatar($legacyClassNum, false);
            // If account is online (API), mark character online; otherwise fallback to cache
            $characterOnlineStatus = ($__accOnline === true)
                ? '<img src="'.__PATH_ONLINE_STATUS__.'" class="online-status-indicator"/>'
                : (in_array($characterName, $onlineCharacters)
                    ? '<img src="'.__PATH_ONLINE_STATUS__.'" class="online-status-indicator"/>'
                    : '<img src="'.__PATH_OFFLINE_STATUS__.'" class="online-status-indicator"/>'
                );
			echo '<div class="col-xs-3">';
				echo '<div class="myaccount-character-name">'.playerProfile($characterName).$characterOnlineStatus.'</div>';
				echo '<div class="myaccount-character-block">';
					echo '<a href="'.playerProfile($characterName, true).'" target="_blank">';
						echo '<img src="'.$characterClassAvatar.'" />';
					echo '</a>';
				echo '</div>';
            $mapId = isset($characterData[_CLMN_CHR_MAP_]) ? $characterData[_CLMN_CHR_MAP_] : ($characterData['CurrentMapId'] ?? ($characterData_l['currentmapid'] ?? 0));
            $posX = isset($characterData[_CLMN_CHR_MAP_X_]) ? $characterData[_CLMN_CHR_MAP_X_] : ($characterData['PositionX'] ?? ($characterData_l['positionx'] ?? 0));
            $posY = isset($characterData[_CLMN_CHR_MAP_Y_]) ? $characterData[_CLMN_CHR_MAP_Y_] : ($characterData['PositionY'] ?? ($characterData_l['positiony'] ?? 0));
            $mapLabel = (is_numeric($mapId) ? returnMapName((int)$mapId) : (function_exists('getOpenMUMapName') ? getOpenMUMapName($mapId) : 'Unknown'));
            echo '<div class="myaccount-character-block-location">'.$mapLabel.'<br />'.$posX.', '.$posY.'</div>';
                // Display calculated level if OpenMU (experience based) or fallback to existing value
                $dispLevel = isset($characterData[_CLMN_CHR_LVL_]) && is_numeric($characterData[_CLMN_CHR_LVL_]) ? (int)$characterData[_CLMN_CHR_LVL_] : null;
                if(!is_numeric($dispLevel)) {
				$exp = $characterData['Experience'] ?? ($characterData_l['experience'] ?? ($characterData[_CLMN_CHAR_EXPERIENCE_] ?? null));
                    if(is_numeric($exp)) {
                        if(function_exists('calculateOpenMULevel')) {
                            $dispLevel = calculateOpenMULevel($exp);
                        }
                    }
                }
				$combinedLevel = (is_numeric($dispLevel)?$dispLevel:0) + $masterLevel;
				echo '<span class="myaccount-character-block-level">'.$combinedLevel.'</span>';
			echo '</div>';
		}
	echo '</div>';
} else {
	message('warning', lang('error_46'));
}

// Connection History (IGCN)
if(defined('_TBL_CH_')) {
	echo '<div class="page-title"><span>'.lang('myaccount_txt_16').'</span></div>';
	$me = Connection::Database('Me_MuOnline');
	$connectionHistory = $me->query_fetch("SELECT TOP 10 * FROM "._TBL_CH_." WHERE "._CLMN_CH_ACCID_." = ? ORDER BY "._CLMN_CH_ID_." DESC", array($_SESSION['username']));
	if(is_array($connectionHistory)) {
		echo '<table class="table table-condensed general-table-ui">';
			echo '<tr>';
				echo '<td>'.lang('myaccount_txt_13').'</td>';
				echo '<td>'.lang('myaccount_txt_17').'</td>';
				echo '<td>'.lang('myaccount_txt_18').'</td>';
				echo '<td>'.lang('myaccount_txt_19').'</td>';
			echo '</tr>';
			foreach($connectionHistory as $row) {
				echo '<tr>';
					echo '<td>'.$row[_CLMN_CH_DATE_].'</td>';
					echo '<td>'.$row[_CLMN_CH_SRVNM_].'</td>';
					echo '<td>'.$row[_CLMN_CH_IP_].'</td>';
					echo '<td>'.$row[_CLMN_CH_STATE_].'</td>';
				echo '</tr>';
			}
		echo '</table>';
	}
}