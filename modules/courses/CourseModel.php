<?php
require_once __DIR__ . '/../../includes/helpers.php';

class CourseModel {
    private PDO $db;
    public function __construct() { $this->db = db(); }

    public function generateCourseNumber(): string {
        $year = date('Y');
        $this->db->prepare("INSERT INTO course_number_seq (year,seq) VALUES(:y,1) ON DUPLICATE KEY UPDATE seq=seq+1")->execute([':y'=>$year]);
        $row = $this->db->prepare("SELECT seq FROM course_number_seq WHERE year=:y");
        $row->execute([':y'=>$year]);
        $seq = (int)$row->fetchColumn();
        return 'CRS-'.$year.'-'.str_pad($seq,3,'0',STR_PAD_LEFT);
    }

    public function getAll(array $filters=[], ?array $allowedIds=null): array {
        $w=['1=1']; $p=[];
        if($allowedIds!==null){
            if(empty($allowedIds)) return['courses'=>[],'pagination'=>getPagination(0)];
            $ph=implode(',',array_fill(0,count($allowedIds),'?'));
            $w[]="c.id IN ($ph)";
        }
        if(!empty($filters['search'])){$w[]='(c.title LIKE :s OR c.course_number LIKE :s OR c.category LIKE :s)';$p[':s']='%'.$filters['search'].'%';}
        if(!empty($filters['status'])){$w[]='c.status=:st';$p[':st']=$filters['status'];}
        if(!empty($filters['level'])){$w[]='c.level=:lv';$p[':lv']=$filters['level'];}
        $where=implode(' AND ',$w);
        $cStmt=$this->db->prepare("SELECT COUNT(*) FROM courses c WHERE $where");
        if($allowedIds!==null){foreach($allowedIds as $i=>$id)$cStmt->bindValue($i+1,$id,PDO::PARAM_INT);}
        foreach($p as $k=>$v)$cStmt->bindValue($k,$v);
        $cStmt->execute();
        $total=(int)$cStmt->fetchColumn();
        $page=max(1,(int)($filters['page']??1));$pp=(int)($filters['per_page']??20);
        $pag=getPagination($total,$page,$pp);
        $sql="SELECT c.*,u.name AS coordinator_name,cb.name AS created_by_name,COUNT(DISTINCT t.id) AS topic_count,SUM(t.status='completed') AS topics_done FROM courses c LEFT JOIN users u ON u.id=c.coordinator_id LEFT JOIN users cb ON cb.id=c.created_by LEFT JOIN topics t ON t.course_id=c.id WHERE $where GROUP BY c.id ORDER BY c.created_at DESC LIMIT :lim OFFSET :off";
        $stmt=$this->db->prepare($sql);
        if($allowedIds!==null){foreach($allowedIds as $i=>$id)$stmt->bindValue($i+1,$id,PDO::PARAM_INT);}
        foreach($p as $k=>$v)$stmt->bindValue($k,$v);
        $stmt->bindValue(':lim',$pag['per_page'],PDO::PARAM_INT);$stmt->bindValue(':off',$pag['offset'],PDO::PARAM_INT);
        $stmt->execute();
        return['courses'=>$stmt->fetchAll(),'pagination'=>$pag];
    }

    public function getById(int $id): ?array {
        $stmt=$this->db->prepare("SELECT c.*,u.name AS coordinator_name,cb.name AS created_by_name,COUNT(DISTINCT t.id) AS topic_count,SUM(t.status='completed') AS topics_done FROM courses c LEFT JOIN users u ON u.id=c.coordinator_id LEFT JOIN users cb ON cb.id=c.created_by LEFT JOIN topics t ON t.course_id=c.id WHERE c.id=:id GROUP BY c.id");
        $stmt->execute([':id'=>$id]); return $stmt->fetch()?:null;
    }

    public function getProgress(int $id): array {
        $lr=$this->db->prepare("SELECT COUNT(*) total, SUM(status='completed') done FROM topics WHERE course_id=:id");
        $lr->execute([':id'=>$id]);$lt=$lr->fetch();
        $lectPct=$lt['total']?round($lt['done']/$lt['total']*100):0;
        $mr=$this->db->prepare("SELECT SUM(received_count) recv,SUM(uploaded_count) upl FROM materials WHERE course_id=:id");
        $mr->execute([':id'=>$id]);$mt=$mr->fetch();
        $matPct=($mt['recv']??0)>0?round(($mt['upl']/$mt['recv'])*100):0;
        $cr=$this->db->prepare("SELECT SUM(total_amount) total FROM contracts WHERE course_id=:id AND status!='cancelled'");
        $cr->execute([':id'=>$id]);$ct=$cr->fetch();
        $pr=$this->db->prepare("SELECT COALESCE(SUM(p.amount),0) paid FROM payments p JOIN contracts c ON c.id=p.contract_id WHERE c.course_id=:id AND p.status='paid'");
        $pr->execute([':id'=>$id]);$paid=(float)$pr->fetchColumn();
        $cnPct=($ct['total']??0)>0?round($paid/($ct['total'])*100):0;
        $overall=round($lectPct*0.4+$matPct*0.3+$cnPct*0.3);
        return['lecture_progress'=>$lectPct,'material_progress'=>$matPct,'contract_progress'=>$cnPct,'overall_progress'=>$overall,'lecture_total'=>(int)($lt['total']??0),'lecture_done'=>(int)($lt['done']??0),'material_received'=>(int)($mt['recv']??0),'material_uploaded'=>(int)($mt['upl']??0),'contract_total'=>(float)($ct['total']??0),'contract_paid'=>$paid];
    }

    public function create(array $d): int {
        $num=$this->generateCourseNumber();
        $this->db->prepare("INSERT INTO courses (course_number,title,description,category,level,status,coordinator_id,created_by) VALUES(:num,:ti,:de,:ca,:lv,:st,:co,:by)")->execute([':num'=>$num,':ti'=>$d['title'],':de'=>$d['description']??null,':ca'=>$d['category']??null,':lv'=>$d['level']??'beginner',':st'=>$d['status']??'draft',':co'=>$d['coordinator_id']??null,':by'=>$d['created_by']??1]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool {
        $allowed=['title','description','category','level','status','coordinator_id'];$fields=[];$p=[':id'=>$id];
        foreach($allowed as $k){if(isset($d[$k])){$fields[]="$k=:$k";$p[":$k"]=$d[$k];}}
        if(!$fields)return false;
        return $this->db->prepare("UPDATE courses SET ".implode(',',$fields)." WHERE id=:id")->execute($p);
    }

    public function delete(int $id): bool { return $this->db->prepare("DELETE FROM courses WHERE id=:id")->execute([':id'=>$id]); }
    public function getStats(): array { return $this->db->query("SELECT COUNT(*) total,SUM(status='active') active,SUM(status='draft') draft,SUM(status='completed') completed,SUM(status='archived') archived FROM courses")->fetch(); }
}
