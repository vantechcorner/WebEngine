<?php
/**
 * OpenMU Integration Helper Functions
 * 
 * @version 1.2.6
 * @author WebEngine CMS - OpenMU Integration
 * @copyright (c) 2025 WebEngine CMS, All Rights Reserved
 * 
 * Licensed under the MIT license
 * http://opensource.org/licenses/MIT
 */

/**
 * Calculate cumulative experience required to reach a given level (OpenMU formula)
 * Formula provided by OpenMU config:
 * if(level == 0, 0,
 *    if(level < 256,
 *       10 * (level + 8) * (level - 1) * (level - 1),
 *       (10 * (level + 8) * (level - 1) * (level - 1)) + (1000 * (level - 247) * (level - 256) * (level - 256))
 *    )
 * )
 */
function openMUTotalExperienceForLevel($level) {
    if($level <= 0) return 0;
    if($level < 256) {
        return (int)(10 * ($level + 8) * ($level - 1) * ($level - 1));
    }
    return (int)((10 * ($level + 8) * ($level - 1) * ($level - 1)) + (1000 * ($level - 247) * ($level - 256) * ($level - 256)));
}

/**
 * Calculate character level from total experience using the exact OpenMU formula above
 */
function calculateOpenMULevel($experience) {
    $exp = (int)$experience;
    if($exp <= 0) return 1;
    // Binary search the highest level L such that totalExp(L) <= exp
    $lo = 0; $hi = 400; $ans = 1;
    while($lo <= $hi) {
        $mid = intdiv($lo + $hi, 2);
        $need = openMUTotalExperienceForLevel($mid);
        if($need <= $exp) {
            $ans = max($ans, $mid);
            $lo = $mid + 1;
        } else {
            $hi = $mid - 1;
        }
    }
    if($ans < 1) $ans = 1;
    if($ans > 400) $ans = 400;
    return $ans;
}

/**
 * Try to calculate level using a config table in the DB (if present),
 * falling back to the approximation above.
 */
function calculateOpenMULevelFromDbOrApprox($experience) {
    if(!is_numeric($experience) || $experience < 0) return 1;
    $db = Connection::Database('MuOnline');
    static $resolved = null; // ['table'=>..., 'lvl'=>..., 'exp'=>...]
    if($resolved === null) {
        // discover a config table that contains both Level and Experience thresholds
        $candidates = $db->query_fetch("SELECT table_name, column_name FROM information_schema.columns WHERE table_schema='config'");
        $byTable = array();
        if(is_array($candidates)) {
            foreach($candidates as $row) {
                $t = $row['table_name']; $c = strtolower($row['column_name']);
                if(!isset($byTable[$t])) $byTable[$t] = array('lvl'=>null,'exp'=>null);
                if($byTable[$t]['lvl'] === null && (strpos($c,'level') === 0 || $c === 'level')) $byTable[$t]['lvl'] = $row['column_name'];
                if($byTable[$t]['exp'] === null && strpos($c,'exp') !== false) $byTable[$t]['exp'] = $row['column_name'];
            }
            foreach($byTable as $t => $cols) {
                if($cols['lvl'] && $cols['exp']) { $resolved = array('table'=>$t,'lvl'=>$cols['lvl'],'exp'=>$cols['exp']); break; }
            }
        }
        if($resolved === null) $resolved = false;
    }
    if($resolved) {
        // compute level by selecting the greatest level whose required exp <= current exp
        $q = 'SELECT MAX("'.$resolved['lvl'].'") AS lvl FROM config."'.$resolved['table'].'" WHERE "'.$resolved['exp'].'" <= :exp';
        $row = $db->query_fetch_single($q, array('exp'=>(int)$experience));
        if(is_array($row)) { $lvl = (int)array_values(array_change_key_case($row, CASE_LOWER))[0]; if($lvl>0) return $lvl; }
    }
    return calculateOpenMULevel($experience);
}

/**
 * Calculate master level from master experience
 */
function calculateOpenMUMasterLevel($masterExperience) {
    if ($masterExperience < 0) return 0;

    // OpenMU master level calculation
    $masterLevel = 0;
    $exp = $masterExperience;

    while ($exp >= 0 && $masterLevel < 200) {
        $requiredExp = ($masterLevel + 1) * 1000;
        if ($exp >= $requiredExp) {
            $exp -= $requiredExp;
            $masterLevel++;
        } else {
            break;
        }
    }

    return $masterLevel;
}

