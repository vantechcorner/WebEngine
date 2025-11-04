<?php
/**
 * WebEngine CMS
 * https://webenginecms.org/
 * 
 * @version 1.2.0
 * @author Lautaro Angelico <http://lautaroangelico.com/>
 * @copyright (c) 2013-2019 Lautaro Angelico, All Rights Reserved
 * 
 * Licensed under the MIT license
 * http://opensource.org/licenses/MIT
 */
?>
<h2>Top Voters</h2>
<?php
$database = $dB;

$currentMonth = date("m");
$nextMonth = $currentMonth+1;

$ts1 = strtotime(date("m/01/Y 00:00"));
$ts2 = strtotime(date("$nextMonth/01/Y 00:00"));
// OpenMU: read from data.webengine_vote_logs (voted_at) with UUID account_id
$voteLogs = $database->query_fetch("SELECT account_id, COUNT(*) as totalvotes FROM data.webengine_vote_logs WHERE voted_at BETWEEN TO_TIMESTAMP(?) AND TO_TIMESTAMP(?) GROUP BY account_id ORDER BY totalvotes DESC LIMIT 100", array($ts1,$ts2));

if($voteLogs && is_array($voteLogs)) {
	
	echo '<table class="table table-condensed table-hover">';
		echo '<tr>';
			echo '<th>#</th>';
			echo '<th>Account</th>';
			echo '<th>Votes</th>';
		echo '</tr>';
		
        foreach($voteLogs as $key => $thisVote) {
            $accountInfo = $common->accountInformation($thisVote['account_id']);
			$keyx = $key+1;
			echo '<tr>';
				echo '<td>'.$keyx.'</td>';
                $ai = is_array($accountInfo) ? array_change_key_case($accountInfo, CASE_LOWER) : array();
                echo '<td>'.($ai['loginname'] ?? $ai[strtolower(_CLMN_USERNM_)] ?? '-').'</td>';
				echo '<td>'.$thisVote['totalvotes'].'</td>';
			echo '</tr>';
		}
	echo '</table>';
	
} else {
	message('error', 'No vote logs found. This feature needs vote logs enabled.');
}
?>