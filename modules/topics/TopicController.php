<?php
require_once __DIR__ . '/TopicModel.php';

class TopicController {
    private TopicModel $model;
    public function __construct() { $this->model = new TopicModel(); }

    public function handle(string $method, array $seg): void {
        $id  = isset($seg[0]) && is_numeric($seg[0]) ? (int)$seg[0] : null;
        $sub = $seg[1] ?? null;

        if ($method === 'GET' && $id && $sub === 'stats')      { successResponse($this->model->getStatsByCourse($id)); return; }
        if ($method === 'GET' && $id)                           { $r=$this->model->getById($id); $r?successResponse($r):errorResponse('Not found',404); return; }
        if ($method === 'GET')                                  { $cid=(int)($_GET['course_id']??0); if(!$cid)errorResponse('course_id required'); successResponse($this->model->getByCourse($cid,$_GET)); return; }
        if ($method === 'POST' && $id && $sub === 'reschedule') { $this->reschedule($id); return; }
        if ($method === 'POST')                                 { $this->store(); return; }
        if (in_array($method,['PUT','PATCH']) && $id)           { $this->update($id); return; }
        if ($method === 'DELETE' && $id)                        { $this->model->delete($id); successResponse(null,'Deleted'); return; }
        errorResponse('Bad request', 400);
    }

    private function store(): void {
        $d = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($d['course_id']) || empty($d['title'])) errorResponse('course_id & title required');
        $id = $this->model->create($d);
        successResponse($this->model->getById($id), 'Topic created');
    }

    private function update(int $id): void {
        $d = json_decode(file_get_contents('php://input'), true) ?? [];
        if (!$this->model->getById($id)) errorResponse('Topic not found', 404);
        $this->model->update($id, $d);
        successResponse($this->model->getById($id), 'Updated');
    }

    private function reschedule(int $id): void {
        $d = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($d['reason']) || empty($d['new_date'])) errorResponse('reason and new_date required');
        if (!$this->model->getById($id)) errorResponse('Topic not found', 404);
        $this->model->reschedule($id, $d['reason'], $d['new_date']);
        successResponse($this->model->getById($id), 'Topic rescheduled');
    }
}
