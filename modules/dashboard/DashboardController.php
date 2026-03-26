<?php
// ============================================================
// Dashboard Controller — v3 (faculty master table, new material schema)
// ============================================================
require_once __DIR__ . '/../../includes/helpers.php';

class DashboardModel {
    private PDO $db;
    public function __construct() { $this->db = db(); }

    public function getSummary(): array {
        $courses   = $this->db->query("SELECT COUNT(*) total, SUM(status='active') active FROM courses")->fetch();
        $topics    = $this->db->query("SELECT COUNT(*) total, SUM(status='completed') completed, SUM(status='in_progress') in_progress, SUM(status='pending') pending, SUM(lecture_type='recorded') recorded, SUM(lecture_type='live') live, SUM(lecture_type='not_decided') not_decided FROM topics")->fetch();
        $materials = $this->db->query("SELECT COUNT(*) total, COALESCE(SUM(received_count),0) received, COALESCE(SUM(uploaded_count),0) uploaded, COALESCE(SUM(received_count-uploaded_count),0) pending FROM materials")->fetch();
        $contracts = $this->db->query("SELECT COUNT(*) total, COALESCE(SUM(total_amount),0) value FROM contracts WHERE status='active'")->fetch();
        $payments  = $this->db->query("SELECT COALESCE(SUM(CASE WHEN status='paid' THEN amount ELSE 0 END),0) paid, COALESCE(SUM(CASE WHEN status='pending' THEN amount ELSE 0 END),0) pending FROM payments")->fetch();
        $faculty   = $this->db->query("SELECT COUNT(*) total FROM faculty WHERE status='active'")->fetch();
        $tasks     = $this->db->query("SELECT COUNT(*) total, SUM(status='open') open, SUM(status='in_progress') in_progress FROM tasks")->fetch();
        return compact('courses','topics','materials','contracts','payments','faculty','tasks');
    }

    // Course progress = Lecture×40% + Material×30% + Contract×30%
    public function getCourseProgress(): array {
        $courses = $this->db->query("SELECT id, course_number, title FROM courses WHERE status != 'archived'")->fetchAll();
        $result  = [];
        foreach ($courses as $c) {
            $cid = $c['id'];

            // Lecture progress
            $lt = $this->db->prepare("SELECT COUNT(*) total, SUM(status='completed') done FROM topics WHERE course_id=:id");
            $lt->execute([':id' => $cid]); $lt = $lt->fetch();
            $lectPct = $lt['total'] > 0 ? round($lt['done'] / $lt['total'] * 100) : 0;

            // Material progress
            $mt = $this->db->prepare("SELECT SUM(received_count) recv, SUM(uploaded_count) upl FROM materials WHERE course_id=:id");
            $mt->execute([':id' => $cid]); $mt = $mt->fetch();
            $matPct = ($mt['recv'] ?? 0) > 0 ? round($mt['upl'] / $mt['recv'] * 100) : 0;

            // Contract progress
            $ct = $this->db->prepare("SELECT SUM(total_amount) total FROM contracts WHERE course_id=:id AND status!='cancelled'");
            $ct->execute([':id' => $cid]); $ct = $ct->fetch();
            $paid = $this->db->prepare("SELECT COALESCE(SUM(p.amount),0) FROM payments p JOIN contracts c ON c.id=p.contract_id WHERE c.course_id=:id AND p.status='paid'");
            $paid->execute([':id' => $cid]); $paidAmt = (float)$paid->fetchColumn();
            $cnPct = ($ct['total'] ?? 0) > 0 ? round($paidAmt / $ct['total'] * 100) : 0;

            $overall = round($lectPct * 0.4 + $matPct * 0.3 + $cnPct * 0.3);
            $result[] = [
                'id'                => $cid,
                'course_number'     => $c['course_number'],
                'title'             => $c['title'],
                'lecture_progress'  => $lectPct,
                'material_progress' => $matPct,
                'contract_progress' => $cnPct,
                'overall_progress'  => $overall,
            ];
        }
        return $result;
    }

    public function getMaterialBreakdown(): array {
        return $this->db->query(
            "SELECT c.course_number, c.title,
               COALESCE(SUM(m.received_count),0) received,
               COALESCE(SUM(m.uploaded_count),0) uploaded,
               COALESCE(SUM(m.received_count - m.uploaded_count),0) pending
             FROM courses c
             LEFT JOIN materials m ON m.course_id = c.id
             WHERE c.status = 'active'
             GROUP BY c.id ORDER BY c.title"
        )->fetchAll();
    }

    public function getFacultyWorkload(): array {
        return $this->db->query(
            "SELECT f.id, f.name, f.designation,
               COUNT(DISTINCT t.id)                                       AS total_topics,
               SUM(t.status='completed')                                  AS completed_topics,
               SUM(t.status='pending' OR t.status='in_progress')          AS pending_topics,
               COUNT(DISTINCT ct.id)                                      AS contracts,
               COALESCE(SUM(CASE WHEN p.status='paid' THEN p.amount END),0) AS paid_amount
             FROM faculty f
             LEFT JOIN topics    t  ON t.faculty_id  = f.id
             LEFT JOIN contracts ct ON ct.faculty_id = f.id
             LEFT JOIN payments  p  ON p.faculty_id  = f.id
             WHERE f.status = 'active'
             GROUP BY f.id ORDER BY total_topics DESC"
        )->fetchAll();
    }

    public function getUpcomingLectures(): array {
        return $this->db->query(
            "SELECT t.id, t.title, t.lecture_type, t.scheduled_at, t.meeting_link,
               c.title AS course_title, c.course_number,
               f.name  AS faculty_name
             FROM topics t
             LEFT JOIN courses c ON c.id = t.course_id
             LEFT JOIN faculty f ON f.id = t.faculty_id
             WHERE t.status = 'pending'
               AND t.lecture_type != 'not_decided'
               AND t.scheduled_at >= NOW()
             ORDER BY t.scheduled_at ASC LIMIT 10"
        )->fetchAll();
    }

    public function getTasksSummary(): array {
        return $this->db->query(
            "SELECT t.*, u.name AS assigned_to_name
             FROM tasks t LEFT JOIN users u ON u.id=t.assigned_to
             WHERE t.status != 'completed'
             ORDER BY FIELD(t.priority,'urgent','high','medium','low'), t.due_date ASC
             LIMIT 10"
        )->fetchAll();
    }
}

class DashboardController {
    private DashboardModel $model;
    public function __construct() { $this->model = new DashboardModel(); }

    public function handle(string $method, array $seg): void {
        $sub = $seg[0] ?? 'summary';
        match($sub) {
            'summary'   => successResponse($this->model->getSummary()),
            'progress'  => successResponse($this->model->getCourseProgress()),
            'materials' => successResponse($this->model->getMaterialBreakdown()),
            'faculty'   => successResponse($this->model->getFacultyWorkload()),
            'upcoming'  => successResponse($this->model->getUpcomingLectures()),
            'tasks'     => successResponse($this->model->getTasksSummary()),
            default     => errorResponse('Unknown dashboard endpoint', 404),
        };
    }
}
