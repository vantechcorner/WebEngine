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

if(!isLoggedIn()) redirect(1,'login');

echo '<div class="page-title"><span>'.lang('module_titles_txt_18',true).'</span></div>';

try {
	
	if(!mconfig('active')) throw new Exception(lang('error_47',true));
	
	$Character = new Character();
	$AccountCharacters = $Character->AccountCharacter(isset($_SESSION['userid'])?$_SESSION['userid']:$_SESSION['username']);
	if(!is_array($AccountCharacters)) throw new Exception(lang('error_46',true));
	
	if(isset($_POST['submit'])) {
		try {
			$Character->setUserid($_SESSION['userid']);
			$Character->setUsername($_SESSION['username']);
			$Character->setCharacter($_POST['character']);
			$Character->CharacterResetStats();
		} catch(Exception $ex) {
			message('error', $ex->getMessage());
		}
	}
	
	echo '<table class="table general-table-ui">';
		echo '<tr>';
			echo '<td></td>';
			echo '<td>'.lang('resetstats_txt_1',true).'</td>';
			echo '<td>'.lang('resetstats_txt_2',true).'</td>';
			echo '<td>'.lang('resetstats_txt_3',true).'</td>';
			echo '<td>'.lang('resetstats_txt_4',true).'</td>';
			echo '<td>'.lang('resetstats_txt_5',true).'</td>';
			echo '<td>'.lang('resetstats_txt_6',true).'</td>';
			echo '<td>'.lang('resetstats_txt_7',true).'</td>';
			echo '<td></td>';
		echo '</tr>';
		
		foreach($AccountCharacters as $thisCharacter) {
			$characterData = $Character->CharacterData($thisCharacter);
			if(!is_array($characterData)) continue;
			$characterData_l = array_change_key_case($characterData, CASE_LOWER);
			$rawClass = isset($characterData[_CLMN_CHR_CLASS_]) ? $characterData[_CLMN_CHR_CLASS_] : ($characterData['CharacterClassId'] ?? ($characterData_l['characterclassid'] ?? 0));
			$legacyClassNum = 0;
			if(is_numeric($rawClass)) { $legacyClassNum = (int)$rawClass; } else { if(function_exists('getOpenMUClassNumberById')) $legacyClassNum = getOpenMUClassNumberById($rawClass); }
			$characterIMG = $Character->GenerateCharacterClassAvatar($legacyClassNum);
			
			echo '<form action="" method="post">';
					echo '<input type="hidden" name="character" value="'.($characterData[_CLMN_CHR_NAME_] ?? ($characterData['Name'] ?? ($characterData_l['name'] ?? ''))).'"/>';
				echo '<tr>';
					echo '<td>'.$characterIMG.'</td>';
					echo '<td>'.($characterData[_CLMN_CHR_NAME_] ?? ($characterData['Name'] ?? ($characterData_l['name'] ?? ''))).'</td>';
					$exp = $characterData['Experience'] ?? ($characterData_l['experience'] ?? ($characterData[_CLMN_CHAR_EXPERIENCE_] ?? null));
					$dispLevel = (is_numeric($characterData[_CLMN_CHR_LVL_] ?? null) ? (int)$characterData[_CLMN_CHR_LVL_] : (is_numeric($exp) && function_exists('calculateOpenMULevel') ? calculateOpenMULevel($exp) : 0));
					echo '<td>'.$dispLevel.'</td>';
					$charId = $characterData['Id'] ?? ($characterData_l['id'] ?? null);
					$stats = function_exists('getOpenMUCharacterStats') && $charId ? getOpenMUCharacterStats($charId) : array();
					echo '<td>'.number_format($stats['Strength'] ?? ($characterData[_CLMN_CHR_STAT_STR_] ?? 0)).'</td>';
					echo '<td>'.number_format($stats['Agility'] ?? ($characterData[_CLMN_CHR_STAT_AGI_] ?? 0)).'</td>';
					echo '<td>'.number_format($stats['Vitality'] ?? ($characterData[_CLMN_CHR_STAT_VIT_] ?? 0)).'</td>';
					echo '<td>'.number_format($stats['Energy'] ?? ($characterData[_CLMN_CHR_STAT_ENE_] ?? 0)).'</td>';
					echo '<td>'.number_format($stats['Leadership'] ?? ($characterData[_CLMN_CHR_STAT_CMD_] ?? 0)).'</td>';
					echo '<td><button name="submit" value="submit" class="btn btn-primary">'.lang('resetstats_txt_8',true).'</button></td>';
				echo '</tr>';
			echo '</form>';
		}
	echo '</table>';
	
	echo '<div class="module-requirements text-center">';
		if(mconfig('zen_cost') > 0) echo '<p>'.langf('resetstats_txt_9', array(number_format(mconfig('zen_cost')))).'</p>';
	echo '</div>';
	
} catch(Exception $ex) {
	message('error', $ex->getMessage());
}