<?php
require_once __DIR__ . '/../../includes/helpers.php';

class FacultyController {
    private PDO $db;
    public function __construct() { $this->db = db(); }

    public function handle(string $method, array $seg): void {
        $id = isset($seg[0])&&is_numeric($seg[0]) ? (int)$seg[0] : null;
        if ($method==='GET'&&$id)  { $this->show($id); return; }
        if ($method==='GET')       { $this->index(); return; }
        if ($method==='POST')      { $this->store(); return; }
        if (in_array($method,['PUT','PATCH'])&&$id) { $this->update($id); return; }
        errorResponse('Method not allowed',405);
    }

    private function index(): void {
        $sql="SELECT u.*, COUNT(DISTINCT c.id) AS active_contracts,
                     COALESCE(SUM(CASE WHEN p.status='paid' THEN p.amount ELSE 0 END),0) AS total_paid
              FROM users u
              LEFT JOIN contracts c ON c.faculty_id=u.id AND c.status='active'
              LEFT JOIN payments p ON p.faculty_id=u.id
              WHERE u.role='faculty'
              GROUP BY u.id ORDER BY u.name";
        successResponse($this->db->query($sql)->fetchAll());
    }

    private function show(int $id): void {
        $u = $this->db->prepare("SELECT * FROM users WHERE id=:id AND role='faculty'");
        $u->execute([':id'=>$id]); $user=$u->fetch();
        if (!$user) { errorResponse('Faculty not found',404); return; }

        $contracts = $this->db->prepare("SELECT c.*, co.title AS course_title FROM contracts c LEFT JOIN courses co ON co.id=c.course_id WHERE c.faculty_id=:id ORDER BY c.created_at DESC");
        $contracts->execute([':id'=>$id]);

        $payments = $this->db->prepare("SELECT * FROM payments WHERE faculty_id=:id ORDER BY created_at DESC LIMIT 10");
        $payments->execute([':id'=>$id]);

        $topics = $this->db->prepare("SELECT t.*, c.title AS course_title FROM topics t LEFT JOIN courses c ON c.id=t.course_id WHERE t.faculty_id=:id ORDER BY t.scheduled_at DESC LIMIT 10");
        $topics->execute([':id'=>$id]);

        successResponse([
            'user'      => $user,
            'contracts' => $contracts->fetchAll(),
            'payments'  => $payments->fetchAll(),
            'topics'    => $topics->fetchAll(),
        ]);
    }

    private function store(): void {
        $d=json_decode(file_get_contents('php://input'),true)??[];
        if(empty($d['name'])||empty($d['email'])) errorResponse('name & email required');
        $pw = password_hash($d['password']??'password123', PASSWORD_BCRYPT);
        $stmt=$this->db->prepare("INSERT INTO users (name,email,password,role,phone,status) VALUES(:n,:e,:p,'faculty',:ph,:st)");
        $stmt->execute([':n'=>$d['name'],':e'=>$d['email'],':p'=>$pw,':ph'=>$d['phone']??null,':st'=>$d['status']??'active']);
        $id=(int)$this->db->lastInsertId();
        $u=$this->db->prepare("SELECT * FROM users WHERE id=:id"); $u->execute([':id'=>$id]);
        successResponse($u->fetch(),'Faculty created');
    }

    private function update(int $id): void {
        $d=json_decode(file_get_contents('php://input'),true)??[];
        $allowed=['name','email','phone','status']; $fields=[]; $p=[':id'=>$id];
        foreach($allowed as $f){ if(isset($d[$f])){ $fields[]="$f=:$f"; $p[":$f"]=$d[$f]; } }
        if($fields) $this->db->prepare("UPDATE users SET ".implode(',',$fields)." WHERE id=:id")->execute($p);
        $u=$this->db->prepare("SELECT * FROM users WHERE id=:id"); $u->execute([':id'=>$id]);
        successResponse($u->fetch(),'Updated');
    }
}
