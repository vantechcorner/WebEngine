<?php
/**
 * OpenMU Rankings Class
 * Extends WebEngine CMS Rankings for OpenMU compatibility
 * 
 * @version 1.2.6
 * @author WebEngine CMS - OpenMU Integration
 * @copyright (c) 2025 WebEngine CMS, All Rights Reserved
 * 
 * Licensed under the MIT license
 * http://opensource.org/licenses/MIT
 */

require_once(__PATH_CLASSES__ . 'class.rankings.php');

class RankingsOpenMU extends Rankings {

    function __construct() {
        parent::__construct();
    }

    private function _buildExcludedCharactersList() {
        if(!is_array($this->_excludedCharacters) || empty($this->_excludedCharacters)) return '';
        $quoted = array();
        foreach($this->_excludedCharacters as $name) {
            if(!is_string($name) || $name==='') continue;
            $quoted[] = "'" . str_replace("'", "''", $name) . "'";
        }
        if(empty($quoted)) return '';
        return implode(',', $quoted);
    }

    private function mapClassNameToLegacyNumber($className) {
        if(!is_string($className)) return 0;
        $n = strtolower($className);
        $map = array(
            'dark wizard' => 0,
            'soul master' => 1,
            'grand master' => 3,
            'dark knight' => 16,
            'blade knight' => 17,
            'blade master' => 19,
            'fairy elf' => 32,
            'muse elf' => 33,
            'high elf' => 35,
            'magic gladiator' => 48,
            'duel master' => 50,
            'dark lord' => 64,
            'lord emperor' => 66,
            'summoner' => 80,
            'bloody summoner' => 81,
            'dimension master' => 83,
            'rage fighter' => 96,
            'fist master' => 98,
            'grow lancer' => 112,
            'mirage lancer' => 114,
            'rune mage' => 128,
            'rune spell master' => 129,
            'grand rune master' => 131,
        );
        return array_key_exists($n, $map) ? $map[$n] : 0;
    }

    /**
     * Get level rankings for OpenMU
     */
    public function getLevelRankings() {
        $this->mu = Connection::Database('MuOnline');

        $excludeList = $this->_buildExcludedCharactersList();
        $where = "WHERE c."._CLMN_CHAR_EXPERIENCE_." > 0";
        if($excludeList !== '') {
            $where .= " AND c."._CLMN_CHAR_NAME_." NOT IN (".$excludeList.")";
        }
        $query = "SELECT
                    c."._CLMN_CHAR_NAME_." as character_name,
                    c."._CLMN_CHAR_EXPERIENCE_." as experience,
                    c."._CLMN_CHAR_MASTER_EXPERIENCE_." as master_experience,
                    c."._CLMN_CHAR_LEVEL_UP_POINTS_." as level_up_points,
                    c."._CLMN_CHAR_MASTER_LEVEL_UP_POINTS_." as master_level_up_points,
                    cc."._CLMN_CHARACTER_CLASS_NUMBER_." as class_id,
                    cc."._CLMN_CHARACTER_CLASS_NAME_." as class_name,
                    COALESCE(gm."._CLMN_GAME_MAP_NUMBER_.", 0) as map,
                    c."._CLMN_CHAR_PK_COUNT_." as pk_count,
                    a."._CLMN_ACCOUNT_LOGIN_." as account_name
                  FROM "._TBL_CHARACTER_." c
                  INNER JOIN "._TBL_ACCOUNT_." a ON c."._CLMN_CHAR_ACCOUNT_ID_." = a."._CLMN_ACCOUNT_ID_."
                  LEFT JOIN "._TBL_CHARACTER_CLASS_." cc ON c."._CLMN_CHAR_CLASS_ID_." = cc."._CLMN_CHARACTER_CLASS_ID_."
                  LEFT JOIN "._TBL_GAME_MAP_." gm ON c."._CLMN_CHAR_MAP_ID_." = gm."._CLMN_GAME_MAP_ID_."
                  ".$where."
                  ORDER BY c."._CLMN_CHAR_EXPERIENCE_." DESC
                  LIMIT " . intval($this->_results);

        $results = $this->mu->query_fetch($query);

        if (!is_array($results)) return false;

        // Calculate levels and add to results
        $formattedResults = array();
        foreach ($results as $character) {
            $level = calculateOpenMULevel($character['experience']);
            $masterLevel = calculateOpenMUMasterLevel($character['master_experience']);

            // Format for cache: [name, class_id, level, map_id, class_number, map_number]
            $formattedResults[] = array(
                'character_name' => $character['character_name'],
                'class_id' => isset($character['class_name']) ? (int)$this->mapClassNameToLegacyNumber($character['class_name']) : (isset($character['class_id']) ? (int)$character['class_id'] : 0),
                'level' => (int)$level,
                'map' => isset($character['map']) ? (int)$character['map'] : 0,
                'pk_count' => isset($character['pk_count']) ? (int)$character['pk_count'] : 0,
                'experience' => (int)$character['experience'],
                'master_experience' => (int)$character['master_experience']
            );
        }

        // Sort by calculated level instead of experience
        usort($formattedResults, function($a, $b) {
            return $b['level'] <=> $a['level'];
        });

        return $formattedResults;
    }
    
