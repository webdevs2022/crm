<?php
require_once __DIR__ . '/../config/database.php';

// ============================================================
// Response Helpers
// ============================================================
function jsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function successResponse($data = null, string $message = 'Success'): void {
    jsonResponse(['success' => true, 'message' => $message, 'data' => $data]);
}

function errorResponse(string $message, int $statusCode = 400): void {
    jsonResponse(['success' => false, 'message' => $message], $statusCode);
}

// ============================================================
// Input Sanitization
// ============================================================
function sanitize(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function getInput(string $key, $default = null) {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $merged = array_merge($_GET, $_POST, $data);
    return isset($merged[$key]) ? sanitize((string)$merged[$key]) : $default;
}

// ============================================================
// Pagination Helper
// ============================================================
function getPagination(int $total, int $page = 1, int $perPage = 10): array {
    $totalPages = (int)ceil($total / $perPage);
    $page = max(1, min($page, $totalPages ?: 1));
    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current_page'=> $page,
        'total_pages' => $totalPages,
        'offset'      => ($page - 1) * $perPage,
    ];
}

// ============================================================
// DB instance shorthand
// ============================================================
function db(): PDO {
    return Database::getInstance()->getConnection();
}
