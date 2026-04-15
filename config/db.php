<?php
// ─── Database Configuration ───────────────────────────────────────────────────
require_once __DIR__ . '/config.php';

// ─── PDO Connection (Singleton) ───────────────────────────────────────────────
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            die(json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// ─── Auth Helpers ─────────────────────────────────────────────────────────────
function requireAuth(): array {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']));
    }
    return [
        'id'   => $_SESSION['user_id'],
        'role' => $_SESSION['role'],
        'name' => $_SESSION['name'],
    ];
}

function requireAdmin(): void {
    requireAuth();
    if ($_SESSION['role'] !== 'admin') {
        http_response_code(403);
        header('Content-Type: application/json');
        die(json_encode(['success' => false, 'message' => 'Forbidden. Admin access required.']));
    }
}

// ─── Response Helper ──────────────────────────────────────────────────────────
function jsonOut(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    die(json_encode($data));
}
