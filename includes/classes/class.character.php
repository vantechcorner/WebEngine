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

class Character {
	
	protected $_classData;
	
	protected $_userid;
	protected $_username;
	protected $_character;
	
	protected $_unstickMap = 0;
	protected $_unstickCoordX = 125;
	protected $_unstickCoordY = 125;
	
	protected $_clearPkLevel = 3;
	
	protected $_skilEnhanceTreeLevel = 800;
	
	protected $_strength = 0;
	protected $_agility = 0;
	protected $_vitality = 0;
	protected $_energy = 0;
	protected $_command = 0;
	
	protected $muonline;
	protected $common;
	
	function __construct() {
		
		// load databases
		$this->muonline = Connection::Database('MuOnline');
		
		// common
		$this->common = new common();
		
		// class data
		$classData = custom('character_class');
		if(!is_array($classData)) throw new Exception(lang('error_108'));
		$this->_classData = $classData;
		
	}
	
	public function setUserid($userid) {
        // Allow OpenMU UUID or legacy numeric id
        if(!(is_string($userid) && preg_match('/^[0-9a-fA-F\-]{36}$/', $userid))) {
		if(!Validator::UnsignedNumber($userid)) throw new Exception(lang('error_111'));
        }
		$this->_userid = $userid;
	}
	
	public function setUsername($username) {
        // In OpenMU flows we pass login name here
		if(!Validator::UsernameLength($username)) throw new Exception(lang('error_112'));
		$this->_username = $username;
	}
	
	public function setCharacter($character) {
		$this->_character = $character;
	}
	
	public function setStrength($value) {
		if(!Validator::UnsignedNumber($value)) throw new Exception(lang('error_122'));
		$this->_strength = $value;
	}
	
	public function setAgility($value) {
		if(!Validator::UnsignedNumber($value)) throw new Exception(lang('error_122'));
		$this->_agility = $value;
	}
	
	public function setVitality($value) {
		if(!Validator::UnsignedNumber($value)) throw new Exception(lang('error_122'));
		$this->_vitality = $value;
	}
	
	public function setEnergy($value) {
		if(!Validator::UnsignedNumber($value)) throw new Exception(lang('error_122'));
		$this->_energy = $value;
	}
	
	public function setCommand($value) {
		if(!Validator::UnsignedNumber($value)) throw new Exception(lang('error_122'));
		$this->_command = $value;
	}
	
