<?php
// Start session for auth
if (session_status() === PHP_SESSION_NONE) session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

// Public endpoints (no auth required)
$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri      = trim(preg_replace('#^.*?/api#', '', $uri), '/');
$segments = array_values(array_filter(explode('/', $uri)));
$method   = $_SERVER['REQUEST_METHOD'];
$resource = $segments[0] ?? '';
$rest     = array_slice($segments, 1);

// Auth endpoint — no login required
if ($resource === 'auth') {
    if ($method === 'POST' && ($rest[0]??'') === 'login') {
        $d = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $r = Auth::login($d['email'] ?? '', $d['password'] ?? '');
        $r['success'] ? successResponse($r['user'], 'Login successful') : errorResponse($r['message'], 401);
    } elseif ($method === 'POST' && ($rest[0]??'') === 'logout') {
        Auth::logout();
        successResponse(null, 'Logged out');
    } elseif ($method === 'GET' && ($rest[0]??'') === 'me') {
        Auth::require();
        successResponse(Auth::user());
    } else {
        errorResponse('Unknown auth endpoint', 404);
    }
    exit;
}

// All other endpoints require auth
Auth::require();

// Load controllers on demand
require_once __DIR__ . '/../modules/courses/CourseController.php';
require_once __DIR__ . '/../modules/topics/TopicController.php';
require_once __DIR__ . '/../modules/workflow/WorkflowController.php';
require_once __DIR__ . '/../modules/materials/MaterialController.php';
require_once __DIR__ . '/../modules/contracts/ContractController.php';
require_once __DIR__ . '/../modules/payments/PaymentController.php';
require_once __DIR__ . '/../modules/dashboard/DashboardController.php';
require_once __DIR__ . '/../modules/faculty/FacultyMasterController.php';
require_once __DIR__ . '/../modules/tasks/TaskController.php';

match($resource) {
    'courses'   => (new CourseController())->handle($method, $rest),
    'topics'    => (new TopicController())->handle($method, $rest),
    'workflow'  => (new WorkflowController())->handle($method, $rest),
    'materials' => (new MaterialController())->handle($method, $rest),
    'contracts' => (new ContractController())->handle($method, $rest),
    'payments'  => (new PaymentController())->handle($method, $rest),
    'dashboard' => (new DashboardController())->handle($method, $rest),
    'faculty'   => (new FacultyMasterController())->handle($method, $rest),
    'tasks'     => (new TaskController())->handle($method, $rest),
    default     => errorResponse("Unknown endpoint: /$resource", 404),
};
