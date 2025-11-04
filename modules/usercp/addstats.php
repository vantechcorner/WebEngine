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

echo '<div class="page-title"><span>'.lang('module_titles_txt_25').'</span></div>';

try {
	
	if(!mconfig('active')) throw new Exception(lang('error_47',true));
	if(!isset($custom) || !is_array($custom)) $custom = array();
	if(!array_key_exists('character_cmd', $custom) || !is_array($custom['character_cmd'])) $custom['character_cmd'] = array(64,66,70);
	$maxStats = mconfig('addstats_max_stats');
	
	$Character = new Character();
	$AccountCharacters = $Character->AccountCharacter(isset($_SESSION['userid'])?$_SESSION['userid']:$_SESSION['username']);
	if(!is_array($AccountCharacters)) throw new Exception(lang('error_46',true));
	
	if(isset($_POST['submit'])) {
		try {
			$Character->setUserid($_SESSION['userid']);
			$Character->setUsername($_SESSION['username']);
			$Character->setCharacter($_POST['character']);
			if(isset($_POST['add_str']) && $_POST['add_str'] > 0) $Character->setStrength($_POST['add_str']);
			if(isset($_POST['add_agi']) && $_POST['add_agi'] > 0) $Character->setAgility($_POST['add_agi']);
			if(isset($_POST['add_vit']) && $_POST['add_vit'] > 0) $Character->setVitality($_POST['add_vit']);
			if(isset($_POST['add_ene']) && $_POST['add_ene'] > 0) $Character->setEnergy($_POST['add_ene']);
			if(isset($_POST['add_com']) && $_POST['add_com'] > 0) $Character->setCommand($_POST['add_com']);
			
			$Character->CharacterAddStats();
		} catch(Exception $ex) {
			message('error', $ex->getMessage());
		}
	}
	
	foreach($AccountCharacters as $thisCharacter) {
		$characterData = $Character->CharacterData($thisCharacter);
		if(!is_array($characterData)) continue;
		$characterData_l = array_change_key_case($characterData, CASE_LOWER);
		$rawClass = isset($characterData[_CLMN_CHR_CLASS_]) ? $characterData[_CLMN_CHR_CLASS_] : ($characterData['CharacterClassId'] ?? ($characterData_l['characterclassid'] ?? 0));
		$legacyClassNum = 0;
		if(is_numeric($rawClass)) { $legacyClassNum = (int)$rawClass; } else { if(function_exists('getOpenMUClassNumberById')) $legacyClassNum = getOpenMUClassNumberById($rawClass); }
		$characterIMG = $Character->GenerateCharacterClassAvatar($legacyClassNum);
		$charId = $characterData['Id'] ?? ($characterData_l['id'] ?? null);
		$charName = $characterData[_CLMN_CHR_NAME_] ?? ($characterData['Name'] ?? ($characterData_l['name'] ?? ''));
		$lvlupPoints = $characterData[_CLMN_CHR_LVLUP_POINT_] ?? ($characterData['LevelUpPoints'] ?? ($characterData_l['leveluppoints'] ?? 0));
		$stats = function_exists('getOpenMUCharacterStats') && $charId ? getOpenMUCharacterStats($charId) : array();
		
		echo '<div class="panel panel-addstats">';
			echo '<div class="panel-body">';
				echo '<div class="col-xs-3 nopadding text-center character-avatar">';
					echo $characterIMG;
				echo '</div>';
				echo '<div class="col-xs-9 nopadding">';
					echo '<div class="col-xs-12 nopadding character-name">';
					echo $charName;
					echo '</div>';
					echo '<div class="col-sm-10">';
						echo '<form class="form-horizontal" action="" method="post">';
							
					echo '<input type="hidden" name="character" value="'.$charName.'"/>';
							
							echo '<div class="form-group">';
								echo '<label for="inputStat" class="col-sm-5 control-label"></label>';
								echo '<div class="col-sm-7">';
						echo langf('addstats_txt_2', array(number_format($lvlupPoints)));
								echo '</div>';
							echo '</div>';
							echo '<div class="form-group">';
						echo '<label for="inputStat1" class="col-sm-5 control-label">'.lang('addstats_txt_3',true).' ('.number_format($stats['Strength'] ?? ($characterData[_CLMN_CHR_STAT_STR_] ?? 0)).')</label>';
								echo '<div class="col-sm-7">';
									echo '<input type="number" class="form-control" id="inputStat1" min="1" step="1" max="'.$maxStats.'" name="add_str" placeholder="0">';
								echo '</div>';
							echo '</div>';
							echo '<div class="form-group">';
						echo '<label for="inputStat2" class="col-sm-5 control-label">'.lang('addstats_txt_4',true).' ('.number_format($stats['Agility'] ?? ($characterData[_CLMN_CHR_STAT_AGI_] ?? 0)).')</label>';
								echo '<div class="col-sm-7">';
									echo '<input type="number" class="form-control" id="inputStat2" min="1" step="1" max="'.$maxStats.'" name="add_agi" placeholder="0">';
								echo '</div>';
							echo '</div>';
							echo '<div class="form-group">';
						echo '<label for="inputStat3" class="col-sm-5 control-label">'.lang('addstats_txt_5',true).' ('.number_format($stats['Vitality'] ?? ($characterData[_CLMN_CHR_STAT_VIT_] ?? 0)).')</label>';
								echo '<div class="col-sm-7">';
									echo '<input type="number" class="form-control" id="inputStat3" min="1" step="1" max="'.$maxStats.'" name="add_vit" placeholder="0">';
								echo '</div>';
							echo '</div>';
							echo '<div class="form-group">';
						echo '<label for="inputStat4" class="col-sm-5 control-label">'.lang('addstats_txt_6',true).' ('.number_format($stats['Energy'] ?? ($characterData[_CLMN_CHR_STAT_ENE_] ?? 0)).')</label>';
								echo '<div class="col-sm-7">';
									echo '<input type="number" class="form-control" id="inputStat4" min="1" step="1" max="'.$maxStats.'" name="add_ene" placeholder="0">';
								echo '</div>';
							echo '</div>';
							
						if(in_array($legacyClassNum, $custom['character_cmd'])) {
								echo '<div class="form-group">';
								echo '<label for="inputStat5" class="col-sm-5 control-label">'.lang('addstats_txt_7',true).' ('.number_format($stats['Leadership'] ?? ($characterData[_CLMN_CHR_STAT_CMD_] ?? 0)).')</label>';
									echo '<div class="col-sm-7">';
										echo '<input type="number" class="form-control" id="inputStat5" min="1" step="1" max="'.$maxStats.'" name="add_com" placeholder="0">';
									echo '</div>';
								echo '</div>';
							}
							
							echo '<div class="form-group">';
								echo '<div class="col-sm-12 text-right">';
									echo '<button name="submit" value="submit" class="btn btn-primary">'.lang('addstats_txt_8',true).'</button>';
								echo '</div>';
							echo '</div>';
						echo '</form>';
					echo '</div>';
					
				echo '</div>';
			echo '</div>';
		echo '</div>';
	}
	
	echo '<div class="module-requirements text-center">';
		if(mconfig('required_level') > 0) echo '<p>'.langf('addstats_txt_11', array(number_format(mconfig('required_level')))).'</p>';
		if(mconfig('required_master_level') > 0) echo '<p>'.langf('addstats_txt_10', array(number_format(mconfig('required_master_level')))).'</p>';
		if(mconfig('zen_cost') > 0) echo '<p>'.langf('addstats_txt_9', array(number_format(mconfig('zen_cost')))).'</p>';
		echo '<p>'.langf('addstats_txt_12', array(number_format(mconfig('max_stats')))).'</p>';
		if(mconfig('minimum_limit') > 0) echo '<p>'.langf('addstats_txt_13', array(number_format(mconfig('minimum_limit')))).'</p>';
	echo '</div>';
	
} catch(Exception $ex) {
	message('error', $ex->getMessage());
}