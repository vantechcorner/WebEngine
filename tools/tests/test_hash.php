<?php
define('access','cron');
require_once(__DIR__.'/includes/webengine.php');

echo "<pre>";
try {
    $db = Connection::Database('MuOnline');
    $user = isset($_GET['u']) ? $_GET['u'] : 'test1';
    $row = $db->query_fetch_single('SELECT '._CLMN_ACCOUNT_PASSWORD_.' as hash FROM '._TBL_ACCOUNT_.' WHERE '._CLMN_ACCOUNT_LOGIN_.' = :u', array('u'=>$user));
    if(!$row) { echo "User not found\n"; exit; }
    $hash = $row['hash'] ?? (isset($row['Hash'])?$row['Hash']:reset($row));
    echo "User: $user\nStored hash: ".substr($hash,0,20)."... (len ".strlen($hash).")\n";

    $password = isset($_GET['p']) ? $_GET['p'] : 'test1';
    $phpHash = preg_replace('/^\$2a\$/', '$2y$', $hash);
    $bin384 = hash('sha384', $password, true);
    $bin512 = hash('sha512', $password, true);
    $bin384user = hash('sha384', $password.$user, true);
    $bin512user = hash('sha512', $password.$user, true);
    $tests = array(
        'bcrypt(password)' => $password,
        'bcrypt(trim(password))' => trim($password),
        'bcrypt(strtolower(password))' => strtolower($password),
        'bcrypt(strtoupper(password))' => strtoupper($password),
        'bcrypt(password.username)' => $password.$user,
        'bcrypt(username.password)' => $user.$password,
        'bcrypt(sha384_bin(password))' => $bin384,
        'bcrypt(sha512_bin(password))' => $bin512,
        'bcrypt(sha384_bin(password.username))' => $bin384user,
        'bcrypt(sha512_bin(password.username))' => $bin512user,
    );
    foreach($tests as $label => $candidate) {
        $ok = (str_starts_with($hash,'$2') && password_verify($candidate, $phpHash));
        echo $label.': '.($ok?'OK':'FAIL')."\n";
    }
    echo "hash info: ";
    $info = password_get_info($phpHash);
    echo json_encode($info)."\n";
    echo "md5 verify: ".((md5($password) === $hash)?'OK':'FAIL')."\n";
    echo "sha256(username) verify: ".((hash('sha256',$password.$user) === $hash)?'OK':'FAIL')."\n";
} catch(Exception $e) {
    echo "Error: ".$e->getMessage()."\n";
}
echo "</pre>";

