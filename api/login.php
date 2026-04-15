<?php
// Suppress PHP warnings from polluting JSON output
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');
$remember = ($_POST['remember'] ?? '') === 'true';

// Basic validation
if (empty($email) || empty($password)) {
    jsonOut(['success' => false, 'message' => 'Email and password are required.'], 422);
}

try {
    $db   = getDB();
    $stmt = $db->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        jsonOut(['success' => false, 'message' => 'Invalid email or password.'], 401);
    }

    // Set PHP session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name']    = $user['name'];
    $_SESSION['role']    = $user['role'];

    // Remember Me cookie (30 days)
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, [
            'expires'  => time() + (86400 * 30),
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    jsonOut([
        'success' => true,
        'role'    => $user['role'],
        'name'    => $user['name'],
    ]);

} catch (Exception $e) {
    jsonOut(['success' => false, 'message' => 'Server error during login. Please try again.'], 500);
}
