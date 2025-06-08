<?
namespace App\Controllers\Promotion;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\Response;
use CodeIgniter\API\ResponseTrait;
use App\Models\Promotion\M_Player;
use App\Models\Promotion\M_Promotion;
use App\Models\Promotion\M_PromotionItem;
use App\Models\Promotion\M_Line;
use App\Models\M_Common as M_Common_Model;
use App\Models\Promotion\M_CustomizedDb;

class PromotionItem extends BaseController
{
    use ResponseTrait;

    private $M_PromotionItem;
    private $M_Common_Model;

    public function __construct()
    {
        $this->M_PromotionItem = new M_PromotionItem();
        $this->M_Common_Model = new M_Common_Model();
    }

    public function index($promotionId)
    {
        $data = $this->M_PromotionItem->getData(['promotion_id' => $promotionId], [], True);

        foreach ($data as $_key => $_val) {
            if ($_val['type'] === 'image') {
                $data[$_key]['content'] = base_url() . 'api/promotion/file/show/' . $_val['content'];
            }
        }

        return $this->response->setJSON($data);
    }

    public function create()
    {
        $postData = $this->request->getJson(True);

        $insertData = array(
            'promotion_id' => $postData['promotionId'],
            'type' => 'text',
            'content' => $postData['content'],
        );        

        $M_PromotionItem = new M_PromotionItem();
        $M_PromotionItem->create($insertData);

        $result = array(
            'success' => True,
            'msg' => '上傳成功',
        );

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    public function update($id)
    {
        $result = array('success' => False);
        $postData = $this->request->getJson(True);

        $updateData = array(
            'status' => $postData['status'],
        );

        $this->M_PromotionItem->updateData($updateData, ['id' => $id]);

        // 取得推廣ID
        $detailData = $this->M_PromotionItem->getData(['id' => $id], [], False);
        $promotionId = $detailData['promotion_id'];

        // 推廣審核狀況
        $M_Promotion = new M_Promotion();
        $auditData = $M_Promotion->getPromotionAudit($promotionId);
        [$isFinished, $auditResult] = $auditData;

        // 推廣資料
        $promotionData = $this->M_Common_Model->getData('promotions', ['id' => $promotionId]);

        // 伺服器資料
        $serverData = $this->M_Common_Model->getData('server', ['code' => $promotionData['server']]);

        // 玩家資料
        $playerData = $this->M_Common_Model->getData('player', ['id' => $promotionData['user_id']]);

        // 完成審核
        if ($isFinished === true){
            // ### 寄送獎勵 ###
            $M_CustomizedDb = new M_CustomizedDb($serverData['code']);
            $databaseData = $M_CustomizedDb->getDbInfo();
            
            // 寫入資料，預設一定有帳號
            $insertData = array($databaseData['account_field'] => $playerData['username']);

            // 角色欄位
            if ($databaseData['character_field'] != ''){
                $insertData[$databaseData['character_field']] = $playerData['character_name'];
            }

            foreach ($M_CustomizedDb->getDbField() as $_val){
                $insertData[$_val['field']] = $_val['value'];
            }
            $M_CustomizedDb->insertData($insertData);

            // ### 發送通知 ###
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
            $notificationData = $M_Promotion->getNotification($promotionId);

            // 通知內容
            $mailText = ($auditResult === True) ? '已通過' : '未通過';
            $content = "您的推廣審核{$mailText}，請至PCGame 推廣審核系統查看審核結果";

            // Email 通知
            if ($notificationData['email']['status'] === True){
                if (!($notificationData['email']['data'] === null)){
                    // 發送Email
                    
                    $M_Player = new M_Player();
                    $subject = 'Promotion Test';
                    $sendResult = $M_Player->sendEmail($notificationData['email']['data'], $subject, $content);

                    // 更新通知結果
                    $notifyResult['email']['status'] = True;
                    $notifyResult['email']['msg'] = 'Email 發送成功';
                    $notifyResult['email']['isFinished'] = ($sendResult === True) ? True : False;
                }
            }

            // Line 通知
            if ($notificationData['line']['status'] === True){
                if (!($notificationData['line']['data'] === null)){
                    // 發送Line
                    $M_Line = new M_Line();
                    $M_Line->pushMessage($notificationData['line']['data'], $content);

                    // 更新通知結果
                    $notifyResult['line']['status'] = True;
                    $notifyResult['line']['msg'] = 'Line 發送成功';
                    $notifyResult['line']['isFinished'] = True;
                }
            }
            
            // 更新推廣結果
            $promotionStatus = ($auditResult === True) ? 'success' : 'failed';
            $M_Promotion->updateData($promotionId, ['status' => $promotionStatus]);
        }

        $result = array(
            'success' => True,
            'msg' => '更新成功',
        );

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    public function checkUrl()
    {
        $postData = $this->request->getJson(True);
        $url = $postData['url'];

        $checkResult = $this->M_PromotionItem->checkUrl($url);

        $result = array(
            'success' => True,
            'msg' => '確認成功',
            'isExist' => $checkResult,
        );

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
}