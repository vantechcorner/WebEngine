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

echo '<div class="page-title"><span>'.lang('module_titles_txt_16',true).'</span></div>';

try {
	
	if(!mconfig('active')) throw new Exception(lang('error_47',true));

    // Experimental feature notice for OpenMU unstick
    echo '<div class="alert alert-warning text-center" style="margin:10px 0;">Please contact MU administrator for support if your account is stuck.</div>';
	
	$Character = new Character();
	$AccountCharacters = $Character->AccountCharacter(isset($_SESSION['userid'])?$_SESSION['userid']:$_SESSION['username']);
	if(!is_array($AccountCharacters)) throw new Exception(lang('error_46',true));
	
	if(isset($_POST['submit'])) {
		try {
			$Character->setUserid($_SESSION['userid']);
			$Character->setUsername($_SESSION['username']);
			$Character->setCharacter($_POST['character']);
			$Character->CharacterUnstick();
		} catch(Exception $ex) {
			message('error', $ex->getMessage());
		}
	}
	
	echo '<table class="table general-table-ui">';
		echo '<tr>';
			echo '<td></td>';
			echo '<td>'.lang('unstickcharacter_txt_1',true).'</td>';
			echo '<td>'.lang('unstickcharacter_txt_2',true).'</td>';
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
					$charId = $characterData['Id'] ?? ($characterData_l['id'] ?? null);
					// Avoid DB money reads in OpenMU path to prevent UUID issues on some installs
					$dispMoney = '-';
					if(is_numeric($characterData[_CLMN_CHR_ZEN_] ?? null)) { $dispMoney = number_format((int)$characterData[_CLMN_CHR_ZEN_]); }
					echo '<td>'.($dispMoney).'</td>';
					echo '<td><button name="submit" value="submit" class="btn btn-primary">'.lang('unstickcharacter_txt_3',true).'</button></td>';
				echo '</tr>';
			echo '</form>';
		}
	echo '</table>';
	
	echo '<div class="module-requirements text-center">';
		if(mconfig('zen_cost') > 0) echo '<p>'.langf('unstickcharacter_txt_4', array(number_format(mconfig('zen_cost')))).'</p>';
	echo '</div>';
	
} catch(Exception $ex) {
	message('error', $ex->getMessage());
}