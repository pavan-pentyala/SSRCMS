<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonOut(['success' => false, 'message' => 'Method not allowed.'], 405);
}

requireAdmin();

$complaintId = intval($_POST['complaint_id'] ?? 0);
$status      = trim($_POST['status']         ?? '');
$assignedTo  = trim($_POST['assigned_to']    ?? '');
$adminNotes  = trim($_POST['admin_notes']    ?? '');

if ($complaintId <= 0) {
    jsonOut(['success' => false, 'message' => 'Invalid complaint ID.'], 422);
}

$validStatuses = ['Pending', 'In-Progress', 'Resolved', 'Closed'];
if (!in_array($status, $validStatuses)) {
    jsonOut(['success' => false, 'message' => 'Invalid status value.'], 422);
}

$db   = getDB();

// Verify complaint exists
$stmt = $db->prepare("SELECT id FROM complaints WHERE id = ? LIMIT 1");
$stmt->execute([$complaintId]);
if (!$stmt->fetch()) {
    jsonOut(['success' => false, 'message' => 'Complaint not found.'], 404);
}

// Update
$stmt = $db->prepare("
    UPDATE complaints
    SET status = ?, assigned_to = ?, admin_notes = ?, updated_at = NOW()
    WHERE id = ?
");
$stmt->execute([$status, $assignedTo ?: null, $adminNotes ?: null, $complaintId]);

// Return updated row
$stmt = $db->prepare("SELECT * FROM complaints WHERE id = ?");
$stmt->execute([$complaintId]);
$updated = $stmt->fetch();

jsonOut([
    'success'    => true,
    'message'    => 'Complaint updated successfully.',
    'complaint'  => $updated,
]);
