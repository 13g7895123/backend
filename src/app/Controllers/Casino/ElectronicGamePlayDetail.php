<?php
namespace App\Controllers\Casino;

use App\Controllers\BaseController;
use App\Models\Casino\ElectronicGamePlayDetailModel;
use App\Models\Casino\FileModel;
use App\Models\M_Common as M_Model_Common;

class ElectronicGamePlayDetail extends BaseController
{
    protected $db;
    protected $table;
    protected $FileModel;

    public function __construct()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->table = 'electronic-game-play-detail';
        $this->FileModel = new FileModel();    
    }

    // 編輯用，取單筆資料
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

        $M_Model_Common =new M_Model_Common();
        $M_Model_Common->setDatabase('casino');  
        $data = $M_Model_Common->getData($this->table, $where, [], $multiple, [], $sort);

        if (!empty($data) && $id == null) {
            foreach ($data as $_key => $_val) {
                $data[$_key]['image'] = base_url() . 'api/casino/image/show/' . $_val['image-id'];
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

        $electronicGamePlayDetailModel = new ElectronicGamePlayDetailModel();
        $insertId = $electronicGamePlayDetailModel->createData($postData);

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
        
        $electronicGamePlayDetailModel = new ElectronicGamePlayDetailModel();
        $updateResult = $electronicGamePlayDetailModel->updateData($postData);

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

        $Model_Common = new M_Model_Common();
        $Model_Common->setDatabase('casino');
        $data = $Model_Common->getData($this->table, ['id' => $postData['id']], [], false);

        $electronicGamePlayDetailModel = new ElectronicGamePlayDetailModel();
        $deleteResult = $electronicGamePlayDetailModel->deleteData($postData['id']);

        if ($deleteResult) {
            // 更新排序
            $electronicGamePlayDetailModel->resetSort($data['data-id']);

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

        $electronicGamePlayDetailModel = new ElectronicGamePlayDetailModel();

        $postData = $this->request->getPost();
        $updateData = array(
            'id' => $postData['id'],
            'image-id' => $fileId,
        );
        $updateResult = $electronicGamePlayDetailModel->updateData($updateData);

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
        
        $electronicGamePlayDetailModel = new ElectronicGamePlayDetailModel();
        $sortResult = $electronicGamePlayDetailModel->updateSort($postData['id'], $postData['type']);

        if ($sortResult) {
            $result['success'] = true;
            $result['msg'] = '排序成功';
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
    
    // 取特定類型資料
    public function fetchTypeData()
    {
        $result = array('success' => false);
        $postData = $this->request->getJSON(true);
        $parentId = $postData['id'];

        $where = array('data-id' => $parentId);

        $M_Model_Common =new M_Model_Common();
        $M_Model_Common->setDatabase('casino');  
        $sort = ['field' => 'sort', 'direction' => 'ASC'];
        $data = $M_Model_Common->getData($this->table, $where, [], true, [], $sort);

        if (!empty($data)) {
            foreach ($data as $_key => $_val) {
                $data[$_key]['image'] = base_url() . 'api/casino/image/show/' . $_val['image-id'];
            }
        }

        $result['success'] = true;
        $result['data'] = $data;

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
}