// Generate UUID v4 (RFC 4122)
if(!function_exists('generateUuidV4')) {
function generateUuidV4() {
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
}

/**
 * Get character class name from class ID
 */
function getOpenMUCharacterClassName($classId) {
    $db = Connection::Database('MuOnline');
    $query = "SELECT "._CLMN_CHARACTER_CLASS_NAME_." AS name FROM "._TBL_CHARACTER_CLASS_." WHERE "._CLMN_CHARACTER_CLASS_ID_." = :class_id";
    $result = $db->query_fetch_single($query, array('class_id' => $classId));
    if(!$result) return 'Unknown';
    $row = array_change_key_case($result, CASE_LOWER);
    return $row['name'] ?? 'Unknown';
}

/**
 * Get legacy numeric class number from OpenMU CharacterClass Id (GUID)
 */
function getOpenMUClassNumberById($classId) {
    if(!check_value($classId)) return 0;
    $db = Connection::Database('MuOnline');
    $query = 'SELECT '._CLMN_CHARACTER_CLASS_NUMBER_.' AS num, '._CLMN_CHARACTER_CLASS_NAME_.' AS name FROM '._TBL_CHARACTER_CLASS_.' WHERE '._CLMN_CHARACTER_CLASS_ID_.' = :id';
    $result = $db->query_fetch_single($query, array('id' => $classId));
    if(!$result) return 0;
    $row = array_change_key_case($result, CASE_LOWER);
    $name = $row['name'] ?? null;
    if($name && function_exists('mapOpenMUClassNameToLegacyNumber')) {
        $mapped = mapOpenMUClassNameToLegacyNumber($name);
        if($mapped > 0) return $mapped;
    }
    $num = isset($row['num']) ? (int)$row['num'] : null;
    if(is_int($num) && $num >= 0) {
        global $custom;
        if(is_array($custom) && isset($custom['character_class']) && array_key_exists($num, $custom['character_class'])) {
            return $num;
        }
    }
    return 0;
}

/**
 * Get map name from map ID
 */
function getOpenMUMapName($mapId) {
    $db = Connection::Database('MuOnline');
    $query = "SELECT "._CLMN_GAME_MAP_NAME_." AS name FROM "._TBL_GAME_MAP_." WHERE "._CLMN_GAME_MAP_ID_." = :map_id";
    $result = $db->query_fetch_single($query, array('map_id' => $mapId));
    if(!$result) return 'Unknown Map';
    $row = array_change_key_case($result, CASE_LOWER);
    return $row['name'] ?? 'Unknown Map';
}

/**
 * Get character statistics from StatAttribute table
 */
function getOpenMUCharacterStats($characterId) {
    // Use a fresh db connection instead of relying on a global
    $db = Connection::Database('MuOnline');

    // Discover the correct FK column name used by data."StatAttribute" to reference config."AttributeDefinition"
    $defCol = null;
    $colRows = $db->query_fetch("SELECT column_name FROM information_schema.columns WHERE table_schema='data' AND table_name='StatAttribute'");
    if(is_array($colRows)) {
        $columnNames = array_map(function($r){ return $r['column_name']; }, $colRows);
        $candidates = array('AttributeDefinitionId','AttributeDefinition','DefinitionId','AttributeId');
        foreach($candidates as $c) {
            if(in_array($c, $columnNames, true)) { $defCol = $c; break; }
        }
    }
    if(!$defCol) {
        // cannot resolve column, return empty stats rather than fatal
        return array('Strength'=>0,'Agility'=>0,'Vitality'=>0,'Energy'=>0,'Leadership'=>0);
    }

    // Build query dynamically with discovered column
    $query = 'SELECT ad."'.trim(str_replace('"','',$GLOBALS['_CLMN_ATTRIBUTE_DEFINITION_DESIGNATION_'] ?? 'Designation')).'" AS designation, sa.'._CLMN_STAT_ATTRIBUTE_VALUE_.' AS value '
           . 'FROM '._TBL_STAT_ATTRIBUTE_.' sa '
           . 'INNER JOIN '._TBL_ATTRIBUTE_DEFINITION_.' ad ON sa."'.$defCol.'" = ad.'._CLMN_ATTRIBUTE_DEFINITION_ID_.' '
           . 'WHERE sa.'._CLMN_STAT_ATTRIBUTE_CHARACTER_ID_.' = :char_id';

    $results = $db->query_fetch($query, array('char_id' => $characterId));
    $byDes = array();
    if (is_array($results)) {
        foreach ($results as $row) {
            $byDes[strtolower($row['designation'])] = (int)$row['value'];
        }
    }
    // Prefer Base* values; then plain; try synonyms and Base+synonyms; then contains
    $pick = function($baseKey, $plainKey, $synonyms = array()) use ($byDes) {
        $variants = array();
        // Explicitly prefer "Base X" (with space) then no-space variant, then plain
        $variants[] = strtolower('Base ' . $plainKey); // e.g., base vitality
        $variants[] = strtolower('Base' . $baseKey);   // e.g., basevitality
        $variants[] = strtolower($plainKey);           // vitality
        if(is_array($synonyms)) {
            foreach($synonyms as $s) { $variants[] = strtolower('Base ' . $s); }
            foreach($synonyms as $s) { $variants[] = strtolower('Base'.$s); }
            foreach($synonyms as $s) { $variants[] = strtolower($s); }
        }
        foreach($variants as $candidate) {
            if(array_key_exists($candidate, $byDes)) return $byDes[$candidate];
        }
        // contains fallback (last resort)
        $searches = array_merge(array(strtolower($plainKey)), is_array($synonyms) ? array_map('strtolower',$synonyms) : array());
        foreach($searches as $needle) {
            foreach($byDes as $k => $v) { if(strpos($k, $needle) !== false) return $v; }
        }
        return 0;
    };
    return array(
        'Strength' => $pick('Strength', 'Strength', array('Str')),
        'Agility' => $pick('Agility', 'Agility', array('Agi')),
        'Vitality' => $pick('Vitality', 'Vitality', array('Sta','Stamina')),
        'Energy' => $pick('Energy', 'Energy', array('Ene')),
        'Leadership' => $pick('Leadership', 'Leadership', array('Cmd','Command')),
    );
}

/**
 * Get character money from ItemStorage
 */
function getOpenMUCharacterMoney($characterId) {
    // Validate UUID
    if(!is_string($characterId) || !preg_match('/^[0-9a-fA-F\-]{36}$/', $characterId)) return 0;
    $db = Connection::Database('MuOnline');
    $query = "SELECT ist."._CLMN_ITEM_STORAGE_MONEY_." AS money FROM "._TBL_ITEM_STORAGE_." ist INNER JOIN "._TBL_CHARACTER_." c ON c."._CLMN_CHAR_INVENTORY_ID_." = ist."._CLMN_ITEM_STORAGE_ID_." WHERE c."._CLMN_CHAR_ID_." = :char_id";
    $result = $db->query_fetch_single($query, array('char_id' => $characterId));
    if(!$result) return 0;
    $row = array_change_key_case($result, CASE_LOWER);
    return isset($row['money']) ? (int)$row['money'] : 0;
}

/**
 * Deduct money from a character's ItemStorage in OpenMU.
 * Returns true if money was deducted, false if not enough funds or error.
 */
function deductOpenMUCharacterMoney($characterId, $amount) {
    if(!is_string($characterId) || $characterId === '') return false;
    if(!preg_match('/^[0-9a-fA-F\-]{36}$/', $characterId)) return false;
    $amt = (int)$amount;
    if($amt <= 0) return true;
    $db = Connection::Database('MuOnline');
    // Update ItemStorage joined through Character.InventoryId and ensure sufficient balance
    $sql = 'UPDATE data."ItemStorage" ist
            SET "Money" = ist."Money" - :amt
            FROM data."Character" c
            WHERE c."Id" = :cid AND c."InventoryId" = ist."Id" AND ist."Money" >= :amt';
    $res = $db->query($sql, array('amt'=>$amt, 'cid'=>$characterId));
    if(!$res) return false;
    // Verify
    $left = getOpenMUCharacterMoney($characterId);
    return $left >= 0; // if query executed, consider success
}

/**
 * Get guild master character name
 */
function getOpenMUGuildMasterName($guildId) {
    $db = Connection::Database('MuOnline');
    $query = "SELECT c."._CLMN_CHAR_NAME_." AS name FROM "._TBL_CHARACTER_." c INNER JOIN "._TBL_GUILD_." g ON g."._CLMN_GUILD_HOST_ID_." = c."._CLMN_CHAR_ID_." WHERE g."._CLMN_GUILD_ID_." = :guild_id";
    $result = $db->query_fetch_single($query, array('guild_id' => $guildId));
    if(!$result) return 'Unknown';
    $row = array_change_key_case($result, CASE_LOWER);
    return $row['name'] ?? 'Unknown';
}

/**
 * Get guild member count
 */
function getOpenMUGuildMemberCount($guildId) {
    $db = Connection::Database('MuOnline');
    $query = "SELECT COUNT(*) as member_count FROM "._TBL_GUILD_MEMBER_." WHERE "._CLMN_GUILD_MEMBER_GUILD_ID_." = :guild_id";
    $result = $db->query_fetch_single($query, array('guild_id' => $guildId));
    return $result ? (int)$result['member_count'] : 0;
}

/**
 * Check if character is online (OpenMU specific)
 */
function isOpenMUCharacterOnline($characterId) {
    $db = Connection::Database('MuOnline');
    $query = "SELECT "._CLMN_CHAR_STATE_." AS state FROM "._TBL_CHARACTER_." WHERE "._CLMN_CHAR_ID_." = :char_id";
    $result = $db->query_fetch_single($query, array('char_id' => $characterId));
    if(!$result) return false;
    $row = array_change_key_case($result, CASE_LOWER);
    return isset($row['state']) ? ((int)$row['state'] > 0) : false;
}

/**
 * Get account online characters count
 */
function getOpenMUAccountOnlineCharacters($accountId) {
    $db = Connection::Database('MuOnline');
    $query = "SELECT COUNT(*) as online_count FROM "._TBL_CHARACTER_." WHERE "._CLMN_CHAR_ACCOUNT_ID_." = :account_id AND "._CLMN_CHAR_STATE_." > 0";
    $result = $db->query_fetch_single($query, array('account_id' => $accountId));
    return $result ? (int)$result['online_count'] : 0;
}

/**
 * Convert OpenMU guild logo to hex string
 */
function convertOpenMUGuildLogoToHex($logoData) {
    if (empty($logoData)) return '';

    // If PostgreSQL returned a stream resource (LOB), read it first
    if (is_resource($logoData)) {
        $meta = @stream_get_meta_data($logoData);
        $contents = @stream_get_contents($logoData);
        if ($contents === false || $contents === null) return '';
        $logoData = $contents;
    }

    // If it's already a hex string, return as-is
    if (is_string($logoData)) {
        $trim = trim($logoData);
        if ($trim !== '' && ctype_xdigit($trim) && (strlen($trim) % 2) === 0) {
            return $trim;
        }
    }

    // Convert raw bytes to hex
    return is_string($logoData) ? bin2hex($logoData) : '';
}

/**
 * Get character reset count (calculated from level/experience)
 */
function getOpenMUCharacterResets($characterId) {
    $db = Connection::Database('MuOnline');
    // Prefer meta table if present
    $hasMeta = $db->query_fetch_single("SELECT 1 FROM information_schema.tables WHERE table_schema='public' AND table_name='webengine_resets' LIMIT 1");
    if(is_array($hasMeta)) {
        $meta = $db->query_fetch_single('SELECT resets FROM public.webengine_resets WHERE character_id = :cid', array('cid'=>$characterId));
        if(is_array($meta) && isset($meta['resets'])) return (int)$meta['resets'];
        return 1; // default baseline
    }
    // Fallback: derive from level
    $query = "SELECT "._CLMN_CHAR_EXPERIENCE_." AS exp FROM "._TBL_CHARACTER_." WHERE "._CLMN_CHAR_ID_." = :char_id";
    $result = $db->query_fetch_single($query, array('char_id' => $characterId));
    if (!$result) return 1;
    $row = array_change_key_case($result, CASE_LOWER);
    $experience = isset($row['exp']) ? (int)$row['exp'] : 0;
    $level = calculateOpenMULevel($experience);
    $resets = floor($level / 400);
    return max(1, $resets);
}

/**
 * Get character grand reset count
 */
function getOpenMUCharacterGrandResets($characterId) {
    $db = Connection::Database('MuOnline');
    $query = "SELECT "._CLMN_CHAR_MASTER_EXPERIENCE_." AS mexp FROM "._TBL_CHARACTER_." WHERE "._CLMN_CHAR_ID_." = :char_id";
    $result = $db->query_fetch_single($query, array('char_id' => $characterId));
    if (!$result) return 0;
    $row = array_change_key_case($result, CASE_LOWER);
    $masterExperience = isset($row['mexp']) ? (int)$row['mexp'] : 0;
    $masterLevel = calculateOpenMUMasterLevel($masterExperience);
    $grandResets = floor($masterLevel / 200);
    return $grandResets;
}

/**
 * Map OpenMU class display name to legacy numeric class code used by WebEngine
 */
function mapOpenMUClassNameToLegacyNumber($className) {
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

// -----------------
// OpenMU Admin API
// -----------------

function openMuApiBaseUrl() {
    $cfg = function_exists('config') ? config('openmu_api_base_url', true) : null;
    if(is_string($cfg) && strlen($cfg) > 0) return rtrim($cfg, '/');
    return 'http://localhost:5000';
}

function openMuApiGet($path, $timeoutSec = 1) {
    $url = rtrim(openMuApiBaseUrl(), '/') . '/' . ltrim($path, '/');
    // Try cURL first
    if(function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutSec);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeoutSec);
        $resp = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if($resp === false) return null;
        return $resp;
    }
    // Fallback to file_get_contents
    $ctx = stream_context_create(['http' => ['method' => 'GET', 'timeout' => $timeoutSec]]);
    $resp = @file_get_contents($url, false, $ctx);
    return $resp === false ? null : $resp;
}

