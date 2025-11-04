<?php
/**
 * Test Password Verification for OpenMU
 * This script tests if the password verification works correctly
 */

// Load WebEngine
require_once('includes/webengine.php');

echo "<h2>Password Verification Test</h2>";

// Test credentials
$testUsername = 'test1';
$testPassword = 'test1';

echo "<p>Testing with: Username = '$testUsername', Password = '$testPassword'</p>";

try {
    // Get the account data
    $query = "SELECT \"Id\", \"LoginName\", \"PasswordHash\" FROM data.\"Account\" WHERE \"LoginName\" = :username";
    $result = $mu->query_fetch_single($query, array('username' => $testUsername));
    
    if ($result) {
        echo "<p>✅ Account found: " . $result['LoginName'] . "</p>";
        echo "<p>Password Hash: " . substr($result['PasswordHash'], 0, 20) . "...</p>";
        
        // Test password verification
        if (password_verify($testPassword, $result['PasswordHash'])) {
            echo "<p>✅ Password verification: SUCCESS</p>";
        } else {
            echo "<p>❌ Password verification: FAILED</p>";
        }
        
        // Test with LoginOpenMU class
        $login = new LoginOpenMU();
        if ($login->isValidLogin($testUsername, $testPassword)) {
            echo "<p>✅ LoginOpenMU::isValidLogin: SUCCESS</p>";
        } else {
            echo "<p>❌ LoginOpenMU::isValidLogin: FAILED</p>";
        }
        
    } else {
        echo "<p>❌ Account not found</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>Back to Home</a></p>";
?>



