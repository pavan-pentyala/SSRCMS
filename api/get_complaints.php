<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

$user = requireAuth();

$db            = getDB();
$filterStatus  = $_GET['status']   ?? '';
$filterCat     = $_GET['category'] ?? '';

if ($user['role'] === 'admin') {
    // Admin: fetch all complaints with submitter info
    $sql    = "SELECT c.*, u.name AS user_name, u.email AS user_email, u.department
               FROM complaints c
               JOIN users u ON c.user_id = u.id
               WHERE 1=1";
    $params = [];

    if (!empty($filterStatus)) {
        $sql .= " AND c.status = ?";
        $params[] = $filterStatus;
    }
    if (!empty($filterCat)) {
        $sql .= " AND c.category = ?";
        $params[] = $filterCat;
    }

    $sql .= " ORDER BY c.created_at DESC";
} else {
    // User: fetch only their own complaints
    $sql    = "SELECT c.* FROM complaints c
               WHERE c.user_id = ?";
    $params = [$user['id']];

    if (!empty($filterStatus)) {
        $sql .= " AND c.status = ?";
        $params[] = $filterStatus;
    }
    if (!empty($filterCat)) {
        $sql .= " AND c.category = ?";
        $params[] = $filterCat;
    }

    $sql .= " ORDER BY c.created_at DESC";
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

jsonOut(['success' => true, 'data' => $complaints]);
