<?php
/**
 * WebEngine
 * http://muengine.net/
 * 
 * @version 1.0.9
 * @author Lautaro Angelico <http://lautaroangelico.com/>
 * @copyright (c) 2013-2017 Lautaro Angelico, All Rights Reserved
 * 
 * Licensed under the MIT license
 * http://opensource.org/licenses/MIT
 */

echo '<h1 class="page-header">New Registrations</h1>';

    $db = $dB;
    $newRegs = $db->query_fetch("SELECT \"Id\" AS id, \"LoginName\" AS login, \"EMail\" AS email FROM data.\"Account\" ORDER BY \"RegistrationDate\" DESC LIMIT 200");
	
	if(is_array($newRegs)) {
		echo '<table id="new_registrations" class="table display">';
			echo '<thead>';
			echo '<tr>';
				echo '<th>Id</th>';
				echo '<th>Username</th>';
				echo '<th>Email</th>';
				echo '<th></th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
			foreach($newRegs as $thisReg) {
				echo '<tr>';
                echo '<td>'.$thisReg['id'].'</td>';
                echo '<td>'.$thisReg['login'].'</td>';
                echo '<td>'.$thisReg['email'].'</td>';
                echo '<td style="text-align:right;"><a href="'.admincp_base("accountinfo&id=".$thisReg['id']).'" class="btn btn-xs btn-default">Account Information</a></td>';
				echo '</tr>';
			}
			echo '</tbody>';
		echo '</table>';
	}
?>