<?php

namespace App\Models\Promotion;

use CodeIgniter\Model;

class M_Line extends Model
{
    protected $db;
    private $tokenUrl;
    private $profileUrl;
    private $verifyUrl;

    public function __construct()
    {
        $this->db = \Config\Database::connect('promotion');  // 預設資料庫

        // Line API URL
        $config = $this->config();
        $lineUrl = $config['line']['url'];
        $lineBaseUrl = $lineUrl['base'];
        $this->tokenUrl = "{$lineBaseUrl}{$lineUrl['token']}";
        $this->profileUrl = "{$lineBaseUrl}{$lineUrl['profile']}";
        $this->verifyUrl = "{$lineBaseUrl}{$lineUrl['verify']}";
    }

    /* 新增資料 */
    public function createData($data)
    {
        $this->db->table('line')->insert($data);
    }

    /* 更新資料 */
    public function updateData($uid, $data)
    {
        $this->db->table('line')
            ->where('uid', $uid)
            ->update($data);
    }

    /* 保存Line資料 */
    public function saveData($data)
    {
        // 取得Line資料
        $condition = array('uid' => $data['uid']);
        $lineData = $this->getLineData($condition);

        // 轉換Array Key
        $data['image_url'] = $data['image-url'];
        unset($data['image-url']);

        // 如果資料不存在，則新增資料
        if ($lineData === False){
            $this->createData($data);
        }

        // 如果資料存在，則更新資料
        $this->updateData($data['uid'], $data);

        return True;
    }

    /* 取得Line資料 */
    public function getLineData($condition, $multiData=False)
    {
        $builder = $this->db->table('line');
        $builder->where($condition);

        // 取得資料
        $data = ($multiData === True) ? $builder->get()->getResultArray() : $builder->get()->getRowArray();

        return $data;
    }

    /* 保存State碼 */
    public function saveState($state, $userId, $token)
    {
        // print_r($userId); die();
        $insertData = array(
            'state' => $state,
            'user_id' => $userId,
            'token' => $token,
        );
        // print_r($insertData); die();
        // $sql = $this->db->table('line_state')->set($insertData)->getCompiledInsert();
        // print_r($sql); die();
        $this->db->table('line_state')->insert($insertData);
    }

    /* 接收Line Callback */
    // public function callback($state, $code, $userId, $token)
    public function callback($state, $code)
    {
        $result = array('success' => False);

        /* 驗證state是否存在 */
        $lineState = $this->getLineState($state);
        if ($lineState === False){
            $result = array(
                'success' => False,
                'msg' => 'state不存在',
            );

            return $result;
        }

        // 前後端路徑
        $config = $this->config();
        $frontend = $config['frontend'];
        $backend = $config['backend'];
        $domainUrl = $frontend['linkMethod'] . '://' . $frontend['domain'];
        $apiDomainUrl = $backend['linkMethod'] . '://' . $backend['domain'];

        $frontendUrl = "{$domainUrl}/promotion/{$lineState['server']}/{$lineState['token']}";   // 導回前端
        $redirectUrl = $apiDomainUrl . '/api/promotion/line/callback';                          // Line導向路徑

        // Line相關參數
        $lineCustomInfo = $config['line']['customInfo'];
        $robotInfo = array(
            'clientId' => $lineCustomInfo['clientId'],
            'clientSecret' => $lineCustomInfo['clientSecret'],
        );

        // CurlRequest
        $client = \Config\Services::curlrequest();

        // 取得Line AccessToken
        $response = $this->getAccessToken($code, $redirectUrl, $robotInfo, $client);

        if (!isset($response['access_token'])){
            $result['msg'] = '取得AccessToken失敗';
            return $result;
        }

        // 使用者Line資訊
        $lineInfo = array('user_id' => $lineState['user_id']);

        // 取得Line Profile
        [$uid, $name, $imageUrl] = $this->getProfile($response['access_token'], $client);
        $lineInfo['uid'] = $uid;
        $lineInfo['name'] = $name;
        $lineInfo['image-url'] = $imageUrl;

        // 取得Line Email
        $lineInfo['email'] = $this->getEmail($response['id_token'], $robotInfo['clientId'], $client);

        if ($lineInfo['uid'] == ''){
            $result['msg'] = '取得Line Profile失敗';
            return $result;
        }

        // 儲存Line資訊
        $saveResult = $this->saveData($lineInfo);

        if ($saveResult === False){
            $result['msg'] = '儲存Line資訊失敗';
            return $result;
        }

        $result['success'] = True;
        $result['url'] = $frontendUrl;

        return $result;
    }

    /**
     * 取得LineAccessToken
     * @param str $code 授權碼
     * @param str $redirectUrl 重導向網址
     * @param array $robotInfo Line機器人資訊
     * @param object $client CurlRequest
     */
    public function getAccessToken($code, $redirectUrl, $robotInfo, $client)
    {
        $params = array(
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUrl,
            'client_id' => $robotInfo['clientId'],
            'client_secret' => $robotInfo['clientSecret'],
        );

        // 呼叫 api
        // $client = \Config\Services::curlrequest();
        $response = $client->post($this->tokenUrl, [
            'form_params' => $params,
        ]);

        // api 回應
        $responseData = json_decode($response->getBody(), true);

        return $responseData;
    }

    /**
     * 取得Line Profile
     * @param str $accessToken AccessToken
     * @param object $client CurlRequest
     */
    public function getProfile($accessToken, $client)
    {
        $response = $client->get($this->profileUrl, [
            'headers' => [
                'Authorization' => "Bearer $accessToken",
            ],
        ]);

        $responseData = json_decode($response->getBody(), true);

        $uid = $responseData['userId'] ?? '';
        $name = $responseData['displayName'] ?? '';
        $imageUrl = $responseData['pictureUrl'] ?? '';

        return array($uid, $name, $imageUrl);
    }

    /**
     * 取得Line Email
     * @param str $idToken 身分驗證碼
     * @param str $clientId 機器人ID
     * @param object $client CurlRequest
     */
    public function getEmail($idToken, $clientId, $client)
    {
        $params = array(
            'id_token' => $idToken,
            'client_id' => $clientId,
        );

        $response = $client->post($this->verifyUrl, [
            'form_params' => $params,
        ]);

        $responseData = json_decode($response->getBody(), true);

        return $responseData['email'] ?? '';
    }

    /**
     * Line相關參數
     */
    public function config()
    {
        $config = array(
            'frontend' => array(
                'linkMethod' => 'http',
                'domain' => 'localhost:3000',
            ),
            'backend' => array(
                'linkMethod' => 'https',
                'domain' => 'dev-capi.mercylife.cc',
            ),            
            'line' => array(
                'customInfo' => array(
                    'clientId' => '2006270481',
                    'clientSecret' => 'e5d008893d451d72ba01fa31554dece4',
                ),
                'url' => array(
                    'base' => 'https://api.line.me',
                    'token' => '/oauth2/v2.1/token',
                    'profile' => '/v2/profile',
                    'verify' => '/oauth2/v2.1/verify',
                ),
            ),
        );

        return $config;
    }

    /**
     * 取得Line State
     */
    public function getLineState($state)
    {
        $data = $this->db->table('line_state')
            ->join('users', 'users.id = line_state.user_id')
            ->select('*, users.id as uid')
            ->where('state', $state)
            ->get()
            ->getRowArray();

        return (!empty($data)) ? $data : False;
    }
}
