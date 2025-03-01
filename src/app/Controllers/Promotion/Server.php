<?php

namespace App\Controllers\Promotion;

use App\Controllers\BaseController;
use App\Models\Promotion\M_Common;
use App\Models\Promotion\M_Server;

class Server extends BaseController
{
    protected $db;
    protected $response;
    protected $M_Common;
    protected $M_Server;

    public function __construct()
    {
        $this->db = \Config\Database::connect('promotion');
        
        $this->M_Server = new M_Server();
        $this->M_Common = new M_Common();
    }

    public function index()
    {
        $data = $this->M_Common->index('server');
        $result = array('success' => False);

        // 資料轉換
        foreach ($data as $_key => $_val){
            $cycle = $_val['cycle'];
            if ($cycle == 'monthly'){
                $data[$_key]['cycle'] = '月';
            }else if ($cycle == 'weekly'){
                $data[$_key]['cycle'] = '週';
            }else if ($cycle == 'daily'){
                $data[$_key]['cycle'] = '日';
            }
        }

        if (empty($data)){
            $result['msg'] = '查無資料';

            $this->response->noCache();
            $this->response->setContentType('application/json');
            return $this->response->setJSON($result);
        }

        $result['success'] = True;
        $result['data'] = $data;

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    // 建立伺服器
    public function create()
    {
        $data = $this->request->getPost();
        $data = $this->M_Common->convertFields('server', $data);
        $data = $this->M_Common->convertSpecialField('server', 'require_character', $data);

        $insertId = $this->M_Common->create('server', $data);
        $result = array('success' => False);

        if ($insertId === False){
            $result['msg'] = '建立失敗';

            $this->response->noCache();
            $this->response->setContentType('application/json');
            return $this->response->setJSON($result);
        }

        $result['success'] = True;
        $result['msg'] = '建立成功';

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    // 取得伺服器資料
    public function getServer()
    {
        $postData = $this->request->getJson(True);

        $where = (!empty($postData)) ? ['code' => $postData['code']] : [];
        $queryMultiple = (!empty($postData)) ? False : True;
        $data = $this->M_Server->getServer($where, [], $queryMultiple);
        
        $result = array('success' => False);

        if (empty($data)){
            $result['msg'] = '查無資料';

            $this->response->noCache();
            $this->response->setContentType('application/json');
            return $this->response->setJSON($result);
        }

        // $data = array(
        //     'name' => $data['name'],
        //     'require_character' => $data['require_character'],
        // );

        $result['success'] = True;
        $result['data'] = $data;

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    public function getDataTableData()
    {
        $postData = $this->request->getJson(True);
        $data = $this->M_Server->getDataTableData($postData);
        $result = array('success' => False);

        if (empty($data)){
            $result['msg'] = '查無資料';

            $this->response->noCache();
            $this->response->setContentType('application/json');
            return $this->response->setJSON($result);
        }

        $result['success'] = True;
        $result['data'] = $data;

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
}