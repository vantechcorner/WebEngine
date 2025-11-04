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

if(isset($_GET['name'])) {
	try {
		if(!Validator::AlphaNumeric($_GET['name'])) throw new Exception("Invalid character name.");
		$Character = new Character();
		if(!$Character->CharacterExists($_GET['name'])) throw new Exception("Character does not exist.");
		
		if(isset($_POST['characteredit_submit'])) {
			try {
				$isOpenMU = defined('_TBL_STAT_ATTRIBUTE_');
				if($isOpenMU) {
					if($_POST['characteredit_name'] != $_GET['name']) throw new Exception("Invalid character name.");
					if(!isset($_POST['characteredit_lvlpoints']) || !Validator::UnsignedNumber($_POST['characteredit_lvlpoints'])) throw new Exception("Level-Up Points must be numeric.");
					$ok = $dB->query("UPDATE "._TBL_CHR_." SET "._CLMN_CHR_LVLUP_POINT_." = :lvlpoints WHERE "._CLMN_CHR_NAME_." = :name", array('lvlpoints'=>(int)$_POST['characteredit_lvlpoints'], 'name'=>$_POST['characteredit_name']));
					if(!$ok) throw new Exception("Could not update character data.");
					message('success', 'Saved (OpenMU): Only Level-Up Points can be edited here.');
				} else {
					if($_POST['characteredit_name'] != $_GET['name']) throw new Exception("Invalid character name.");
					if(!isset($_POST['characteredit_account'])) throw new Exception("Invalid account name.");
					if(!Validator::UnsignedNumber($_POST['characteredit_class'])) throw new Exception("All the entered values must be numeric.");
					if(!Validator::UnsignedNumber($_POST['characteredit_level'])) throw new Exception("All the entered values must be numeric.");
					if(isset($_POST['characteredit_resets'])) if(!Validator::UnsignedNumber($_POST['characteredit_resets'])) throw new Exception("All the entered values must be numeric.");
					if(isset($_POST['characteredit_gresets'])) if(!Validator::UnsignedNumber($_POST['characteredit_gresets'])) throw new Exception("All the entered values must be numeric.");
					if(!Validator::UnsignedNumber($_POST['characteredit_zen'])) throw new Exception("All the entered values must be numeric.");
					if(!Validator::UnsignedNumber($_POST['characteredit_lvlpoints'])) throw new Exception("All the entered values must be numeric.");
					if(!Validator::UnsignedNumber($_POST['characteredit_pklevel'])) throw new Exception("All the entered values must be numeric.");
					if(!Validator::UnsignedNumber($_POST['characteredit_str'])) throw new Exception("All the entered values must be numeric.");
					if(!Validator::UnsignedNumber($_POST['characteredit_agi'])) throw new Exception("All the entered values must be numeric.");
					if(!Validator::UnsignedNumber($_POST['characteredit_vit'])) throw new Exception("All the entered values must be numeric.");
					if(!Validator::UnsignedNumber($_POST['characteredit_ene'])) throw new Exception("All the entered values must be numeric.");
					if(!Validator::UnsignedNumber($_POST['characteredit_cmd'])) throw new Exception("All the entered values must be numeric.");
					if(!Validator::UnsignedNumber($_POST['characteredit_mlevel'])) throw new Exception("All the entered values must be numeric.");
					if(!Validator::UnsignedNumber($_POST['characteredit_mlexp'])) throw new Exception("All the entered values must be numeric.");
					if(isset($_POST['characteredit_mlnextexp'])) if(!Validator::UnsignedNumber($_POST['characteredit_mlnextexp'])) throw new Exception("All the entered values must be numeric.");
					if(!Validator::UnsignedNumber($_POST['characteredit_mlpoint'])) throw new Exception("All the entered values must be numeric.");
					// check online
					if($common->accountOnline($_POST['characteredit_account'])) throw new Exception("The account is currently online.");
					// update database
					$updateData = array(
						'name' => $_POST['characteredit_name'],
						'class' => $_POST['characteredit_class'],
						'level' => $_POST['characteredit_level'],
						'zen' => $_POST['characteredit_zen'],
						'lvlpoints' => $_POST['characteredit_lvlpoints'],
						'pklevel' => $_POST['characteredit_pklevel'],
						'str' => $_POST['characteredit_str'],
						'agi' => $_POST['characteredit_agi'],
						'vit' => $_POST['characteredit_vit'],
						'ene' => $_POST['characteredit_ene'],
						'cmd' => $_POST['characteredit_cmd']
					);
					if(isset($_POST['characteredit_resets'])) { $updateData['resets'] = $_POST['characteredit_resets']; }
					if(isset($_POST['characteredit_gresets'])) { $updateData['gresets'] = $_POST['characteredit_gresets']; }
					$query = "UPDATE "._TBL_CHR_." SET ";
						$query .= _CLMN_CHR_CLASS_ . " = :class,";
						$query .= _CLMN_CHR_LVL_ . " = :level,";
						if(check_value($updateData['resets'])) $query .= _CLMN_CHR_RSTS_ . " = :resets,";
						if(check_value($updateData['gresets'])) $query .= _CLMN_CHR_GRSTS_ . " = :gresets,";
						$query .= _CLMN_CHR_ZEN_ . " = :zen,";
						$query .= _CLMN_CHR_LVLUP_POINT_ . " = :lvlpoints,";
						$query .= _CLMN_CHR_PK_LEVEL_ . " = :pklevel,";
						$query .= _CLMN_CHR_STAT_STR_ . " = :str,";
						$query .= _CLMN_CHR_STAT_AGI_ . " = :agi,";
						$query .= _CLMN_CHR_STAT_VIT_ . " = :vit,";
						$query .= _CLMN_CHR_STAT_ENE_ . " = :ene,";
						$query .= _CLMN_CHR_STAT_CMD_ . " = :cmd";
						$query .= " WHERE "._CLMN_CHR_NAME_." = :name";
					$updateCharacter = $dB->query($query, $updateData);
					if(!$updateCharacter) throw new Exception("Could not update character data.");
					// Update master level info
					$updateMlData = array(
						'name' => $_POST['characteredit_name'],
						'level' => $_POST['characteredit_mlevel'],
						'exp' => $_POST['characteredit_mlexp'],
						'points' => $_POST['characteredit_mlpoint']
					);
					if(isset($_POST['characteredit_mlnextexp'])) { $updateMlData['nextexp'] = $_POST['characteredit_mlnextexp']; }
					$mlQuery = "UPDATE "._TBL_MASTERLVL_." SET ";
						$mlQuery .= _CLMN_ML_LVL_ . " = :level,";
						$mlQuery .= _CLMN_ML_EXP_ . " = :exp,";
						if(check_value($updateMlData['nextexp'])) $mlQuery .= _CLMN_ML_NEXP_ . " = :nextexp,";
						$mlQuery .= _CLMN_ML_POINT_ . " = :points";
						$mlQuery .= " WHERE "._CLMN_ML_NAME_." = :name";
					$updateMlCharacter = $dB->query($mlQuery, $updateMlData);
					if(!$updateCharacter) throw new Exception("Master level data could not be updated.");
				}
				
			} catch(Exception $ex) {
				message('error', $ex->getMessage());
			}
		}
		
		$charData = $Character->CharacterData($_GET['name']);
		if(!$charData) throw new Exception("Could not retrieve character information (invalid character).");
		
		// OpenMU-aware normalization and derived fields
		$cd = is_array($charData) ? array_change_key_case($charData, CASE_LOWER) : array();
		$isOpenMU = defined('_TBL_STAT_ATTRIBUTE_');
		$name = $cd['name'] ?? $_GET['name'];
		$accountId = $cd['accountid'] ?? ($charData[_CLMN_CHR_ACCID_] ?? '');
		$characterId = $cd['id'] ?? null;
		// Class (resolve legacy numeric from GUID if needed)
		$currentClassLegacy = 0;
		if(isset($charData[_CLMN_CHR_CLASS_]) && is_numeric($charData[_CLMN_CHR_CLASS_])) {
			$currentClassLegacy = (int)$charData[_CLMN_CHR_CLASS_];
		} elseif(isset($cd['characterclassid']) && function_exists('getOpenMUClassNumberById')) {
			$currentClassLegacy = (int)getOpenMUClassNumberById($cd['characterclassid']);
		}
		// Level (computed from Experience in OpenMU)
		$level = isset($charData[_CLMN_CHR_LVL_]) ? (int)$charData[_CLMN_CHR_LVL_] : (function_exists('calculateOpenMULevel') ? (int)calculateOpenMULevel($cd['experience'] ?? 0) : 0);
		// Level-Up Points
		$levelUpPoints = isset($charData[_CLMN_CHR_LVLUP_POINT_]) ? (int)$charData[_CLMN_CHR_LVLUP_POINT_] : (int)($cd['leveluppoints'] ?? 0);
		// Money (from ItemStorage if not present)
		$money = isset($charData[_CLMN_CHR_ZEN_]) ? (int)$charData[_CLMN_CHR_ZEN_] : ( ($characterId && function_exists('getOpenMUCharacterMoney')) ? (int)getOpenMUCharacterMoney($characterId) : 0 );
		// PK Level (computed from PlayerKillCount if available)
		$pkCount = isset($cd['playerkillcount']) ? (int)$cd['playerkillcount'] : 0;
		$pkLevelDisplay = function_exists('returnPkLevel') ? returnPkLevel($pkCount) : $pkCount;
		// Stats (read-only in OpenMU)
		$stats = ($characterId && function_exists('getOpenMUCharacterStats')) ? getOpenMUCharacterStats($characterId) : array('Strength'=>0,'Agility'=>0,'Vitality'=>0,'Energy'=>0,'Leadership'=>0);
		
		echo '<h1 class="page-header">Edit Character: <small>'.$name.'</small></h1>';
		
		echo '<form role="form" method="post">';
		echo '<input type="hidden" name="characteredit_name" value="'.$name.'"/>';
		echo '<input type="hidden" name="characteredit_account" value="'.$accountId.'"/>';
		
		echo '<div class="row">';
			echo '<div class="col-md-6">';
				
				// COMMON
				echo '<div class="panel panel-primary">';
				echo '<div class="panel-heading">Common</div>';
				echo '<div class="panel-body">';
					echo '<table class="table table-no-border table-hover">';
						echo '<tr>';
							echo '<th>Account:</th>';
						echo '<td><a href="'.admincp_base("accountinfo&id=".$accountId).'">'.$accountId.'</a></td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th>Class:</th>';
							echo '<td>';
								echo '<select class="form-control" name="characteredit_class">';
									foreach($custom['character_class'] as $classID => $thisClass) {
									if($classID == $currentClassLegacy) {
											echo '<option value="'.$classID.'" selected="selected">'.$thisClass[0].' ('.$thisClass[1].')</option>';
										} else {
											echo '<option value="'.$classID.'">'.$thisClass[0].' ('.$thisClass[1].')</option>';
										}
									}
								echo '</select>';
							echo '</td>';
						echo '</tr>';
						echo '<tr>';
						echo '<th>Level:</th>';
						echo '<td><input class="form-control" type="number" name="characteredit_level" value="'.$level.'" '.($isOpenMU?'disabled':'').'/>' . ($isOpenMU?'<p class="help-block">Computed from Experience (OpenMU).</p>':'') . '</td>';
						echo '</tr>';
						
						if(defined('_CLMN_CHR_RSTS_') && isset($charData[_CLMN_CHR_RSTS_])) {
							echo '<tr>';
								echo '<th>Resets:</th>';
								echo '<td><input class="form-control" type="number" name="characteredit_resets" value="'.$charData[_CLMN_CHR_RSTS_].'"/></td>';
								echo '</tr>';
						} elseif($isOpenMU) {
							$computedResets = ($characterId && function_exists('getOpenMUCharacterResets')) ? (int)getOpenMUCharacterResets($characterId) : 0;
							echo '<tr>';
								echo '<th>Resets:</th>';
								echo '<td><input class="form-control" type="number" value="'.$computedResets.'" disabled/><p class="help-block">Computed from Level (OpenMU).</p></td>';
								echo '</tr>';
						}
						
						if(defined('_CLMN_CHR_GRSTS_') && isset($charData[_CLMN_CHR_GRSTS_])) {
							echo '<tr>';
								echo '<th>Grand Resets:</th>';
								echo '<td><input class="form-control" type="number" name="characteredit_gresets" value="'.$charData[_CLMN_CHR_GRSTS_].'"/></td>';
								echo '</tr>';
						} elseif($isOpenMU) {
							$computedGResets = ($characterId && function_exists('getOpenMUCharacterGrandResets')) ? (int)getOpenMUCharacterGrandResets($characterId) : 0;
							echo '<tr>';
								echo '<th>Grand Resets:</th>';
								echo '<td><input class="form-control" type="number" value="'.$computedGResets.'" disabled/><p class="help-block">Computed from Master Level (OpenMU).</p></td>';
								echo '</tr>';
						}
						
						echo '<tr>';
						echo '<th>Money:</th>';
						echo '<td><input class="form-control" type="number" name="characteredit_zen" value="'.$money.'" '.($isOpenMU?'disabled':'').'/>' . ($isOpenMU?'<p class="help-block">From ItemStorage (OpenMU).</p>':'') . '</td>';
						echo '</tr>';
						echo '<tr>';
						echo '<th>Level-Up Points:</th>';
						echo '<td><input class="form-control" type="number" name="characteredit_lvlpoints" value="'.$levelUpPoints.'"/></td>';
						echo '</tr>';
						echo '<tr>';
						echo '<th>PK Level:</th>';
						echo '<td>'.(defined('_CLMN_CHR_PK_LEVEL_') ? '<input class="form-control" type="number" name="characteredit_pklevel" value="'.$charData[_CLMN_CHR_PK_LEVEL_].'"/>' : '<input class="form-control" type="text" value="'.$pkLevelDisplay.'" disabled/><p class="help-block">Computed from PlayerKillCount (OpenMU), not directly editable.</p>').'</td>';
						echo '</tr>';
					echo '</table>';
				echo '</div>';
				echo '</div>';
				
			echo '</div>';
			echo '<div class="col-md-6">';
			
				// STATS
				echo '<div class="panel panel-default">';
				echo '<div class="panel-heading">Stats</div>';
				echo '<div class="panel-body">';
					echo '<table class="table table-no-border table-hover">';
						echo '<tr>';
							echo '<th>Strength:</th>';
						echo '<td><input class="form-control" type="number" name="characteredit_str" value="'.($stats['Strength'] ?? ($charData[_CLMN_CHR_STAT_STR_] ?? 0)).'" '.($isOpenMU?'disabled':'').'/>' . ($isOpenMU?'<p class="help-block">Edit via in-game or OpenMU tools.</p>':'') . '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th>Dexterity:</th>';
						echo '<td><input class="form-control" type="number" name="characteredit_agi" value="'.($stats['Agility'] ?? ($charData[_CLMN_CHR_STAT_AGI_] ?? 0)).'" '.($isOpenMU?'disabled':'').'/>' . ($isOpenMU?'<p class="help-block">Edit via in-game or OpenMU tools.</p>':'') . '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th>Vitality:</th>';
						echo '<td><input class="form-control" type="number" name="characteredit_vit" value="'.($stats['Vitality'] ?? ($charData[_CLMN_CHR_STAT_VIT_] ?? 0)).'" '.($isOpenMU?'disabled':'').'/>' . ($isOpenMU?'<p class="help-block">Edit via in-game or OpenMU tools.</p>':'') . '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th>Energy:</th>';
						echo '<td><input class="form-control" type="number" name="characteredit_ene" value="'.($stats['Energy'] ?? ($charData[_CLMN_CHR_STAT_ENE_] ?? 0)).'" '.($isOpenMU?'disabled':'').'/>' . ($isOpenMU?'<p class="help-block">Edit via in-game or OpenMU tools.</p>':'') . '</td>';
						echo '</tr>';
						echo '<tr>';
							echo '<th>Command:</th>';
						echo '<td><input class="form-control" type="number" name="characteredit_cmd" value="'.($stats['Leadership'] ?? ($charData[_CLMN_CHR_STAT_CMD_] ?? 0)).'" '.($isOpenMU?'disabled':'').'/>' . ($isOpenMU?'<p class="help-block">Edit via in-game or OpenMU tools.</p>':'') . '</td>';
						echo '</tr>';
					echo '</table>';
				echo '</div>';
				echo '</div>';
				
				// MASTER LEVEL
					if(defined('_TBL_MASTERLVL_')) {
						// For OpenMU, derive values using helper if placeholders/constants missing
						$mLinfo = $dB->query_fetch_single("SELECT * FROM "._TBL_MASTERLVL_." WHERE "._CLMN_ML_NAME_." = ?", array($name));
						if($isOpenMU) {
							$ml = array('level'=>0,'points'=>0,'exp'=>0,'nexp'=>null);
							if(function_exists('calculateOpenMUMasterLevel')) {
								$mx = $cd['masterexperience'] ?? 0;
								$ml['level'] = (int)calculateOpenMUMasterLevel((int)$mx);
							}
							$ml['points'] = (int)($cd['masterleveluppoints'] ?? 0);
						}
					echo '<div class="panel panel-default">';
					echo '<div class="panel-heading">Master Level</div>';
					echo '<div class="panel-body">';
						if(is_array($mLinfo) || $isOpenMU) {
							echo '<table class="table table-no-border table-hover">';
								echo '<tr>';
									echo '<th>Master Level:</th>';
									echo '<td><input class="form-control" type="number" name="characteredit_mlevel" value="'.($isOpenMU ? ($ml['level'] ?? 0) : $mLinfo[_CLMN_ML_LVL_]).'" '.($isOpenMU?'disabled':'').'/>' . ($isOpenMU?'<p class="help-block">Computed from MasterExperience (OpenMU).</p>':'') . '</td>';
								echo '</tr>';
								echo '<tr>';
									echo '<th>Experience:</th>';
									echo '<td><input class="form-control" type="number" name="characteredit_mlexp" value="'.($isOpenMU ? (int)($cd['masterexperience'] ?? 0) : $mLinfo[_CLMN_ML_EXP_]).'" '.($isOpenMU?'disabled':'').'/></td>';
								echo '</tr>';
								if(!$isOpenMU && defined('_CLMN_ML_NEXP_')) {
									echo '<tr>';
										echo '<th>Next Experience:</th>';
										echo '<td><input class="form-control" type="number" name="characteredit_mlnextexp" value="'.$mLinfo[_CLMN_ML_NEXP_].'"/></td>';
									echo '</tr>';
								}
								echo '<tr>';
									echo '<th>Points:</th>';
									echo '<td><input class="form-control" type="number" name="characteredit_mlpoint" value="'.($isOpenMU ? ($ml['points'] ?? 0) : $mLinfo[_CLMN_ML_POINT_]).'" '.($isOpenMU?'disabled':'').'/></td>';
								echo '</tr>';
							echo '</table>';
						} else {
							message('warning', 'Could not retrieve Master Level information.', ' ');
						}
					echo '</div>';
					echo '</div>';
				}
				
			echo '</div>';
		echo '</div>';
		
		echo '<div class="row">';
			echo '<div class="col-md-12">';
				echo '<button type="submit" class="btn btn-large btn-block btn-success" name="characteredit_submit" value="ok">Save Changes</button>';
			echo '</div>';
		echo '</div>';
		
		echo '</form>';
	} catch(Exception $ex) {
		echo '<h1 class="page-header">Account Information</h1>';
		message('error', $ex->getMessage());
	}
	
} else {
	echo '<h1 class="page-header">Account Information</h1>';
	message('error', 'Please provide a valid user id.');
}
?>