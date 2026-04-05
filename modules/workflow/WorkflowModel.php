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
    // Combined steps based on new requirements
    const RECORDED_STEPS = [
        ['key' => 'email_sent',          'label' => 'STEP 1: Email Sent?'],
        ['key' => 'reply_received',      'label' => 'STEP 2: Reply Received?'],
        ['key' => 'reminder_sent',       'label' => 'STEP 2.1: Reminder Email Sent?'],
        ['key' => 'recording_scheduled', 'label' => 'STEP 3: Recording Scheduled?'],
        ['key' => 'recording_shared',    'label' => 'STEP 4: Recording Link Shared?'],
        ['key' => 'recording_done',      'label' => 'STEP 5: Recording Done?'],
        ['key' => 'editing_started',     'label' => 'STEP 6: Video Editing: Started'],
        ['key' => 'editing_done',        'label' => 'STEP 7: Video Editing: Completed'],
        ['key' => 'uploaded',            'label' => 'STEP 8: Final Uploaded?'],
    ];

    const LIVE_STEPS = [
        ['key' => 'email_sent',          'label' => 'STEP 1: Email Sent?'],
        ['key' => 'reply_received',      'label' => 'STEP 2: Reply Received?'],
        ['key' => 'reminder_sent',       'label' => 'STEP 2.1: Reminder Email Sent?'],
        ['key' => 'scheduling_done',     'label' => 'STEP 3: Date & Time Received?'],
        ['key' => 'flyer_created',       'label' => 'STEP 4.1: Flyer Created?'],
        ['key' => 'flyer_circulated',    'label' => 'STEP 4.2: Flyer Circulated?'],
        ['key' => 'session_link_sent',   'label' => 'STEP 4.3: Session Link Sent?'],
        ['key' => 'lecture_completed',   'label' => 'STEP 5: Live Lecture Completed?'],
        ['key' => 'uploaded',            'label' => 'STEP 6: Post Session: Uploaded?'],
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
        $stmt = $this->db->prepare("SELECT * FROM workflow_steps WHERE topic_id=:tid ORDER BY step_order ASC");
        $stmt->execute([':tid' => $topicId]);
        $allSteps = $stmt->fetchAll();
        
        $targetIndex = -1;
        foreach ($allSteps as $i => $s) {
            if ($s['step_key'] === $stepKey) { $targetIndex = $i; break; }
        }
        
        if ($targetIndex === -1) return ['error' => 'Step not found'];
        $step = $allSteps[$targetIndex];

        // NEW LOGIC: Cannot proceed unless previous step completed
        if (!$step['is_completed'] && $targetIndex > 0) {
                $isReminder = ($step['step_key'] === 'reminder_sent');
                $prevStepIdx = $targetIndex - 1;
                $prevStep = $allSteps[$prevStepIdx];

                // Special Case: reminder_sent doesn't require reply_received to be done (it's the alternative)
                if ($isReminder && $prevStep['step_key'] === 'reply_received') {
                    // Allowed
                } 
                // Special Case: Next step after reminder_sent only needs reply_received (or reminder_sent)
                else if ($prevStep['step_key'] === 'reminder_sent') {
                    $replyValue = $allSteps[$prevStepIdx - 1] ?? null; // reply_received
                    if ($replyValue && !$replyValue['is_completed']) {
                        return ['error' => 'Step "' . $replyValue['step_label'] . '" must be completed first'];
                    }
                }
                else if (!$prevStep['is_completed']) {
                    return ['error' => 'Previous step "' . $prevStep['step_label'] . '" must be completed first'];
                }
        } else if ($step['is_completed']) {
            // Optional: prevent unchecking if later steps are completed?
            if ($targetIndex < count($allSteps) - 1 && $allSteps[$targetIndex+1]['is_completed']) {
                return ['error' => 'Cannot uncheck while subsequent steps are completed'];
            }
        }

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
    public function getAllSteps(): array {
        $sql = "SELECT w.*, u.name AS completed_by_name
                FROM workflow_steps w
                LEFT JOIN users u ON w.completed_by = u.id
                ORDER BY w.topic_id, w.step_order ASC";
        return $this->db->query($sql)->fetchAll();
    }
    public function update(int $id, array $d): bool {
        $allowed = ['is_completed', 'completed_by', 'completed_at'];
        $fields = []; $p = [':id' => $id];
        foreach ($allowed as $k) {
            if (array_key_exists($k, $d)) { $fields[] = "$k=:$k"; $p[":$k"] = $d[$k]; }
        }
        if (!$fields) return false;
        return $this->db->prepare("UPDATE workflow_steps SET " . implode(',', $fields) . " WHERE id=:id")->execute($p);
    }
}
