<?php
require_once __DIR__ . '/../../config/database.php';

class UserModel {
    private $db;
    public function __construct() { 
        $this->db = Database::getInstance()->getConnection(); 
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT id, name, email, role, phone, status, created_at FROM users ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT id, name, email, role, phone, status, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO users (name, email, password, role, phone, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $pass = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt->execute([
            $data['name'], 
            $data['email'], 
            $pass, 
            $data['role'] ?? 'coordinator', 
            $data['phone'] ?? null, 
            $data['status'] ?? 'active'
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $fields = ["name=?", "email=?", "role=?", "phone=?", "status=?"];
        $params = [$data['name'], $data['email'], $data['role'], $data['phone'] ?? null, $data['status'] ?? 'active'];
        
        if(!empty($data['password'])) {
            $fields[] = "password=?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id=?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
