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

echo '<div class="page-title"><span>'.lang('module_titles_txt_12',true).'</span></div>';

try {
	
	if(!mconfig('active')) throw new Exception(lang('error_47',true));

    // Experimental feature notice for OpenMU reset
    echo '<div class="alert alert-warning text-center" style="margin:10px 0;">Experimental Feature. Please use in-game command <strong>/reset</strong> to avoid errors.</div>';
	
	$Character = new Character();
	$AccountCharacters = $Character->AccountCharacter($_SESSION['userid']);
	if(!is_array($AccountCharacters)) throw new Exception(lang('error_46',true));
	
	if(isset($_POST['submit'])) {
		try {
			$Character->setUserid($_SESSION['userid']);
			$Character->setUsername($_SESSION['username']);
			$Character->setCharacter($_POST['character']);
			$Character->CharacterReset();
		} catch(Exception $ex) {
			message('error', $ex->getMessage());
		}
	}
	
	echo '<table class="table general-table-ui">';
		echo '<tr>';
			echo '<td></td>';
			echo '<td>'.lang('resetcharacter_txt_1',true).'</td>';
			echo '<td>'.lang('resetcharacter_txt_2',true).'</td>';
			echo '<td>'.lang('resetcharacter_txt_3',true).'</td>';
			echo '<td>'.lang('resetcharacter_txt_4',true).'</td>';
			echo '<td></td>';
		echo '</tr>';
		
		foreach($AccountCharacters as $thisCharacter) {
			$characterData = $Character->CharacterData($thisCharacter);
			if(!is_array($characterData)) continue;
			$characterData_l = array_change_key_case($characterData, CASE_LOWER);
			// resolve legacy class id for avatar
			$rawClass = isset($characterData[_CLMN_CHR_CLASS_]) ? $characterData[_CLMN_CHR_CLASS_] : ($characterData['CharacterClassId'] ?? ($characterData_l['characterclassid'] ?? 0));
			$legacyClassNum = 0;
			if(is_numeric($rawClass)) {
				$legacyClassNum = (int)$rawClass;
			} else {
				if(function_exists('getOpenMUClassNumberById')) $legacyClassNum = getOpenMUClassNumberById($rawClass);
			}
			$characterIMG = $Character->GenerateCharacterClassAvatar($legacyClassNum);
			// character name
			$charName = isset($characterData[_CLMN_CHR_NAME_]) ? $characterData[_CLMN_CHR_NAME_] : ($characterData['Name'] ?? ($characterData_l['name'] ?? ''));
			// display level (calculate from Experience for OpenMU)
			$dispLevel = isset($characterData[_CLMN_CHR_LVL_]) && is_numeric($characterData[_CLMN_CHR_LVL_]) ? (int)$characterData[_CLMN_CHR_LVL_] : null;
			if(!is_numeric($dispLevel)) {
				$exp = $characterData['Experience'] ?? ($characterData_l['experience'] ?? ($characterData[_CLMN_CHAR_EXPERIENCE_] ?? null));
				if(is_numeric($exp)) {
					if(function_exists('calculateOpenMULevelFromDbOrApprox')) {
						$dispLevel = calculateOpenMULevelFromDbOrApprox($exp);
					} elseif(function_exists('calculateOpenMULevel')) {
						$dispLevel = calculateOpenMULevel($exp);
					}
				}
			}
			if(!is_numeric($dispLevel)) $dispLevel = 0;
			// money (warehouse money linked via ItemStorage)
			$charId = $characterData['Id'] ?? ($characterData_l['id'] ?? null);
			$dispMoney = is_numeric($characterData[_CLMN_CHR_ZEN_] ?? null) ? (int)$characterData[_CLMN_CHR_ZEN_] : (function_exists('getOpenMUCharacterMoney') && $charId ? getOpenMUCharacterMoney($charId) : 0);
			// resets (calculated helper)
			$dispResets = function_exists('getOpenMUCharacterResets') && $charId ? getOpenMUCharacterResets($charId) : (int)($characterData['Resets'] ?? ($characterData_l['resets'] ?? 0));
			
			echo '<form action="" method="post">';
				echo '<input type="hidden" name="character" value="'.$charName.'"/>';
				echo '<tr>';
					echo '<td>'.$characterIMG.'</td>';
					echo '<td>'.$charName.'</td>';
					echo '<td>'.$dispLevel.'</td>';
					echo '<td>'.number_format($dispMoney).'</td>';
					echo '<td>'.number_format($dispResets).'</td>';
					echo '<td><button name="submit" value="submit" class="btn btn-primary">'.lang('resetcharacter_txt_5',true).'</button></td>';
				echo '</tr>';
			echo '</form>';
		}
	echo '</table>';
	
	echo '<div class="module-requirements text-center">';
		if(mconfig('required_level') >= 1) echo '<p>'.langf('resetcharacter_txt_6', array(mconfig('required_level'))).'</p>';
		if(mconfig('zen_cost') >= 1) echo '<p>'.langf('resetcharacter_txt_7', array(number_format(mconfig('zen_cost')))).'</p>';
		if(mconfig('credit_cost') >= 1) echo '<p>'.langf('resetcharacter_txt_9', array(number_format(mconfig('credit_cost')))).'</p>';
		if(mconfig('maximum_resets') >= 1) echo '<p>'.langf('resetcharacter_txt_10', array(number_format(mconfig('maximum_resets')))).'</p>';
		if(mconfig('credit_reward') >= 1) echo '<p>'.langf('resetcharacter_txt_8', array(number_format(mconfig('credit_reward')))).'</p>';
		if(mconfig('clear_inventory') == 1) echo '<p>'.lang('resetcharacter_txt_11').'</p>';
	echo '</div>';
	
} catch(Exception $ex) {
	message('error', $ex->getMessage());
}