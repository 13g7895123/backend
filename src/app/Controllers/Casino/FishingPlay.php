<?php
namespace App\Controllers\Casino;

use App\Controllers\BaseController;
use App\Models\Casino\FishingPlayModel;
use App\Models\Casino\FileModel;
use App\Models\M_Common as M_Model_Common;

class FishingPlay extends BaseController
{
    protected $db;
    protected $table;
    protected $FileModel;

    public function __construct()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->table = 'fishing-play';
        $this->FileModel = new FileModel();    
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

        $FishingPlayModel = new FishingPlayModel();
        $insertId = $FishingPlayModel->createData($postData);

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
        
        $FishingPlayModel = new FishingPlayModel();
        $updateResult = $FishingPlayModel->updateData($postData);

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
        
        $FishingPlayModel = new FishingPlayModel();
        $deleteResult = $FishingPlayModel->deleteData($postData['id']);

        if ($deleteResult) {
            // 更新排序
            $FishingPlayModel->resetSort();

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

        $postData = $this->request->getPost();
        $updateData = array(
            'id' => $postData['id'],
            'image-id' => $fileId,
        );
        $fishingPlayModel = new FishingPlayModel();
        $updateResult = $fishingPlayModel->updateData($updateData);

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
        
        $FishingPlayModel = new FishingPlayModel();
        $sortResult = $FishingPlayModel->updateSort($postData['id'], $postData['type']);

        if ($sortResult) {
            $result['success'] = true;
            $result['msg'] = '排序成功';
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
    
}