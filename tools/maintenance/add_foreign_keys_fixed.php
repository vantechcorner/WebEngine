<?php
/**
 * Add Foreign Key Constraints to WebEngine Tables (Fixed Version)
 */

echo "<h1>Add Foreign Key Constraints (Fixed)</h1>\n";
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
    
    // First, let's check the exact column names in OpenMU tables
    echo "<h2>🔍 Checking OpenMU Table Structure</h2>\n";
    
    // Check Account table columns
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'data' AND table_name = 'Account' AND column_name = 'Id'");
    $account_id = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Account ID column: " . ($account_id ? $account_id['column_name'] : 'Not found') . "<br>\n";
    
    // Check Character table columns  
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_schema = 'data' AND table_name = 'Character' AND column_name = 'Id'");
    $character_id = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Character ID column: " . ($character_id ? $character_id['column_name'] : 'Not found') . "<br>\n";
    
    echo "<br>\n";
    
    // Define foreign key constraints with correct column references
    $foreign_keys = [
        'webengine_vote_logs' => [
            "ALTER TABLE data.webengine_vote_logs ADD CONSTRAINT fk_vote_logs_account FOREIGN KEY (account_id) REFERENCES data.\"Account\"(\"Id\")",
            "ALTER TABLE data.webengine_vote_logs ADD CONSTRAINT fk_vote_logs_site FOREIGN KEY (vote_site_id) REFERENCES data.webengine_vote_sites(id)"
        ],
        'webengine_credits_logs' => [
            "ALTER TABLE data.webengine_credits_logs ADD CONSTRAINT fk_credits_logs_account FOREIGN KEY (account_id) REFERENCES data.\"Account\"(\"Id\")",
            "ALTER TABLE data.webengine_credits_logs ADD CONSTRAINT fk_credits_logs_character FOREIGN KEY (character_id) REFERENCES data.\"Character\"(\"Id\")"
        ],
        'webengine_bans' => [
            "ALTER TABLE data.webengine_bans ADD CONSTRAINT fk_bans_account FOREIGN KEY (account_id) REFERENCES data.\"Account\"(\"Id\")",
            "ALTER TABLE data.webengine_bans ADD CONSTRAINT fk_bans_character FOREIGN KEY (character_id) REFERENCES data.\"Character\"(\"Id\")"
        ],
        'webengine_paypal_transactions' => [
            "ALTER TABLE data.webengine_paypal_transactions ADD CONSTRAINT fk_paypal_account FOREIGN KEY (account_id) REFERENCES data.\"Account\"(\"Id\")",
            "ALTER TABLE data.webengine_paypal_transactions ADD CONSTRAINT fk_paypal_character FOREIGN KEY (character_id) REFERENCES data.\"Character\"(\"Id\")"
        ],
        'webengine_password_requests' => [
            "ALTER TABLE data.webengine_password_requests ADD CONSTRAINT fk_password_requests_account FOREIGN KEY (account_id) REFERENCES data.\"Account\"(\"Id\")"
        ],
        'webengine_email_verification' => [
            "ALTER TABLE data.webengine_email_verification ADD CONSTRAINT fk_email_verification_account FOREIGN KEY (account_id) REFERENCES data.\"Account\"(\"Id\")"
        ],
        'webengine_account_country' => [
            "ALTER TABLE data.webengine_account_country ADD CONSTRAINT fk_account_country_account FOREIGN KEY (account_id) REFERENCES data.\"Account\"(\"Id\")"
        ]
    ];
    
    echo "<h2>🔧 Adding Foreign Key Constraints (Fixed)</h2>\n";
    $success_count = 0;
    $error_count = 0;
    
    foreach ($foreign_keys as $table_name => $constraints) {
        echo "<h3>Adding constraints to $table_name</h3>\n";
        foreach ($constraints as $constraint_sql) {
            try {
                $pdo->exec($constraint_sql);
                $success_count++;
                echo "✅ Added constraint<br>\n";
            } catch (PDOException $e) {
                $error_count++;
                echo "❌ Error adding constraint: " . $e->getMessage() . "<br>\n";
                echo "SQL: " . htmlspecialchars($constraint_sql) . "<br>\n";
            }
        }
        echo "<br>\n";
    }
    
    echo "<h2>📊 Summary</h2>\n";
    echo "✅ Successful constraints: $success_count<br>\n";
    echo "❌ Failed constraints: $error_count<br><br>\n";
    
    if ($error_count == 0) {
        echo "<h2>🎉 All Foreign Keys Added Successfully!</h2>\n";
        echo "<p>Your WebEngine tables now have proper foreign key constraints.</p>\n";
    } else {
        echo "<h2>⚠️ Some Constraints Failed</h2>\n";
        echo "<p>Some foreign key constraints could not be added. This might be because:</p>\n";
        echo "<ul>\n";
        echo "<li>Data exists that violates the constraint</li>\n";
        echo "<li>Referenced tables/columns don't exist</li>\n";
        echo "<li>Constraint already exists</li>\n";
        echo "</ul>\n";
        echo "<p><strong>Note:</strong> This is not critical - WebEngine will work without foreign keys.</p>\n";
    }
    
    echo "<p><strong>Next Steps:</strong></p>\n";
    echo "<ol>\n";
    echo "<li><a href='test_complete_integration.php'>Run complete integration test</a></li>\n";
    echo "<li><a href='index.php'>Start WebEngine CMS</a></li>\n";
    echo "<li><a href='admincp/'>Access Admin Panel</a></li>\n";
    echo "</ol>\n";
    
} catch (PDOException $e) {
    echo "<h2>❌ Database Connection Failed</h2>\n";
    echo "Error: " . $e->getMessage() . "<br>\n";
}
?>




