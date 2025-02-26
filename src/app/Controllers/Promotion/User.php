<?
namespace App\Controllers\Promotion;

use App\Controllers\BaseController;
use App\Models\M_Common as M_Model_Common;
use App\Models\Promotion\M_Common;
use App\Models\Promotion\M_User;
use App\Models\Promotion\M_Token;
use App\Models\Promotion\M_Promotion;
use App\Models\Promotion\M_Server;
use App\Models\Promotion\M_Line;

class User extends BaseController
{
    protected $db;
    protected $response;
    protected $M_Common;
    protected $M_User;
    protected $M_Token;
    protected $M_Promotion;
    protected $M_Server;
    protected $M_Line;
    protected $M_Model_Common;

    public function __construct()
    {
        $this->db = \Config\Database::connect('promotion');
        $this->M_Common = new M_Common();
        $this->M_User = new M_User();
        $this->M_Token = new M_Token();
        $this->M_Promotion = new M_Promotion();
        $this->M_Server = new M_Server();
        $this->M_Line = new M_Line();
        $this->M_Model_Common = new M_Model_Common();
    }

    /**
     * 取得使用者資料
     */
    public function index()
    {
        $result = array('success' => False);
        $data = $this->M_Model_Common->getData('users', ['type !=' => 'admin'], [], True);

        foreach ($data as $_key => $_val) {
            $data[$_key]['server'] = [];
            $server = $this->M_User->getServerPermission($_val['id']);

            if (!empty($server)) {
                $data[$_key]['server'] = $server;
            }
        }

        if (empty($data)) {
            $result['msg'] = '查無資料';
        }

        foreach ($data as $_key => $_val) {
            unset($data[$_key]['password']);
        }

        $result['success'] = True;
        $result['msg'] = '查詢成功';
        $result['data'] = $data;

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    public function condition()
    {
        $result = array('success' => False);
        $postData = $this->request->getJSON(True);

        $data = $this->M_Model_Common->getData('user', $postData, []);

        $result['success'] = True;
        $result['msg'] = '查詢成功';
        $result['data'] = $data;

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    /**
     * 新增使用者
     */
    public function create()
    {
        $result = array('success' => False);
        $postData = $this->request->getJSON(True);        

        $userId = $this->M_User->create($postData);

        $result['success'] = True;
        $result['msg'] = '新增成功';
        $result['user_id'] = $userId;

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    /**
     * 取得管理者資料
     */
    public function getManager()
    {
        $result = array('success' => False);
        $data = $this->M_Model_Common->getData('users', ['type' => 'admin'], [], True);

        if (empty($data)) {
            $result['msg'] = '查無資料';
        }

        foreach ($data as $_key => $_val) {
            unset($data[$_key]['password']);
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
        $postData = $this->request->getJSON(True);
        $checkResult = $this->M_User->checkUser($postData);
        $result = array('success' => False);
        [$success] = $checkResult;

        if (is_array($checkResult) && count($checkResult) == 2) {
            [$success, $userData] = $checkResult;
        }        

        // 如果使用者資料不存在，則建立使用者資料
        if ($success === False) {
            $createResult = $this->M_User->create($postData);

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
     * 確認使用者資料
     * @param array $data 使用者資料
     */
    public function checkUser($data)
    {
        // 確認使用者資料是否存在
        $checkResult = $this->M_User->checkUser($data);

        // 如果使用者資料不存在，則建立使用者資料
        if ($checkResult === False) {
            $this->M_User->createUser($data);
        }

        $result = array(
            'success' => True,
            'message' => 'User data is valid',
        );

        return $this->response->setJSON($result);
    }

    /**
     * 取得使用者ID
     * @param string $token Token
     */ 
    public function getUserInfo()
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

        $userData = $this->M_User->getUserInfo($tokenData['user_id']);
        $promotionData = $this->M_Promotion->getPromotion($tokenData['user_id']);
        $lineData = $this->M_Line->getLineData(array('user_id' => $tokenData['user_id']));
        $serverData = $this->M_Server->getServer($tokenData['server']);

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

    /**
     * 更新信箱通知
     */
    public function updateEmailNotify()
    {
        $postData = $this->request->getJSON(True);

        $this->M_User->updateEmailNotify($postData['user'], $postData['server'], $postData['emailNotify'], $postData['email']);
        
        $result = array('success' => True);

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    /**
     * 更新Line通知
     */
    public function updateLineNotify()
    {
        $postData = $this->request->getJSON(True);
        $this->M_User->updateLineNotify($postData['user'], $postData['server'], $postData['lineNotify']);

        $result = array('success' => True);

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    /**
     * 儲存state
     */
    public function saveState()
    {
        $postData = $this->request->getJSON(True);
        $this->M_Line->saveState($postData['state'], $postData['userId'], $postData['token']);
    }

    public function callback()
    {
        $getData = $this->request->getGet();
        $result = $this->M_Line->callback($getData['state'], $getData['code']);

        if ($result['success'] === False){
            echo $result['msg'];
            die();
        }

        header("Location: {$result['url']}");
    }

    public function test()
    {
        $content = "<h1>Promotion Test</h1><p>test</p>";
        print_r($this->M_User->sendEmail('13gt7895123@gmail.com', 'Promotion Test', $content)); die();

        $result = array('success' => True);
        
        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
}