    /**
     * Get reset rankings for OpenMU
     */
    public function getResetRankings() {
        $query = "SELECT 
                    c."._CLMN_CHAR_NAME_." as character_name,
                    c."._CLMN_CHAR_EXPERIENCE_." as experience,
                    c."._CLMN_CHAR_MASTER_EXPERIENCE_." as master_experience,
                    c."._CLMN_CHAR_CLASS_ID_." as class_id,
                    a."._CLMN_ACCOUNT_LOGIN_." as account_name,
                    cc."._CLMN_CHARACTER_CLASS_NAME_." as class_name
                  FROM "._TBL_CHARACTER_." c
                  INNER JOIN "._TBL_ACCOUNT_." a ON c."._CLMN_CHAR_ACCOUNT_ID_." = a."._CLMN_ACCOUNT_ID_."
                  LEFT JOIN "._TBL_CHARACTER_CLASS_." cc ON c."._CLMN_CHAR_CLASS_ID_." = cc."._CLMN_CHARACTER_CLASS_ID_."
                  WHERE c."._CLMN_CHAR_EXPERIENCE_." > 0
                  AND c."._CLMN_CHAR_NAME_." NOT IN (".$this->_rankingsExcludeCharacters().")
                  ORDER BY c."._CLMN_CHAR_EXPERIENCE_." DESC
                  LIMIT " . intval($this->_results);
        
        $results = $this->mu->query_fetch($query);
        
        if (!is_array($results)) return false;
        
        // Calculate resets and sort by reset count
        foreach ($results as &$character) {
            $character['level'] = calculateOpenMULevel($character['experience']);
            $character['resets'] = getOpenMUCharacterResets($character['character_name']);
        }
        
        // Sort by reset count
        usort($results, function($a, $b) {
            return $b['resets'] - $a['resets'];
        });
        
        return array_slice($results, 0, $this->_results);
    }
    
    /**
     * Get grand reset rankings for OpenMU
     */
    public function getGrandResetRankings() {
        $query = "SELECT 
                    c."._CLMN_CHAR_NAME_." as character_name,
                    c."._CLMN_CHAR_EXPERIENCE_." as experience,
                    c."._CLMN_CHAR_MASTER_EXPERIENCE_." as master_experience,
                    c."._CLMN_CHAR_CLASS_ID_." as class_id,
                    a."._CLMN_ACCOUNT_LOGIN_." as account_name,
                    cc."._CLMN_CHARACTER_CLASS_NAME_." as class_name
                  FROM "._TBL_CHARACTER_." c
                  INNER JOIN "._TBL_ACCOUNT_." a ON c."._CLMN_CHAR_ACCOUNT_ID_." = a."._CLMN_ACCOUNT_ID_."
                  LEFT JOIN "._TBL_CHARACTER_CLASS_." cc ON c."._CLMN_CHAR_CLASS_ID_." = cc."._CLMN_CHARACTER_CLASS_ID_."
                  WHERE c."._CLMN_CHAR_MASTER_EXPERIENCE_." > 0
                  AND c."._CLMN_CHAR_NAME_." NOT IN (".$this->_rankingsExcludeCharacters().")
                  ORDER BY c."._CLMN_CHAR_MASTER_EXPERIENCE_." DESC
                  LIMIT " . intval($this->_results);
        
        $results = $this->mu->query_fetch($query);
        
        if (!is_array($results)) return false;
        
        // Calculate grand resets and sort by grand reset count
        foreach ($results as &$character) {
            $character['master_level'] = calculateOpenMUMasterLevel($character['master_experience']);
            $character['grand_resets'] = getOpenMUCharacterGrandResets($character['character_name']);
        }
        
        // Sort by grand reset count
        usort($results, function($a, $b) {
            return $b['grand_resets'] - $a['grand_resets'];
        });
        
        return array_slice($results, 0, $this->_results);
    }
    
