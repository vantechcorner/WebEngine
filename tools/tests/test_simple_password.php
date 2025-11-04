<?php
/**
 * Simple Password Test (without WebEngine)
 */

// Database connection
$host = 'localhost';
$port = '5432';
$dbname = 'openmu';
$username = 'postgres';
$password = 'Muahe2025~';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;options=--search_path=data,guild,friend,config,public", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Simple Password Verification Test</h2>";
    
    // Test credentials
    $testUsername = 'test1';
    $testPassword = 'test1';
    
    echo "<p>Testing with: Username = '$testUsername', Password = '$testPassword'</p>";
    
    // Get the account data
    $query = "SELECT \"Id\", \"LoginName\", \"PasswordHash\" FROM data.\"Account\" WHERE \"LoginName\" = :username";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['username' => $testUsername]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo "<p>✅ Account found: " . $result['LoginName'] . "</p>";
        echo "<p>Password Hash: " . substr($result['PasswordHash'], 0, 30) . "...</p>";
        
        // Test password verification
        if (password_verify($testPassword, $result['PasswordHash'])) {
            echo "<p>✅ Password verification: SUCCESS</p>";
        } else {
            echo "<p>❌ Password verification: FAILED</p>";
            
            // Try alternative methods
            echo "<p>Testing alternative methods:</p>";
            
            // Try MD5
            if (md5($testPassword) === $result['PasswordHash']) {
                echo "<p>✅ MD5 verification: SUCCESS</p>";
            } else {
                echo "<p>❌ MD5 verification: FAILED</p>";
            }
            
            // Try SHA256
            if (hash('sha256', $testPassword) === $result['PasswordHash']) {
                echo "<p>✅ SHA256 verification: SUCCESS</p>";
            } else {
                echo "<p>❌ SHA256 verification: FAILED</p>";
            }
        }
        
    } else {
        echo "<p>❌ Account not found</p>";
        
        // List available accounts
        $accounts = $pdo->query("SELECT \"LoginName\" FROM data.\"Account\" LIMIT 5")->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>Available accounts: " . implode(', ', $accounts) . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>



