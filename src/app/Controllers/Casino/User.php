<?
namespace App\Controllers\Casino;

use App\Controllers\BaseController;
use App\Models\Casino\UserModel;
use App\Models\Casino\TokenModel;
use App\Models\M_Common as M_Model_Common;

class User extends BaseController
{
    protected $db;
    protected $UserModel;
    protected $TokenModel;
    protected $M_Model_Common;

    public function __construct()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->UserModel = new UserModel();
        $this->TokenModel = new TokenModel();
        $this->M_Model_Common = new M_Model_Common();
        $this->db = \Config\Database::connect('casino');
    }

    /**
     * 新增使用者(建立測試資料用)
     */
    public function create()
    {
        $result = array('success' => false);
        $postData = $this->request->getJSON(true); 

        $userId = $this->UserModel->createData($postData);

        $result['success'] = true;
        $result['msg'] = '新增成功';
        $result['user_id'] = $userId;

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    public function login()
    {
        $result = array('success' => false);
        $postData = $this->request->getJSON(true);
        $loginResult = $this->UserModel->login($postData['account'], $postData['password']);

        if ($loginResult['success'] === false) {
            $result['msg'] = $loginResult['message'];

            $this->response->noCache();
            $this->response->setContentType('application/json');
            return $this->response->setJSON($result);
        }

        $refreshTokenData = $this->TokenModel->createAdminToken('refresh');
        $accessTokenData = $this->TokenModel->createAdminToken('access', $refreshTokenData[2]);

        $result['success'] = true;
        $result['msg'] = '登入成功';
        $result['user'] = $loginResult['user'];
        $result['token'] = array(
            'access' => $accessTokenData[0],
            'access_expired_at' => $accessTokenData[1],
            'refresh' => $refreshTokenData[0],
            'refresh_expired_at' => $refreshTokenData[1],
        );

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
}