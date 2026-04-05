<?php
require_once __DIR__ . '/UserModel.php';
require_once __DIR__ . '/../../includes/auth.php';

class UserController {
    private UserModel $model;
    public function __construct() { $this->model = new UserModel(); }

    public function handle(string $method, array $seg): void {
        Auth::require();
        // Permission check: only admin and owner can see users list.
        if (!Auth::hasRole('admin')) {
            errorResponse('Access Denied', 403);
        }

        $id = isset($seg[0]) && is_numeric($seg[0]) ? (int)$seg[0] : null;

        if ($method === 'GET' && !$id) {
            successResponse($this->model->getAll());
            return;
        }

        if ($method === 'GET' && $id) {
            $r = $this->model->getById($id);
            $r ? successResponse($r) : errorResponse('User not found', 404);
            return;
        }

        // Writing operations (POST, PUT, DELETE) — strictly Owner only.
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            if (!Auth::hasRole('owner')) {
                errorResponse('Unauthorized. Only Owner can modify users.', 403);
            }
        }

        if ($method === 'POST') {
            $this->store();
            return;
        }

        if (in_array($method, ['PUT', 'PATCH']) && $id) {
            $this->update($id);
            return;
        }

        if ($method === 'DELETE' && $id) {
            $this->model->delete($id);
            successResponse(null, 'User deleted');
            return;
        }

        errorResponse('Request method not supported', 400);
    }

    private function store(): void {
        $d = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($d['name']) || empty($d['email']) || empty($d['password'])) {
            errorResponse('Name, email, and password are required.');
        }
        $id = $this->model->create($d);
        successResponse($this->model->getById($id), 'User created successfully.');
    }

    private function update(int $id): void {
        $d = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($d['name']) || empty($d['email'])) {
            errorResponse('Name and email are required.');
        }
        $this->model->update($id, $d);
        successResponse($this->model->getById($id), 'User updated successfully.');
    }
}