    /**
     * Get guild rankings for OpenMU
     */
    public function getGuildRankings() {
        $query = "SELECT 
                    g."._CLMN_GUILD_NAME_." as guild_name,
                    g."._CLMN_GUILD_SCORE_." as score,
                    g."._CLMN_GUILD_HOST_ID_." as host_id,
                    g."._CLMN_GUILD_LOGO_." as logo,
                    g."._CLMN_GUILD_NOTICE_." as notice,
                    c."._CLMN_CHAR_NAME_." as master_name
                  FROM "._TBL_GUILD_." g
                  LEFT JOIN "._TBL_CHARACTER_." c ON g."._CLMN_GUILD_HOST_ID_." = c."._CLMN_CHAR_ID_."
                  WHERE g."._CLMN_GUILD_NAME_." NOT IN (".$this->_rankingsExcludeGuilds().")
                  ORDER BY g."._CLMN_GUILD_SCORE_." DESC
                  LIMIT " . intval($this->_results);
        
        $results = $this->mu->query_fetch($query);
        
        if (!is_array($results)) return false;
        
        // Add member count and convert logo
        foreach ($results as &$guild) {
            $guild['member_count'] = getOpenMUGuildMemberCount($guild['guild_name']);
            $guild['logo_hex'] = convertOpenMUGuildLogoToHex($guild['logo']);
        }
        
        return $results;
    }
    
    /**
     * Get PvP (Player Kill) rankings for OpenMU
     */
    public function getPvPRankings() {
        // Include characters with zero PK for OpenMU default datasets
        $this->mu = Connection::Database('MuOnline');

        $excludeList = $this->_buildExcludedCharactersList();
        $where = "WHERE 1=1";
        if($excludeList !== '') {
            $where .= " AND c."._CLMN_CHAR_NAME_." NOT IN (".$excludeList.")";
        }

        $query = "SELECT
                    c."._CLMN_CHAR_NAME_." as character_name,
                    c."._CLMN_CHAR_PK_COUNT_." as pk_count,
                    c."._CLMN_CHAR_EXPERIENCE_." as experience,
                    COALESCE(cc."._CLMN_CHARACTER_CLASS_NUMBER_.", 0) as class_id,
                    COALESCE(gm."._CLMN_GAME_MAP_NUMBER_.", 0) as map
                  FROM "._TBL_CHARACTER_." c
                  LEFT JOIN "._TBL_CHARACTER_CLASS_." cc ON c."._CLMN_CHAR_CLASS_ID_." = cc."._CLMN_CHARACTER_CLASS_ID_."
                  LEFT JOIN "._TBL_GAME_MAP_." gm ON c."._CLMN_CHAR_MAP_ID_." = gm."._CLMN_GAME_MAP_ID_."
                  ".$where."
                  ORDER BY c."._CLMN_CHAR_PK_COUNT_." DESC, c."._CLMN_CHAR_EXPERIENCE_." DESC
                  LIMIT " . intval($this->_results);

        $results = $this->mu->query_fetch($query);

        if (!is_array($results)) return false;

        // Format for cache: [name, class_id, pk_count, level, map_id, pk_level]
        $formattedResults = array();
        foreach ($results as $character) {
            $level = calculateOpenMULevel($character['experience']);

            $formattedResults[] = array(
                'character_name' => $character['character_name'],
                'class_id' => isset($character['class_id']) ? (int)$character['class_id'] : 0,
                'pk_count' => isset($character['pk_count']) ? (int)$character['pk_count'] : 0,
                'level' => (int)$level,
                'map' => isset($character['map']) ? (int)$character['map'] : 0,
            );
        }

        return $formattedResults;
    }
    
