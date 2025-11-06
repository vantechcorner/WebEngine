<?php
/**
 * WebEngine CMS
 * https://webenginecms.org/
 * 
 * @version 1.2.1
 * @author Lautaro Angelico <http://lautaroangelico.com/>
 * @copyright (c) 2013-2020 Lautaro Angelico, All Rights Reserved
 * 
 * Licensed under the MIT license
 * http://opensource.org/licenses/MIT
 */

echo '<h1 class="page-header">Online Accounts</h1>';

$Account = new Account();
// OpenMU: No server grouping needed

echo '<div class="row">';
    echo '<h3>Total Online:</h3>';
    echo '<div class="col-xs-12 col-md-4 col-lg-3 text-center">';
        echo '<pre><strong>TOTAL</strong>: '.number_format($Account->getOnlineAccountCount()).'</pre>';
    echo '</div>';
echo '</div>';

$onlineAccounts = $Account->getOnlineAccountList();
echo '<div class="row">';
	echo '<h3>Online Characters:</h3>';
	if(is_array($onlineAccounts) && count($onlineAccounts) > 0) {
		echo '<table class="table table-condensed table-hover">';
			echo '<thead>';
				echo '<tr>';
					echo '<th>Character</th>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			foreach($onlineAccounts as $name) {
				$nameSafe = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
				echo '<tr><td>'.$nameSafe.'</td></tr>';
			}
			echo '</tbody>';
		echo '</table>';
	} else {
		message('warning', 'There are no online characters.');
	}
echo '</div>';