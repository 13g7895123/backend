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

    /**
     * 提交資料(身分驗證)
     */
    public function submit()
    {
        $result = array('success' => False);
        $postData = $this->request->getJSON(True);

        $checkResult = $this->M_Player->checkUser($postData);
        [$success] = $checkResult;

        if (is_array($checkResult) && count($checkResult) == 2) {
            [$success, $userData] = $checkResult;
        }        

        // 如果使用者資料不存在，則建立使用者資料
        if ($success === False) {
            $createResult = $this->M_Player->create($postData);

            // 如果建立使用者資料失敗，則回傳錯誤訊息
            if (isset($createResult['error'])) {
                $result['msg'] = $createResult['error'];

                $this->response->noCache();
                $this->response->setContentType('application/json');
                return $this->response->setJSON($result);
            }

            // 如果建立使用者資料成功，則回傳使用者ID
            $result['success'] = True;
            $result['msg'] = '建立成功';
            $result['user_id'] = $createResult['user_id'];
            $result['token'] = $this->M_Token->getToken($postData['server'], $createResult['user_id'], 'promotion');

            $this->response->noCache();
            $this->response->setContentType('application/json');
            return $this->response->setJSON($result);
        }

        // 如果使用者資料存在，則回傳使用者ID
        $result['success'] = True;
        $result['msg'] = '使用者資料已存在';
        $result['user_id'] = $userData['id'];
        $result['server'] = $userData['server'];
        $result['token'] = $this->M_Token->getToken($userData['server'], $userData['id'], 'promotion');

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    /**
     * 取得使用者ID
     * @param string $token Token
     */ 
    public function getPlayerInfo()
    {
        $result = array('success' => False);
        $postData = $this->request->getJSON(True);
        $token = $postData['token'];

        $tokenData = $this->M_Token->getTokenInfo($token);

        if (empty($tokenData)) {
            $result['msg'] = 'Token不存在';

            $this->response->noCache();
            $this->response->setContentType('application/json');
            return $this->response->setJSON($result);
        }

        $userData = $this->M_Player->getPlayerInfo($tokenData['user_id']);
        $promotionData = $this->M_Promotion->getPromotion($tokenData['user_id']);
        $lineData = $this->M_Line->getLineData(array('user_id' => $tokenData['user_id']));
        $serverData = $this->M_Server->getServer(['code' => $tokenData['server']]);

        $result['success'] = True;
        $result['msg'] = 'Token存在';

        if (!empty($userData)) {
            $result['user'] = array(
                'id' => $userData['id'],
                'email' => empty($userData['email']) ? '' : $userData['email'],
                'notify_email' => $userData['notify_email'],
                'notify_line' => $userData['notify_line'],
            );
        }

        if (!empty($promotionData)) {
            $result['promotion'] = $promotionData;
        }

        if (!empty($lineData)) {
            // 移除要隱藏的資料
            $unsetFields = array('id', 'created_at', 'uid', 'email');
            foreach ($unsetFields as $_val) {
                unset($lineData[$_val]);
            }

            $result['line'] = $lineData;
        }

        if (!empty($serverData)) {
            // 取得使用者推廣狀態
            $result['promotion_status'] = array(
                'used' => $this->M_Promotion->getPromotionByFrequency($tokenData['user_id'], $serverData['cycle']),
                'max' => $serverData['limit_number'],
                'cycle' => $serverData['cycle'],
            );
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
}