<?php
// ============================================================
// Topic Model — v3 Schema
// FK faculty_id → faculty table (master), not users
// Added: lecture_type (recorded/live/not_decided), reschedule fields
// ============================================================
require_once __DIR__ . '/../../includes/helpers.php';

class TopicModel {
    private PDO $db;
    public function __construct() { $this->db = db(); }

    public function getByCourse(int $courseId, array $filters = []): array {
        $where  = ['t.course_id = :cid'];
        $params = [':cid' => $courseId];
        if (!empty($filters['status']))       { $where[] = 't.status=:st';       $params[':st'] = $filters['status']; }
        if (!empty($filters['lecture_type'])) { $where[] = 't.lecture_type=:lt'; $params[':lt'] = $filters['lecture_type']; }

        $sql = "SELECT t.*, f.name AS faculty_name, f.email AS faculty_email, f.designation AS faculty_designation
                FROM topics t
                LEFT JOIN faculty f ON f.id = t.faculty_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY t.sort_order ASC, t.created_at ASC";
        $stmt = $this->db->prepare($sql); $stmt->execute($params);
        $topics = $stmt->fetchAll();
        return ['course_id' => $courseId, 'total' => count($topics), 'topics' => $topics];
    }

    public function getById(int $id): ?array {
        $sql = "SELECT t.*, f.name AS faculty_name, f.email AS faculty_email,
                       c.title AS course_title, c.course_number
                FROM topics t
                LEFT JOIN faculty  f ON f.id = t.faculty_id
                LEFT JOIN courses  c ON c.id = t.course_id
                WHERE t.id = :id";
        $stmt = $this->db->prepare($sql); $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $d): int {
        if (!isset($d['sort_order'])) {
            $stmt = $this->db->prepare("SELECT COALESCE(MAX(sort_order),0)+1 FROM topics WHERE course_id=:cid");
            $stmt->execute([':cid' => $d['course_id']]);
            $d['sort_order'] = (int)$stmt->fetchColumn();
        }
        $sql = "INSERT INTO topics
                  (course_id, title, description, lecture_type, sort_order, duration_minutes,
                   faculty_id, status, scheduled_at, meeting_link, notes)
                VALUES
                  (:cid,:title,:desc,:lt,:ord,:dur,:fid,:st,:sched,:link,:notes)";
        $this->db->prepare($sql)->execute([
            ':cid'   => $d['course_id'],
            ':title' => $d['title'],
            ':desc'  => $d['description']      ?? null,
            ':lt'    => $d['lecture_type']      ?? 'not_decided',
            ':ord'   => $d['sort_order'],
            ':dur'   => $d['duration_minutes']  ?? 0,
            ':fid'   => $d['faculty_id']        ?? null,
            ':st'    => $d['status']            ?? 'pending',
            ':sched' => $d['scheduled_at']      ?? null,
            ':link'  => $d['meeting_link']      ?? null,
            ':notes' => $d['notes']             ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool {
        $allowed = ['title','description','lecture_type','sort_order','duration_minutes',
                    'faculty_id','status','scheduled_at','meeting_link','notes'];
        $fields = []; $p = [':id' => $id];
        foreach ($allowed as $k) { if (array_key_exists($k, $d)) { $fields[] = "$k=:$k"; $p[":$k"] = $d[$k]; } }
        if (!$fields) return false;
        return $this->db->prepare("UPDATE topics SET " . implode(',', $fields) . " WHERE id=:id")->execute($p);
    }

    public function reschedule(int $id, string $reason, string $newDate): bool {
        return $this->db->prepare(
            "UPDATE topics SET
               status='rescheduled',
               scheduled_at=:newdate,
               reschedule_reason=:reason,
               rescheduled_to=:newdate,
               rescheduled_at=NOW()
             WHERE id=:id"
        )->execute([':newdate' => $newDate, ':reason' => $reason, ':id' => $id]);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM topics WHERE id=:id")->execute([':id' => $id]);
    }

    public function getStatsByCourse(int $courseId): array {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) total, SUM(status='pending') pending,
               SUM(status='in_progress') in_progress, SUM(status='completed') completed,
               SUM(status='rescheduled') rescheduled, SUM(status='cancelled') cancelled,
               SUM(lecture_type='recorded') recorded, SUM(lecture_type='live') live,
               SUM(lecture_type='not_decided') not_decided,
               COALESCE(SUM(duration_minutes),0) total_duration
             FROM topics WHERE course_id=:cid"
        );
        $stmt->execute([':cid' => $courseId]);
        return $stmt->fetch();
    }
}
