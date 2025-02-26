<?
namespace App\Controllers\Promotion;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\Response;
use CodeIgniter\API\ResponseTrait;
use App\Models\Promotion\M_Player;
use App\Models\Promotion\M_Promotion;
use App\Models\Promotion\M_PromotionItem;

class PromotionItem extends BaseController
{
    use ResponseTrait;

    private $M_PromotionItem;

    public function __construct()
    {
        $this->M_PromotionItem = new M_PromotionItem();
    }

    public function index($promotionId)
    {
        $data = $this->M_PromotionItem->getData(['promotion_id' => $promotionId], [], True);

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

        // 取得該推廣項目細項
        $checkData = $this->M_PromotionItem->getData(['promotion_id' => $promotionId], [], True);

        // 推廣審核狀況
        $M_Promotion = new M_Promotion();
        $auditData = $M_Promotion->getPromotionAudit($promotionId);
        [$isFinished, $auditResult] = $auditData;

        // 發送通知
        if ($isFinished === True){
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

            // Email 通知
            if ($notificationData['email']['status'] === True){
                if (!($notificationData['email']['data'] === null)){
                    // 發送Email
                    $mailText = ($auditResult === True) ? '已通過' : '未通過';
                    $M_Player = new M_Player();
                    $subject = 'Promotion Test';
                    $content = "您的推廣審核${mailText}，請至PCGame 推廣審核系統查看審核結果";
                    $sendResult = $M_Player->sendEmail($notificationData['email']['data'], $subject, $content);

                    // 更新通知結果
                    $notifyResult['email']['status'] = True;
                    $notifyResult['email']['msg'] = 'Email 發送成功';
                    $notifyResult['email']['isFinished'] = ($sendResult === True) ? True : False;
                }
            }

            // Line 通知
            if ($notificationData['email']['status'] === True){
                if (!($notificationData['email']['data'] === null)){
                    // 發送Line
                    $M_Player = new M_Player();
                    // $M_Player->sendLine($notificationData['line']['data'], $auditResult);
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
}