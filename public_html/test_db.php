<?php
/**
 * Database Connection Test
 * Upload this to root and visit: https://yourdomain.com/test_db.php
 */

echo "<h2>Database Connection Test</h2>";

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=u164058768_sample_trackin;charset=utf8mb4',
        'u164058768_admin_track',
        'O^I~KYTdlykfPCa4',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p style='color: green;'>✅ <strong>Database Connection: SUCCESS</strong></p>";
    
    // Check tables
    $tables = ['users', 'samples', 'rfid_tags', 'audit_logs'];
    echo "<h3>Table Check:</h3><ul>";
    
    foreach ($tables as $table) {
        $result = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $row = $result->fetch();
        echo "<li>✅ <strong>$table</strong>: {$row['count']} records</li>";
    }
    
    echo "</ul>";
    
    // Test admin user
    $stmt = $pdo->prepare('SELECT username, role FROM users WHERE username = ? LIMIT 1');
    $stmt->execute(['admin']);
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "<h3>Admin User Check:</h3>";
        echo "<p>✅ <strong>Admin user found:</strong> " . $admin['username'] . " (" . $admin['role'] . ")</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Admin user NOT found - seed data may not be imported</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Database Connection Failed:</strong></p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p><strong>Troubleshooting:</strong></p>";
    echo "<ul>";
    echo "<li>Check database credentials in config/db.php</li>";
    echo "<li>Verify database exists: u164058768_sample_trackin</li>";
    echo "<li>Verify user exists: u164058768_admin_track</li>";
    echo "<li>Check database is not corrupted</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Back to App</a></p>";
?>
