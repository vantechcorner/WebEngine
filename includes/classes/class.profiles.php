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

class weProfiles {
	
	private $_request;
	private $_type;
	
	private $_reqMaxLen;
	private $_guildsCachePath;
	private $_playersCachePath;
	private $_cacheUpdateTime;
	
	private $_fileData;
	
	protected $common;
	protected $dB;
	protected $cfg;
	protected $serverFiles;
	
	function __construct() {
		
		# database
		$this->common = new common();
		$this->dB = Connection::Database('MuOnline');
		
		# settings
		$this->_guildsCachePath = __PATH_CACHE__ . 'profiles/guilds/';
		$this->_playersCachePath = __PATH_CACHE__ . 'profiles/players/';
		$this->_cacheUpdateTime = 300;
		$config = webengineConfigs();
		$this->serverFiles = strtolower($config['server_files']);
		if($this->serverFiles === 'openmu') {
			// refresh more frequently for OpenMU while integrating
			$this->_cacheUpdateTime = 1;
		}
		
		# check cache directories
		$this->checkCacheDir($this->_guildsCachePath);
		$this->checkCacheDir($this->_playersCachePath);
		
		# configs
		$profileConfig = loadConfigurations('profiles');
		if(!is_array($profileConfig)) throw new Exception(lang('error_25',true));
		$this->cfg = $profileConfig;
		
	}
	
	public function setType($input) {
		switch($input) {
			case "guild":
				$this->_type = "guild";
				$this->_reqMaxLen = 8;
				break;
			default:
				$this->_type = "player";
				$this->_reqMaxLen = 10;
		}
	}
	
	public function setRequest($input) {
		if(array_key_exists('encode', $this->cfg) && $this->cfg['encode'] == 1) {
			if(!Validator::Chars($input, array('a-z', 'A-Z', '0-9', '_', '-'))) throw new Exception(lang('error_25',true));
			$decodedReq = base64url_decode($input);
			if($decodedReq == false) throw new Exception(lang('error_25',true));
			$this->_request = $decodedReq;
			return true;
		}
		
		if(!Validator::AlphaNumeric($input)) throw new Exception(lang('error_25',true));
		if(strlen($input) > $this->_reqMaxLen) throw new Exception(lang('error_25',true));
		if(strlen($input) < 4) throw new Exception(lang('error_25',true));
		
		$this->_request = $input;
	}
	
	private function checkCacheDir($path) {
		if(check_value($path)) {
			if(!file_exists($path) || !is_dir($path)) {
				if(config('error_reporting',true)) {
					throw new Exception("Invalid cache directory ($path)");
				} else {
					throw new Exception(lang('error_21',true));
				}
			} else {
				if(!is_writable($path)) {
					if(config('error_reporting',true)) {
						throw new Exception("The cache directory is not writable ($path)");
					} else {
						throw new Exception(lang('error_21',true));
					}
				}
			}
		}
	}
	
	private function checkCache() {
		switch($this->_type) {
			case "guild":
				$reqFile = $this->_guildsCachePath . strtolower($this->_request) . '.cache';
				if(!file_exists($reqFile)) {
					$this->cacheGuildData();
				}
				$fileData = file_get_contents($reqFile);
				$fileData = explode("|", $fileData);
				if(is_array($fileData)) {
					if(time() > ($fileData[0]+$this->_cacheUpdateTime)) {
						$this->cacheGuildData();
					}
				} else {
					throw new Exception(lang('error_21',true));
				}
				$this->_fileData = file_get_contents($reqFile);
				break;
			default:
				$reqFile = $this->_playersCachePath . strtolower($this->_request) . '.cache';
				// For OpenMU always rebuild to ensure fresh stats mapping
				if($this->serverFiles === 'openmu') {
					$this->cachePlayerData();
					$this->_fileData = file_get_contents($reqFile);
					break;
				}
				if(!file_exists($reqFile)) {
					$this->cachePlayerData();
				}
				$fileData = file_get_contents($reqFile);
				$fileData = explode("|", $fileData);
				if(is_array($fileData)) {
					if(time() > ($fileData[0]+$this->_cacheUpdateTime)) {
						$this->cachePlayerData();
					}
				} else {
					throw new Exception(lang('error_21',true));
				}
				$this->_fileData = file_get_contents($reqFile);
		}
	}
	
	private function cacheGuildData() {
		// General Data
		$guildData = $this->dB->query_fetch_single("SELECT *, CONVERT(varchar(max), "._CLMN_GUILD_LOGO_.", 2) as "._CLMN_GUILD_LOGO_." FROM "._TBL_GUILD_." WHERE "._CLMN_GUILD_NAME_." = ?", array($this->_request));
		if(!$guildData) throw new Exception(lang('error_25',true));
		
		// Members
		$guildMembers = $this->dB->query_fetch("SELECT * FROM "._TBL_GUILDMEMB_." WHERE "._CLMN_GUILDMEMB_NAME_." = ?", array($this->_request));
		if(!$guildMembers) throw new Exception(lang('error_25',true));
		$members = array();
		foreach($guildMembers as $gmember) {
			$members[] = $gmember[_CLMN_GUILDMEMB_CHAR_];
		}
		$gmembers_str = implode(",", $members);
		
		// Cache
		$data = array(
			time(),
			$guildData[_CLMN_GUILD_NAME_],
			$guildData[_CLMN_GUILD_LOGO_],
			$guildData[_CLMN_GUILD_SCORE_],
			$guildData[_CLMN_GUILD_MASTER_],
			$gmembers_str
		);
		
		// Cache Ready Data
		$cacheData = implode("|", $data);
		
		// Update Cache File
		$reqFile = $this->_guildsCachePath . strtolower($this->_request) . '.cache';
		$fp = fopen($reqFile, 'w+');
		fwrite($fp, $cacheData);
		fclose($fp);
	}
	
