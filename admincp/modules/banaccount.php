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
?>
<h1 class="page-header">Ban Account</h1>
<?php
	$database = $dB;
	
	// Add ban system cron if doesn't exist
	$banCron = "INSERT INTO ".WEBENGINE_CRON." (cron_name, cron_description, cron_file_run, cron_run_time, cron_status, cron_protected, cron_file_md5) VALUES ('Ban System', 'Scheduled task to lift temporal bans', 'temporal_bans.php', '3600', 1, 1, '1a3787c5179afddd1bfb09befda3d1c7')";
	$checkBanCron = $database->query_fetch_single("SELECT * FROM ".WEBENGINE_CRON." WHERE cron_file_run = ?", array("temporal_bans.php"));
	if(!is_array($checkBanCron)) $database->query($banCron);
	
	if(isset($_POST['submit_ban'])) {
		try {
			if(!isset($_POST['ban_account'])) throw new Exception("Please enter the account username.");
			if(!$common->userExists($_POST['ban_account'])) throw new Exception("Invalid account username.");
			if(!isset($_POST['ban_days'])) throw new Exception("Please enter the amount of days.");
			if(!Validator::UnsignedNumber($_POST['ban_days'])) throw new Exception("Invalid ban days.");
			if(isset($_POST['ban_reason'])) {
				if(!Validator::Length($_POST['ban_reason'], 100, 1)) throw new Exception("Invalid ban reason.");
			}
			
			// Check Online Status
			if($common->accountOnline($_POST['ban_account'])) throw new Exception("The account is currently online.");
			
			// Account Information (OpenMU: resolve UUID and use it)
			$userID = $common->retrieveUserID($_POST['ban_account']);
			if(!$userID) {
				// try resolve from Account table for OpenMU
				$accRow = $database->query_fetch_single('SELECT "Id" AS id FROM data."Account" WHERE "LoginName" = ?', array($_POST['ban_account']));
				$userID = is_array($accRow) ? ($accRow['id'] ?? null) : null;
			}
			$accountData = $common->accountInformation($userID ?? $_POST['ban_account']);
			
			// Check if aready banned
			if(isset($accountData[_CLMN_BLOCCODE_]) && $accountData[_CLMN_BLOCCODE_] == 1) throw new Exception("This account is already banned.");
			
			// Ban Type
			$banType = ($_POST['ban_days'] >= 1 ? "temporal" : "permanent");
			
			// Log Ban
			$banLogData = array(
				'acc' => ($userID ?? $_POST['ban_account']),
				'by' => $_SESSION['username'],
				'type' => $banType,
				'date' => time(),
				'days' => $_POST['ban_days'],
				'reason' => (isset($_POST['ban_reason']) ? $_POST['ban_reason'] : "")
			);
			
			// Ensure ban tables exist (OpenMU installer should create these in public schema)
			$database->query("CREATE TABLE IF NOT EXISTS public.webengine_ban_log (id SERIAL PRIMARY KEY, account_id VARCHAR(64) NOT NULL, banned_by VARCHAR(64) NOT NULL, ban_type VARCHAR(16) NOT NULL, ban_date BIGINT NOT NULL, ban_days INT NOT NULL DEFAULT 0, ban_reason VARCHAR(255) NOT NULL DEFAULT '')");
			$database->query("CREATE TABLE IF NOT EXISTS public.webengine_bans (id SERIAL PRIMARY KEY, account_id VARCHAR(64) NOT NULL, banned_by VARCHAR(64) NOT NULL, ban_date BIGINT NOT NULL, ban_days INT NOT NULL DEFAULT 0, ban_reason VARCHAR(255) NOT NULL DEFAULT '')");
			// Ensure required columns exist on both schemas (public and default)
			$targets = array('public.webengine_bans','webengine_bans');
			foreach($targets as $t) {
				list($schema,$table) = (strpos($t,'.')!==false) ? explode('.', $t, 2) : array(null,$t);
				if($schema) {
					$chk = $database->query_fetch_single("SELECT 1 FROM information_schema.columns WHERE table_schema=? AND table_name=? AND column_name='ban_date'", array($schema, $table));
					if(!is_array($chk)) { $database->query("ALTER TABLE $t ADD COLUMN ban_date BIGINT"); }
					$chk = $database->query_fetch_single("SELECT 1 FROM information_schema.columns WHERE table_schema=? AND table_name=? AND column_name='ban_days'", array($schema, $table));
					if(!is_array($chk)) { $database->query("ALTER TABLE $t ADD COLUMN ban_days INT DEFAULT 0"); }
					$chk = $database->query_fetch_single("SELECT 1 FROM information_schema.columns WHERE table_schema=? AND table_name=? AND column_name='ban_reason'", array($schema, $table));
					if(!is_array($chk)) { $database->query("ALTER TABLE $t ADD COLUMN ban_reason VARCHAR(255) DEFAULT ''"); }
				}
			}
			$logBan = $database->query("INSERT INTO ".WEBENGINE_BAN_LOG." (account_id, banned_by, ban_type, ban_date, ban_days, ban_reason) VALUES (:acc, :by, :type, :date, :days, :reason)", $banLogData);
			if(!$logBan) throw new Exception("Could not log ban (check tables)[1].");
			
			// Note: temporal bans are tracked in WEBENGINE_BAN_LOG (ban_type='temporal') and lifted by cron; no insert into WEBENGINE_BANS needed on OpenMU
			
			// Ban Account
			// OpenMU: ban via data."Account"."State" = 1 (UUID)
			if($userID && preg_match('/^[0-9a-fA-F\-]{36}$/', $userID)) {
				$banAccount = $database->query('UPDATE data."Account" SET "State" = 1 WHERE "Id" = ?', array($userID));
			} else {
				$banAccount = $database->query("UPDATE "._TBL_MI_." SET "._CLMN_BLOCCODE_." = 1 WHERE "._CLMN_USERNM_." = ?", array($_POST['ban_account']));
			}
			if(!$banAccount) throw new Exception("Could not ban account.");
			
			message('success', 'Account Banned');
		} catch(Exception $ex) {
			message('error', $ex->getMessage());
		}
	}
?>
<div class="row">
	<div class="col-md-6">
		<form action="" method="post" role="form">
			<div class="form-group">
				<label for="acc">Account</label>
				<input type="text" name="ban_account" class="form-control" id="acc">
			</div>
			<div class="form-group">
				<label for="days">Days (0 for permanent ban)</label>
				<input type="text" name="ban_days" class="form-control" id="days" value="0">
			</div>
			<div class="form-group">
				<label for="reason">Reason (optional)</label>
				<input type="text" name="ban_reason" class="form-control" id="reason">
			</div>
			<input type="submit" name="submit_ban" class="btn btn-primary" value="Ban Account"/>
		</form>
	</div>
</div>