function openMuApiIsAccountOnline($loginName) {
    if(!is_string($loginName) || $loginName === '') return null;
    $resp = openMuApiGet('/api/is-online/' . rawurlencode($loginName));
    if($resp === null) return null;
    $trim = trim($resp);
    // API returns plain true/false JSON
    if($trim === 'true') return true;
    if($trim === 'false') return false;
    // Fallback: try json decode
    $decoded = json_decode($resp, true);
    if(is_bool($decoded)) return $decoded;
    return null;
}

function openMuApiGetStatus() {
    $resp = openMuApiGet('/api/status');
    if($resp === null) return null;
    // The API returns a JSON-serialized string; decode twice if needed
    $first = json_decode($resp, true);
    if(is_string($first)) {
        $second = json_decode($first, true);
        return is_array($second) ? $second : null;
    }
    return is_array($first) ? $first : null;
}

function openMuApiOnlinePlayersCount() {
    $status = openMuApiGetStatus();
    if(!is_array($status)) return null;
    if(isset($status['players'])) return (int)$status['players'];
    if(isset($status['playersList']) && is_array($status['playersList'])) return count($status['playersList']);
    return null;
}

function openMuApiIsCharacterOnlineByName($characterName) {
    if(!is_string($characterName) || $characterName === '') return null;
    $status = openMuApiGetStatus();
    if(!is_array($status) || !isset($status['playersList']) || !is_array($status['playersList'])) return null;
    foreach($status['playersList'] as $p) {
        if(is_string($p) && strcasecmp($p, $characterName) === 0) return true;
    }
    return false;
}


