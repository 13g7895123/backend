<?php

namespace App\Models\Promotion;
use CodeIgniter\Model;
use App\Models\M_Common as M_Model_Common;
use App\Models\Promotion\M_PromotionItem;
use App\Models\Promotion\M_CustomizedDb;
use App\Models\Promotion\M_Line;
use App\Models\Promotion\M_Player;
use App\Models\Promotion\M_Mail;

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
        $where = array(
            'user_id' => $data['user_id'],
            'server' => $data['server'],
            'DATE(created_at)' => date('Y-m-d'),
        );
        $promotionData = $this->M_Model_Common->getData('promotions', $where, [], false);

        if (!empty($promotionData)){
            $promotionId = $promotionData['id'];

            $updateData = array(
                'status' => 'standby',
            );
            $this->db->table('promotions')
                ->where('id', $promotionId)
                ->update($updateData);

            // 如果已經有當天的推廣資料，則不再新增
            return $promotionId;
        }

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

    public function getPromotionByFrequency($userId, $frequency, $type=null)
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

        $builder = $this->db->table('promotions');
        $builder->where('user_id', $userId);
        $builder->where($where);

        if ($type === 'finished'){
            $builder->where('status', 'success');
        }

        $promotion = $builder->get()->getResultArray();

        if (empty($promotion)){
            return 0;
        }

        $promotionIds = array_column($promotion, 'id');

        $builder = $this->db->table('promotion_items');
        $builder->whereIn('promotion_id', $promotionIds);

        if ($type === 'finished'){
            $builder->where('status', 'success');
        }
        
        $detail = $builder->get()->getResultArray();
        
        return count($detail);
    }

    /**
     * 取得推廣審核狀況
     * @param int $promotionId 推廣資料Id
     * @return array
     */
    public function getPromotionAudit($promotionId)
    {
        // 取得該推廣項目細項
        $promotionDetail = $this->M_PromotionItem->getData(['promotion_id' => $promotionId], [], true);
        
        // 該推廣項目審核結果
        $status = array_column($promotionDetail, 'status');

        // 審核結果
        $isFinished = false;    // 是否審核完成
        $auditResult = false;   // 審核結果
        if (!in_array('standby', $status)){
            $isFinished = true;
            $promotionStatus = 'failed';

            // 審核成功
            if (!in_array('failed', $status)){
                $auditResult = true;
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
                if (isset($promotionData[$type]) && $promotionData[$type] != ''){
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

    /**
     * 批次審核推廣資料
     * @param array $promotionId 推廣資料Id
     * @param string $status 審核狀態
     * @return void
     */
    public function batchAudit($promotionId, $status)
    {
        $tempLog = array();

        // 取得未更新細項
        $where = array(
            'promotion_id' => $promotionId,
            'status' => 'standby',
        );
        $promotionDetails = $this->M_Model_Common->getData('promotion_items', $where, [], true);
        $tempLog['promotionDetails'] = $promotionDetails;

        // 更新各細項資料
        foreach ($promotionDetails as $_val){
            $this->M_PromotionItem->updateData(['status' => $status], ['id' => $_val['id']]);
        }

        // 取得資料唯一值(帳號 + 角色)
        $promotionData = array();
        foreach ($promotionId as $_val){
            $promotion = $this->M_Model_Common->getData('promotions', ['id' => $_val]);
            $promotionData[] = array(
                'id' => $_val,
                'player_id' => $promotion['user_id'],
            );
        }
        $tempLog['promotionData'] = $promotionData;

        // 更新主資料狀態
        $counts = array_count_values(array_column($promotionData, 'player_id'));      // 各資料筆數
        $tempLog['check'] = array();
        foreach ($promotionData as $_key => $_val){
            $auditData = $this->getPromotionAudit($_val['id']);
            [$isFinished, $auditResult] = $auditData;

            if ($isFinished === true){
                // 更新推廣結果
                $promotionStatus = ($auditResult === true) ? 'success' : 'failed';
                $this->updateData($_val['id'], ['status' => $promotionStatus]);

                if ($auditResult === true){
                    // 進一步確認是否達標
                    $promotion = $this->M_Model_Common->getData('promotions', ['id' => $_val['id']]);
                    $userId = $promotion['user_id'];
                    $serverCode = $promotion['server'];
                    $server = $this->M_Model_Common->getData('server', ['code' => $serverCode]);
                    
                    // 當前進度
                    $nowSchedule = $this->getPromotionByFrequency($userId, $server['cycle'], 'finished');

                    $tempLog['check'][]['nowSchedule'] = array(
                        'id' => $_val['id'],
                        'nowSchedule' => $nowSchedule,
                        'limit_number' => $server['limit_number'],
                    );

                    // 達標送禮
                    if ($nowSchedule >= $server['limit_number']){
                        $playerData = $this->M_Model_Common->getData('player', ['id' => $userId]);
                        $isReward = $this->checkReward($playerData['id'], $serverCode, $_val['created_at']);

                        if ($isReward === false){
                            $this->sendRewards($_val['id'], $serverCode, $playerData);
                            $this->sendNotification($_val['id'], $auditResult, $_val['player_id']);
                        }
                    }
                }            
            }
        }

        return $tempLog;
    }

    public function batchAuditV2($promotionId, $status)
    {
        // 引用Model
        $M_Player = new M_Player();

        // 推廣資料
        $where = array(
            'id' => $promotionId,
            'status' => 'standby',
        );
        $promotionData = $this->M_Model_Common->getData('promotions', $where, [], true);

        $playerIds = array_column($promotionData, 'user_id');
        $playerIds = array_unique($playerIds); // 取得唯一的玩家ID
        sort($playerIds); // 排序玩家ID

        // 依使用者分類推廣
        $data = array();
        foreach ($playerIds as $_key => $_val){
            foreach ($promotionData as $p_key => $p_val){
                if ($_val == $p_val['user_id']){
                    $data[$_val][] = $p_val['id'];
                }
            }
        }

        // 依使用者逐項推廣審核
        foreach ($data as $_key => $_val){
            // 各使用者的個別主項資料
            foreach ($_val as $__key => $__val){
                // 取得明細資料
                $where = array(
                    'promotion_id' => $__val,
                    'status' => 'standby',
                );
                $promotionDetails = $this->M_Model_Common->getData('promotion_items', $where, [], true);

                // 更新各細項資料
                foreach ($promotionDetails as $_val){
                    $this->M_PromotionItem->updateData(['status' => $status], ['id' => $_val['id']]);
                }

                // 取得主項資料
                $where = array(
                    'id' => $__val,
                    'status' => 'standby',
                );
                $promotionData = $this->M_Model_Common->getData('promotions', $where, [], true);

                // 更新主項資料
                foreach ($promotionData as $_key => $_val){
                    $this->updateData($_val['id'], ['status' => $status]); 
                }
            }

            // 確認使用者審核是否已完成
            $playerId = $_key;
            $promotionStatus = $M_Player->fetchPromotionStatus($playerId);

            if ($promotionStatus['isFinished'] === true){
                // 達標送禮
                $playerData = $this->M_Model_Common->getData('player', ['id' => $playerId]);
                $isReward = $this->checkReward($playerId, $promotionStatus['serverCode']);

                if ($isReward === false){
                    $this->sendRewards($_val[0], $promotionStatus['serverCode'], $playerData);
                }
            }
        }
    }

    /**
     * Undocumented function
     *
     * @param [type] $promotionId
     * @param [type] $status
     * @return void
     */
    public function batchAuditV3($promotionId, $status)
    {
        $tempLog = array();

        // 取得未更新細項
        $where = array(
            'promotion_id' => $promotionId,
            'status' => 'standby',
        );
        $promotionDetails = $this->M_Model_Common->getData('promotion_items', $where, [], true);

        // 沒有資料不繼續動作
        if (empty($promotionDetails)){
            // 沒有需要更新的細項
            return array('code' => 0);
        }

        $promotionDetailIds = array_column($promotionDetails, 'id');
        $tempLog['promotionDetails'] = $promotionDetails;

        // 更新各細項資料
        $updateData = array('status' => $status);
        $where = array('id' => $promotionDetailIds);
        $this->M_PromotionItem->updateDataNew($updateData, $where);

        // 更新主項資料
        $this->db->table('promotions')
            ->whereIn('id', $promotionId)
            ->update($updateData);

        // 取得更新後細項資料(必須為完成的)
        $where = array(
            'id' => $promotionDetailIds,
            'status' => 'success',
        );
        $updatedPromotionDetails = $this->M_Model_Common->getData('promotion_items', $where, [], true);

        if (empty($updatedPromotionDetails)){
            // 更新後沒有完成的細項資料
            return array('code' => 1);
        }

        $checkPromotionData = array();      // 確認用的資料(主資料對細項資料)
        foreach ($promotionId as $_val){
            $checkPromotionData[] = array(
                'id' => $_val,
                'detail' => array_filter($updatedPromotionDetails, function($item) use ($_val){
                    return $item['promotion_id'] == $_val;
                }),
            );
        }

        // 主項目資料
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
        );
        $promotionData = $this->M_Model_Common->getData('promotions', ['promotions.id' => $promotionId], ['*', 'promotions.id', 'promotions.created_at'], true, $join);

        foreach ($checkPromotionData as $_val){
            $id = $_val['id'];

            $filterData = array_filter($promotionData, function($item) use ($id){
                return $item['id'] == $id;
            });

            $filterData = reset($filterData);

            if (count($_val['detail']) >= $filterData['limit_number']){
                $isReward = $this->checkReward($filterData['user_id'], $filterData['server'], $filterData['created_at']);

                if ($isReward === false){
                    $this->sendRewards($_val['id'], $filterData['server'], $filterData);
                    $this->sendNotification($_val['id'], true, $filterData['user_id']);
                }
            }
        }

        return $tempLog;
    }

    /**
     * 寄送獎勵
     * @param string $serverCode 伺服器代碼
     * @param array $playerData 玩家資料
     * @return void
     */
    public function sendRewards($promotionId, $serverCode, $playerData)
    {
        // 連線至對方資料庫
        $M_CustomizedDb = new M_CustomizedDb($serverCode);
        $databaseData = $M_CustomizedDb->getDbInfo();

        // 寫入資料，預設一定有帳號
        $insertData = array($databaseData['account_field'] => $playerData['username']);

        // 角色欄位
        if ($databaseData['character_field'] != ''){
            $insertData[$databaseData['character_field']] = $playerData['character_name'];
        }

        // 寫入資料
        foreach ($M_CustomizedDb->getDbField() as $_val){
            $insertData[$_val['field']] = $_val['value'];
        }
        $M_CustomizedDb->insertData($insertData); 

        // 寫入獎勵紀錄
        $this->rewardLog($promotionId, $serverCode, $playerData, $insertData);

        return true;
    }

    /**
     * 發送通知
     * @param int $promotionId 推廣資料Id
     * @param bool $auditResult 審核結果
     * @param int $playerId 玩家Id
     */
    public function sendNotification($promotionId, $auditResult, $playerId)
    {
        // 玩家資訊
        $playerData = $this->M_Model_Common->getData('player', ['id' => $playerId]);
        $account = $playerData['username'];
        $character = $playerData['character_name'];

        // 伺服器資訊
        $serverData = $this->M_Model_Common->getData('server', ['code' => $playerData['server']]);
        $server = $serverData['name'];

        // 預設通知結果
        $notifyResult = array(
            'email' => array(
                'status' => False,
                'msg' => '',
                'isFinished' => False,
            ),
            'line' => array(
                'status' => False,
                'msg' => '',
                'isFinished' => True,
            )
        );

        // 取得通知資訊
        $notificationData = $this->getNotification($promotionId);

        // 通知內容
        $mailText = ($auditResult === true) ? '已通過' : '未通過';

        // 通知
        $content = array();
        $content['line'] = "伺服器: {$server}\n";
        $content['line'] .= "帳號: {$account}\n";
        $content['line'] .= ($character != '') ? "角色: {$character}\n" : '';
        $content['line'] .= "您的推廣審核{$mailText}，請至PCGame 推廣審核系統查看審核結果";

        $content['mail'] = "<h3>伺服器: {$server}</h3>";
        $content['mail'] .= "<h3>帳號: {$account}</h3>";
        $content['mail'] .= ($character != '') ? "<h3>角色: {$character}</h3>" : '';
        $content['mail'] .= "<h3>您的推廣審核{$mailText}，請至PCGame 推廣審核系統查看審核結果</h3>";

        // Email 通知
        if ($notificationData['email']['status'] === true){
            if (!($notificationData['email']['data'] === null)){
                // 發送Email                
                $M_Mail = new M_Mail();
                $subject = '推廣審核完畢';
                $sendResult = $M_Mail->mailJet($notificationData['email']['data'], $subject, $content['mail']);

                // 更新通知結果
                $notifyResult['email']['status'] = true;
                $notifyResult['email']['msg'] = 'Email 發送成功';
                $notifyResult['email']['isFinished'] = ($sendResult === true) ? true : false;
            }
        }

        // Line 通知
        if ($notificationData['line']['status'] === true){
            if (!($notificationData['line']['data'] === null)){
                // 發送Line
                $M_Line = new M_Line();
                $M_Line->pushMessage($notificationData['line']['data'], $content['line']);

                // 更新通知結果
                $notifyResult['line']['status'] = true;
                $notifyResult['line']['msg'] = 'Line 發送成功';
                $notifyResult['line']['isFinished'] = true;
            }
        }
    }

    private function rewardLog($promotionId, $serverCode, $playerData, $insertData)
    {
        $rewardData = array(
            'player_id' => $playerData['id'],
            'server_code' => $serverCode,
            'reward' => json_encode($insertData),
        );

        $this->db->table('reward')
            ->insert($rewardData);

        $logData = array(
            'promotion_id' => $promotionId,
            'server_code' => $serverCode,
            'player_data' => json_encode($playerData),
            'insert_data' => json_encode($insertData),
        );

        $this->db->table('reward_log')
            ->insert($logData);
    }

    /**
     * 檢查玩家是否已領取獎勵
     * @param int $playerId 玩家Id
     * @param string $serverCode 伺服器代碼
     * @return bool
     */
    public function checkReward($playerId, $serverCode, $time=null)
    {
        $serverData = $this->M_Model_Common->getData('server', ['code' => $serverCode]);
        $frequency = $serverData['cycle'];

        $builder = $this->db->table('reward');

        // 預設為現在時間
        if (empty($time)) {
            $time = date('Y-m-d H:i:s');
        }

        // 解析傳入時間
        $timestamp = strtotime($time);
        if ($timestamp === false) {
            return []; // 無效時間格式
        }

        $date = date('Y-m-d', $timestamp);
        $year = date('Y', $timestamp);
        $month = date('m', $timestamp);
        $week = date('oW', $timestamp); // o = ISO year, W = ISO week

        switch ($frequency) {
            case 'daily':
                $builder->where('DATE(created_at)', $date);
                break;

            case 'weekly':
                $builder->where("YEARWEEK(created_at, 1) = ", $week);  // 例如 202420
                break;

            case 'monthly':
                $builder->where('YEAR(created_at)', $year);
                $builder->where('MONTH(created_at)', $month);
                break;

            default:
                return []; // 頻率無效
        }

        $builder->where('player_id', $playerId);
        $builder->where('server_code', $serverCode);

        $reward = $builder->get()->getRowArray();

        return (empty($reward)) ? false : true;     // true為已領取，false為未領取
    }
}