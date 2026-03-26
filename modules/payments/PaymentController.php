<?php
require_once __DIR__ . '/../../includes/helpers.php';

class PaymentModel {
    private PDO $db;
    public function __construct() { $this->db = db(); }

    public function getAll(array $f=[]): array {
        $w=[]; $p=[];
        if (!empty($f['faculty_id']))  { $w[]='p.faculty_id=:fid'; $p[':fid']=$f['faculty_id']; }
        if (!empty($f['contract_id'])) { $w[]='p.contract_id=:cid'; $p[':cid']=$f['contract_id']; }
        if (!empty($f['status']))      { $w[]='p.status=:st'; $p[':st']=$f['status']; }
        if (!empty($f['search']))      { $w[]='p.invoice_number LIKE :s'; $p[':s']='%'.$f['search'].'%'; }
        $where = $w ? 'WHERE '.implode(' AND ',$w) : '';
        $sql = "SELECT p.*, u.name AS faculty_name, c.contract_number, c.title AS contract_title
                FROM payments p
                LEFT JOIN users u ON u.id=p.faculty_id
                LEFT JOIN contracts c ON c.id=p.contract_id
                $where ORDER BY p.created_at DESC";
        $stmt=$this->db->prepare($sql); $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $sql="SELECT p.*,u.name AS faculty_name,u.email AS faculty_email,c.contract_number,c.title AS contract_title
              FROM payments p LEFT JOIN users u ON u.id=p.faculty_id
              LEFT JOIN contracts c ON c.id=p.contract_id WHERE p.id=:id";
        $stmt=$this->db->prepare($sql); $stmt->execute([':id'=>$id]);
        return $stmt->fetch()?:null;
    }

    public function create(array $d): int {
        $inv='INV-'.date('Y').'-'.str_pad(rand(1000,9999),4,'0',STR_PAD_LEFT);
        $sql="INSERT INTO payments (contract_id,faculty_id,invoice_number,amount,currency,payment_type,status,due_date,payment_method,notes,created_by)
              VALUES(:cid,:fid,:inv,:amt,:cur,:pt,:st,:dd,:pm,:notes,:by)";
        $this->db->prepare($sql)->execute([':cid'=>$d['contract_id']??null,':fid'=>$d['faculty_id'],
            ':inv'=>$inv,':amt'=>$d['amount'],':cur'=>$d['currency']??'INR',
            ':pt'=>$d['payment_type']??'milestone',':st'=>$d['status']??'pending',
            ':dd'=>$d['due_date']??null,':pm'=>$d['payment_method']??null,
            ':notes'=>$d['notes']??null,':by'=>$d['created_by']??1]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool {
        $allowed=['amount','status','due_date','paid_date','payment_method','transaction_id','notes'];
        $fields=[]; $p=[':id'=>$id];
        foreach($allowed as $f){ if(isset($d[$f])){ $fields[]="$f=:$f"; $p[":$f"]=$d[$f]; } }
        if(!$fields) return false;
        return $this->db->prepare("UPDATE payments SET ".implode(',',$fields)." WHERE id=:id")->execute($p);
    }

    public function delete(int $id): bool { return $this->db->prepare("DELETE FROM payments WHERE id=:id")->execute([':id'=>$id]); }

    public function getStats(): array {
        return $this->db->query("SELECT COUNT(*) total, COALESCE(SUM(amount),0) total_amount,
            COALESCE(SUM(CASE WHEN status='paid' THEN amount ELSE 0 END),0) paid_amount,
            COALESCE(SUM(CASE WHEN status='pending' THEN amount ELSE 0 END),0) pending_amount,
            SUM(status='paid') paid_count, SUM(status='pending') pending_count,
            SUM(status='processing') processing_count FROM payments")->fetch();
    }

    public function getMonthly(): array {
        return $this->db->query("SELECT DATE_FORMAT(paid_date,'%Y-%m') AS month, COALESCE(SUM(amount),0) AS total
            FROM payments WHERE status='paid' AND paid_date IS NOT NULL
            GROUP BY month ORDER BY month DESC LIMIT 12")->fetchAll();
    }
}

class PaymentController {
    private PaymentModel $model;
    public function __construct() { $this->model = new PaymentModel(); }

    public function handle(string $method, array $seg): void {
        $id = isset($seg[0]) && is_numeric($seg[0]) ? (int)$seg[0] : null;
        if ($method==='GET'&&($seg[0]??'')==='stats')   { successResponse($this->model->getStats()); return; }
        if ($method==='GET'&&($seg[0]??'')==='monthly') { successResponse($this->model->getMonthly()); return; }
        if ($method==='GET'&&$id)  { $r=$this->model->getById($id); $r?successResponse($r):errorResponse('Not found',404); return; }
        if ($method==='GET')       { successResponse($this->model->getAll($_GET)); return; }
        if ($method==='POST')      { $d=json_decode(file_get_contents('php://input'),true)??[]; if(empty($d['faculty_id'])||empty($d['amount'])) errorResponse('faculty_id & amount required'); $id=$this->model->create($d); successResponse($this->model->getById($id),'Payment created'); return; }
        if (in_array($method,['PUT','PATCH'])&&$id) { $d=json_decode(file_get_contents('php://input'),true)??[]; $this->model->update($id,$d); successResponse($this->model->getById($id),'Updated'); return; }
        if ($method==='DELETE'&&$id) { $this->model->delete($id); successResponse(null,'Deleted'); return; }
        errorResponse('Bad request',400);
    }
}
