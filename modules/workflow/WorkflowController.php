<?php
require_once __DIR__ . '/WorkflowModel.php';
require_once __DIR__ . '/../../includes/auth.php';


class WorkflowController {
    private WorkflowModel $model;
    public function __construct() { $this->model = new WorkflowModel(); }

    public function handle(string $method, array $seg): void {
        $id  = isset($seg[0]) && is_numeric($seg[0]) ? (int)$seg[0] : null;
        $act = $seg[0] ?? null;
        $sub = $seg[1] ?? null;

        if ($method==='GET' && $act==='steps')       { $this->allSteps(); return; }
        if ($method==='GET' && $id && $sub==='course') { $this->byCourse($id); return; }
        if ($method==='GET' && !$id && !$act)        { $this->allProgress(); return; }
        if ($method==='GET' && $id)                   { $this->byTopic($id); return; }
        if (in_array($method, ['PUT', 'PATCH']) && $id) { $this->patchStep($id); return; }
        if ($method==='POST' && $id && $sub==='init') { $this->init($id); return; }
        if ($method==='POST' && $id && $sub==='toggle'){ $this->toggle($id); return; }
        errorResponse('Unknown workflow endpoint', 404);
    }

    private function patchStep(int $id): void {
        Auth::requireRole('admin');
        $d = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $this->model->update($id, $d);
        successResponse(null, 'Step updated');
    }

    private function allProgress():void { successResponse($this->model->getAllProgress()); }
    private function allSteps():void    { successResponse($this->model->getAllSteps()); }
    private function byCourse(int $id):void { successResponse($this->model->getProgressByCourse($id)); }
    private function byTopic(int $id):void  { successResponse($this->model->getByTopic($id)); }

    private function init(int $topicId):void {
        Auth::requireRole('admin');
        $data = json_decode(file_get_contents('php://input'),true)??[];
        $type = $data['lecture_type'] ?? 'recorded';
        $this->model->initForTopic($topicId, $type);
        successResponse($this->model->getByTopic($topicId), 'Workflow initialised');
    }

    private function toggle(int $topicId):void {
        Auth::requireRole('admin');
        $data = json_decode(file_get_contents('php://input'),true)??[];
        if (empty($data['step_key'])) errorResponse('step_key required');
        $r = $this->model->toggleStep($topicId, $data['step_key'], (int)($data['user_id']??1));
        if (isset($r['error'])) {
            errorResponse($r['error'], 400);
            return;
        }
        successResponse($r, 'Step updated');
    }
}
