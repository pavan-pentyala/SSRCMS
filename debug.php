<?php
// ─── SSRCMS Connection Debugger ───────────────────────────────
// Visit: http://localhost/SSRCMS/debug.php
// DELETE this file after fixing the issue!

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$user = 'root';
$pass = 'pavan@123';
$db   = 'ssrcms_proj_db';

echo "<pre style='background:#111;color:#0f0;padding:20px;font-size:13px;'>";
echo "=== SSRCMS Connection Debugger ===\n\n";

// Test 1: Basic connection (no DB selected)
echo "TEST 1: Connecting to MySQL (no DB)...\n";
try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "  ✅ Connected to MySQL successfully!\n";
    echo "  Server version: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n\n";
} catch (PDOException $e) {
    echo "  ❌ FAILED: " . $e->getMessage() . "\n\n";
    echo "STOP: Fix this connection error first.\n";
    echo "</pre>";
    exit;
}

// Test 2: Does the database exist?
echo "TEST 2: Checking database '$db'...\n";
try {
    $result = $pdo->query("SHOW DATABASES LIKE '$db'")->fetchAll();
    if (!empty($result)) {
        echo "  ✅ Database '$db' exists.\n\n";
    } else {
        echo "  ⚠️  Database '$db' does NOT exist yet. Run install.php first.\n\n";
    }
} catch (PDOException $e) {
    echo "  ❌ FAILED: " . $e->getMessage() . "\n\n";
}

// Test 3: Select the database
echo "TEST 3: Connecting to '$db'...\n";
try {
    $pdo2 = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "  ✅ Connected to '$db' successfully.\n\n";
} catch (PDOException $e) {
    echo "  ❌ FAILED: " . $e->getMessage() . "\n\n";
}

// Test 4: Check tables
echo "TEST 4: Checking tables...\n";
try {
    $pdo->exec("USE `$db`");
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tables)) {
        echo "  ⚠️  No tables found. Run install.php.\n\n";
    } else {
        foreach ($tables as $t) echo "  ✅ Table: $t\n";
        echo "\n";
    }
} catch (PDOException $e) {
    echo "  ⚠️  Could not check tables: " . $e->getMessage() . "\n\n";
}

// Test 5: Check users table contents
echo "TEST 5: Checking users...\n";
try {
    $pdo->exec("USE `$db`");
    $users = $pdo->query("SELECT id, name, email, role FROM users")->fetchAll();
    if (empty($users)) {
        echo "  ⚠️  No users found. Run install.php to seed data.\n\n";
    } else {
        foreach ($users as $u) echo "  ✅ User #{$u['id']}: {$u['name']} ({$u['email']}) [{$u['role']}]\n";
        echo "\n";
    }
} catch (PDOException $e) {
    echo "  ⚠️  " . $e->getMessage() . "\n\n";
}

// Test 6: PHP & PDO info
echo "TEST 6: Environment info...\n";
echo "  PHP version:      " . PHP_VERSION . "\n";
echo "  PDO drivers:      " . implode(', ', PDO::getAvailableDrivers()) . "\n";
echo "  MySQL host:       $host\n";
echo "  MySQL user:       $user\n";
echo "  Using password:   " . ($pass !== '' ? 'YES' : 'NO') . "\n";

echo "\n=== END OF DEBUG ===\n";
echo "</pre>";
echo "<p style='font-family:sans-serif;color:#ccc;padding:0 20px;'>
    <a href='install.php' style='color:#6c63ff;'>→ Run Installer</a> &nbsp;|&nbsp; 
    <a href='index.php' style='color:#6c63ff;'>→ Go to Login</a>
</p>";