    /**
     * Get online characters for OpenMU
     */
    public function getOnlineRankings() {
        $query = "SELECT 
                    c."._CLMN_CHAR_NAME_." as character_name,
                    c."._CLMN_CHAR_EXPERIENCE_." as experience,
                    c."._CLMN_CHAR_CLASS_ID_." as class_id,
                    c."._CLMN_CHAR_STATE_." as state,
                    a."._CLMN_ACCOUNT_LOGIN_." as account_name,
                    cc."._CLMN_CHARACTER_CLASS_NAME_." as class_name
                  FROM "._TBL_CHARACTER_." c
                  INNER JOIN "._TBL_ACCOUNT_." a ON c."._CLMN_CHAR_ACCOUNT_ID_." = a."._CLMN_ACCOUNT_ID_."
                  LEFT JOIN "._TBL_CHARACTER_CLASS_." cc ON c."._CLMN_CHAR_CLASS_ID_." = cc."._CLMN_CHARACTER_CLASS_ID_."
                  WHERE c."._CLMN_CHAR_STATE_." > 0
                  AND c."._CLMN_CHAR_NAME_." NOT IN (".$this->_rankingsExcludeCharacters().")
                  ORDER BY c."._CLMN_CHAR_EXPERIENCE_." DESC
                  LIMIT " . intval($this->_results);
        
        $results = $this->mu->query_fetch($query);
        
        if (!is_array($results)) return false;
        
        // Add level information
        foreach ($results as &$character) {
            $character['level'] = calculateOpenMULevel($character['experience']);
            $character['is_online'] = true;
        }
        
        return $results;
    }
    
    /**
     * Get vote rankings for OpenMU (if vote system is implemented)
     */
    public function getVoteRankings() {
        // This would need to be implemented based on your vote system
        // For now, return empty array
        return array();
    }
    
