<?php
require_once __DIR__ . '/../../includes/helpers.php';

// ────────────────────────────────────────────────────────────
// Faculty Master Model
// ────────────────────────────────────────────────────────────
class FacultyMasterModel {
    private PDO $db;
    public function __construct() { $this->db = db(); }

    public function getAll(array $f = []): array {
        $w = []; $p = [];
        if (!empty($f['search'])) {
            $w[] = '(name LIKE :s OR email LIKE :s OR city LIKE :s OR designation LIKE :s)';
            $p[':s'] = '%' . $f['search'] . '%';
        }
        if (!empty($f['status'])) { $w[] = 'status = :st'; $p[':st'] = $f['status']; }
        $where = $w ? 'WHERE ' . implode(' AND ', $w) : '';
        $sql   = "SELECT f.*, u.name AS created_by_name,
                         COUNT(DISTINCT t.id) AS topic_count,
                         COUNT(DISTINCT c.id) AS contract_count
                  FROM faculty f
                  LEFT JOIN users u    ON u.id = f.created_by
                  LEFT JOIN topics t   ON t.faculty_id = f.id
                  LEFT JOIN contracts c ON c.faculty_id = f.id
                  $where GROUP BY f.id ORDER BY f.name";
        $stmt = $this->db->prepare($sql); $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT f.*, u.name AS created_by_name FROM faculty f LEFT JOIN users u ON u.id=f.created_by WHERE f.id=:id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getForDropdown(): array {
        return $this->db->query("SELECT id, name, designation, city FROM faculty WHERE status='active' ORDER BY name")->fetchAll();
    }

    public function create(array $d): int {
        $sql = "INSERT INTO faculty (name,mobile,email,city,state,country,designation,status,created_by)
                VALUES(:n,:m,:e,:c,:st,:co,:des,:s,:by)";
        $this->db->prepare($sql)->execute([
            ':n' => $d['name'], ':m' => $d['mobile'] ?? null, ':e' => $d['email'] ?? null,
            ':c' => $d['city'] ?? null, ':st' => $d['state'] ?? null, ':co' => $d['country'] ?? 'India',
            ':des' => $d['designation'] ?? null, ':s' => $d['status'] ?? 'active', ':by' => $d['created_by'] ?? 1,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool {
        $allowed = ['name','mobile','email','city','state','country','designation','status'];
        $fields  = []; $p = [':id' => $id];
        foreach ($allowed as $k) { if (isset($d[$k])) { $fields[] = "$k=:$k"; $p[":$k"] = $d[$k]; } }
        if (!$fields) return false;
        return $this->db->prepare("UPDATE faculty SET " . implode(',', $fields) . " WHERE id=:id")->execute($p);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM faculty WHERE id=:id")->execute([':id' => $id]);
    }

    public function getStats(): array {
        return $this->db->query("SELECT COUNT(*) total, SUM(status='active') active, SUM(status='inactive') inactive FROM faculty")->fetch();
    }

    public function getWithPaymentSummary(): array {
        $sql = "SELECT f.*,
                  COUNT(DISTINCT t.id) AS topic_count,
                  COUNT(DISTINCT c.id) AS contract_count,
                  COALESCE(SUM(CASE WHEN p.status='paid'    THEN p.amount ELSE 0 END),0) AS paid_amount,
                  COALESCE(SUM(CASE WHEN p.status='pending' THEN p.amount ELSE 0 END),0) AS pending_amount
                FROM faculty f
                LEFT JOIN topics    t ON t.faculty_id = f.id
                LEFT JOIN contracts c ON c.faculty_id = f.id
                LEFT JOIN payments  p ON p.faculty_id = f.id
                WHERE f.status = 'active'
                GROUP BY f.id ORDER BY topic_count DESC";
        return $this->db->query($sql)->fetchAll();
    }
}

// ────────────────────────────────────────────────────────────
// Faculty Master Controller
// ────────────────────────────────────────────────────────────
class FacultyMasterController {
    private FacultyMasterModel $model;
    public function __construct() { $this->model = new FacultyMasterModel(); }

    public function handle(string $method, array $seg): void {
        $id  = isset($seg[0]) && is_numeric($seg[0]) ? (int)$seg[0] : null;
        $sub = $seg[1] ?? null;

        if ($method === 'GET' && $sub === 'dropdown') { successResponse($this->model->getForDropdown()); return; }
        if ($method === 'GET' && $sub === 'stats')    { successResponse($this->model->getStats()); return; }
        if ($method === 'GET' && $id)   { $r=$this->model->getById($id); $r?successResponse($r):errorResponse('Not found',404); return; }
        if ($method === 'GET')          { successResponse($this->model->getAll($_GET)); return; }
        if ($method === 'POST')         { $this->store(); return; }
        if (in_array($method,['PUT','PATCH']) && $id) { $this->update($id); return; }
        if ($method === 'DELETE' && $id){ $this->model->delete($id); successResponse(null,'Deleted'); return; }
        errorResponse('Bad request', 400);
    }

    private function store(): void {
        $d = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($d['name'])) errorResponse('Name is required');
        $d['created_by'] = $_SESSION['user_id'] ?? 1;
        $id = $this->model->create($d);
        successResponse($this->model->getById($id), 'Faculty created');
    }

    private function update(int $id): void {
        $d = json_decode(file_get_contents('php://input'), true) ?? [];
        $this->model->update($id, $d);
        successResponse($this->model->getById($id), 'Faculty updated');
    }
}
