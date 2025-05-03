<?
namespace App\Controllers\Casino;

use App\Controllers\BaseController;
use App\Models\Casino\ElectronicGamePlayModel;
use App\Models\M_Common as M_Model_Common;

class ElectronicGamePlay extends BaseController
{
    protected $db;
    protected $ElectronicGamePlayModel;
    protected $M_Model_Common;

    public function __construct()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->ElectronicGamePlayModel = new ElectronicGamePlayModel();
        $this->M_Model_Common = new M_Model_Common();
        $this->db = \Config\Database::connect('casino');
    }

    public function index($id=null)
    {
        $result = array('success' => false);
        $postData = $this->request->getJSON(true);
        // $playId = $postData['play_id'];
        $id = isset($postData['id']) ? $postData['id'] : null;

        // print_r($postData); die();

        $data = $this->ElectronicGamePlayModel->fetchData($id);

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
        $insertId = $this->ElectronicGamePlayModel->createData($postData);

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
        $updateResult = $this->ElectronicGamePlayModel->updateData($postData);

        if ($updateResult === true) {
            $result['success'] = true;
            $result['msg'] = '更新成功';
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
}