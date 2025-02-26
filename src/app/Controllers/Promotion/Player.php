<?
namespace App\Controllers\Promotion;

use App\Controllers\BaseController;
use App\Models\M_Common as M_Model_Common;
use App\Models\Promotion\M_Common;
use App\Models\Promotion\M_Player;
use App\Models\Promotion\M_Token;
use App\Models\Promotion\M_Promotion;
use App\Models\Promotion\M_Server;
use App\Models\Promotion\M_Line;

class Player extends BaseController
{
    protected $db;
    protected $response;
    protected $M_Common;
    protected $M_Player;
    protected $M_Token;
    protected $M_Promotion;
    protected $M_Server;
    protected $M_Line;
    protected $M_Model_Common;

    public function __construct()
    {
        $this->db = \Config\Database::connect('promotion');
        $this->M_Common = new M_Common();
        $this->M_Player = new M_Player();
        $this->M_Token = new M_Token();
        $this->M_Promotion = new M_Promotion();
        $this->M_Server = new M_Server();
        $this->M_Line = new M_Line();
        $this->M_Model_Common = new M_Model_Common();
    }

    /**
     * 取得玩家資料
     */
    public function index()
    {
        $result = array('success' => False);
        $data = $this->M_Model_Common->getData('player', [], [], True);

        if (empty($data)) {
            $result['msg'] = '查無資料';
        }
        
        $result['success'] = True;
        $result['msg'] = '查詢成功';
        $result['data'] = $data;

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
}