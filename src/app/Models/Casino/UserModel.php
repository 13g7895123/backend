<?php

namespace App\Models\Casino;
use CodeIgniter\Model;
use App\Models\M_Common as M_Model_Common;

class UserModel extends Model
{
    protected $db;
    protected $M_Model_Common;

    public function __construct()
    {
        $this->db = \Config\Database::connect('casino');  // 預設資料庫
        $this->M_Model_Common = new M_Model_Common();
    }

    /**
     * 新增資料
     * @param array $data
     */
    public function createData(array $data)
    {
        $type = 'admin';
        $data['type'] = $type;
        $data['password'] = hash('sha256', $data['password']);

        $this->db->table('users')->insert($data);
        $result['success'] = True;
        $result['msg'] = '新增成功';
        $result['user_id'] = $this->db->insertID();

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
        return $this->db->insertID();
    }

    /**
     * 登入
     * @param string $account 帳號
     * @param string $password 密碼
     */
    public function login(string $account, string $password)
    {
        $user = $this->db->table('users')
            ->where('account', $account)
            ->where('password', hash('sha256', $password))
            ->get()
            ->getRowArray();

        if (empty($user)){
            return array('success' => False, 'message' => '帳號或密碼錯誤');
        }

        unset($user['password']);

        return array('success' => True, 'message' => '登入成功', 'user' => $user);
    }
}