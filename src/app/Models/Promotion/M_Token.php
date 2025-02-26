<?php

namespace App\Models\Promotion;
use CodeIgniter\Model;
use App\Models\M_Common as M_Model_Common;

class M_Token extends Model
{
    protected $db;
    protected $table;
    protected $M_Model_Common;

    public function __construct()
    {
        $this->db = \Config\Database::connect('promotion');  // 預設資料庫
        $this->M_Model_Common = new M_Model_Common();
    }

    /**
     * 建立新Token
     * @param   string    $server 伺服器
     * @param   int       $userId 使用者ID
     * @param   string    $page   頁面
     * @return  string    $token
     */
    public function getToken($server, $userId, $page)
    {
        $length = 20;
        $characters = 'abcdefghjklmnpqrstuvwxyz23456789';  // 排除 I, O, 1, 0
        $maxIndex = strlen($characters) - 1;
        
        do{
            $token = '';

            for ($i = 0; $i < $length; $i++) {
                $randomIndex = mt_rand(0, $maxIndex);
                $token .= $characters[$randomIndex];
            }

            $checkToken = $this->checkTokenExist($token);
        }while($checkToken === False);
        
        $insertData = array(
            'token' => $token,
            'server' => $server,
            'user_id' => $userId,
            'page' => $page,
        );
        $this->db->table('token')->insert($insertData);

        return $token;
    }

    /**
     * 確認Token是否存在
     * @param string $token Token
     * @return boolean
     */
    private function checkTokenExist($token)
    {
        $tokenData = $this->db->table('token')
            ->where('token', $token)
            ->get()
            ->getRowArray();

        return (empty($tokenData)) ? True : False;
    }

    /**
     * 取得Token資料
     * @param string $token Token
     * @return array
     */
    public function getTokenInfo($token)
    {
        $tokenData = $this->db->table('token')
            ->where('token', $token)
            ->get()
            ->getRowArray();

        return $tokenData;
    }
}