	public function CharacterReset() {
		// filters
		if(!check_value($this->_username)) throw new Exception(lang('error_21'));
		if(!check_value($this->_character)) throw new Exception(lang('error_21'));
		if(!check_value($this->_userid)) throw new Exception(lang('error_21'));
		if(!$this->CharacterExists($this->_character)) throw new Exception(lang('error_32'));
		if(!$this->CharacterBelongsToAccount($this->_character, $this->_username)) throw new Exception(lang('error_32'));
		
		// check online status
		$Account = new Account();
		if($Account->accountOnline($this->_username)) throw new Exception(lang('error_14'));
		
		// character data
		$characterData = $this->CharacterData($this->_character);
		
		// Experimental notice
		message('warning', 'Experimental Feature. Please use in-game command /reset to avoid errors.');

		// OpenMU-specific reset path (no inventory wipe, reset to level 1, clear stats, award points)
		$openMuMode = function_exists('calculateOpenMULevel') ? true : false;
		if($openMuMode) {
			// Resolve essential fields
			$row = $this->muonline->query_fetch_single("SELECT "._CLMN_CHAR_ID_." AS cid, "._CLMN_CHAR_CLASS_ID_." AS cls, "._CLMN_CHAR_EXPERIENCE_." AS exp, "._CLMN_CHAR_LEVEL_UP_POINTS_." AS lup FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." = ?", array($this->_character));
			if(!is_array($row)) throw new Exception(lang('error_21'));
			$charId = $row['cid'] ?? null;
			if(!$charId) throw new Exception(lang('error_21'));
			$expVal = isset($row['exp']) ? (int)$row['exp'] : 0;
			$currentLevel = calculateOpenMULevel($expVal);
			$requiredLevel = (int)mconfig('required_level');
			if($requiredLevel < 1) $requiredLevel = 400;
			if($currentLevel < $requiredLevel) throw new Exception(lang('error_33'));

			// Determine current resets from our meta table (create if missing); baseline is 1
			$this->muonline->query("CREATE TABLE IF NOT EXISTS public.webengine_resets (character_id uuid PRIMARY KEY, resets integer NOT NULL DEFAULT 1, updated_at timestamp without time zone NOT NULL DEFAULT NOW())");
			$meta = $this->muonline->query_fetch_single("SELECT resets FROM public.webengine_resets WHERE character_id = ?", array($charId));
			$currentResets = is_array($meta) && isset($meta['resets']) ? (int)$meta['resets'] : 1;
			$nextResets = $currentResets + 1;

			// Award points: 500 * current reset count (before increment)
			$newLevelUpPoints = 500 * $currentResets;

			// Clear stats to class base
			$legacyClassNum = 0;
			$rawCls = $row['cls'] ?? ($characterData[_CLMN_CHR_CLASS_] ?? null);
			if(is_numeric($rawCls)) { $legacyClassNum = (int)$rawCls; }
			elseif(function_exists('getOpenMUClassNumberById')) { $legacyClassNum = getOpenMUClassNumberById($rawCls); }
			if(!is_int($legacyClassNum)) $legacyClassNum = 0;
			$base_stats = array('str'=>18,'agi'=>18,'vit'=>15,'ene'=>30,'cmd'=>0);
			global $custom;
			if(is_array($custom) && isset($custom['character_class'][$legacyClassNum]['base_stats'])) {
				$base_stats = $custom['character_class'][$legacyClassNum]['base_stats'];
			}
			if(function_exists('setOpenMUCharacterBaseStats')) {
				$ok = setOpenMUCharacterBaseStats($charId, $base_stats);
				if(!$ok) throw new Exception(lang('error_21'));
			}

			// Reset to level 1 by setting Experience = 0 and update LevelUpPoints
			$upd = $this->muonline->query("UPDATE "._TBL_CHR_." SET "._CLMN_CHAR_EXPERIENCE_." = 0, "._CLMN_CHAR_LEVEL_UP_POINTS_." = :lup WHERE "._CLMN_CHAR_ID_." = :cid", array('lup'=>$newLevelUpPoints, 'cid'=>$charId));
			if(!$upd) throw new Exception(lang('error_23'));

			// Persist resets
			$this->muonline->query("INSERT INTO public.webengine_resets (character_id, resets, updated_at) VALUES (?, ?, NOW()) ON CONFLICT (character_id) DO UPDATE SET resets = EXCLUDED.resets, updated_at = NOW()", array($charId, $nextResets));

			// success
			message('success', lang('success_8'));
			return; // skip legacy path
		}

		// next reset (legacy/sql-server flows)
		$resetNumber = (isset($characterData[_CLMN_CHR_RSTS_]) && is_numeric($characterData[_CLMN_CHR_RSTS_])) ? ((int)$characterData[_CLMN_CHR_RSTS_] + 1) : 1;
		
        // level requirement (OpenMU calculates level from Experience)
		if(mconfig('required_level') >= 1) {
            $currentLevel = 1;
            if(defined('_CLMN_CHAR_EXPERIENCE_')) {
                $expVal = isset($characterData[_CLMN_CHAR_EXPERIENCE_]) ? (int)$characterData[_CLMN_CHAR_EXPERIENCE_] : 0;
                $currentLevel = function_exists('calculateOpenMULevel') ? calculateOpenMULevel($expVal) : 1;
            } else {
                $currentLevel = isset($characterData[_CLMN_CHR_LVL_]) ? (int)$characterData[_CLMN_CHR_LVL_] : 1;
            }
            if($currentLevel < mconfig('required_level')) throw new Exception(lang('error_33'));
		}
		
		// maximum resets
		$maxResets = mconfig('maximum_resets');
		if($maxResets > 0) {
			if($resetNumber > $maxResets) throw new Exception(lang('error_127'));
		}
		
		// stats
		$clearStats = mconfig('keep_stats') == 1 ? false : true;
		
		// points
		$newLevelUpPoints = mconfig('points_reward') >= 1 ? mconfig('points_reward') : 0;
		if(mconfig('multiply_points_by_resets') == 1) {
			$newLevelUpPoints = $newLevelUpPoints*$resetNumber;
		}
		
		// existing lvl up points (only when keeping stats)
		if(!$clearStats) {
			$newLevelUpPoints += $characterData[_CLMN_CHR_LVLUP_POINT_];
		}
		
		// class
		$revertClass = mconfig('revert_class_evolution') == 1 ? true : false;
		if($revertClass) {
			if(!array_key_exists('class_group', $this->_classData[$characterData[_CLMN_CHR_CLASS_]])) throw new Exception(lang('error_128'));
			$classGroup = $this->_classData[$characterData[_CLMN_CHR_CLASS_]]['class_group'];
		}
		
		// zen requirement
		$zenRequirement = mconfig('zen_cost');
		if($zenRequirement > 0) if($characterData[_CLMN_CHR_ZEN_] < $zenRequirement) throw new Exception(lang('error_34'));
		$newZen = $characterData[_CLMN_CHR_ZEN_]-$zenRequirement;
		
		// credit requirement
		$creditConfig = mconfig('credit_config');
		$creditCost = mconfig('credit_cost');
		if($creditCost > 0 && $creditConfig != 0) {
			$creditSystem = new CreditSystem();
			$creditSystem->setConfigId($creditConfig);
			$configSettings = $creditSystem->showConfigs(true);
			switch($configSettings['config_user_col_id']) {
				case 'userid':
					$creditSystem->setIdentifier($this->_userid);
					break;
				case 'username':
					$creditSystem->setIdentifier($this->_username);
					break;
				case 'character':
					$creditSystem->setIdentifier($this->_character);
					break;
				default:
					throw new Exception("Invalid identifier (credit system).");
			}
			if($creditSystem->getCredits() < $creditCost) throw new Exception(langf('error_126', array($configSettings['config_title'])));
		}
		
		// OpenMU-specific reset stats path
		$openMuMode = function_exists('getOpenMUCharacterStats') ? true : false;
		if(!$openMuMode) {
			// Detect by presence of OpenMU tables
			$chk = $this->muonline->query_fetch_single("SELECT 1 FROM information_schema.tables WHERE table_schema='data' AND table_name='StatAttribute' LIMIT 1");
			$openMuMode = is_array($chk) ? true : false;
		}
		if($openMuMode) {
			$characterData_l = array_change_key_case($characterData, CASE_LOWER);
			$cidRow = $this->muonline->query_fetch_single("SELECT "._CLMN_CHAR_ID_." AS cid, "._CLMN_CHAR_CLASS_ID_." AS cls FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." = ?", array($this->_character));
			$charId = is_array($cidRow) && isset($cidRow['cid']) ? $cidRow['cid'] : null;
			$rawClass = $cidRow['cls'] ?? ($characterData[_CLMN_CHR_CLASS_] ?? ($characterData['CharacterClassId'] ?? ($characterData_l['characterclassid'] ?? 0)));
			$legacyClassNum = 0;
			if(is_numeric($rawClass)) { $legacyClassNum = (int)$rawClass; }
			elseif(function_exists('getOpenMUClassNumberById')) { $legacyClassNum = getOpenMUClassNumberById($rawClass); }
			$base_stats = $this->_getClassBaseStats($legacyClassNum);
			$base_stats_points = array_sum($base_stats);
			$currentStats = ($charId && function_exists('getOpenMUCharacterStats')) ? getOpenMUCharacterStats($charId) : array('Strength'=>0,'Agility'=>0,'Vitality'=>0,'Energy'=>0,'Leadership'=>0);
			$current_points = (int)($currentStats['Strength'] ?? 0) + (int)($currentStats['Agility'] ?? 0) + (int)($currentStats['Vitality'] ?? 0) + (int)($currentStats['Energy'] ?? 0) + (int)($currentStats['Leadership'] ?? 0);
			$levelUpPoints = max(0, $current_points - $base_stats_points);
			// Apply: set base stats, add points, deduct zen
			if($charId && function_exists('setOpenMUCharacterBaseStats')) {
				$ok = setOpenMUCharacterBaseStats($charId, $base_stats);
				if(!$ok) throw new Exception(lang('error_21'));
			}
			// Deduct zen
			if($zenRequirement > 0) if(!$this->DeductZEN($this->_character, $zenRequirement)) throw new Exception(lang('error_34'));
			// Add points on Character table
			if($charId) {
				$this->muonline->query("UPDATE "._TBL_CHR_." SET "._CLMN_CHR_LVLUP_POINT_." = "._CLMN_CHR_LVLUP_POINT_." + :pts WHERE "._CLMN_CHAR_ID_." = :cid", array('pts'=>$levelUpPoints, 'cid'=>$charId));
			}
			// subtract credits
			if($creditCost > 0 && $creditConfig != 0) $creditSystem->subtractCredits($creditCost);
			message('success', lang('success_9'));
			return;
		}

		// base stats (legacy)
		$characterData_l = array_change_key_case($characterData, CASE_LOWER);
		$rawClass = isset($characterData[_CLMN_CHR_CLASS_]) ? $characterData[_CLMN_CHR_CLASS_] : ($characterData['CharacterClassId'] ?? ($characterData_l['characterclassid'] ?? 0));
		$legacyClassNum = is_numeric($rawClass) ? (int)$rawClass : 0;
		$base_stats = $this->_getClassBaseStats($legacyClassNum);
		
		// inventory
		$clearInventory = mconfig('clear_inventory') == 1 ? true : false;
		
		// query data
		if($revertClass) $data['class'] = $classGroup;
		if($clearStats) $data['str'] = $base_stats['str'];
		if($clearStats) $data['agi'] = $base_stats['agi'];
		if($clearStats) $data['vit'] = $base_stats['vit'];
		if($clearStats) $data['ene'] = $base_stats['ene'];
		if($clearStats) $data['cmd'] = $base_stats['cmd'];
		$data['points'] = $newLevelUpPoints;
		if($zenRequirement > 0) $data['zen'] = $newZen;
		$data['name'] = $characterData[_CLMN_CHR_NAME_];
		
		// query
		$query = "UPDATE Character SET ";
		$query .= _CLMN_CHR_LVL_ . " = 1, ";
		if($revertClass) $query .= _CLMN_CHR_CLASS_ . " = :class, ";
		if($revertClass) $query .= _CLMN_CHR_QUEST_ . " = NULL, ";
		if($clearStats) $query .= _CLMN_CHR_STAT_STR_ . " = :str, ";
		if($clearStats) $query .= _CLMN_CHR_STAT_AGI_ . " = :agi, ";
		if($clearStats) $query .= _CLMN_CHR_STAT_VIT_ . " = :vit, ";
		if($clearStats) $query .= _CLMN_CHR_STAT_ENE_ . " = :ene, ";
		if($clearStats) $query .= _CLMN_CHR_STAT_CMD_ . " = :cmd, ";
		if($zenRequirement > 0) $query .= _CLMN_CHR_ZEN_ . " = :zen, ";
		if($clearInventory) $query .= _CLMN_CHR_INV_ . " = NULL, ";
		$query .= _CLMN_CHR_LVLUP_POINT_ . " = :points, ";
		$query .= _CLMN_CHR_RSTS_ . " = "._CLMN_CHR_RSTS_."+1 ";
		$query .= "WHERE "._CLMN_CHR_NAME_." = :name";
		
		// reset
		$result = $this->muonline->query($query, $data);
		if(!$result) throw new Exception(lang('error_23'));
		
		// subtract credits
		if($creditCost > 0 && $creditConfig != 0) $creditSystem->subtractCredits($creditCost);
		
		// reward credits
		$creditRewardConfig = mconfig('credit_reward_config');
		$creditReward = mconfig('credit_reward');
		if($creditReward > 0 && $creditRewardConfig != 0) {
			$creditSystem = new CreditSystem();
			$creditSystem->setConfigId($creditRewardConfig);
			$configSettings = $creditSystem->showConfigs(true);
			switch($configSettings['config_user_col_id']) {
				case 'userid':
					$creditSystem->setIdentifier($this->_userid);
					break;
				case 'username':
					$creditSystem->setIdentifier($this->_username);
					break;
				case 'character':
					$creditSystem->setIdentifier($this->_character);
					break;
				default:
					throw new Exception("Invalid identifier (credit system).");
			}
			$creditSystem->addCredits($creditReward);
		}
		
		// success
		message('success', lang('success_8'));
	}
	
	public function CharacterResetStats() {
		// filters
		if(!check_value($this->_username)) throw new Exception(lang('error_21'));
		if(!check_value($this->_character)) throw new Exception(lang('error_21'));
		if(!check_value($this->_userid)) throw new Exception(lang('error_21'));
		if(!$this->CharacterExists($this->_character)) throw new Exception(lang('error_35'));
		if(!$this->CharacterBelongsToAccount($this->_character, $this->_username)) throw new Exception(lang('error_35'));
		
		// check online status
		$Account = new Account();
		if($Account->accountOnline($this->_username)) throw new Exception(lang('error_14'));
		
		// character data
		$characterData = $this->CharacterData($this->_character);
		
		// zen requirement
		$zenRequirement = mconfig('zen_cost');
		
		// credit requirement
		$creditConfig = mconfig('credit_config');
		$creditCost = mconfig('credit_cost');
		if($creditCost > 0 && $creditConfig != 0) {
			$creditSystem = new CreditSystem();
			$creditSystem->setConfigId($creditConfig);
			$configSettings = $creditSystem->showConfigs(true);
			switch($configSettings['config_user_col_id']) {
				case 'userid':
					$creditSystem->setIdentifier($this->_userid);
					break;
				case 'username':
					$creditSystem->setIdentifier($this->_username);
					break;
				case 'character':
					$creditSystem->setIdentifier($this->_character);
					break;
				default:
					throw new Exception("Invalid identifier (credit system).");
			}
			if($creditSystem->getCredits() < $creditCost) throw new Exception(langf('error_113', array($configSettings['config_title'])));
		}
		
		// check zen (OpenMU: from ItemStorage)
		if($zenRequirement > 0) {
			$cidRow = $this->muonline->query_fetch_single("SELECT "._CLMN_CHAR_ID_." AS cid FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." = ?", array($this->_character));
			$cid = is_array($cidRow) && isset($cidRow['cid']) ? $cidRow['cid'] : null;
			$have = ($cid && function_exists('getOpenMUCharacterMoney')) ? getOpenMUCharacterMoney($cid) : ($characterData[_CLMN_CHR_ZEN_] ?? 0);
			if($have < $zenRequirement) throw new Exception(lang('error_34'));
		}
		
		// base stats (fallback/legacy path): resolve class safely
		$characterData_l = array_change_key_case($characterData, CASE_LOWER);
		$rawClass = isset($characterData[_CLMN_CHR_CLASS_]) ? $characterData[_CLMN_CHR_CLASS_] : ($characterData['CharacterClassId'] ?? ($characterData_l['characterclassid'] ?? 0));
		$legacyClassNum = is_numeric($rawClass) ? (int)$rawClass : (function_exists('getOpenMUClassNumberById') ? getOpenMUClassNumberById($rawClass) : 0);
		$base_stats = $this->_getClassBaseStats($legacyClassNum);
		$base_stats_points = array_sum($base_stats);
		
		// calculate new level up points (use safe defaults if legacy columns missing)
		$levelUpPoints = (int)($characterData[_CLMN_CHR_STAT_STR_] ?? 0)
			+ (int)($characterData[_CLMN_CHR_STAT_AGI_] ?? 0)
			+ (int)($characterData[_CLMN_CHR_STAT_VIT_] ?? 0)
			+ (int)($characterData[_CLMN_CHR_STAT_ENE_] ?? 0);
		if(array_key_exists(_CLMN_CHR_STAT_CMD_, $characterData)) {
			$levelUpPoints += (int)$characterData[_CLMN_CHR_STAT_CMD_];
		}
		if($base_stats_points > 0) {
			$levelUpPoints -= $base_stats_points;
		}

		// Initialize flags and detect if legacy Strength columns exist; if not, do OpenMU stat reset and return
		$hasCharZen = false;
		$schema = 'public'; $table = null;
		if(preg_match('/([a-zA-Z0-9_]+)\.\"?([a-zA-Z0-9_]+)\"?/', _TBL_CHR_, $m)) { $schema=$m[1]; $table=$m[2]; }
		$hasStrengthCol = false;
		if($table) {
			$colStr = str_replace('"','', _CLMN_CHR_STAT_STR_);
			$chkStr = $this->muonline->query_fetch_single("SELECT 1 FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?", array($schema, $table, $colStr));
			$hasStrengthCol = is_array($chkStr) ? true : false;
		}
		if(!$hasStrengthCol) {
			$cidRow = $this->muonline->query_fetch_single("SELECT "._CLMN_CHAR_ID_." AS cid FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." = ?", array($this->_character));
			$charId = is_array($cidRow) && isset($cidRow['cid']) ? $cidRow['cid'] : null;
			if($charId && function_exists('setOpenMUCharacterBaseStats')) {
				$ok = setOpenMUCharacterBaseStats($charId, $base_stats);
				if(!$ok) throw new Exception(lang('error_21'));
			}
			if($zenRequirement > 0) if(!$this->DeductZEN($this->_character, $zenRequirement)) throw new Exception(lang('error_34'));
			if($charId) {
				$this->muonline->query("UPDATE "._TBL_CHR_." SET "._CLMN_CHR_LVLUP_POINT_." = "._CLMN_CHR_LVLUP_POINT_." + :pts WHERE "._CLMN_CHAR_ID_." = :cid", array('pts'=>$levelUpPoints, 'cid'=>$charId));
			}
			if($creditCost > 0 && $creditConfig != 0) $creditSystem->subtractCredits($creditCost);
			message('success', lang('success_9'));
			return;
		}

		// query data (only include :cmd when it's part of the query)
		$playerName = $characterData[_CLMN_CHR_NAME_] ?? ($characterData['Name'] ?? ($characterData_l['name'] ?? $this->_character));
		$hasCmd = array_key_exists(_CLMN_CHR_STAT_CMD_, $characterData);
		$data = array(
			'player' => $playerName,
				'points' => $levelUpPoints,
			'str' => $base_stats['str'],
			'agi' => $base_stats['agi'],
			'vit' => $base_stats['vit'],
			'ene' => $base_stats['ene'],
		);
		if($hasCharZen && $zenRequirement > 0) $data['zen'] = $zenRequirement;
		if($hasCmd) $data['cmd'] = $base_stats['cmd'];
		
		// query
		$query = "UPDATE "._TBL_CHR_." SET "._CLMN_CHR_STAT_STR_." = :str, "._CLMN_CHR_STAT_AGI_." = :agi, "._CLMN_CHR_STAT_VIT_." = :vit, "._CLMN_CHR_STAT_ENE_." = :ene";
		if($hasCmd) $query .= ", "._CLMN_CHR_STAT_CMD_." = :cmd";
		// Only subtract zen directly if the character table has that column; otherwise we'll use DeductZEN()
		$schema = 'public'; $table = null;
		if(preg_match('/([a-zA-Z0-9_]+)\.\"?([a-zA-Z0-9_]+)\"?/', _TBL_CHR_, $m)) { $schema=$m[1]; $table=$m[2]; }
		$zenColName = str_replace('"','', _CLMN_CHR_ZEN_);
		$hasCharZen = false;
		if($table) {
			$chkZen = $this->muonline->query_fetch_single("SELECT 1 FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ?", array($schema, $table, $zenColName));
			$hasCharZen = is_array($chkZen) ? true : false;
		}
		if($hasCharZen) {
		$query .= ", "._CLMN_CHR_ZEN_." = "._CLMN_CHR_ZEN_." - :zen";
		}
		$query .= ", "._CLMN_CHR_LVLUP_POINT_." = "._CLMN_CHR_LVLUP_POINT_." + :points WHERE "._CLMN_CHR_NAME_." = :player";
		
		// reset stats
		// If zen column not in character table, deduct via helper first
		if(!$hasCharZen && $zenRequirement > 0) {
			if(!$this->DeductZEN($this->_character, $zenRequirement)) throw new Exception(lang('error_34'));
		}
		$result = $this->muonline->query($query, $data);
		if(!$result) throw new Exception(lang('error_21'));
		
		// subtract credits
		if($creditCost > 0 && $creditConfig != 0) $creditSystem->subtractCredits($creditCost);
		
		// success
		message('success', lang('success_9'));
	}
	
	public function CharacterClearPK() {
		// filters
		if(!check_value($this->_username)) throw new Exception(lang('error_21'));
		if(!check_value($this->_character)) throw new Exception(lang('error_21'));
		if(!check_value($this->_userid)) throw new Exception(lang('error_21'));
		if(!$this->CharacterExists($this->_character)) throw new Exception(lang('error_36'));
		if(!$this->CharacterBelongsToAccount($this->_character, $this->_username)) throw new Exception(lang('error_36'));
		
		// check online status
		$Account = new Account();
		if($Account->accountOnline($this->_username)) throw new Exception(lang('error_14'));
		
		// character data
		$characterData = $this->CharacterData($this->_character);
		
        // OpenMU path: if PK level column undefined, use PlayerKillCount check instead
        $useOpenMuPk = !defined('_CLMN_CHR_PK_LEVEL_') || !array_key_exists(_CLMN_CHR_PK_LEVEL_, $characterData);
        if(!$useOpenMuPk) {
		if($characterData[_CLMN_CHR_PK_LEVEL_] == $this->_clearPkLevel) throw new Exception(lang('error_117'));
        } else {
            $rowPk = $this->muonline->query_fetch_single("SELECT "._CLMN_CHAR_ID_." AS cid, "._CLMN_CHAR_PK_COUNT_." AS pk FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." = ?", array($this->_character));
            $pkVal = is_array($rowPk) && isset($rowPk['pk']) ? (int)$rowPk['pk'] : 0;
            if($pkVal <= 0) throw new Exception(lang('error_117'));
        }
		
		// zen requirement
		$zenRequirement = mconfig('zen_cost');
		
		// credit requirement
		$creditConfig = mconfig('credit_config');
		$creditCost = mconfig('credit_cost');
		if($creditCost > 0 && $creditConfig != 0) {
			$creditSystem = new CreditSystem();
			$creditSystem->setConfigId($creditConfig);
			$configSettings = $creditSystem->showConfigs(true);
			switch($configSettings['config_user_col_id']) {
				case 'userid':
					$creditSystem->setIdentifier($this->_userid);
					break;
				case 'username':
					$creditSystem->setIdentifier($this->_username);
					break;
				case 'character':
					$creditSystem->setIdentifier($this->_character);
					break;
				default:
					throw new Exception("Invalid identifier (credit system).");
			}
			if($creditSystem->getCredits() < $creditCost) throw new Exception(langf('error_116', array($configSettings['config_title'])));
		}
		
		// check zen
        if($zenRequirement > 0) {
            if($useOpenMuPk) {
                $cidRow = $this->muonline->query_fetch_single("SELECT "._CLMN_CHAR_ID_." AS cid FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." = ?", array($this->_character));
                $cid = is_array($cidRow) && isset($cidRow['cid']) ? $cidRow['cid'] : null;
                $have = ($cid && function_exists('getOpenMUCharacterMoney')) ? getOpenMUCharacterMoney($cid) : 0;
                if($have < $zenRequirement) throw new Exception(lang('error_34'));
            } else {
                if($characterData[_CLMN_CHR_ZEN_] < $zenRequirement) throw new Exception(lang('error_34'));
            }
        }
		
        if($useOpenMuPk) {
            // Deduct zen from ItemStorage
            if($zenRequirement > 0) {
                $cidRow = isset($cid) ? array('cid'=>$cid) : $this->muonline->query_fetch_single("SELECT "._CLMN_CHAR_ID_." AS cid FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." = ?", array($this->_character));
                $cid = is_array($cidRow) && isset($cidRow['cid']) ? $cidRow['cid'] : null;
                if($cid && function_exists('deductOpenMUCharacterMoney')) {
                    if(!deductOpenMUCharacterMoney($cid, $zenRequirement)) throw new Exception(lang('error_34'));
                }
            }
            // Clear kills
            $result = $this->muonline->query("UPDATE "._TBL_CHR_." SET "._CLMN_CHR_PK_KILLS_." = 0 WHERE "._CLMN_CHR_NAME_." = ?", array($this->_character));
            if(!$result) throw new Exception(lang('error_21'));
        } else {
            // legacy path
		// query data
		$data = array(
			'player' => $characterData[_CLMN_CHR_NAME_],
			'pklevel' => $this->_clearPkLevel,
			'zen' => $zenRequirement,
		);
		// query
		$query = "UPDATE "._TBL_CHR_." SET "._CLMN_CHR_PK_LEVEL_." = :pklevel, "._CLMN_CHR_PK_TIME_." = 0, "._CLMN_CHR_ZEN_." = "._CLMN_CHR_ZEN_." - :zen WHERE "._CLMN_CHR_NAME_." = :player";
		// clear pk
		$result = $this->muonline->query($query, $data);
		if(!$result) throw new Exception(lang('error_21'));
        }
		
		// subtract credits
		if($creditCost > 0 && $creditConfig != 0) $creditSystem->subtractCredits($creditCost);
		
		// success
		message('success', lang('success_10'));
	}
	
	public function CharacterUnstick() {
		// filters
		if(!check_value($this->_username)) throw new Exception(lang('error_21'));
		if(!check_value($this->_character)) throw new Exception(lang('error_21'));
		if(!check_value($this->_userid)) throw new Exception(lang('error_21'));
		if(!$this->CharacterExists($this->_character)) throw new Exception(lang('error_37'));
		if(!$this->CharacterBelongsToAccount($this->_character, $this->_username)) throw new Exception(lang('error_37'));
		
		// check online status
		$Account = new Account();
		if($Account->accountOnline($this->_username)) throw new Exception(lang('error_14'));
		
		// character data
		$characterData = $this->CharacterData($this->_character);
		
		// check position (OpenMU-safe via explicit aliases)
		$posRow = $this->muonline->query_fetch_single("SELECT "._CLMN_CHAR_ID_." AS cid, "._CLMN_CHAR_MAP_ID_." AS map, "._CLMN_CHAR_POSITION_X_." AS px, "._CLMN_CHAR_POSITION_Y_." AS py FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." = ?", array($this->_character));
		$curMap = is_array($posRow) && isset($posRow['map']) ? (int)$posRow['map'] : null;
		$curX = is_array($posRow) && isset($posRow['px']) ? (int)$posRow['px'] : null;
		$curY = is_array($posRow) && isset($posRow['py']) ? (int)$posRow['py'] : null;
		if($curMap === $this->_unstickMap && $curX === $this->_unstickCoordX && $curY === $this->_unstickCoordY) throw new Exception(lang('error_115'));
		
		// zen requirement
		$zenRequirement = mconfig('zen_cost');
		
		// credit requirement
		$creditConfig = mconfig('credit_config');
		$creditCost = mconfig('credit_cost');
		if($creditCost > 0 && $creditConfig != 0) {
			$creditSystem = new CreditSystem();
			$creditSystem->setConfigId($creditConfig);
			$configSettings = $creditSystem->showConfigs(true);
			switch($configSettings['config_user_col_id']) {
				case 'userid':
					$creditSystem->setIdentifier($this->_userid);
					break;
				case 'username':
					$creditSystem->setIdentifier($this->_username);
					break;
				case 'character':
					$creditSystem->setIdentifier($this->_character);
					break;
				default:
					throw new Exception("Invalid identifier (credit system).");
			}
			if($creditSystem->getCredits() < $creditCost) throw new Exception(langf('error_114', array($configSettings['config_title'])));
		}
		
		// skip upfront zen check; DeductZEN() below will validate and handle OpenMU safely
		
		// deduct zen
		if($zenRequirement > 0) if(!$this->DeductZEN($this->_character, $zenRequirement)) throw new Exception(lang('error_34'));
		
		// move character
		$update = $this->_moveCharacter($this->_character, $this->_unstickMap, $this->_unstickCoordX, $this->_unstickCoordY);
		if(!$update) throw new Exception(lang('error_21'));
		
		// subtract credits
		if($creditCost > 0 && $creditConfig != 0) $creditSystem->subtractCredits($creditCost);
		
		// success
		message('success', lang('success_11'));
	}
	
	public function CharacterClearSkillTree() {
		// filters
		if(!check_value($this->_username)) throw new Exception(lang('error_21'));
		if(!check_value($this->_character)) throw new Exception(lang('error_21'));
		if(!check_value($this->_userid)) throw new Exception(lang('error_21'));
		if(!$this->CharacterExists($this->_character)) throw new Exception(lang('error_38'));
		if(!$this->CharacterBelongsToAccount($this->_character, $this->_username)) throw new Exception(lang('error_38'));
		
		// check online status
		$Account = new Account();
		if($Account->accountOnline($this->_username)) throw new Exception(lang('error_14'));
		
		// character data
		$characterData = $this->CharacterData($this->_character);
		
		// check required level (regular) - OpenMU calculates from Experience
		$reqLvl = (int)mconfig('required_level');
		if($reqLvl > 0) {
			$characterData_l = array_change_key_case($characterData, CASE_LOWER);
			$exp = $characterData['Experience'] ?? ($characterData_l['experience'] ?? ($characterData[_CLMN_CHAR_EXPERIENCE_] ?? 0));
			$dispLevel = (is_numeric($characterData[_CLMN_CHR_LVL_] ?? null) ? (int)$characterData[_CLMN_CHR_LVL_] : (function_exists('calculateOpenMULevel') ? calculateOpenMULevel((int)$exp) : 0));
			if($dispLevel < $reqLvl) throw new Exception(lang('error_120'));
		}
		
		// character master level data
		$characterMasterLvlData = _TBL_CHR_ != _TBL_MASTERLVL_ ? $this->getMasterLevelInfo($this->_character) : $characterData;
		if(!is_array($characterMasterLvlData)) throw new Exception(lang('error_119'));
		
		// check required level (master)
		if($characterMasterLvlData[_CLMN_ML_LVL_] < mconfig('required_master_level')) throw new Exception(lang('error_121'));
		
		// combined character level
		$characterData_l = array_change_key_case($characterData, CASE_LOWER);
		$exp = $characterData['Experience'] ?? ($characterData_l['experience'] ?? ($characterData[_CLMN_CHAR_EXPERIENCE_] ?? 0));
		$baseLevel = (is_numeric($characterData[_CLMN_CHR_LVL_] ?? null) ? (int)$characterData[_CLMN_CHR_LVL_] : (function_exists('calculateOpenMULevel') ? calculateOpenMULevel((int)$exp) : 0));
		$characterLevel = $baseLevel + $characterMasterLvlData[_CLMN_ML_LVL_];
		
		// skill enhancement tree points
		$skillEnhancementPoints = 0;
		
		// skill enhancement support
		if(defined('_CLMN_ML_I4SP_')) {
			$skillEnhancementTreeEnabled = array_key_exists(_CLMN_ML_I4SP_, $characterMasterLvlData) ? true : false;
		}
		
		// skill enhancement points
		if($skillEnhancementTreeEnabled) {
			if($characterLevel > $this->_skilEnhanceTreeLevel) {
				$skillEnhancementPoints = $characterLevel-$this->_skilEnhanceTreeLevel;
			}
		}
		
		// zen requirement
		$zenRequirement = mconfig('zen_cost');
		
		// credit requirement
		$creditConfig = mconfig('credit_config');
		$creditCost = mconfig('credit_cost');
		if($creditCost > 0 && $creditConfig != 0) {
			$creditSystem = new CreditSystem();
			$creditSystem->setConfigId($creditConfig);
			$configSettings = $creditSystem->showConfigs(true);
			switch($configSettings['config_user_col_id']) {
				case 'userid':
					$creditSystem->setIdentifier($this->_userid);
					break;
				case 'username':
					$creditSystem->setIdentifier($this->_username);
					break;
				case 'character':
					$creditSystem->setIdentifier($this->_character);
					break;
				default:
					throw new Exception("Invalid identifier (credit system).");
			}
			if($creditSystem->getCredits() < $creditCost) throw new Exception(langf('error_118', array($configSettings['config_title'])));
		}
		
		// check zen
		if($zenRequirement > 0) if($characterData[_CLMN_CHR_ZEN_] < $zenRequirement) throw new Exception(lang('error_34'));
		
		// data
		$data = array(
			'player' => $this->_character,
			'masterpoints' => $characterMasterLvlData[_CLMN_ML_LVL_]-$skillEnhancementPoints,
		);
		
		if($skillEnhancementTreeEnabled && $skillEnhancementPoints > 0) {
			$data['skillenhancementpoints'] = $skillEnhancementPoints;
		}
		
		// query
		$query = "UPDATE "._TBL_MASTERLVL_." SET "._CLMN_ML_POINT_." = :masterpoints";
		if(defined('_CLMN_ML_EXP_')) if(array_key_exists(_CLMN_ML_EXP_, $characterMasterLvlData)) $query .= ", "._CLMN_ML_EXP_." = 0";
		if(defined('_CLMN_ML_NEXP_')) if(array_key_exists(_CLMN_ML_NEXP_, $characterMasterLvlData)) $query .= ", "._CLMN_ML_NEXP_." = 0";
		if($skillEnhancementTreeEnabled && $skillEnhancementPoints > 0) $query .= ", "._CLMN_ML_I4SP_." = :skillenhancementpoints";
		$query .= " WHERE "._CLMN_ML_NAME_." = :player";
		
		// clear magic list (skills)
		$resetMagicList = $this->_resetMagicList($this->_character);
		if(!$resetMagicList) throw new Exception(lang('error_21'));
		
		// clear master skill tree
		$clearMasterSkillTree = $this->muonline->query($query, $data);
		if(!$clearMasterSkillTree) throw new Exception(lang('error_21'));
		
		// deduct zen
		if($zenRequirement > 0) if(!$this->DeductZEN($this->_character, $zenRequirement)) throw new Exception(lang('error_34'));
		
		// subtract credits
		if($creditCost > 0 && $creditConfig != 0) $creditSystem->subtractCredits($creditCost);
		
		// success
		message('success', lang('success_12'));
	}
	
	public function CharacterAddStats() {
		// filters
		if(!check_value($this->_username)) throw new Exception(lang('error_21'));
		if(!check_value($this->_character)) throw new Exception(lang('error_21'));
		if(!check_value($this->_userid)) throw new Exception(lang('error_21'));
		if(!$this->CharacterExists($this->_character)) throw new Exception(lang('error_64'));
		if(!$this->CharacterBelongsToAccount($this->_character, $this->_username)) throw new Exception(lang('error_64'));
		
		// points
		$pointsTotal = $this->_strength+$this->_agility+$this->_vitality+$this->_energy+$this->_command;
		
		// points minimum limit
		if($pointsTotal < mconfig('minimum_limit')) throw new Exception(langf('error_54', array(mconfig('minimum_limit'))));
		
		// check online status
		$Account = new Account();
		if($Account->accountOnline($this->_username)) throw new Exception(lang('error_14'));
		
		// character data
		$characterData = $this->CharacterData($this->_character);
		
		// fetch id, level up points, experience, and class with clear aliases to avoid quoted-key issues
		$row = $this->muonline->query_fetch_single("SELECT "._CLMN_CHAR_ID_." AS cid, "._CLMN_CHAR_LEVEL_UP_POINTS_." AS lup, "._CLMN_CHAR_EXPERIENCE_." AS exp, "._CLMN_CHAR_CLASS_ID_." AS cls FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." = ?", array($this->_character));
		$charId = is_array($row) && isset($row['cid']) ? $row['cid'] : null;
		$lvlPoints = is_array($row) && isset($row['lup']) ? (int)$row['lup'] : 0;
		if($lvlPoints < $pointsTotal) throw new Exception(lang('error_51'));
		
		// current stats (OpenMU: from StatAttribute)
		$currentStats = function_exists('getOpenMUCharacterStats') && $charId ? getOpenMUCharacterStats($charId) : array('Strength'=>0,'Agility'=>0,'Vitality'=>0,'Energy'=>0,'Leadership'=>0);
		$newStr = $currentStats['Strength'] + $this->_strength;
		$newAgi = $currentStats['Agility'] + $this->_agility;
		$newVit = $currentStats['Vitality'] + $this->_vitality;
		$newEne = $currentStats['Energy'] + $this->_energy;
		
		// check stat limits
		if($newStr > mconfig('max_stats')) throw new Exception(langf('error_53', array(number_format(mconfig('max_stats')))));
		if($newAgi > mconfig('max_stats')) throw new Exception(langf('error_53', array(number_format(mconfig('max_stats')))));
		if($newVit > mconfig('max_stats')) throw new Exception(langf('error_53', array(number_format(mconfig('max_stats')))));
		if($newEne > mconfig('max_stats')) throw new Exception(langf('error_53', array(number_format(mconfig('max_stats')))));
		
		// cmd
		$cmd = 0;
		if($this->_command >= 1) {
			// leadership allowed only for classes that support it
			$cls = is_array($row) && isset($row['cls']) ? $row['cls'] : null;
			if(!in_array($cls, custom('character_cmd'))) throw new Exception(lang('error_52'));
			$cmd = $currentStats['Leadership'] + $this->_command;
			if($cmd > mconfig('max_stats')) throw new Exception(langf('error_53', array(number_format(mconfig('max_stats')))));
		}
		
		// check required level (regular) using Experience → Level
		$reqLvl = (int)mconfig('required_level');
		if($reqLvl >= 1) {
			$expVal = is_array($row) && isset($row['exp']) ? (int)$row['exp'] : 0;
			$baseLevel = function_exists('calculateOpenMULevel') ? calculateOpenMULevel($expVal) : 1;
			if($baseLevel < $reqLvl) throw new Exception(lang('error_123'));
		}
		
		if(mconfig('required_master_level') >= 1) {
			// character master level data
			$characterMasterLvlData = _TBL_CHR_ != _TBL_MASTERLVL_ ? $this->getMasterLevelInfo($this->_character) : $characterData;
			if(!is_array($characterMasterLvlData)) throw new Exception(lang('error_119'));
			
			// check required level (master)
			if($characterMasterLvlData[_CLMN_ML_LVL_] < mconfig('required_master_level')) throw new Exception(lang('error_124'));
		}
		
		// zen requirement
		$zenRequirement = mconfig('zen_cost');
		
		// check zen (OpenMU: money from ItemStorage)
		if($zenRequirement > 0) {
			$money = (function_exists('getOpenMUCharacterMoney') && $charId) ? getOpenMUCharacterMoney($charId) : 0;
			if($money < $zenRequirement) throw new Exception(lang('error_34'));
		}
		
		// credit requirement
		$creditConfig = mconfig('credit_config');
		$creditCost = mconfig('credit_cost');
		if($creditCost > 0 && $creditConfig != 0) {
			$creditSystem = new CreditSystem();
			$creditSystem->setConfigId($creditConfig);
			$configSettings = $creditSystem->showConfigs(true);
			switch($configSettings['config_user_col_id']) {
				case 'userid':
					$creditSystem->setIdentifier($this->_userid);
					break;
				case 'username':
					$creditSystem->setIdentifier($this->_username);
					break;
				case 'character':
					$creditSystem->setIdentifier($this->_character);
					break;
				default:
					throw new Exception("Invalid identifier (credit system).");
			}
			if($creditSystem->getCredits() < $creditCost) throw new Exception(langf('error_125', array($configSettings['config_title'])));
		}
		
		// deduct zen
		if($zenRequirement > 0) if(!$this->DeductZEN($this->_character, $zenRequirement)) throw new Exception(lang('error_34'));
		
		// apply stat increments in StatAttribute (OpenMU)
		$incs = array(
			'Strength' => (int)$this->_strength,
			'Agility' => (int)$this->_agility,
			'Vitality' => (int)$this->_vitality,
			'Energy' => (int)$this->_energy,
		);
		if($cmd >= 1) $incs['Leadership'] = (int)$this->_command;
		if(function_exists('updateOpenMUCharacterStatsIncrement') && $charId) {
			$okStats = updateOpenMUCharacterStatsIncrement($charId, $incs);
			if(!$okStats) throw new Exception(lang('error_21'));
		}
		
		// decrement level up points on Character
		$data = array(
			'total' => $pointsTotal,
			'player' => $this->_character,
		);
		$query = "UPDATE "._TBL_CHR_." SET "._CLMN_CHR_LVLUP_POINT_." = "._CLMN_CHR_LVLUP_POINT_." - :total WHERE "._CLMN_CHR_NAME_." = :player";
		$result = $this->muonline->query($query, $data);
		if(!$result) throw new Exception(lang('error_21'));
		
		// subtract credits
		if($creditCost > 0 && $creditConfig != 0) $creditSystem->subtractCredits($creditCost);
		
		// success
		message('success', lang('success_17'));
	}	
	
    public function AccountCharacter($accountIdOrName) {
        if(!check_value($accountIdOrName)) return;
        // OpenMU uses AccountId (uuid) in Character table
        // Accept either uuid or login name and resolve when needed
        $param = $accountIdOrName;
        if(!preg_match('/^[0-9a-fA-F\-]{36}$/', $accountIdOrName)) {
            // resolve account uuid from login name
            $acc = $this->muonline->query_fetch_single("SELECT "._CLMN_ACCOUNT_ID_." AS id FROM "._TBL_ACCOUNT_." WHERE "._CLMN_ACCOUNT_LOGIN_." = ?", array($accountIdOrName));
            if(is_array($acc) && isset($acc['id'])) $param = $acc['id']; else return;
        }
        
        $result = $this->muonline->query_fetch("SELECT "._CLMN_CHR_NAME_." AS character_name FROM "._TBL_CHR_." WHERE "._CLMN_CHR_ACCID_." = ?", array($param));
		if(!is_array($result)) return;
        $return = array();
		foreach($result as $row) {
            $name = $row['character_name'] ?? null;
            if(!check_value($name)) continue;
            $return[] = $name;
		}
        if(empty($return)) return;
		return $return;
	}
	
	public function CharacterData($character_name) {
		if(!check_value($character_name)) return;
		$result = $this->muonline->query_fetch_single("SELECT * FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." = ?", array($character_name));
		if(!is_array($result)) return;
		return $result;
		
	}
	
    public function CharacterBelongsToAccount($character_name,$accountIdOrLogin) {
		if(!check_value($character_name)) return;
        if(!check_value($accountIdOrLogin)) return;
        // Resolve UUID from login if needed
        $param = $accountIdOrLogin;
        if(!(is_string($accountIdOrLogin) && preg_match('/^[0-9a-fA-F\-]{36}$/', $accountIdOrLogin))) {
            $acc = $this->muonline->query_fetch_single("SELECT "._CLMN_ACCOUNT_ID_." AS id FROM "._TBL_ACCOUNT_." WHERE "._CLMN_ACCOUNT_LOGIN_." = ?", array($accountIdOrLogin));
            if(!is_array($acc) || !isset($acc['id'])) return;
            $param = $acc['id'];
        }
        $row = $this->muonline->query_fetch_single("SELECT 1 FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." = ? AND "._CLMN_CHR_ACCID_." = ?", array($character_name, $param));
        if(!is_array($row)) return;
		return true;
	}
	
	public function CharacterExists($character_name) {
		if(!check_value($character_name)) return;
		$check = $this->muonline->query_fetch_single("SELECT * FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." = ?", array($character_name));
		if(!is_array($check)) return;
		return true;
	}
	
	public function DeductZEN($character_name,$zen_amount) {
		if(!check_value($character_name)) return;
		if(!check_value($zen_amount)) return;
		if(!Validator::UnsignedNumber($zen_amount)) return;
		if($zen_amount < 1) return;
		if(!$this->CharacterExists($character_name)) return;
		$characterData = $this->CharacterData($character_name);
		if(!is_array($characterData)) return;
		// OpenMU path: money stored in ItemStorage
		if(function_exists('getOpenMUCharacterMoney') && function_exists('deductOpenMUCharacterMoney')) {
			$cidRow = $this->muonline->query_fetch_single("SELECT "._CLMN_CHAR_ID_." AS cid FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." = ?", array($character_name));
			$cid = is_array($cidRow) && isset($cidRow['cid']) ? $cidRow['cid'] : null;
			// Only proceed in OpenMU path if we have a valid UUID
			if(is_string($cid) && preg_match('/^[0-9a-fA-F\-]{36}$/', $cid)) {
				$have = getOpenMUCharacterMoney($cid);
				if($have < $zen_amount) return;
				return deductOpenMUCharacterMoney($cid, (int)$zen_amount) ? true : null;
			} else {
				// In OpenMU mode, avoid attempting legacy Character money update when no UUID
				return null;
			}
		}
		// Legacy path: deduct from character's zen column
		if(($characterData[_CLMN_CHR_ZEN_] ?? 0) < $zen_amount) return;
		$deduct = $this->muonline->query("UPDATE "._TBL_CHR_." SET "._CLMN_CHR_ZEN_." = "._CLMN_CHR_ZEN_." - ? WHERE "._CLMN_CHR_NAME_." = ?", array($zen_amount, $character_name));
		if(!$deduct) return;
		return true;
	}
	
	public function AccountCharacterIDC($username) {
		if(!check_value($username)) return;
		if(!Validator::UsernameLength($username)) return;
		if(!Validator::AlphaNumeric($username)) return;
		$data = $this->muonline->query_fetch_single("SELECT * FROM "._TBL_AC_." WHERE "._CLMN_AC_ID_." = ?", array($username));
		if(!is_array($data)) return;
		return $data[_CLMN_GAMEIDC_];
	}
	
	// To be removed (backwards compatibility)
	public function GenerateCharacterClassAvatar($code=0,$alt=true,$img_tags=true) {
		return getPlayerClassAvatar($code, $img_tags, $alt, 'tables-character-class-img');
	}
	
	public function getMasterLevelInfo($character_name) {
		if(!check_value($character_name)) return;
		if(!$this->CharacterExists($character_name)) return;
		// OpenMU: master info is on Character (MasterExperience, MasterLevelUpPoints)
		$CharInfo = $this->muonline->query_fetch_single("SELECT "._CLMN_CHR_NAME_." AS name, "._CLMN_CHAR_MASTER_EXPERIENCE_." AS master_exp, "._CLMN_CHAR_MASTER_LEVEL_UP_POINTS_." AS master_points FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." = ?", array($character_name));
		if(!is_array($CharInfo)) return;
		$level = 0;
		if(isset($CharInfo['master_exp']) && is_numeric($CharInfo['master_exp']) && function_exists('calculateOpenMUMasterLevel')) {
			$level = calculateOpenMUMasterLevel((int)$CharInfo['master_exp']);
		}
		return array(
			_CLMN_ML_NAME_ => $CharInfo['name'] ?? $character_name,
			_CLMN_ML_LVL_ => $level,
			_CLMN_ML_POINT_ => $CharInfo['master_points'] ?? 0,
		);
	}
	
	protected function _moveCharacter($character_name,$map=0,$x=125,$y=125) {
		if(!check_value($character_name)) return;
		// Choose appropriate column names for OpenMU or legacy schemas
		$mapCol = defined('_CLMN_CHR_MAP_') ? _CLMN_CHR_MAP_ : (defined('_CLMN_CHAR_MAP_ID_') ? _CLMN_CHAR_MAP_ID_ : '"CurrentMapId"');
		$xCol = defined('_CLMN_CHR_MAP_X_') ? _CLMN_CHR_MAP_X_ : (defined('_CLMN_CHAR_POSITION_X_') ? _CLMN_CHAR_POSITION_X_ : '"PositionX"');
		$yCol = defined('_CLMN_CHR_MAP_Y_') ? _CLMN_CHR_MAP_Y_ : (defined('_CLMN_CHAR_POSITION_Y_') ? _CLMN_CHAR_POSITION_Y_ : '"PositionY"');
		$move = $this->muonline->query("UPDATE "._TBL_CHR_." SET $mapCol = ?, $xCol = ?, $yCol = ? WHERE "._CLMN_CHR_NAME_." = ?", array($map, $x, $y, $character_name));
		if(!$move) return;
		return true;
	}
	
	protected function _resetMagicList($character) {
		$result = $this->muonline->query("UPDATE "._TBL_CHR_." SET "._CLMN_CHR_MAGIC_L_." = null WHERE "._CLMN_CHR_NAME_." = ?", array($character));
		if(!$result) return;
		return true;
	}
	
	protected function _getClassBaseStats($class) {
		if(!array_key_exists($class, $this->_classData)) throw new Exception(lang('error_109'));
		if(!array_key_exists('base_stats', $this->_classData[$class])) throw new Exception(lang('error_110'));
		if(!is_array($this->_classData[$class]['base_stats'])) throw new Exception(lang('error_110'));
		return $this->_classData[$class]['base_stats'];
	}
	
}