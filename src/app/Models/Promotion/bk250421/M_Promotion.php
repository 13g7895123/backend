<?php

namespace App\Models\Promotion;
use CodeIgniter\Model;
use App\Models\M_Common as M_Model_Common;
use App\Models\Promotion\M_PromotionItem;

class M_Promotion extends Model
{
    protected $db;
    protected $table;
    protected $M_Model_Common;
    protected $M_PromotionItem;

    public function __construct()
    {
        $this->db = \Config\Database::connect('promotion');  // 預設資料庫
        $this->M_Model_Common = new M_Model_Common();
        $this->M_PromotionItem = new M_PromotionItem();
    }

    public function getData($where = [], $field = [], $queryMultiple = False, $join = [])
    {
        $table = 'promotions';
        $builder = $this->db->table($table);

        if (!empty($field)){
            $select = implode(',', $field);
            $builder->select($select);
        }
        
        if (!empty($join)){
            foreach ($join as $item) {
                $builder->join($item['table'], "{$item['table']}.{$item['field']} = {$table}.{$item['source_field']}");
            }
        }

        if (!empty($where)){
            $builder->where($where);
        }

        $data = ($queryMultiple) ? $builder->get()->getResultArray() : $builder->get()->getRowArray();

        return $data;
    }   

    /**
     * 建立推廣資料
     * @param array $data
     */
    public function create($data)
    {
        $this->db->table('promotions')
            ->insert($data);

        return $this->db->insertID();
    }

    /**
     * 更新推廣資料
     * @param int $promotionId 推廣資料Id
     * @param array $data
     */
    public function updateData($promotionId, $data)
    {
        $this->db->table('promotions')
            ->where('id', $promotionId)
            ->update($data);
    }

    /**
     * 刪除推廣資料
     * @param int $promotionId 推廣資料Id
     * @return void
     */
    public function deleteData($promotionId)
    {
        // 先刪除細項
        $this->M_PromotionItem->deleteData($promotionId);

        // 再刪除主資料
        $builder = $this->db->table('promotions');

        if (is_array($promotionId)){
            $builder->whereIn('id', $promotionId);
        } else {
            $builder->where('id', $promotionId);
        }

        $builder->delete();
    
        return True;
    }

    /**
     * 取得推廣資料(透過使用者ID)
     * @param int $userId User Id
     * @return array
     */
    public function getPromotion($userId)
    {
        $promotionData = $this->db->table('promotions')
            ->where('user_id', $userId)
            ->get()
            ->getResultArray();

        foreach ($promotionData as $_key => $_val) {
            $promotionData[$_key]['detail'] = $this->M_PromotionItem->getPromotionItem($_val['id']);
        }

        return $promotionData;  
    }

    public function getPromotionByFrequency($userId, $frequency)
    {
        switch ($frequency) {
            case 'daily':
                $where = "DATE(created_at) = CURDATE()"; // 當日
                break;    
            case 'weekly':
                $where = "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)"; // 當週
                break;    
            case 'monthly':
                $where = "YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())"; // 當月
                break;    
            default:
                return []; // 如果頻率無效，返回空陣列
        }

        $count = $this->db->table('promotions')
            ->where('user_id', $userId)
            ->where($where)
            ->countAllResults();

        return $count;
    }

    /**
     * 取得推廣審核狀況
     * @param int $promotionId 推廣資料Id
     * @return array
     */
    public function getPromotionAudit($promotionId)
    {
        // 取得該推廣項目細項
        $promotionDetail = $this->M_PromotionItem->getData(['promotion_id' => $promotionId], [], True);
        
        // 該推廣項目審核結果
        $status = array_column($promotionDetail, 'status');

        // 審核結果
        $isFinished = False;    // 是否審核完成
        $auditResult = False;   // 審核結果
        if (!in_array('standby', $status)){
            $isFinished = True;
            $promotionStatus = 'failed';

            // 審核成功
            if (!in_array('failed', $status)){
                $auditResult = True;
                $promotionStatus = 'success';                
            }

            // 更新推廣狀態
            $this->updateData($promotionId, ['status' => $promotionStatus]);
        }

        return [$isFinished, $auditResult];
    }

    /**
     * 取得推廣通知資料
     * @param int $promotionId 推廣資料Id
     * @return array
     */
    public function getNotification($promotionId)
    {
        $where = array('promotions.id' => $promotionId);
        $join = array(
            array(
                'table' => 'server',
                'field' => 'code',
                'source_field' => 'server',
            ),
            array(
                'table' => 'player',
                'field' => 'id',
                'source_field' => 'user_id',
            ),
            // array(
            //     'table' => 'line',
            //     'field' => 'player_id',
            //     'source_field' => 'user_id',
            // ),
        );
        $field = array('*', 'promotions.id');
        $promotionData = $this->getData($where, $field, False, $join);

        // 取得Line資料，不使用JOIN是因為有可能玩家尚未綁定Line
        $line = $this->M_Model_Common->getData('line', ['player_id' => $promotionData['user_id']]);

        if (!empty($line)){
            $promotionData = array_merge($promotionData, $line);
        }

        // 通知資訊
        $notification = array(
            'email' => array(
                'status' => False,
                'data' => null,
            ),
            'line' => array(
                'status' => False,
                'data' => null,
            ),
        );

        // 判斷是否有開啟
        $notifyType = array('email', 'line');
        foreach ($notifyType as $type){
            if ($promotionData["notify_{$type}"] == '1'){
                $notification[$type]['status'] = True;

                // 使用者是否有提供資訊
                $type = ($type === 'email') ? 'email' : 'uid';
                if ($promotionData[$type] != ''){
                    if ($type == 'uid'){
                        $notification['line']['data'] = $promotionData[$type];
                        continue;
                    }

                    $notification[$type]['data'] = $promotionData[$type];
                }
            }
        }

        return $notification;
    }
}