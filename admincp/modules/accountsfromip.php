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
<h1 class="page-header">Find accounts from IP</h1>
<form class="form-inline" role="form" method="post">
	<div class="form-group">
		<input type="text" class="form-control" id="input_1" name="ip_address" placeholder="Ip Address"/>
	</div>
	<button type="submit" class="btn btn-primary" name="search_ip" value="ok">Search</button>
</form>
<br />
<?php
if(isset($_POST['ip_address'])) {
	try {
		if(!Validator::Ip($_POST['ip_address'])) throw new Exception("You have entered an invalid IP address.");
		
		echo '<h4>Search results for <span style="color:red;font-weight:bold;"><i>'.$_POST['ip_address'].'</i></span>:</h4>';
		echo '<div class="row">';
			echo '<div class="col-md-6">';
				echo '<div class="panel panel-primary">';
				echo '<div class="panel-heading">Results:</div>';
				echo '<div class="panel-body">';
					
					$searchdb = $dB;
					// OpenMU does not store account IPs in Character; fallback to login attempts table
					$ipLogRows = $searchdb->query_fetch("SELECT DISTINCT username FROM public.webengine_fla WHERE ip_address = ?", array($_POST['ip_address']));
					$membStatData = array();
					if(is_array($ipLogRows)) {
						foreach($ipLogRows as $row) {
							$acc = $searchdb->query_fetch_single("SELECT \"Id\" AS id FROM data.\"Account\" WHERE \"LoginName\" ILIKE ? LIMIT 1", array($row['username']));
							if(is_array($acc) && isset($acc['id'])) $membStatData[] = array('AccountId' => $acc['id']);
						}
					}
					if(is_array($membStatData)) {
						echo '<table class="table table-no-border table-hover">';
							foreach($membStatData as $membStatUser) {
								echo '<tr>';
							echo '<td>'.$membStatUser['AccountId'].'</td>';
							echo '<td style="text-align:right;"><a href="'.admincp_base("accountinfo&id=".$membStatUser['AccountId']).'" class="btn btn-xs btn-default">Account Information</a></td>';
								echo '</tr>';
							}
							echo '</table>';
					} else {
						message('warning', 'No accounts found linked to this Ip.', ' ');
					}
				echo '</div>';
				echo '</div>';
			echo '</div>';
		echo '</div>';
	} catch(Exception $ex) {
		message('error', $ex->getMessage());
	}
}
?>