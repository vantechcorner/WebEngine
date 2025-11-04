<?php
/**
 * Check OpenMU Password Hashes
 */

echo "<h1>Check OpenMU Password Hashes</h1>\n";
echo "<hr>\n";

// Database connection parameters
$host = 'localhost';
$port = '5432';
$dbname = 'openmu';
$username = 'postgres';
$password = 'Muahe2025~';

try {
    // Connect to OpenMU database
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>✅ Connected to OpenMU Database</h2>\n";
    
    // Get sample accounts with their password hashes
    echo "<h2>🔍 Checking Password Hashes</h2>\n";
    $query = 'SELECT "LoginName", "PasswordHash" FROM data."Account" LIMIT 5';
    $stmt = $pdo->query($query);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($accounts) {
        echo "<table border='1' style='border-collapse: collapse;'>\n";
        echo "<tr><th>Username</th><th>Password Hash</th><th>Hash Type</th></tr>\n";
        
        foreach ($accounts as $account) {
            $hash = $account['PasswordHash'];
            $hashType = 'Unknown';
            
            // Check hash type
            if (strpos($hash, '$2y$') === 0 || strpos($hash, '$2a$') === 0 || strpos($hash, '$2b$') === 0) {
                $hashType = 'bcrypt';
            } elseif (strlen($hash) == 32 && ctype_xdigit($hash)) {
                $hashType = 'MD5';
            } elseif (strlen($hash) == 64 && ctype_xdigit($hash)) {
                $hashType = 'SHA256';
            } elseif (strpos($hash, '$pbkdf2') === 0) {
                $hashType = 'PBKDF2';
            } elseif (strpos($hash, '$argon2') === 0) {
                $hashType = 'Argon2';
            }
            
            echo "<tr><td>{$account['LoginName']}</td><td>" . substr($hash, 0, 30) . "...</td><td>$hashType</td></tr>\n";
        }
        echo "</table><br>\n";
    } else {
        echo "❌ No accounts found<br>\n";
    }
    
    // Test password verification
    echo "<h2>🧪 Testing Password Verification</h2>\n";
    
    // Get the first account for testing
    $query = 'SELECT "LoginName", "PasswordHash" FROM data."Account" LIMIT 1';
    $stmt = $pdo->query($query);
    $testAccount = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($testAccount) {
        echo "Testing with account: {$testAccount['LoginName']}<br>\n";
        echo "Hash: " . substr($testAccount['PasswordHash'], 0, 30) . "...<br><br>\n";
        
        // Test different password verification methods
        $testPassword = 'test'; // The password you're trying to use
        
        echo "<h3>Password Verification Tests:</h3>\n";
        
        // Test 1: bcrypt
        if (password_verify($testPassword, $testAccount['PasswordHash'])) {
            echo "✅ bcrypt verification: SUCCESS<br>\n";
        } else {
            echo "❌ bcrypt verification: FAILED<br>\n";
        }
        
        // Test 2: MD5
        if (md5($testPassword) === $testAccount['PasswordHash']) {
            echo "✅ MD5 verification: SUCCESS<br>\n";
        } else {
            echo "❌ MD5 verification: FAILED<br>\n";
        }
        
        // Test 3: SHA256
        if (hash('sha256', $testPassword) === $testAccount['PasswordHash']) {
            echo "✅ SHA256 verification: SUCCESS<br>\n";
        } else {
            echo "❌ SHA256 verification: FAILED<br>\n";
        }
        
        // Test 4: SHA256 with username
        if (hash('sha256', $testPassword . $testAccount['LoginName']) === $testAccount['PasswordHash']) {
            echo "✅ SHA256 with username verification: SUCCESS<br>\n";
        } else {
            echo "❌ SHA256 with username verification: FAILED<br>\n";
        }
        
        // Test 5: SHA1
        if (sha1($testPassword) === $testAccount['PasswordHash']) {
            echo "✅ SHA1 verification: SUCCESS<br>\n";
        } else {
            echo "❌ SHA1 verification: FAILED<br>\n";
        }
        
        // Test 6: Check if it's a simple string match
        if ($testPassword === $testAccount['PasswordHash']) {
            echo "✅ Plain text verification: SUCCESS<br>\n";
        } else {
            echo "❌ Plain text verification: FAILED<br>\n";
        }
        
    } else {
        echo "❌ No test account found<br>\n";
    }
    
    // Show hash details
    echo "<h2>📊 Hash Analysis</h2>\n";
    if ($testAccount) {
        $hash = $testAccount['PasswordHash'];
        echo "Hash length: " . strlen($hash) . " characters<br>\n";
        echo "Hash starts with: " . substr($hash, 0, 10) . "<br>\n";
        echo "Is hex: " . (ctype_xdigit($hash) ? 'YES' : 'NO') . "<br>\n";
        echo "Contains special chars: " . (preg_match('/[^a-zA-Z0-9]/', $hash) ? 'YES' : 'NO') . "<br>\n";
    }
    
} catch (PDOException $e) {
    echo "<h2>❌ Database Connection Failed</h2>\n";
    echo "Error: " . $e->getMessage() . "<br>\n";
}
?>




