<?php
/**
 * Add Foreign Key Constraints to WebEngine Tables
 */

echo "<h1>Add Foreign Key Constraints</h1>\n";
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
    
    // Define foreign key constraints to add
    $foreign_keys = [
        'webengine_vote_logs' => [
            "ALTER TABLE data.webengine_vote_logs ADD CONSTRAINT fk_vote_logs_account FOREIGN KEY (account_id) REFERENCES data.\"Account\"(id)",
            "ALTER TABLE data.webengine_vote_logs ADD CONSTRAINT fk_vote_logs_site FOREIGN KEY (vote_site_id) REFERENCES data.webengine_vote_sites(id)"
        ],
        'webengine_credits_logs' => [
            "ALTER TABLE data.webengine_credits_logs ADD CONSTRAINT fk_credits_logs_account FOREIGN KEY (account_id) REFERENCES data.\"Account\"(id)",
            "ALTER TABLE data.webengine_credits_logs ADD CONSTRAINT fk_credits_logs_character FOREIGN KEY (character_id) REFERENCES data.\"Character\"(id)"
        ],
        'webengine_bans' => [
            "ALTER TABLE data.webengine_bans ADD CONSTRAINT fk_bans_account FOREIGN KEY (account_id) REFERENCES data.\"Account\"(id)",
            "ALTER TABLE data.webengine_bans ADD CONSTRAINT fk_bans_character FOREIGN KEY (character_id) REFERENCES data.\"Character\"(id)"
        ],
        'webengine_paypal_transactions' => [
            "ALTER TABLE data.webengine_paypal_transactions ADD CONSTRAINT fk_paypal_account FOREIGN KEY (account_id) REFERENCES data.\"Account\"(id)",
            "ALTER TABLE data.webengine_paypal_transactions ADD CONSTRAINT fk_paypal_character FOREIGN KEY (character_id) REFERENCES data.\"Character\"(id)"
        ],
        'webengine_password_requests' => [
            "ALTER TABLE data.webengine_password_requests ADD CONSTRAINT fk_password_requests_account FOREIGN KEY (account_id) REFERENCES data.\"Account\"(id)"
        ],
        'webengine_email_verification' => [
            "ALTER TABLE data.webengine_email_verification ADD CONSTRAINT fk_email_verification_account FOREIGN KEY (account_id) REFERENCES data.\"Account\"(id)"
        ],
        'webengine_account_country' => [
            "ALTER TABLE data.webengine_account_country ADD CONSTRAINT fk_account_country_account FOREIGN KEY (account_id) REFERENCES data.\"Account\"(id)"
        ]
    ];
    
    echo "<h2>🔧 Adding Foreign Key Constraints</h2>\n";
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
        echo "<p>Some foreign key constraints could not be added. This is usually because:</p>\n";
        echo "<ul>\n";
        echo "<li>Referenced tables don't exist</li>\n";
        echo "<li>Referenced columns don't exist</li>\n";
        echo "<li>Data exists that violates the constraint</li>\n";
        echo "</ul>\n";
    }
    
    echo "<p><strong>Next Steps:</strong></p>\n";
    echo "<ol>\n";
    echo "<li><a href='test_openmu_integration.php'>Run integration tests</a></li>\n";
    echo "<li><a href='index.php'>Start WebEngine CMS</a></li>\n";
    echo "<li><a href='admincp/'>Access Admin Panel</a></li>\n";
    echo "</ol>\n";
    
} catch (PDOException $e) {
    echo "<h2>❌ Database Connection Failed</h2>\n";
    echo "Error: " . $e->getMessage() . "<br>\n";
}
?>




