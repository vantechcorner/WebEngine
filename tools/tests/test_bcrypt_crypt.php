<?php
define('access','cron');
require_once(__DIR__.'/includes/webengine.php');

$user = isset($_GET['u']) ? $_GET['u'] : 'test1';
$pass = isset($_GET['p']) ? $_GET['p'] : 'test1';

$db = Connection::Database('MuOnline');
$row = $db->query_fetch_single('SELECT '._CLMN_ACCOUNT_PASSWORD_.' AS hash FROM '._TBL_ACCOUNT_.' WHERE '._CLMN_ACCOUNT_LOGIN_.' = :u', array('u'=>$user));
if(!$row){ echo 'User not found'; exit; }
$stored = $row['hash'] ?? (isset($row['Hash'])?$row['Hash']:reset($row));

$salt = substr($stored, 0, 29);
$salt2y = preg_replace('/^\$2a\$/', '$2y$', $salt);

$crypt1 = crypt($pass, $salt);
$crypt2 = crypt($pass, $salt2y);

header('Content-Type: text/plain');
echo "User: $user\n";
echo "Stored:  ".$stored."\n";
echo "Salt:    ".$salt."\n";
echo "crypt(pass, $salt):   ".$crypt1."\n";
echo "crypt(pass, $salt2y): ".$crypt2."\n";
echo "equals(stored, crypt1): ".(hash_equals($stored,$crypt1)?'YES':'NO')."\n";
echo "equals(stored, crypt2): ".(hash_equals($stored,$crypt2)?'YES':'NO')."\n";


