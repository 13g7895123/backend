<?php

namespace App\Models\Promotion;
use CodeIgniter\Model;

class M_CustomizedDb extends Model
{
    protected $db;
    protected $table;
    protected $serverCode;      // 伺服器代碼
    protected $dbInfo;
    protected $dbField;

    public function __construct($serverCode)
    {
        $this->serverCode = $serverCode;
        $this->getDatabase();                   // 取得資料庫連線資訊
        $this->getDatabaseField();              // 取得資料庫欄位資訊

        $this->table = $this->dbField[0]['table_name'];
        $this->connectDatabase();               // 連線資料庫
    }

    // 取得資料庫連線資訊
    private function getDatabase()
    {
        $promotionDb = \Config\Database::connect('promotion');

        $server = $promotionDb->table('customized_db')
            ->where('server_code', $this->serverCode)
            ->get()
            ->getRowArray();

        // 檢查資料庫連線資訊是否存在
        if (empty($server)) {
            throw new \Exception('Database connection info not found for server code: ' . $this->serverCode);
        }
        
        $this->dbInfo = $server;
    }

    // 取得資料庫欄位資訊
    private function getDatabaseField()
    {
        $promotionDb = \Config\Database::connect('promotion');

        $field = $promotionDb->table('customized_field')
            ->where('server_code', $this->serverCode)
            ->get()
            ->getResultArray();

        $this->dbField = $field;
    }

    // 連線資料庫
    private function connectDatabase()
    {
        try{
            // 手動連接資料庫
            $this->db = \Config\Database::connect([
                'DSN'      => '',
                'hostname' => $this->dbInfo['host'],
                'username' => $this->dbInfo['account'],
                'password' => $this->dbInfo['password'],
                'database' => $this->dbInfo['name'],
                'port'     => (int)$this->dbInfo['port'],
                'DBDriver' => 'MySQLi',
                'charset'  => 'utf8mb4',
            ]);

            // 檢查連線是否成功
            try {
                $this->db->connect();
                return true;
            } catch (\Exception $e) {
                throw new \Exception('Database connection failed: ' . $e->getMessage());
            }
        }catch (\Exception $e){
            return false;
        }   
    }

    // 寫入資料
    public function insertData($data)
    {
        $this->db->table($this->table)->insert($data);
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getDbInfo()
    {
        return $this->dbInfo;
    }

    public function getDbField()
    {
        return $this->dbField;
    }
}