	private function cachePlayerData() {
		// OpenMU: custom profile cache build
		$config = webengineConfigs();
		if(strtolower($config['server_files']) == 'openmu') {
			// fetch minimal fields and class number
			$sql = "SELECT c."._CLMN_CHAR_ID_." as cid,
						c."._CLMN_CHAR_NAME_." as name,
						c."._CLMN_CHAR_EXPERIENCE_." as exp,
						c."._CLMN_CHAR_MASTER_EXPERIENCE_." as mexp,
						c."._CLMN_CHAR_PK_COUNT_." as pk,
						COALESCE(cc."._CLMN_CHARACTER_CLASS_NUMBER_.",0) as class_number,
						cc."._CLMN_CHARACTER_CLASS_NAME_." as class_name,
						c."._CLMN_CHAR_MAP_ID_." as map_id,
						c."._CLMN_CHAR_POSITION_X_." as pos_x,
						c."._CLMN_CHAR_POSITION_Y_." as pos_y
				FROM "._TBL_CHARACTER_." c
				LEFT JOIN "._TBL_CHARACTER_CLASS_." cc ON c."._CLMN_CHAR_CLASS_ID_." = cc."._CLMN_CHARACTER_CLASS_ID_."
				WHERE c."._CLMN_CHAR_NAME_." = ?";
			$row = $this->dB->query_fetch_single($sql, array($this->_request));
			if(!$row || !is_array($row)) throw new Exception(lang('error_25',true));
			$level = function_exists('calculateOpenMULevel') ? (int)calculateOpenMULevel($row['exp']) : 0;
			$masterLevel = function_exists('calculateOpenMUMasterLevel') ? (int)calculateOpenMUMasterLevel($row['mexp']) : 0;
			// guild name (optional, tolerant to schema differences)
			$guild = "";
			try {
				$g = $this->dB->query_fetch_single("SELECT "._CLMN_GUILD_MEMBER_GUILD_ID_." as gid FROM "._TBL_GUILD_MEMBER_." WHERE "._CLMN_GUILD_MEMBER_CHARACTER_ID_." = ?", array($row['cid']));
				if(is_array($g) && array_key_exists('gid',$g)) {
					$gName = $this->dB->query_fetch_single("SELECT "._CLMN_GUILD_NAME_." as name FROM "._TBL_GUILD_." WHERE "._CLMN_GUILD_ID_." = ?", array($g['gid']));
					if(is_array($gName) && array_key_exists('name',$gName)) $guild = $gName['name'];
				}
			} catch (Exception $e) {
				$guild = ""; // ignore guild if schema differs
			}
			// pull stats from StatAttribute
			// load stats via helper (handles designation variations)
			$stats = getOpenMUCharacterStats($row['cid']);
			$data = array(
				time(),
				$row['name'],
				(isset($row['class_name']) ? (int)mapOpenMUClassNameToLegacyNumber($row['class_name']) : (int)$row['class_number']),
				$level,
				0, // resets (not used in OpenMU default)
				$stats['Strength'],
				$stats['Agility'],
				$stats['Vitality'],
				$stats['Energy'],
				$stats['Leadership'],
				(int)$row['pk'],
				0, // grand resets
				$guild,
				0,
				$masterLevel,
			);
		} else {
			$Character = new Character();
			// general player data
			$playerData = $Character->CharacterData($this->_request);
			if(!$playerData) throw new Exception(lang('error_25',true));
			// master level data
			if(_TBL_MASTERLVL_ == _TBL_CHR_) {
				$playerMasterLevel = $playerData[_CLMN_ML_LVL_];
			} else {
				$masterLevelInfo = $Character->getMasterLevelInfo($this->_request);
				if(is_array($masterLevelInfo)) {
					$playerMasterLevel = $masterLevelInfo[_CLMN_ML_LVL_];
				}
			}
			// guild data
			$guild = "";
			$guildData = $this->dB->query_fetch_single("SELECT * FROM "._TBL_GUILDMEMB_." WHERE "._CLMN_GUILDMEMB_CHAR_." = ?", array($this->_request));
			if($guildData) $guild = $guildData[_CLMN_GUILDMEMB_NAME_];
			// Cache (legacy)
			$data = array(
				time(),
				$playerData[_CLMN_CHR_NAME_],
				$playerData[_CLMN_CHR_CLASS_],
				$playerData[_CLMN_CHR_LVL_],
				$playerData[_CLMN_CHR_RSTS_],
				$playerData[_CLMN_CHR_STAT_STR_],
				$playerData[_CLMN_CHR_STAT_AGI_],
				$playerData[_CLMN_CHR_STAT_VIT_],
				$playerData[_CLMN_CHR_STAT_ENE_],
				$playerData[_CLMN_CHR_STAT_CMD_],
				$playerData[_CLMN_CHR_PK_KILLS_],
				(check_value($playerData[_CLMN_CHR_GRSTS_]) ? $playerData[_CLMN_CHR_GRSTS_] : 0),
				$guild,
				0,
				check_value($playerMasterLevel) ? $playerMasterLevel : 0,
			);
		}
		
		// Cache Ready Data
		$cacheData = implode("|", $data);
		
		// Update Cache File
		$reqFile = $this->_playersCachePath . strtolower($this->_request) . '.cache';
		$fp = fopen($reqFile, 'w+');
		fwrite($fp, $cacheData);
		fclose($fp);
	}
	
	public function data() {
		if(!check_value($this->_type)) throw new Exception(lang('error_21',true));
		if(!check_value($this->_request)) throw new Exception(lang('error_21',true));
		$this->checkCache();
		return(explode("|", $this->_fileData));
	}
	
}