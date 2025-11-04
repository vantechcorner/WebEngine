<?php
/**
 * OpenMU Login Class
 * Extends WebEngine CMS Login for OpenMU compatibility
 * 
 * @version 1.2.6
 * @author WebEngine CMS - OpenMU Integration
 * @copyright (c) 2025 WebEngine CMS, All Rights Reserved
 * 
 * Licensed under the MIT license
 * http://opensource.org/licenses/MIT
 */

require_once(__PATH_CLASSES__ . 'class.login.php');

class LoginOpenMU extends login {
    
    /**
     * Validate OpenMU account login
     */
    public function validateOpenMULogin($username, $password) {
        
        if(!check_value($username)) throw new Exception(lang('error_4',true));
        if(!check_value($password)) throw new Exception(lang('error_4',true));
        if(!$this->canLogin($_SERVER['REMOTE_ADDR'])) throw new Exception(lang('error_3',true));
        
        // Check if user exists in OpenMU database
        if(!$this->openMUUserExists($username)) throw new Exception(lang('error_2',true));
        
        // Validate password against OpenMU
        if($this->validateOpenMUUser($username, $password)) {
            
            $accountData = $this->getOpenMUAccountData($username);
            if(!is_array($accountData)) throw new Exception(lang('error_12',true));
            $acc = array_change_key_case($accountData, CASE_LOWER);
            
            // Check account state
            if((int)($acc['state'] ?? 0) != 0) { // 0 = Normal, other values might be banned/suspended
                throw new Exception(lang('error_13',true));
            }
            
            // Login success
            $this->removeFailedLogins($_SERVER['REMOTE_ADDR']);
            session_regenerate_id();
            $_SESSION['valid'] = true;
            $_SESSION['timeout'] = time();
            $_SESSION['username'] = $username;
            $_SESSION['userid'] = $acc['id'] ?? null;
            $_SESSION['email'] = $acc['email'] ?? null;
            $_SESSION['account_state'] = $acc['state'] ?? 0;
            $_SESSION['registration_date'] = $acc['registrationdate'] ?? null;
            $_SESSION['timezone'] = $acc['timezone'] ?? null;
            
            // Update last login time (if needed)
            if(isset($acc['id'])) $this->updateLastLogin($acc['id']);
            
            // Redirect to user control panel (match base class behavior)
            redirect(1,'usercp/');
            return true;
        }
        
        // failed login
        $this->addFailedLogin($username,$_SERVER['REMOTE_ADDR']);
        throw new Exception(lang('error_1',true));
    }
    
    /**
     * Check if user exists in OpenMU database
     */
    private function openMUUserExists($username) {
        $query = "SELECT COUNT(*) as count 
                  FROM "._TBL_ACCOUNT_." 
                  WHERE "._CLMN_ACCOUNT_LOGIN_." = :username";
        
        $result = $this->me->query_fetch_single($query, array('username' => $username));
        
        return $result && $result['count'] > 0;
    }
    
    /**
     * Validate OpenMU user password
     */
    private function validateOpenMUUser($username, $password) {
        $query = "SELECT "._CLMN_ACCOUNT_PASSWORD_." 
                  FROM "._TBL_ACCOUNT_." 
                  WHERE "._CLMN_ACCOUNT_LOGIN_." = :username";
        
        $result = $this->me->query_fetch_single($query, array('username' => $username));
        if (!$result) return false;
        
        // Normalize key casing from PDO
        $row = array_change_key_case($result, CASE_LOWER);
        $storedHash = isset($result['PasswordHash']) ? $result['PasswordHash'] : (isset($row['passwordhash']) ? $row['passwordhash'] : null);
        if(!is_string($storedHash) || $storedHash === '') return false;
        $storedHash = trim($storedHash);
        
        // bcrypt ($2a$)
        if (str_starts_with($storedHash, '$2')) {
            $phpHash = preg_replace('/^\$2a\$/', '$2y$', $storedHash);
            if(password_verify($password, $phpHash)) return true;
            if(hash_equals(crypt($password, $storedHash), $storedHash)) return true;
            if(hash_equals(crypt($password, $phpHash), $storedHash)) return true;
        }
        
        // fallback sha256(user mix)
        $hashedPassword = hash('sha256', $password . $username);
        if (hash_equals($storedHash, $hashedPassword)) return true;
        
        // fallback md5
        if (md5($password) === $storedHash) return true;
        
        return false;
    }
    
