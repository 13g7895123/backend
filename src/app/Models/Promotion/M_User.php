<?php

namespace App\Models\Promotion;

use CodeIgniter\Model;
use CodeIgniter\Email\Email;

class M_User extends Model
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect('promotion');  // 預設資料庫
    }

    public function index()
    {
        $users = $this->db->table('users')->get()->getResultArray();
        return $users;
    }

    /**
     * 建立使用者
     * @param array $data 使用者資料
     */
    public function create(array $data): int
    {
        // 帳號類型
        $type = (isset($data['is_admin']) && $data['is_admin'] == 1) ? 'admin' : 'user';

        $insertData = array(
            'account' => $data['account'],
            'password' => hash('sha256', $data['password']),
            'type' => $type,
        );
        $this->db->table('users')->insert($insertData);
        $insertId = $this->db->insertID();

        // 建立使用者伺服器權限
        if (count($data['server']) > 0){
            foreach ($data['server'] as $server) {
                $this->db->table('user_server_permissions')->insert([
                    'user_id' => $insertId,
                    'server_code' => $server,
                ]);
            }
        }

        return $insertId;
    }

    /**
     * 取得使用者伺服器權限
     */
    public function getServerPermission(int $userId): array
    {
        $serverPermission = $this->db->table('user_server_permissions')
            ->join('server', 'server.code = user_server_permissions.server_code')
            ->select('server.code, server.name')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        return $serverPermission;
    }
}