<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    jsonOut(['success' => false, 'message' => 'Unauthorized.'], 401);
}

jsonOut([
    'success' => true,
    'id'      => $_SESSION['user_id'],
    'name'    => $_SESSION['name'],
    'role'    => $_SESSION['role'],
]);
