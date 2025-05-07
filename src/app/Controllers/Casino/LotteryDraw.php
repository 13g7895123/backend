<?php
namespace App\Controllers\Casino;

use App\Controllers\BaseController;
use App\Models\Casino\LotteryDrawModel;
use App\Models\Casino\FileModel;
use App\Models\M_Common as M_Model_Common;

class LotteryDraw extends BaseController
{
    protected $db;
    protected $table;
    protected $FileModel;

    public function __construct()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->table = 'lottery-draw';
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

        $LotteryDrawModel = new LotteryDrawModel();
        $insertId = $LotteryDrawModel->createData($postData);

        if ($insertId > 0) {
            $result['success'] = true;
            $result['msg'] = '新增成功';
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
}