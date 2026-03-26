<?php
require_once __DIR__ . '/CourseModel.php';
require_once __DIR__ . '/../../includes/auth.php';

class CourseController {
    private CourseModel $model;
    public function __construct() { $this->model = new CourseModel(); }

    public function handle(string $method, array $seg): void {
        $id  = isset($seg[0]) && is_numeric($seg[0]) ? (int)$seg[0] : null;
        $sub = $seg[1] ?? null;
        $allowed = Auth::allowedCourseIds();

        if($method==='GET'&&$sub==='stats')            {successResponse($this->model->getStats());return;}
        if($method==='GET'&&$id&&$sub==='progress')    {successResponse($this->model->getProgress($id));return;}
        if($method==='GET'&&$id&&$sub==='topics')      {require_once __DIR__.'/../topics/TopicModel.php';$tm=new TopicModel();successResponse($tm->getByCourse($id));return;}
        if($method==='GET'&&$id)                        {$r=$this->model->getById($id);$r?successResponse($r):errorResponse('Not found',404);return;}
        if($method==='GET')                             {successResponse($this->model->getAll($_GET,$allowed));return;}
        if($method==='POST')                            {Auth::requireRole('admin');$this->store();return;}
        if(in_array($method,['PUT','PATCH'])&&$id)      {$this->update($id);return;}
        if($method==='DELETE'&&$id)                     {Auth::requireRole('admin');$this->model->delete($id);successResponse(null,'Deleted');return;}
        errorResponse('Bad request',400);
    }

    private function store(): void {
        $d=json_decode(file_get_contents('php://input'),true)??[];
        if(empty($d['title']))errorResponse('Title required');
        $d['created_by']=Auth::user()['id']??1;
        $id=$this->model->create($d);
        successResponse($this->model->getById($id),'Course created');
    }

    private function update(int $id): void {
        $d=json_decode(file_get_contents('php://input'),true)??[];
        $this->model->update($id,$d);
        successResponse($this->model->getById($id),'Updated');
    }
}