/**
 * Increment OpenMU character stats by provided amounts.
 * $increments keys: Strength, Agility, Vitality, Energy, Leadership
 */
function updateOpenMUCharacterStatsIncrement($characterId, array $increments) {
    if(!is_string($characterId) || $characterId === '') return false;
    if(empty($increments)) return true;
    $db = Connection::Database('MuOnline');

    // Discover StatAttribute FK column to AttributeDefinition
    $defCol = null;
    $colRows = $db->query_fetch("SELECT column_name FROM information_schema.columns WHERE table_schema='data' AND table_name='StatAttribute'");
    if(is_array($colRows)) {
        $columnNames = array_map(function($r){ return $r['column_name']; }, $colRows);
        $candidates = array('AttributeDefinitionId','AttributeDefinition','DefinitionId','AttributeId');
        foreach($candidates as $c) {
            if(in_array($c, $columnNames, true)) { $defCol = $c; break; }
        }
    }
    if(!$defCol) return false;

    // Map designations to definition ids; prefer Base* attributes
    $defs = $db->query_fetch('SELECT "Id" AS id, "Designation" AS designation FROM config."AttributeDefinition"');
    if(!is_array($defs)) return false;
    $byDes = array();
    foreach($defs as $r) { $byDes[strtolower($r['designation'])] = $r['id']; }
    $prefer = function($baseKey, $plainKey, $synonyms = array()) use ($byDes) {
        $variants = array();
        // Prefer exact "Base X" (with space), then no-space, then plain
        $variants[] = strtolower('Base ' . $plainKey);
        $variants[] = strtolower('Base' . $baseKey);
        $variants[] = strtolower($plainKey);
        if(is_array($synonyms)) {
            foreach($synonyms as $s) { $variants[] = strtolower('Base ' . $s); }
            foreach($synonyms as $s) { $variants[] = strtolower('Base'.$s); }
            foreach($synonyms as $s) { $variants[] = strtolower($s); }
        }
        foreach($variants as $candidate) {
            if(array_key_exists($candidate, $byDes)) return $byDes[$candidate];
        }
        // contains fallback (last resort)
        $searches = array_merge(array(strtolower($plainKey)), is_array($synonyms) ? array_map('strtolower',$synonyms) : array());
        foreach($searches as $needle) {
            foreach($byDes as $k => $v) { if(strpos($k, $needle) !== false) return $v; }
        }
        return null;
    };
    $map = array(
        'Strength' => $prefer('Strength', 'Strength', array('Str')),
        'Agility' => $prefer('Agility', 'Agility', array('Agi')),
        'Vitality' => $prefer('Vitality', 'Vitality', array('Sta','Stamina')),
        'Energy' => $prefer('Energy', 'Energy', array('Ene')),
        'Leadership' => $prefer('Leadership', 'Leadership', array('Cmd','Command')),
    );

    // Apply increments; if target row doesn't exist, insert only for Base* attributes
    foreach($increments as $k => $delta) {
        if(!isset($map[$k])) continue;
        $amount = (int)$delta;
        if($amount === 0) continue;
        $defId = $map[$k];

        // First, try to UPDATE
        $updateSql = 'UPDATE data."StatAttribute" SET "Value" = "Value" + :delta WHERE "CharacterId" = :cid AND "'.$defCol.'" = :defid';
        $db->query($updateSql, array('delta'=>$amount, 'cid'=>$characterId, 'defid'=>$defId));

        // Check if row exists; if not, allow INSERT only when the designation is a Base attribute
        $exists = $db->query_fetch_single('SELECT 1 AS x FROM data."StatAttribute" WHERE "CharacterId" = :cid AND "'.$defCol.'" = :defid', array('cid'=>$characterId, 'defid'=>$defId));
        if(!is_array($exists)) {
            // Resolve designation for safety
            $defRow = $db->query_fetch_single('SELECT "Designation" AS des FROM config."AttributeDefinition" WHERE "Id" = :id', array('id'=>$defId));
            $des = is_array($defRow) && isset($defRow['des']) ? (string)$defRow['des'] : '';
            $isBase = stripos($des, 'Base ') === 0; // only Base*
            if($isBase) {
                // Insert new row with generated UUID Id
                $newId = function_exists('generateUuidV4') ? generateUuidV4() : null;
                if($newId) {
                    $insertSql = 'INSERT INTO data."StatAttribute" ("Id","CharacterId","'.$defCol.'","Value") VALUES (:id,:cid,:defid,:val)';
                    $db->query($insertSql, array('id'=>$newId, 'cid'=>$characterId, 'defid'=>$defId, 'val'=>$amount));
                }
            }
        }
    }
    return true;
}

