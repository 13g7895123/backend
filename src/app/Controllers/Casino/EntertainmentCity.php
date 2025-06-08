<?
namespace App\Controllers\Casino;

use App\Controllers\BaseController;
use App\Models\Casino\FileModel;
use App\Models\Casino\EntertainmentCityModel;
use App\Models\M_Common as M_Model_Common;

class EntertainmentCity extends BaseController
{
    protected $db;
    protected $FileModel;
    protected $EntertainmentCityModel;
    protected $M_Model_Common;

    public function __construct()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->EntertainmentCityModel = new EntertainmentCityModel();
        $this->FileModel = new FileModel();
        $this->M_Model_Common = new M_Model_Common();
        $this->M_Model_Common->setDatabase('casino');
    }

    public function index($id=null)
    {
        $result = array('success' => false);
        $where = [];
        $multiple = true;
        $sort = array('field' => 'sort', 'direction' => 'asc');

        if ($id != null) {
            $where['id'] = $id;
            $multiple = false;
            $sort = [];
        }

        $data = $this->M_Model_Common->getData('entertainment-city', $where, [], $multiple, [], $sort);

        if (!empty($data) && $multiple === true) {
            foreach ($data as $_key => $_val) {
                $data[$_key]['image'] = base_url() . 'api/casino/image/show/' . $_val['image-id'];
            }
        }

        // 單筆資料不會有圖片
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

        $insertId = $this->EntertainmentCityModel->createData($postData);

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

        $updateResult = $this->EntertainmentCityModel->updateData($postData);

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
        
        $deleteResult = $this->EntertainmentCityModel->deleteData($postData['id']);

        if ($deleteResult) {
            // 更新排序
            $this->EntertainmentCityModel->resetSort();


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
        $updateResult = $this->EntertainmentCityModel->updateData($updateData);

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
        
        $sortResult = $this->EntertainmentCityModel->updateSort($postData['id'], $postData['type']);

        if ($sortResult) {
            $result['success'] = true;
            $result['msg'] = '排序成功';
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
}