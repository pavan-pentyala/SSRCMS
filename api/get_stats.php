<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

requireAdmin();

try {
    $db = getDB();

    // Total & by-status counts (Used for Overview cards)
    $stmtStatus = $db->query("
        SELECT status, COUNT(*) AS cnt
        FROM complaints
        GROUP BY status
    ");
    $byStatus = $stmtStatus->fetchAll();

    $statusMap = ['Pending' => 0, 'In-Progress' => 0, 'Resolved' => 0, 'Closed' => 0];
    $total     = 0;
    foreach ($byStatus as $row) {
        $statusMap[$row['status']] = (int)$row['cnt'];
        $total += (int)$row['cnt'];
    }

    // By-category counts (Could be used for summary if needed)
    $stmtCat = $db->query("
        SELECT category, COUNT(*) AS cnt
        FROM complaints
        GROUP BY category
        ORDER BY cnt DESC
    ");
    $byCategory = $stmtCat->fetchAll();

    $categoryMap = [];
    foreach ($byCategory as $row) {
        $categoryMap[$row['category']] = (int)$row['cnt'];
    }

    // Removed monthly trend query as Analytics tab is being removed

    jsonOut([
        'success'    => true,
        'total'      => $total,
        'byStatus'   => $statusMap,
        'byCategory' => $categoryMap
    ]);

} catch (PDOException $e) {
    jsonOut(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
}