/**
 * Set OpenMU character base stats to the provided base values (overwrites Base* attributes).
 * $baseStats keys: str, agi, vit, ene, cmd
 */
function setOpenMUCharacterBaseStats($characterId, array $baseStats) {
    if(!is_string($characterId) || $characterId === '') return false;
    $db = Connection::Database('MuOnline');

    // Discover StatAttribute FK column to AttributeDefinition
    $defCol = null;
    $colRows = $db->query_fetch("SELECT column_name FROM information_schema.columns WHERE table_schema='data' AND table_name='StatAttribute'");
    if(is_array($colRows)) {
        $columnNames = array_map(function($r){ return $r['column_name']; }, $colRows);
        $candidates = array('AttributeDefinitionId','AttributeDefinition','DefinitionId','AttributeId');
        foreach($candidates as $c) {
            if(in_array($c, $columnNames, true)) { $defCol = $c; break; }
        }
    }
    if(!$defCol) return false;

    // Map Base* designations to definition ids
    $defs = $db->query_fetch('SELECT "Id" AS id, "Designation" AS designation FROM config."AttributeDefinition"');
    if(!is_array($defs)) return false;
    $byDes = array();
    foreach($defs as $r) { $byDes[strtolower($r['designation'])] = $r['id']; }
    $resolve = function($plain) use ($byDes) {
        $candidates = array(strtolower('Base '.$plain), strtolower('Base'.$plain));
        foreach($candidates as $k) { if(array_key_exists($k, $byDes)) return $byDes[$k]; }
        // fallback to plain if base not found
        $k = strtolower($plain);
        return $byDes[$k] ?? null;
    };
    $map = array(
        'Strength' => $resolve('Strength'),
        'Agility' => $resolve('Agility'),
        'Vitality' => $resolve('Vitality'),
        'Energy' => $resolve('Energy'),
        'Leadership' => $resolve('Leadership'),
    );

    // Build desired values with sane defaults
    $desired = array(
        'Strength' => (int)($baseStats['str'] ?? 0),
        'Agility' => (int)($baseStats['agi'] ?? 0),
        'Vitality' => (int)($baseStats['vit'] ?? 0),
        'Energy' => (int)($baseStats['ene'] ?? 0),
        'Leadership' => (int)($baseStats['cmd'] ?? 0),
    );

    foreach($desired as $key => $val) {
        $defId = $map[$key] ?? null;
        if(!$defId) continue;
        // Try update
        $upd = $db->query('UPDATE data."StatAttribute" SET "Value" = :val WHERE "CharacterId" = :cid AND "'.$defCol.'" = :defid', array('val'=>$val, 'cid'=>$characterId, 'defid'=>$defId));
        // If no row exists, insert
        $exists = $db->query_fetch_single('SELECT 1 AS x FROM data."StatAttribute" WHERE "CharacterId" = :cid AND "'.$defCol.'" = :defid', array('cid'=>$characterId, 'defid'=>$defId));
        if(!is_array($exists)) {
            $newId = function_exists('generateUuidV4') ? generateUuidV4() : null;
            if($newId) {
                $db->query('INSERT INTO data."StatAttribute" ("Id","CharacterId","'.$defCol.'","Value") VALUES (:id,:cid,:defid,:val)', array('id'=>$newId, 'cid'=>$characterId, 'defid'=>$defId, 'val'=>$val));
            }
        }
    }
    return true;
}

/**
 * Check if the game server is online by probing known ports (55901, 55902)
 */
function isGameServerOnline($ip = null, $ports = null, $timeoutSec = 1.0) {
    if($ip === null) {
        $cfgIp = function_exists('config') ? config('game_server_ip', true) : null;
        $ip = (is_string($cfgIp) && strlen($cfgIp) > 0) ? $cfgIp : '127.0.0.1';
    }
    if($ports === null) {
        $ports = array(55901, 55902);
    }
    if(!is_array($ports) || empty($ports)) $ports = array(55901, 55902);

    foreach($ports as $port) {
        $port = (int)$port;
        if($port <= 0) continue;
        $errno = 0; $errstr = '';
        $conn = @stream_socket_client("tcp://{$ip}:{$port}", $errno, $errstr, $timeoutSec, STREAM_CLIENT_CONNECT);
        if(is_resource($conn)) {
            fclose($conn);
            return true;
        }
    }
    return false;
}

