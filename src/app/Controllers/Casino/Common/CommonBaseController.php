<?php
namespace App\Controllers\Casino\Common;

use App\Controllers\BaseController;
use App\Models\Casino\FileModel;
use App\Models\M_Common as M_Model_Common;

// 共用基礎Controller
class CommonBaseController extends BaseController
{
    protected $db;
    protected $table = '';
    protected $baseModelPath = 'App\\Models\\Casino\\';
    protected $fullModelPath = '';
    protected $FileModel;

    public function __construct()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->table = '';
        $this->FileModel = new FileModel();    
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    // 添加 pascalize 函數
    private function pascalize($string)
    {
        // 將 kebab-case 轉換為 PascalCase
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    public function index($id=null)
    {
        $result = array('success' => false);
        $where = [];
        $multiple = true;
        $sort = ['field' => 'sort', 'direction' => 'ASC'];

        if ($id != null) {
            $where['id'] = $id;
            $multiple = false;
            $sort = [];
        }

        $M_Model_Common = new M_Model_Common();
        $M_Model_Common->setDatabase('casino');  
        $data = $M_Model_Common->getData($this->table, $where, [], $multiple, [], $sort);

        if (!empty($data) && $id == null) {
            foreach ($data as $_key => $_val) {
                if (isset($_val['image-id']) && $_val['image-id'] != '') {
                    $data[$_key]['image'] = base_url() . 'api/casino/image/show/' . $_val['image-id'];
                }
            }
        }

        $result['success'] = true;
        $result['data'] = $data;

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    public function create()
    {
        $result = array('success' => false);
        $postData = $this->request->getJSON(true);

        $modelClass = $this->baseModelPath . $this->pascalize($this->table) . 'Model';
        // 檢查類是否存在
        if (!class_exists($modelClass)) {
            $result['msg'] = '無效的類型: ' . $modelClass;
            $this->response->noCache();
            $this->response->setContentType('application/json');
            return $this->response->setJSON($result);
        }

        $model = model($modelClass);
        $insertId = $model->createData($postData);
        // $insertId = $model->createData($postData, true);

        if ($insertId > 0) {
            $result['success'] = true;
            $result['msg'] = '新增成功';
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    public function update()
    {
        $result = array('success' => false);
        $postData = $this->request->getJSON(true);
        
        $modelClass = $this->baseModelPath . $this->pascalize($this->table) . 'Model';  // 加上 Model 後綴
        // 檢查類是否存在
        if (!class_exists($modelClass)) {
            $result['msg'] = '無效的類型: ' . $modelClass;
            $this->response->noCache();
            $this->response->setContentType('application/json');
            return $this->response->setJSON($result);
        }

        $model = model($modelClass);
        $updateResult = $model->updateData($postData);

        if ($updateResult) {
            $result['success'] = true;
            $result['msg'] = '更新成功';
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    public function delete()
    {
        $result = array('success' => false);
        $postData = $this->request->getJSON(true);
        
        $modelClass = $this->baseModelPath . $this->pascalize($this->table) . 'Model';  // 加上 Model 後綴
        // 檢查類是否存在
        if (!class_exists($modelClass)) {
            $result['msg'] = '無效的類型: ' . $modelClass;
            $this->response->noCache();
            $this->response->setContentType('application/json');
            return $this->response->setJSON($result);
        }

        $model = model($modelClass);
        $deleteResult = $model->deleteData($postData['id']);

        if ($deleteResult) {
            // 更新排序
            $model->resetSort();

            $result['success'] = true;
            $result['msg'] = '刪除成功';
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
    
    public function upload()
    {
        $result = array('success' => false);
        $file = $this->request->getFile('file');
        $fileId = $this->FileModel->saveFile($file, 'images/casino');

        if ($fileId === false) {
            $result['msg'] = '上傳失敗';

            $this->response->noCache();
            $this->response->setContentType('application/json');
            return $this->response->setJSON($result);
        }

        $modelClass = $this->baseModelPath . $this->pascalize($this->table) . 'Model';  // 加上 Model 後綴
        // 檢查類是否存在
        if (!class_exists($modelClass)) {
            $result['msg'] = '無效的類型: ' . $modelClass;
            $this->response->noCache();
            $this->response->setContentType('application/json');
            return $this->response->setJSON($result);
        }

        $postData = $this->request->getPost();
        $updateData = array(
            'id' => $postData['id'],
            'image-id' => $fileId,
        );
        $model = model($modelClass);
        $updateResult = $model->updateData($updateData);

        if ($updateResult) {
            $result['success'] = true;
            $result['msg'] = '上傳成功';
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    public function sort()
    {
        $result = array('success' => false);
        $postData = $this->request->getJSON(true);
        
        $modelClass = $this->baseModelPath . $this->pascalize($this->table) . 'Model';  // 加上 Model 後綴
        // 檢查類是否存在
        if (!class_exists($modelClass)) {
            $result['msg'] = '無效的類型: ' . $modelClass;
            $this->response->noCache();
            $this->response->setContentType('application/json');
            return $this->response->setJSON($result);
        }

        $model = model($modelClass);
        $sortResult = $model->updateSort($postData['id'], $postData['type']);

        if ($sortResult) {
            $result['success'] = true;
            $result['msg'] = '排序成功';
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
}