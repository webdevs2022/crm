<?php
// ============================================================
// Workflow Model — v3 Checklists per documentation
// Recorded: Email Sent → Meeting Link Shared → Recording Done → Editing Done → Uploaded
// Live:     Banner Created → Website Updated → Info Shared to Faculty
// ============================================================
require_once __DIR__ . '/../../includes/helpers.php';

class WorkflowModel {
    private PDO $db;

    // Doc-defined checklists
    const RECORDED_STEPS = [
        ['key' => 'email_sent',          'label' => 'Email Sent'],
        ['key' => 'meeting_link_shared', 'label' => 'Meeting Link Shared'],
        ['key' => 'recording_done',      'label' => 'Recording Done'],
        ['key' => 'editing_done',        'label' => 'Editing Done'],
        ['key' => 'uploaded',            'label' => 'Uploaded'],
    ];
    const LIVE_STEPS = [
        ['key' => 'banner_created',      'label' => 'Banner Created'],
        ['key' => 'website_updated',     'label' => 'Website Updated'],
        ['key' => 'info_shared_faculty', 'label' => 'Info Shared to Faculty'],
    ];

    public function __construct() { $this->db = db(); }

    public function getByTopic(int $topicId): array {
        $sql = "SELECT w.*, u.name AS completed_by_name
                FROM workflow_steps w
                LEFT JOIN users u ON w.completed_by = u.id
                WHERE w.topic_id = :tid ORDER BY w.step_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tid' => $topicId]);
        return $stmt->fetchAll();
    }

    public function initForTopic(int $topicId, string $lectureType): bool {
        $steps = $lectureType === 'live' ? self::LIVE_STEPS : self::RECORDED_STEPS;
        $stmt  = $this->db->prepare(
            "INSERT IGNORE INTO workflow_steps (topic_id, step_key, step_label, step_order)
             VALUES (:tid, :key, :label, :order)"
        );
        foreach ($steps as $i => $s) {
            $stmt->execute([':tid' => $topicId, ':key' => $s['key'], ':label' => $s['label'], ':order' => $i + 1]);
        }
        return true;
    }

    public function toggleStep(int $topicId, string $stepKey, int $userId): array {
        $stmt = $this->db->prepare("SELECT * FROM workflow_steps WHERE topic_id=:tid AND step_key=:key");
        $stmt->execute([':tid' => $topicId, ':key' => $stepKey]);
        $step = $stmt->fetch();
        if (!$step) return ['error' => 'Step not found'];

        $done = $step['is_completed'] ? 0 : 1;
        $this->db->prepare(
            "UPDATE workflow_steps SET is_completed=:done, completed_by=:by, completed_at=:at WHERE id=:id"
        )->execute([
            ':done' => $done,
            ':by'   => $done ? $userId : null,
            ':at'   => $done ? date('Y-m-d H:i:s') : null,
            ':id'   => $step['id'],
        ]);
        return ['step_key' => $stepKey, 'is_completed' => $done];
    }

    public function getProgressByCourse(int $courseId): array {
        $sql = "SELECT t.id AS topic_id, t.title, t.lecture_type, t.status,
                       COUNT(w.id)         AS total_steps,
                       SUM(w.is_completed) AS done_steps
                FROM topics t
                LEFT JOIN workflow_steps w ON w.topic_id = t.id
                WHERE t.course_id = :cid AND t.lecture_type != 'not_decided'
                GROUP BY t.id ORDER BY t.sort_order";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cid' => $courseId]);
        return $stmt->fetchAll();
    }

    public function getAllProgress(): array {
        $sql = "SELECT c.id AS course_id, c.title AS course_title, c.course_number,
                       COUNT(DISTINCT t.id) AS topics,
                       COUNT(w.id)          AS total_steps,
                       SUM(w.is_completed)  AS done_steps
                FROM courses c
                LEFT JOIN topics t         ON t.course_id = c.id AND t.lecture_type != 'not_decided'
                LEFT JOIN workflow_steps w ON w.topic_id   = t.id
                GROUP BY c.id ORDER BY c.title";
        return $this->db->query($sql)->fetchAll();
    }
}
