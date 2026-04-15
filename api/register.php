<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$name       = trim($_POST['name']       ?? '');
$email      = trim($_POST['email']      ?? '');
$password   = trim($_POST['password']   ?? '');
$department = trim($_POST['department'] ?? '');
$phone      = trim($_POST['phone']      ?? '');

// Validation
if (empty($name) || empty($email) || empty($password)) {
    jsonOut(['success' => false, 'message' => 'Name, email and password are required.'], 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonOut(['success' => false, 'message' => 'Invalid email address.'], 422);
}
if (strlen($password) < 6) {
    jsonOut(['success' => false, 'message' => 'Password must be at least 6 characters.'], 422);
}

$db = getDB();

// Check duplicate email
$stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    jsonOut(['success' => false, 'message' => 'An account with this email already exists.'], 409);
}

// Hash password and insert
$hashed = password_hash($password, PASSWORD_BCRYPT);
$stmt   = $db->prepare("
    INSERT INTO users (name, email, password, department, phone, role)
    VALUES (?, ?, ?, ?, ?, 'user')
");
$stmt->execute([$name, $email, $hashed, $department, $phone]);

jsonOut(['success' => true, 'message' => 'Account created successfully. Please log in.']);
