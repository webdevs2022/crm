<?php
require_once __DIR__ . '/WorkflowModel.php';

class WorkflowController {
    private WorkflowModel $model;
    public function __construct() { $this->model = new WorkflowModel(); }

    public function handle(string $method, array $seg): void {
        $id  = isset($seg[0]) && is_numeric($seg[0]) ? (int)$seg[0] : null;
        $sub = $seg[1] ?? null;

        if ($method==='GET' && $sub==='course')      { $this->byCourse($id); return; }
        if ($method==='GET' && !$id)                  { $this->allProgress(); return; }
        if ($method==='GET' && $id)                   { $this->byTopic($id); return; }
        if ($method==='POST' && $id && $sub==='init') { $this->init($id); return; }
        if ($method==='POST' && $id && $sub==='toggle'){ $this->toggle($id); return; }
        errorResponse('Unknown workflow endpoint', 404);
    }

    private function allProgress():void { successResponse($this->model->getAllProgress()); }
    private function byCourse(int $id):void { successResponse($this->model->getProgressByCourse($id)); }
    private function byTopic(int $id):void  { successResponse($this->model->getByTopic($id)); }

    private function init(int $topicId):void {
        $data = json_decode(file_get_contents('php://input'),true)??[];
        $type = $data['lecture_type'] ?? 'recorded';
        $this->model->initForTopic($topicId, $type);
        successResponse($this->model->getByTopic($topicId), 'Workflow initialised');
    }

    private function toggle(int $topicId):void {
        $data = json_decode(file_get_contents('php://input'),true)??[];
        if (empty($data['step_key'])) errorResponse('step_key required');
        $r = $this->model->toggleStep($topicId, $data['step_key'], (int)($data['user_id']??1));
        successResponse($r, 'Step updated');
    }
}
