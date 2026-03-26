<?php
require_once __DIR__ . '/../../includes/helpers.php';

class ContractModel {
    private PDO $db;
    public function __construct() { $this->db = db(); }

    public function getAll(array $f=[]): array {
        $w=[]; $p=[];
        if (!empty($f['faculty_id'])) { $w[]='c.faculty_id=:fid'; $p[':fid']=$f['faculty_id']; }
        if (!empty($f['status']))     { $w[]='c.status=:st';      $p[':st']=$f['status']; }
        if (!empty($f['search']))     { $w[]='(c.title LIKE :s OR c.contract_number LIKE :s)'; $p[':s']='%'.$f['search'].'%'; }
        $where = $w ? 'WHERE '.implode(' AND ',$w) : '';
        $sql = "SELECT c.*, u.name AS faculty_name, co.title AS course_title, co.code AS course_code,
                       (SELECT COALESCE(SUM(amount),0) FROM payments p WHERE p.contract_id=c.id AND p.status='paid') AS paid_amount
                FROM contracts c
                LEFT JOIN users u ON u.id=c.faculty_id
                LEFT JOIN courses co ON co.id=c.course_id
                $where ORDER BY c.created_at DESC";
        $stmt=$this->db->prepare($sql); $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array {
        $sql="SELECT c.*, u.name AS faculty_name, u.email AS faculty_email,
                     co.title AS course_title, co.code AS course_code,
                     (SELECT COALESCE(SUM(amount),0) FROM payments p WHERE p.contract_id=c.id AND p.status='paid') AS paid_amount
              FROM contracts c LEFT JOIN users u ON u.id=c.faculty_id
              LEFT JOIN courses co ON co.id=c.course_id WHERE c.id=:id";
        $stmt=$this->db->prepare($sql); $stmt->execute([':id'=>$id]);
        return $stmt->fetch()?:null;
    }

    public function create(array $d): int {
        $num = 'CNT-'.date('Y').'-'.str_pad(rand(100,999),3,'0',STR_PAD_LEFT);
        $sql="INSERT INTO contracts (faculty_id,course_id,contract_number,title,start_date,end_date,total_amount,currency,status,terms,created_by)
              VALUES(:fid,:cid,:num,:title,:sd,:ed,:amt,:cur,:st,:terms,:by)";
        $this->db->prepare($sql)->execute([':fid'=>$d['faculty_id'],':cid'=>$d['course_id']??null,':num'=>$num,
            ':title'=>$d['title'],':sd'=>$d['start_date'],':ed'=>$d['end_date']??null,
            ':amt'=>$d['total_amount']??0,':cur'=>$d['currency']??'INR',':st'=>$d['status']??'draft',
            ':terms'=>$d['terms']??null,':by'=>$d['created_by']??1]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool {
        $allowed=['title','start_date','end_date','total_amount','currency','status','terms','signed_at'];
        $fields=[]; $p=[':id'=>$id];
        foreach($allowed as $f){ if(isset($d[$f])){ $fields[]="$f=:$f"; $p[":$f"]=$d[$f]; } }
        if(!$fields) return false;
        return $this->db->prepare("UPDATE contracts SET ".implode(',',$fields)." WHERE id=:id")->execute($p);
    }

    public function delete(int $id): bool { return $this->db->prepare("DELETE FROM contracts WHERE id=:id")->execute([':id'=>$id]); }

    public function getStats(): array {
        return $this->db->query("SELECT COUNT(*) total, SUM(status='active') active, SUM(status='draft') draft, SUM(status='completed') completed, SUM(status='cancelled') cancelled, COALESCE(SUM(total_amount),0) total_value FROM contracts")->fetch();
    }
}

class ContractController {
    private ContractModel $model;
    public function __construct() { $this->model = new ContractModel(); }

    public function handle(string $method, array $seg): void {
        $id = isset($seg[0]) && is_numeric($seg[0]) ? (int)$seg[0] : null;
        if ($method==='GET'&&($seg[0]??'')==='stats') { successResponse($this->model->getStats()); return; }
        if ($method==='GET'&&$id)  { $r=$this->model->getById($id); $r?successResponse($r):errorResponse('Not found',404); return; }
        if ($method==='GET')       { successResponse($this->model->getAll($_GET)); return; }
        if ($method==='POST')      { $d=json_decode(file_get_contents('php://input'),true)??[]; if(empty($d['faculty_id'])||empty($d['title'])||empty($d['start_date'])) errorResponse('faculty_id, title, start_date required'); $id=$this->model->create($d); successResponse($this->model->getById($id),'Contract created'); return; }
        if (in_array($method,['PUT','PATCH'])&&$id) { $d=json_decode(file_get_contents('php://input'),true)??[]; $this->model->update($id,$d); successResponse($this->model->getById($id),'Updated'); return; }
        if ($method==='DELETE'&&$id) { $this->model->delete($id); successResponse(null,'Deleted'); return; }
        errorResponse('Bad request',400);
    }
}
