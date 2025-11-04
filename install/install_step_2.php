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

if(!defined('access') or !access or access != 'install') die();
?>
<h3>Database Connection</h3>
<br />
<?php
if(isset($_POST['install_step_2_submit'])) {
	try {

		$_SESSION['install_sql_host'] = $_POST['install_step_2_1'];
		if(!isset($_POST['install_step_2_1'])) throw new Exception('You must complete all required fields.');
		
		$_SESSION['install_sql_port'] = $_POST['install_step_2_7'];
		if(!isset($_POST['install_step_2_7'])) throw new Exception('You must complete all required fields.');
		
		$_SESSION['install_sql_user'] = $_POST['install_step_2_2'];
		if(!isset($_POST['install_step_2_2'])) throw new Exception('You must complete all required fields.');
		
		$_SESSION['install_sql_pass'] = $_POST['install_step_2_3'];
		if(!isset($_POST['install_step_2_3'])) throw new Exception('You must complete all required fields.');
		
		$_SESSION['install_sql_db1'] = $_POST['install_step_2_4'];
		if(!isset($_POST['install_step_2_4'])) throw new Exception('You must complete all required fields.');
		
		$_SESSION['install_sql_dsn'] = $_POST['install_step_2_8'];
		if(!isset($_POST['install_step_2_8'])) throw new Exception('You must complete all required fields.');
		if(!array_key_exists($_POST['install_step_2_8'], $install['PDO_DSN'])) throw new Exception('You must complete all required fields.');
		
		$_SESSION['install_sql_db2'] = ((isset($_POST['install_step_2_5']) && !empty($_POST['install_step_2_5'])) ? $_POST['install_step_2_5'] : null);
		
		$_SESSION['install_sql_passwd_encrypt'] = $_POST['install_step_2_6'];
		if(!isset($_POST['install_step_2_6'])) throw new Exception('You must complete all required fields.');
		if(!in_array(strtolower($_POST['install_step_2_6']), $install['PDO_PWD_ENCRYPT'])) throw new Exception('You must complete all required fields.');
		
		// SHA256 salt is only required if SHA256 encryption is selected
		if(strtolower($_SESSION['install_sql_passwd_encrypt']) === 'sha256') {
			$_SESSION['install_sql_sha256_salt'] = $_POST['install_step_2_9'];
			if(!isset($_POST['install_step_2_9']) || $_POST['install_step_2_9'] === '') throw new Exception('You must complete all required fields.');
		} else {
			$_SESSION['install_sql_sha256_salt'] = isset($_POST['install_step_2_9']) ? $_POST['install_step_2_9'] : '';
		}
		
		# test connection (db1)
		$db1 = new dB($_SESSION['install_sql_host'], $_SESSION['install_sql_port'], $_SESSION['install_sql_db1'], $_SESSION['install_sql_user'], $_SESSION['install_sql_pass'], $_SESSION['install_sql_dsn']);
		if($db1->dead) {
			throw new Exception("Could not connect to database (1)");
		}
		
		# test connection (db2)
		if(isset($_SESSION['install_sql_db2'])) {
			$db2 = new dB($_SESSION['install_sql_host'], $_SESSION['install_sql_port'], $_SESSION['install_sql_db2'], $_SESSION['install_sql_user'], $_SESSION['install_sql_pass'], $_SESSION['install_sql_dsn']);
			if($db2->dead) {
				throw new Exception("Could not connect to database (2)");
			}
		}
		
		# move to next step
		$_SESSION['install_cstep']++;
		header('Location: install.php');
		die();
	} catch (Exception $ex) {
		echo '<div class="alert alert-danger" role="alert">'.$ex->getMessage().'</div>';
	}
}
?>
<form class="form-horizontal" method="post">
	<div class="form-group">
		<label for="input_1" class="col-sm-2 control-label">Host</label>
		<div class="col-sm-10">
			<input type="text" name="install_step_2_1" class="form-control" id="input_1" value="<?php echo (isset($_SESSION['install_sql_host']) ? $_SESSION['install_sql_host'] : 'localhost'); ?>">
			<p class="help-block">Set the IP address of your Database server.</p>
		</div>
	</div>
	<div class="form-group">
		<label for="input_7" class="col-sm-2 control-label">Port</label>
		<div class="col-sm-10">
			<?php $defaultPort = (isset($_SESSION['install_sql_dsn']) ? ($_SESSION['install_sql_dsn']==4 ? '5432' : '1433') : '5432'); ?>
			<input type="text" name="install_step_2_7" class="form-control" id="input_7" value="<?php echo (isset($_SESSION['install_sql_port']) ? $_SESSION['install_sql_port'] : $defaultPort); ?>">
			<p class="help-block">Default MSSQL: 1433. PostgreSQL: 5432.</p>
		</div>
	</div>
	<div class="form-group">
		<label for="input_2" class="col-sm-2 control-label">Username</label>
		<div class="col-sm-10">
			<input type="text" name="install_step_2_2" class="form-control" id="input_2" value="<?php echo (isset($_SESSION['install_sql_user']) ? $_SESSION['install_sql_user'] : 'postgres'); ?>">
			<p class="help-block">MSSQL example: sa. PostgreSQL example: postgres (or your DB user).</p>
		</div>
	</div>
	<div class="form-group">
		<label for="input_3" class="col-sm-2 control-label">Password</label>
		<div class="col-sm-10">
			<input type="text" name="install_step_2_3" class="form-control" id="input_3" value="<?php echo (isset($_SESSION['install_sql_pass']) ? $_SESSION['install_sql_pass'] : null); ?>">
			<p class="help-block">It is recommended that you use a strong password to ensure maximum security.</p>
		</div>
	</div>
	<div class="form-group">
		<label for="input_4" class="col-sm-2 control-label">Database (1)</label>
		<div class="col-sm-10">
			<input type="text" name="install_step_2_4" class="form-control" id="input_4" value="<?php echo (isset($_SESSION['install_sql_db1']) ? $_SESSION['install_sql_db1'] : 'openmu'); ?>">
			<p class="help-block">Default for OpenMU is <strong>openmu</strong>. WebEngine tables will be created in this database.</p>
		</div>
	</div>
	<div class="form-group">
		<label for="input_5" class="col-sm-2 control-label">Database (2)</label>
		<div class="col-sm-10">
			<input type="text" name="install_step_2_5" class="form-control" id="input_5" value="<?php echo (isset($_SESSION['install_sql_db2']) ? $_SESSION['install_sql_db2'] : null); ?>">
			<p class="help-block">Usually <strong>Me_MuOnline</strong>. Leave empty if you only use one database.</p>
		</div>
	</div>
	<div class="form-group">
		<label for="input_8" class="col-sm-2 control-label">PDO Driver</label>
		<div class="col-sm-10">
			<div class="radio">
				<label>
					<input type="radio" name="install_step_2_8" id="input_8" value="1" <?php echo (isset($_SESSION['install_sql_dsn']) && $_SESSION['install_sql_dsn']==1 ? 'checked="checked"' : null); ?>>
					Dblib (Linux)
				</label>
			</div>
			<div class="radio">
				<label>
					<input type="radio" name="install_step_2_8" value="2" <?php echo (isset($_SESSION['install_sql_dsn']) && $_SESSION['install_sql_dsn']==2 ? 'checked="checked"' : null); ?>>
					SqlSrv (Windows)
				</label>
			</div>
			<div class="radio">
				<label>
					<input type="radio" name="install_step_2_8" value="4" <?php echo (!isset($_SESSION['install_sql_dsn']) || $_SESSION['install_sql_dsn']==4 ? 'checked="checked"' : null); ?>>
					PostgreSQL (pgsql)
				</label>
			</div>
		</div>
	</div>
	
	<div class="form-group">
		<label for="input_9" class="col-sm-2 control-label">Password Encryption</label>
		<div class="col-sm-10">
			<?php $enc = isset($_SESSION['install_sql_passwd_encrypt']) ? strtolower($_SESSION['install_sql_passwd_encrypt']) : 'bcrypt'; ?>
			<div class="radio">
				<label>
					<input type="radio" name="install_step_2_6" id="input_9" value="none" <?php echo ($enc==='none'?'checked="checked"':null); ?>>
					None
				</label>
			</div>
			<div class="radio">
				<label>
					<input type="radio" name="install_step_2_6" value="wzmd5">
					MD5 (WZ)
				</label>
			</div>
			<div class="radio">
				<label>
					<input type="radio" name="install_step_2_6" value="phpmd5">
					MD5 (PHP)
				</label>
			</div>
			<div class="radio">
				<label>
					<input type="radio" name="install_step_2_6" value="sha256" <?php echo ($enc==='sha256'?'checked="checked"':null); ?>>
					Sha256
				</label>
			</div>
			<div class="radio">
				<label>
					<input type="radio" name="install_step_2_6" value="bcrypt" <?php echo ($enc==='bcrypt'?'checked="checked"':null); ?>>
					Bcrypt (OpenMU/Game client compatible)
				</label>
			</div>
		</div>
	</div>
	
	<div class="form-group">
		<label for="input_10" class="col-sm-2 control-label">Sha256 Salt</label>
		<div class="col-sm-10">
			<input type="text" name="install_step_2_9" class="form-control" id="input_10" value="<?php echo (isset($_SESSION['install_sql_sha256_salt']) ? $_SESSION['install_sql_sha256_salt'] : ''); ?>">
			<p class="help-block">Required if you selected Sha256 password encryption. The "salt" value must match the configuration on your server.</p>
		</div>
	</div>

	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-10">
			<button type="submit" name="install_step_2_submit" value="continue" class="btn btn-success">Continue</button>
		</div>
	</div>
</form>