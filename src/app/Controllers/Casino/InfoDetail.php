<?php
namespace App\Controllers\Casino;

use App\Controllers\Casino\Common\CommonBaseController;
use App\Models\M_Common;

class InfoDetail extends CommonBaseController
{
    public function __construct()
    {
        $this->setTable('info_detail');
    }

    public function index($id = null)
    {
        $result = array('success' => false);
        $where = array();
        $multiple = true;
        $sort = ['field' => 'sort', 'direction' => 'ASC'];

        if ($id != null) {
            $where['info_id'] = $id;
            $multiple = true;
            $sort = [];
        }

        $M_Model_Common = new M_Common();
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
}