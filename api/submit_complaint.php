<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(['success' => false, 'message' => 'Method not allowed.'], 405);
}

$user = requireAuth();

$title       = trim($_POST['title']       ?? '');
$category    = trim($_POST['category']    ?? '');
$priority    = trim($_POST['priority']    ?? 'Low');
$description = trim($_POST['description'] ?? '');

// Validation
if (empty($title)) {
    jsonOut(['success' => false, 'message' => 'Complaint title is required.'], 422);
}

$validCategories = ['Electrical', 'Network', 'Maintenance', 'Plumbing', 'Other'];
$validPriorities = ['Low', 'Medium', 'High', 'Critical'];

if (!in_array($category, $validCategories)) {
    jsonOut(['success' => false, 'message' => 'Invalid category selected.'], 422);
}
if (!in_array($priority, $validPriorities)) {
    jsonOut(['success' => false, 'message' => 'Invalid priority selected.'], 422);
}

$db   = getDB();
$stmt = $db->prepare("
    INSERT INTO complaints (user_id, title, category, priority, description, status)
    VALUES (?, ?, ?, ?, ?, 'Pending')
");
$stmt->execute([$user['id'], $title, $category, $priority, $description]);
$newId = $db->lastInsertId();

jsonOut([
    'success'      => true,
    'message'      => 'Complaint submitted successfully.',
    'complaint_id' => $newId,
]);
