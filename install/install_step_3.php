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
<h3>Create Tables</h3>
<br />
<?php
try {
	if(isset($_POST['install_step_3_submit'])) {
		if(!isset($_POST['install_step_3_error'])) {
			# move to next step
			$_SESSION['install_cstep']++;
			header('Location: install.php');
			die();
		} else {
			echo '<div class="alert alert-danger" role="alert">One of more errors have been logged, cannot continue.</div>';
		}
	}
	
	$mudb = new dB($_SESSION['install_sql_host'], $_SESSION['install_sql_port'], $_SESSION['install_sql_db1'], $_SESSION['install_sql_user'], $_SESSION['install_sql_pass'], $_SESSION['install_sql_dsn']);
	
	if($mudb->dead) {
		throw new Exception("Could not connect to database");
	}

	// If PostgreSQL selected, run OpenMU WebEngine tables creator and skip MSSQL scripts
	if(isset($_SESSION['install_sql_dsn']) && $_SESSION['install_sql_dsn'] == 4) {
		$openmuSqlPath = 'sql/openmu/webengine_tables.sql';
		if(!file_exists($openmuSqlPath)) throw new Exception('Missing OpenMU WebEngine tables SQL file.');
		$sqlFileContents = file_get_contents($openmuSqlPath);
		if(!$sqlFileContents) throw new Exception('Could not read OpenMU tables SQL file.');
		$error = false;
		$statements = preg_split('/;\s*(\r?\n|$)/', $sqlFileContents);
		echo '<div class="list-group">';
		foreach($statements as $stmt) {
			$raw = trim($stmt);
			if($raw === '') continue;
			// strip line comments starting with --
			$lines = preg_split('/\r?\n/', $raw);
			$cleanLines = array();
			foreach($lines as $line) {
				$trim = ltrim($line);
				if(strpos($trim, '--') === 0) continue;
				$cleanLines[] = $line;
			}
			$statement = trim(implode("\n", $cleanLines));
			if($statement === '') continue;
			$ok = $mudb->query($statement);
			if(!$ok) { $error = true; }
		}
		echo '<div class="list-group-item">OpenMU WebEngine Tables<span class="label label-'.($error?'danger':'success').' pull-right">'.($error?'Error':'Created').'</span></div>';
		echo '</div>';
		echo '<form method="post">';
			if($error) echo '<input type="hidden" name="install_step_3_error" value="1"/>';
			echo '<a href="'.__INSTALL_URL__.'install.php" class="btn btn-default">Re-Check</a> ';
			echo '<button type="submit" name="install_step_3_submit" value="continue" class="btn btn-success">Continue</button>';
		echo '</form>';
		die();
		echo '<form method="post">';
			if($error) echo '<input type="hidden" name="install_step_3_error" value="1"/>';
			echo '<a href="'.__INSTALL_URL__.'install.php" class="btn btn-default">Re-Check</a> ';
			echo '<button type="submit" name="install_step_3_submit" value="continue" class="btn btn-success">Continue</button>';
		echo '</form>';
		die();
	}

    // SQL List (MSSQL path)
    if(!is_array($install['sql_list'])) throw new Exception('Could not load WebEngine CMS SQL tables list.');
    foreach($install['sql_list'] as $sqlFileName => $sqlTableName) {
        if(!file_exists('sql/' . $sqlFileName . '.txt')) {
            throw new Exception('The installation script is missing SQL tables.');
        }
    }
	
	$error = false;
	
	echo '<div class="list-group">';
	foreach($install['sql_list'] as $sqlFileName => $sqlTableName) {
		$sqlFileContents = file_get_contents('sql/'.$sqlFileName.'.txt');
		if(!$sqlFileContents) continue;
		// Load PostgreSQL variant for vote/credits if selected
		if($_SESSION['install_sql_dsn'] == 4 && $sqlFileName === 'WEBENGINE_VOTE_SITES') {
			$sqlFileContents = file_get_contents('sql/openmu/webengine_tables.sql');
			// Create all OpenMU webengine tables in one go
			$create = $mudb->query($sqlFileContents);
			if($create) {
				echo '<div class="list-group-item">OpenMU WebEngine Tables<span class="label label-success pull-right">Created</span></div>';
			} else {
				echo '<div class="list-group-item">OpenMU WebEngine Tables<span class="label label-danger pull-right">Error</span></div>';
				$error = true;
			}
			break;
		}
		// rename table
		$query = str_replace('{TABLE_NAME}', $sqlTableName, $sqlFileContents);
		if(!$query) continue;
		
        // delete table (if forced)
        if(isset($_GET['force']) && $_GET['force'] == 1) {
            $mudb->query("DROP TABLE " . $sqlTableName);
        }
        
        // check if exists (MSSQL sysobjects)
        $tableExists = $mudb->query_fetch_single("SELECT * FROM sysobjects WHERE xtype = 'U' AND name = ?", array($sqlTableName));
		
		if(!$tableExists) {
			$create = $mudb->query($query);
			if($create) {
				echo '<div class="list-group-item">'.$sqlTableName.'<span class="label label-success pull-right">Created</span></div>';
			} else {
				echo '<div class="list-group-item">'.$sqlTableName.'<span class="label label-danger pull-right">Error</span></div>';
				$error = true;
			}
		} else {
			echo '<div class="list-group-item">'.$sqlTableName.'<span class="label label-default pull-right">Already Exists</span></div>';
		}
	}
	echo '</div>';
	
	echo '<form method="post">';
		if($error) echo '<input type="hidden" name="install_step_3_error" value="1"/>';
		echo '<a href="'.__INSTALL_URL__.'install.php" class="btn btn-default">Re-Check</a> ';
		echo '<button type="submit" name="install_step_3_submit" value="continue" class="btn btn-success">Continue</button>';
		echo '<a href="'.__INSTALL_URL__.'install.php?force=1" class="btn btn-danger pull-right">Delete Tables and Create Again</a>';
	echo '</form>';
	
} catch (Exception $ex) {
	echo '<div class="alert alert-danger" role="alert">'.$ex->getMessage().'</div>';
}