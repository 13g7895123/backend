<?
namespace App\Controllers\Casino;

use App\Controllers\BaseController;
use App\Models\Casino\ElectronicGamePlayDetailModel;
use App\Models\M_Common as M_Model_Common;

class ElectronicGamePlayDetail extends BaseController
{
    protected $db;
    protected $ElectronicGamePlayDetailModel;
    protected $M_Model_Common;

    public function __construct()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->ElectronicGamePlayDetailModel = new ElectronicGamePlayDetailModel();
        $this->M_Model_Common = new M_Model_Common();
        $this->db = \Config\Database::connect('casino');
    }

    public function index()
    {
        $result = array('success' => false);
        $postData = $this->request->getJSON(true);
        $playId = isset($postData['play_id']) ? $postData['play_id'] : null;
        $id = isset($postData['id']) ? $postData['id'] : null;

        $data = $this->ElectronicGamePlayDetailModel->fetchData($playId);

        if (!empty($data)) {
            $result['success'] = true;
            $result['data'] = $data;
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    public function create()
    {
        $result = array('success' => false);
        $postData = $this->request->getJSON(true);

        // 新增資料
        $insertId = $this->ElectronicGamePlayDetailModel->createData($postData);

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
        
        // 更新資料
        $updateResult = $this->ElectronicGamePlayDetailModel->updateData($postData);

        if ($updateResult === true) {
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

        $deleteResult = $this->ElectronicGamePlayDetailModel->deleteData($postData);

        if ($deleteResult === true) {
            $result['success'] = true;
            $result['msg'] = '刪除成功';
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
}