    /**
     * Override parent method to use PostgreSQL syntax for level rankings
     */
    private function _getLevelRankingData($combineMasterLevel=false) {
        $this->mu = Connection::Database('MuOnline');

        // level only (no master level)
        if(!$combineMasterLevel) {
            $result = $this->mu->query_fetch("SELECT "._CLMN_CHR_NAME_.","._CLMN_CHR_CLASS_.","._CLMN_CHR_LVL_.","._CLMN_CHR_MAP_." FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." NOT IN(".$this->_rankingsExcludeChars().") ORDER BY "._CLMN_CHR_LVL_." DESC LIMIT " . intval($this->_results));
            if(!is_array($result)) return;
            return $result;
        }

        if(_TBL_CHR_ == _TBL_MASTERLVL_) {

            // level + master level (in same table)
            $result = $this->mu->query_fetch("SELECT "._CLMN_CHR_NAME_.","._CLMN_CHR_CLASS_.",("._CLMN_CHR_LVL_."+"._CLMN_ML_LVL_.") as "._CLMN_CHR_LVL_.","._CLMN_CHR_MAP_." FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." NOT IN(".$this->_rankingsExcludeChars().") ORDER BY "._CLMN_CHR_LVL_." DESC LIMIT " . intval($this->_results));
            if(!is_array($result)) return;
            return $result;
        } else {

            // level + master level (different tables)
            $Character = new Character();
            $characters = $this->mu->query_fetch("SELECT "._CLMN_CHR_NAME_.","._CLMN_CHR_CLASS_.","._CLMN_CHR_LVL_.","._CLMN_CHR_MAP_." FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." NOT IN(".$this->_rankingsExcludeChars().") ORDER BY "._CLMN_CHR_LVL_." DESC");
            if(!is_array($characters)) return;
            foreach($characters as $row) {
                $masterLevelInfo = $Character->getMasterLevelInfo($row[_CLMN_CHR_NAME_]);
                $rankingData[] = array(
                    _CLMN_CHR_NAME_ => $row[_CLMN_CHR_NAME_],
                    _CLMN_CHR_CLASS_ => $row[_CLMN_CHR_CLASS_],
                    _CLMN_CHR_LVL_ => $row[_CLMN_CHR_LVL_]+$masterLevelInfo[_CLMN_ML_LVL_],
                    _CLMN_CHR_MAP_ => $row[_CLMN_CHR_MAP_],
                );
            }

            usort($rankingData, function($a, $b) {
                return $b[_CLMN_CHR_LVL_] - $a[_CLMN_CHR_LVL_];
            });

            $result = array_slice($rankingData, 0, $this->_results);
            if(!is_array($result)) return;
            return $result;
        }
    }

    /**
     * Override parent method to use PostgreSQL syntax for killers rankings
     */
    private function _getKillersRankingData($combineMasterLevel=false) {
        $this->mu = Connection::Database('MuOnline');

        // level only (no master level)
        if(!$combineMasterLevel) {
            $result = $this->mu->query_fetch("SELECT "._CLMN_CHR_NAME_.","._CLMN_CHR_CLASS_.","._CLMN_CHR_PK_KILLS_.","._CLMN_CHR_LVL_.","._CLMN_CHR_MAP_.","._CLMN_CHR_PK_LEVEL_." FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." NOT IN(".$this->_rankingsExcludeChars().") AND "._CLMN_CHR_PK_KILLS_." > 0 ORDER BY "._CLMN_CHR_PK_KILLS_." DESC LIMIT " . intval($this->_results));
            if(!is_array($result)) return;
            return $result;
        }

        if(_TBL_CHR_ == _TBL_MASTERLVL_) {
            // level + master level (in same table)
            $result = $this->mu->query_fetch("SELECT "._CLMN_CHR_NAME_.","._CLMN_CHR_CLASS_.","._CLMN_CHR_PK_KILLS_.",("._CLMN_CHR_LVL_."+"._CLMN_ML_LVL_.") as "._CLMN_CHR_LVL_.","._CLMN_CHR_MAP_.","._CLMN_CHR_PK_LEVEL_." FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." NOT IN(".$this->_rankingsExcludeChars().") AND "._CLMN_CHR_PK_KILLS_." > 0 ORDER BY "._CLMN_CHR_PK_KILLS_." DESC LIMIT " . intval($this->_results));
            if(!is_array($result)) return;
            return $result;
        } else {
            // level + master level (different tables)
            $Character = new Character();
            $result = $this->mu->query_fetch("SELECT "._CLMN_CHR_NAME_.","._CLMN_CHR_CLASS_.","._CLMN_CHR_PK_KILLS_.","._CLMN_CHR_LVL_.","._CLMN_CHR_MAP_.","._CLMN_CHR_PK_LEVEL_." FROM "._TBL_CHR_." WHERE "._CLMN_CHR_NAME_." NOT IN(".$this->_rankingsExcludeChars().") AND "._CLMN_CHR_PK_KILLS_." > 0 ORDER BY "._CLMN_CHR_PK_KILLS_." DESC");
            if(!is_array($result)) return;
            foreach($result as $key => $row) {
                $masterLevelInfo = $Character->getMasterLevelInfo($row[_CLMN_CHR_NAME_]);
                if(!is_array($masterLevelInfo)) continue;
                $result[$key][_CLMN_CHR_LVL_] = $row[_CLMN_CHR_LVL_]+$masterLevelInfo[_CLMN_ML_LVL_];
            }
            return $result;
        }
    }

    /**
     * Override parent method to use OpenMU-specific rankings
     */
    public function loadRankings($type) {
        switch($type) {
            case 'level':
                return $this->getLevelRankings();
            case 'resets':
                return $this->getResetRankings();
            case 'grandresets':
                return $this->getGrandResetRankings();
            case 'guilds':
                return $this->getGuildRankings();
            case 'killers':
                return $this->getPvPRankings();
            case 'online':
                return $this->getOnlineRankings();
            case 'votes':
                return $this->getVoteRankings();
            default:
                return parent::loadRankings($type);
        }
    }
}

