<?php
// ============================================================
// Material Controller — v3 Schema
// Types: osce, mcq, true_false, dicom_long_case, dicom_short_case, spotters
// Tracks received_count, uploaded_count (pending = received - uploaded)
// ============================================================
require_once __DIR__ . '/../../includes/helpers.php';

class MaterialModel {
    private PDO $db;
    public function __construct() { $this->db = db(); }

    public function getAll(array $f = []): array {
        $w = []; $p = [];
        if (!empty($f['course_id']))     { $w[] = 'm.course_id=:cid';      $p[':cid'] = $f['course_id']; }
        if (!empty($f['topic_id']))      { $w[] = 'm.topic_id=:tid';       $p[':tid'] = $f['topic_id']; }
        if (!empty($f['material_type'])) { $w[] = 'm.material_type=:mty';  $p[':mty'] = $f['material_type']; }
        $where = $w ? 'WHERE ' . implode(' AND ', $w) : '';
        $sql = "SELECT m.*,
                  (m.received_count - m.uploaded_count) AS pending_count,
                  c.title AS course_title, c.course_number,
                  t.title AS topic_title,
                  u.name  AS uploaded_by_name
                FROM materials m
                LEFT JOIN courses c ON c.id = m.course_id
                LEFT JOIN topics  t ON t.id = m.topic_id
                LEFT JOIN users   u ON u.id = m.uploaded_by
                $where ORDER BY m.created_at DESC";
        $stmt = $this->db->prepare($sql); $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT m.*, (m.received_count - m.uploaded_count) AS pending_count,
              c.title AS course_title, c.course_number, t.title AS topic_title, u.name AS uploaded_by_name
             FROM materials m
             LEFT JOIN courses c ON c.id=m.course_id
             LEFT JOIN topics  t ON t.id=m.topic_id
             LEFT JOIN users   u ON u.id=m.uploaded_by
             WHERE m.id=:id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $d): int {
        $recv = (int)($d['received_count'] ?? 0);
        $upl  = min((int)($d['uploaded_count'] ?? 0), $recv);
        $status = $recv === 0 ? 'pending' : ($upl >= $recv ? 'complete' : ($upl > 0 ? 'partial' : 'pending'));
        $sql = "INSERT INTO materials
                  (course_id, topic_id, material_type, received_count, uploaded_count, status, notes, uploaded_by)
                VALUES (:cid,:tid,:mty,:recv,:upl,:st,:notes,:by)";
        $this->db->prepare($sql)->execute([
            ':cid'   => $d['course_id'],
            ':tid'   => $d['topic_id']       ?? null,
            ':mty'   => $d['material_type']  ?? 'mcq',
            ':recv'  => $recv,
            ':upl'   => $upl,
            ':st'    => $status,
            ':notes' => $d['notes']          ?? null,
            ':by'    => $d['uploaded_by']    ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool {
        $allowed = ['course_id', 'topic_id', 'material_type', 'received_count', 'uploaded_count', 'notes', 'status'];
        $fields = []; $p = [':id' => $id];
        
        // Recalculate status if counts are being updated
        if (array_key_exists('received_count', $d) || array_key_exists('uploaded_count', $d)) {
            $row = $this->getById($id);
            $recv = (int)(array_key_exists('received_count', $d) ? $d['received_count'] : $row['received_count']);
            $upl  = (int)(array_key_exists('uploaded_count', $d) ? $d['uploaded_count'] : $row['uploaded_count']);
            $upl  = min($upl, $recv);
            $d['status'] = $recv === 0 ? 'pending' : ($upl >= $recv ? 'complete' : ($upl > 0 ? 'partial' : 'pending'));
            $d['uploaded_count'] = $upl; // Ensure uploaded never exceeds received
        }

        foreach ($allowed as $k) {
            if (array_key_exists($k, $d)) { 
                $fields[] = "$k=:$k"; 
                $p[":$k"] = $d[$k]; 
            }
        }

        if (!$fields) return false;
        $sql = "UPDATE materials SET " . implode(', ', $fields) . " WHERE id=:id";
        return $this->db->prepare($sql)->execute($p);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM materials WHERE id=:id")->execute([':id' => $id]);
    }

    public function getStats(): array {
        return $this->db->query(
            "SELECT COUNT(*) total,
              COALESCE(SUM(received_count),0)  total_received,
              COALESCE(SUM(uploaded_count),0)  total_uploaded,
              COALESCE(SUM(received_count - uploaded_count),0) total_pending,
              SUM(status='complete') complete,
              SUM(status='partial')  partial,
              SUM(status='pending')  pending
             FROM materials"
        )->fetch();
    }
}

class MaterialController {
    private MaterialModel $model;
    public function __construct() { $this->model = new MaterialModel(); }

    public function handle(string $method, array $seg): void {
        $id = isset($seg[0]) && is_numeric($seg[0]) ? (int)$seg[0] : null;
        if ($method === 'GET' && ($seg[0] ?? '') === 'stats') { successResponse($this->model->getStats()); return; }
        if ($method === 'GET' && $id)   { $r = $this->model->getById($id); $r ? successResponse($r) : errorResponse('Not found', 404); return; }
        if ($method === 'GET')          { successResponse($this->model->getAll($_GET)); return; }
        if ($method === 'POST')         { $this->store(); return; }
        if (in_array($method, ['PUT','PATCH']) && $id) { $this->update($id); return; }
        if ($method === 'DELETE' && $id){ $this->model->delete($id); successResponse(null, 'Deleted'); return; }
        errorResponse('Bad request', 400);
    }

    private function store(): void {
        $d = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        if (empty($d['course_id']) || empty($d['material_type'])) {
            errorResponse('course_id and material_type required');
        }
        $d['uploaded_by'] = $_SESSION['user_id'] ?? null;
        $id = $this->model->create($d);
        successResponse($this->model->getById($id), 'Material entry added');
    }

    private function update(int $id): void {
        $d = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $this->model->update($id, $d);
        successResponse($this->model->getById($id), 'Updated');
    }
}