    /**
     * Get OpenMU account data
     */
    private function getOpenMUAccountData($username) {
        $query = "SELECT 
                    "._CLMN_ACCOUNT_ID_." as Id,
                    "._CLMN_ACCOUNT_LOGIN_." as LoginName,
                    "._CLMN_ACCOUNT_EMAIL_." as EMail,
                    "._CLMN_ACCOUNT_STATE_." as State,
                    "._CLMN_ACCOUNT_REGISTRATION_DATE_." as RegistrationDate,
                    "._CLMN_ACCOUNT_TIMEZONE_." as TimeZone,
                    "._CLMN_ACCOUNT_VAULT_EXTENDED_." as IsVaultExtended,
                    "._CLMN_ACCOUNT_CHAT_BAN_." as ChatBanUntil
                  FROM "._TBL_ACCOUNT_." 
                  WHERE "._CLMN_ACCOUNT_LOGIN_." = :username";
        
        return $this->me->query_fetch_single($query, array('username' => $username));
    }
    
    /**
     * Update last login time (if OpenMU tracks this)
     */
    private function updateLastLogin($accountId) {
        try {
            $query = "UPDATE "._TBL_ACCOUNT_." 
                      SET last_login = NOW() 
                      WHERE "._CLMN_ACCOUNT_ID_." = :account_id";
            
            $this->me->query($query, array('account_id' => $accountId));
        } catch (Exception $e) { }
    }
    
    /**
     * Get OpenMU account characters
     */
    public function getOpenMUAccountCharacters($accountId) {
        $query = "SELECT 
                    c."._CLMN_CHAR_ID_." as character_id,
                    c."._CLMN_CHAR_NAME_." as character_name,
                    c."._CLMN_CHAR_SLOT_." as character_slot,
                    c."._CLMN_CHAR_EXPERIENCE_." as experience,
                    c."._CLMN_CHAR_MASTER_EXPERIENCE_." as master_experience,
                    c."._CLMN_CHAR_CLASS_ID_." as class_id,
                    c."._CLMN_CHAR_STATE_." as state,
                    c."._CLMN_CHAR_CREATE_DATE_." as create_date,
                    cc."._CLMN_CHARACTER_CLASS_NAME_." as class_name
                  FROM "._TBL_CHARACTER_." c
                  LEFT JOIN "._TBL_CHARACTER_CLASS_." cc ON c."._CLMN_CHAR_CLASS_ID_." = cc."._CLMN_CHARACTER_CLASS_ID_."
                  WHERE c."._CLMN_CHAR_ACCOUNT_ID_." = :account_id
                  ORDER BY c."._CLMN_CHAR_SLOT_." ASC";
        
        $results = $this->me->query_fetch($query, array('account_id' => $accountId));
        
        if (!is_array($results)) return array();
        
        // Add calculated fields
        foreach ($results as &$character) {
            $character['level'] = calculateOpenMULevel($character['experience']);
            $character['master_level'] = calculateOpenMUMasterLevel($character['master_experience']);
            $character['is_online'] = $character['state'] > 0;
            $character['money'] = getOpenMUCharacterMoney($character['character_id']);
        }
        
        return $results;
    }
    
    /**
     * Check if account has online characters
     */
    public function hasOnlineCharacters($accountId) {
        $query = "SELECT COUNT(*) as online_count
                  FROM "._TBL_CHARACTER_."
                  WHERE "._CLMN_CHAR_ACCOUNT_ID_." = :account_id
                  AND "._CLMN_CHAR_STATE_." > 0";
        
        $result = $this->me->query_fetch_single($query, array('account_id' => $accountId));
        
        return $result && $result['online_count'] > 0;
    }
    
    /**
     * Override parent validateLogin method for OpenMU
     */
    public function validateLogin($username, $password) {
        global $config;
        
        // Check if we're using OpenMU
        if (strtolower($config['server_files']) == 'openmu') {
            return $this->validateOpenMULogin($username, $password);
        }
        
        // Fall back to parent method for other server types
        return parent::validateLogin($username, $password);
    }
}
