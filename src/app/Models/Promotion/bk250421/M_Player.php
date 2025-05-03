<?php

namespace App\Models\Promotion;

use CodeIgniter\Model;
use CodeIgniter\Email\Email;

class M_Player extends Model
{
    protected $db;
    protected $table = 'player';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['username', 'server', 'character_name', 'email', 'line_id', 'notify_mail', 'notify_line'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'username' => 'required|min_length[6]|max_length[100]|is_unique[player.username,id,{id}]',
    ];

    protected $validationMessages = [
        'username' => [
            'required' => '帳號為必填項',
            'min_length' => '名稱最少需要2個字元',
            'max_length' => '名稱最多100個字元',
        ],
    ];

    public function __construct()
    {
        $this->db = \Config\Database::connect('promotion');  // 預設資料庫
    }

    /**
     * 取得使用者資訊
     * @param int $userId 使用者ID
     * @return array
     */
    public function getPlayerInfo($userId)
    {
        $userData = $this->db->table('player')
            ->where('id', $userId)
            ->get()
            ->getRowArray();

        return $userData;
    }

    /**
     * 建立使用者
     * @param array $data 使用者資料
     */
    public function create(array $data): array
    {
        try {
            $this->db->transStart();

            if (isset($data['characterName'])) {
                $data['character_name'] = $data['characterName'];
                unset($data['characterName']);
            }            

            if ($this->db->table('player')->insert($data) === false) {
                return ['error' => 'Failed to create user data'];
            }
            $userId = $this->db->insertID();

            $this->db->transComplete();
            return ['success' => true, 'message' => 'User created successfully', 'user_id' => $userId, 'server' => $data['server']];
        } catch (\Exception $e) {
            return ['error' => 'Failed to create user: ' . $e->getMessage()];
        }
    }

    /**
     * 刪除使用者
     * @param int $userId 使用者ID
     */
    public function deleteData($userId)
    {
        $builder = $this->db->table('player');

        if (is_array($userId)){
            $builder->whereIn('id', $userId);
        } else {
            $builder->where('id', $userId);
        }

        $builder->delete();
    }

    /**
     * 確認使用者資料
     */
    public function checkUser(array $data): array
    {
        $builder = $this->db->table('player');
        $builder->where('server', $data['server']);
        $builder->where('username', $data['username']);

        if (isset($data['characterName'])){
            $builder->where('character_name', $data['characterName']);
        }

        $userData = $builder->get()->getRowArray();

        if (empty($userData)) {
            return array(False);
        }

        return [True, $userData];
    }

    /**
     * 建立身分驗證紀錄
     */
    public function identifySubmitLog($data)
    {
        $builder = $this->db->table('identify_submit_log');
        $builder->insert($data);
    }

    /**
     * 更新信箱通知
     */
    public function updateEmailNotify($userId, $server, $emailNotify, $email=null)
    {
        $builder = $this->db->table('player');
        $builder->where('id', $userId);
        $builder->where('server', $server);
        $builder->update(['notify_email' => $emailNotify, 'email' => $email]);

        return True;
    }

    /**
     * 更新Line通知
     */
    public function updateLineNotify($userId, $server, $lineNotify)
    {
        $builder = $this->db->table('player');
        $builder->where('id', $userId);
        $builder->update(['notify_line' => $lineNotify]);
    }

    /**
     * 新增通知結果
     * @param int $promotionId 推廣資料Id
     * @param array $notifyData 通知資料
     */
    public function createNotifyResult($promotionId, $notifyData)
    {
        $insertData = array('promotion_id' => $promotionId);

        if ($notifyData['email']['status'] === True 
        && !($notifyData['email']['data'] === null)){
            $insertData['type'] = 'email';
            $insertData['content'] = $notifyData['email']['data'];
        }

        if ($notifyData['line']['status'] === True){
            $insertData['line'] = $notifyData['line']['data'];
        }
        
    }

    /**
     * 寄送Email
     * @param string $toEmail 收件者Email
     * @param string $subject 主旨
     * @param string $content 內容
     * @return bool 發送結果
     */
    public function sendEmail($toEmail, $subject, $content)
    {
        // 載入Email
        $email = \Config\Services::email();

        // 設置Email
        $email->setFrom('13g7895123@gmail.com', 'Promotion Test');
        $email->setTo($toEmail);
        $email->setSubject($subject);
        $email->setMessage($content);

        // 寄送Email
        return $email->send();
    }
}