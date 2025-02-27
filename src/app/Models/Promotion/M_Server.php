<?php

namespace App\Models\Promotion;
use CodeIgniter\Model;
use App\Models\M_Common as M_Model_Common;

class M_Server extends Model
{
    protected $db;
    protected $table;
    protected $M_Model_Common;
    protected $primaryKey;

    public function __construct()
    {
        $this->db = \Config\Database::connect('promotion');  // 預設資料庫
        $this->M_Model_Common = new M_Model_Common();
    }

    /**
     * 取得伺服器資料
     * @param string $code
     */
    public function getServer($where = [], $field = [], $queryMultiple = False)
    {
        $builder = $this->db->table('server');

        if (!empty($where)){
            $builder->where($where);
        }

        $data = ($queryMultiple) ? $builder->get()->getResultArray() : $builder->get()->getRowArray();

        return $data;
    }

    /**
     * 特殊欄位轉換
     * @param string $table 資料表 
     * @param string $field 資料欄位
     * @param array  $data  資料
     */
    public function convertSpecialField($table, $field, $data)
    {
        $specialField = array(
            'server' => array(
                'require_character' => function () use ($data){
                    if (isset($data['targetType'])){
                        $data['require_character'] = ($data['targetType'] == 'character') ? 1 : 0;
                    }
                }
            ),
        );

        if (isset($specialField[$table][$field])){
            $specialField[$table][$field]($data);
        }

        return $data;
    }

    /**
     * 取得過濾後的總筆數
     */
    public function getFilteredCount($conditions = [])
    {
        $builder = $this->db->table($this->table);
        
        if (!empty($conditions['search'])) {
            $builder->groupStart()
                ->like('name', $conditions['search'])
                ->orLike('code', $conditions['search'])
                ->groupEnd();
        }
        
        return $builder->countAllResults();
    }

    /**
     * 取得 DataTable 資料與統計
     */
    public function getDataTableData($params)
    {
        $builder = $this->db->table($this->table);
        
        // 搜尋條件
        if (!empty($params['search']) && !empty($params['searchColumn'])) {
            $builder->like($params['searchColumn'], $params['search']);
        }

        // 取得過濾後的總筆數
        $filteredRecords = $builder->countAllResults(false);
        
        // 排序
        $builder->orderBy($params['orderColumn'], $params['orderDir']);
        
        // 分頁
        $builder->limit($params['length'], $params['start']);
        
        // 取得資料
        $data = $builder->get()->getResultArray();

        // 取得總筆數
        $totalRecords = $this->db->table($this->table)->countAllResults();
        
        return [
            'total' => $totalRecords,
            'filtered' => $filteredRecords,
            'data' => $data
        ];
    }
}