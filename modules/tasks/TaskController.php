<?php
require_once __DIR__ . '/../../includes/helpers.php';

class TaskController {
    private PDO $db;
    public function __construct() { $this->db = db(); }

    public function handle(string $method, array $seg): void {
        $id = isset($seg[0]) && is_numeric($seg[0]) ? (int)$seg[0] : null;
        if ($method==='GET' && ($seg[0]??'')==='stats') { $this->stats(); return; }
        if ($method==='GET' && $id)   { $this->show($id); return; }
        if ($method==='GET')          { $this->index(); return; }
        if ($method==='POST')         { $this->store(); return; }
        if (in_array($method,['PUT','PATCH']) && $id) { $this->update($id); return; }
        if ($method==='DELETE' && $id){ $this->delete($id); return; }
        errorResponse('Bad request', 400);
    }

    private function index(): void {
        $w = []; $p = [];
        if (!empty($_GET['assigned_to'])) { $w[] = 't.assigned_to=:at'; $p[':at']=$_GET['assigned_to']; }
        if (!empty($_GET['status']))      { $w[] = 't.status=:st'; $p[':st']=$_GET['status']; }
        if (!empty($_GET['priority']))    { $w[] = 't.priority=:pr'; $p[':pr']=$_GET['priority']; }
        if (!empty($_GET['module']))      { $w[] = 't.related_module=:rm'; $p[':rm']=$_GET['module']; }
        $where = $w ? 'WHERE '.implode(' AND ',$w) : '';
        $sql = "SELECT t.*, u.name AS assigned_to_name, a.name AS assigned_by_name
                FROM tasks t
                LEFT JOIN users u ON u.id=t.assigned_to
                LEFT JOIN users a ON a.id=t.assigned_by
                $where ORDER BY
                  FIELD(t.priority,'urgent','high','medium','low'),
                  t.due_date ASC, t.created_at DESC";
        $stmt = $this->db->prepare($sql); $stmt->execute($p);
        successResponse($stmt->fetchAll());
    }

    private function show(int $id): void {
        $stmt = $this->db->prepare("SELECT t.*,u.name AS assigned_to_name,a.name AS assigned_by_name FROM tasks t LEFT JOIN users u ON u.id=t.assigned_to LEFT JOIN users a ON a.id=t.assigned_by WHERE t.id=:id");
        $stmt->execute([':id'=>$id]); $r=$stmt->fetch();
        $r ? successResponse($r) : errorResponse('Not found',404);
    }

    private function store(): void {
        $d = json_decode(file_get_contents('php://input'),true) ?? [];
        if (empty($d['title'])) errorResponse('Title required');
        $d['assigned_by'] = $_SESSION['user_id'] ?? 1;
        $stmt = $this->db->prepare("INSERT INTO tasks (title,description,assigned_to,assigned_by,related_module,related_id,priority,due_date,status,notes) VALUES(:ti,:de,:at,:ab,:rm,:ri,:pr,:dd,:st,:no)");
        $stmt->execute([':ti'=>$d['title'],':de'=>$d['description']??null,':at'=>$d['assigned_to']??null,':ab'=>$d['assigned_by'],':rm'=>$d['related_module']??'general',':ri'=>$d['related_id']??null,':pr'=>$d['priority']??'medium',':dd'=>$d['due_date']??null,':st'=>$d['status']??'open',':no'=>$d['notes']??null]);
        $id = (int)$this->db->lastInsertId();
        $this->show($id);
    }

    private function update(int $id): void {
        $d = json_decode(file_get_contents('php://input'),true) ?? [];
        $allowed = ['title','description','assigned_to','related_module','related_id','priority','due_date','status','notes'];
        $fields=[]; $p=[':id'=>$id];
        // Auto set completed_at
        if (isset($d['status']) && $d['status']==='completed') { $fields[]='completed_at=NOW()'; }
        foreach($allowed as $k){ if(array_key_exists($k, $d)){ $fields[]="$k=:$k"; $p[":$k"]=$d[$k]; } }
        if($fields) $this->db->prepare("UPDATE tasks SET ".implode(',',$fields)." WHERE id=:id")->execute($p);
        $this->show($id);
    }

    private function delete(int $id): void {
        $this->db->prepare("DELETE FROM tasks WHERE id=:id")->execute([':id'=>$id]);
        successResponse(null,'Deleted');
    }

    private function stats(): void {
        $uid = $_SESSION['user_id'] ?? null;
        $row = $this->db->query("SELECT COUNT(*) total, SUM(status='open') open, SUM(status='in_progress') in_progress, SUM(status='completed') completed, SUM(priority='urgent') urgent, SUM(priority='high') high FROM tasks")->fetch();
        successResponse($row);
    }
}
