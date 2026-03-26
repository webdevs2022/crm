<?php
// ============================================================
// Authentication & RBAC Helper
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Role hierarchy — higher index = more power
const ROLE_LEVELS = [
    'coordinator' => 1,
    'accounts'    => 2,
    'admin'       => 3,
    'owner'       => 4,
];

// What each role can access (module => min role)
const MODULE_ACCESS = [
    'dashboard'  => 'coordinator',
    'courses'    => 'coordinator',
    'topics'     => 'coordinator',
    'workflow'   => 'coordinator',
    'materials'  => 'coordinator',
    'faculty'    => 'admin',
    'contracts'  => 'accounts',
    'payments'   => 'accounts',
    'tasks'      => 'coordinator',
    'users'      => 'admin',
];

class Auth {

    // ── Check if logged in ──────────────────────────────────
    public static function check(): bool {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    // ── Require login — redirect to login page if not ──────
    public static function require(): void {
        if (!self::check()) {
            if (self::isApiRequest()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Unauthenticated. Please log in.']);
                exit;
            }
            header('Location: /crm/login.php');
            exit;
        }
    }

    // ── Get current user ────────────────────────────────────
    public static function user(): ?array {
        if (!self::check()) return null;
        return [
            'id'    => $_SESSION['user_id'],
            'name'  => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role'  => $_SESSION['user_role'],
        ];
    }

    // ── Login ───────────────────────────────────────────────
    public static function login(string $email, string $password): array {
        require_once __DIR__ . '/../config/database.php';
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }

        // Regenerate session for security
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role']  = $user['role'];

        return ['success' => true, 'user' => [
            'id'   => $user['id'],
            'name' => $user['name'],
            'role' => $user['role'],
        ]];
    }

    // ── Logout ──────────────────────────────────────────────
    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    // ── Check role level ────────────────────────────────────
    public static function hasRole(string $minRole): bool {
        $userRole  = $_SESSION['user_role'] ?? 'coordinator';
        $userLevel = ROLE_LEVELS[$userRole]  ?? 0;
        $minLevel  = ROLE_LEVELS[$minRole]   ?? 99;
        return $userLevel >= $minLevel;
    }

    // ── Require a minimum role ──────────────────────────────
    public static function requireRole(string $minRole): void {
        self::require();
        if (!self::hasRole($minRole)) {
            if (self::isApiRequest()) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access denied. Insufficient permissions.']);
                exit;
            }
            http_response_code(403);
            echo '<h2>403 — Access Denied</h2><p>You do not have permission to access this area.</p>';
            exit;
        }
    }

    // ── Check module access ─────────────────────────────────
    public static function canAccess(string $module): bool {
        $minRole = MODULE_ACCESS[$module] ?? 'owner';
        return self::hasRole($minRole);
    }

    // ── Coordinator course restriction ──────────────────────
    // Returns array of course IDs the current user can access.
    // Owner/Admin/Accounts can access ALL courses.
    public static function allowedCourseIds(): ?array {
        if (self::hasRole('admin')) return null; // null = all
        // Coordinator: only assigned courses
        require_once __DIR__ . '/../config/database.php';
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id FROM courses WHERE coordinator_id = :uid");
        $stmt->execute([':uid' => $_SESSION['user_id']]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // ── Is JSON/API request? ────────────────────────────────
    private static function isApiRequest(): bool {
        return (
            (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
            (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
            strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false
        );
    }
}
