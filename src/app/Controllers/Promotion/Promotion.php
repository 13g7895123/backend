<?
namespace App\Controllers\Promotion;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\Response;
use CodeIgniter\API\ResponseTrait;
use App\Models\Promotion\M_Promotion;
use App\Models\Promotion\M_User;
use App\Models\M_Common;

class Promotion extends BaseController
{
    use ResponseTrait;

    public $M_Promotion;
    public $M_Common;
    public $M_User;

    public function __construct()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->M_Common = new M_Common();
        $this->M_User = new M_User();
        $this->M_Promotion = new M_Promotion();        
    }

    public function index()
    {
        $postData = $this->request->getJson(True);
        $type = (isset($postData['type']) && $postData['type'] == 'all') ? 'all' : 'finished'; 
        $where = ($type == 'all') ? ['promotions.status' => 'standby'] : ['promotions.status !=' => 'standby'];

        // 權限查詢
        if (isset($postData['user_id'])){
            $userServerPermission = $this->M_User->getServerPermission($postData['user_id']);
            if (!empty($userServerPermission)){
                $where['promotions.server'] = array_column($userServerPermission, 'code');
            }

            // 管理者不適用
            $userPermission = $this->M_User->getUserPermission($postData['user_id']);
            if ($userPermission['type'] === 'admin'){
                unset($where['promotions.server']);
            }
        }

        $join = array(
            array(
                'table' => 'player',
                'field' => 'id',
                'source_field' => 'user_id',
            ),
        );
        $data = $this->M_Common->getData('promotions', $where, ['*, promotions.id, promotions.created_at'], True, $join);

        if (empty($data)){
            return $this->response->setJSON([]);
        }

        foreach ($data as $_key => $_val){
            unset($data[$_key]['password']);

            $promotionDetail = $this->M_Common->getData('promotion_items', ['promotion_id' => $_val['id']], [], True);

            $links = [];
            $firstLink = $firstImage = '';
            foreach ($promotionDetail as $d_key => $d_val){
                // 取得第一個連結
                if ($d_val['type'] == 'text'){
                    $links[] = array(
                        'link' => $d_val['content'],
                        'status' => $d_val['status'],
                    );
                    $firstLink = $d_val['content'];
                }
                // 取得第一個圖片
                if ($d_val['type'] == 'image' && $firstImage == ''){
                    $firstImage = base_url() . 'api/promotion/file/show/' . $d_val['content'];
                }
            }

            $data[$_key]['promotion_detail']['link'] = $links;
            $data[$_key]['promotion_detail']['image'] = (isset($firstImage) && $firstImage != '') ? $firstImage : '';

            $server = $this->M_Common->getData('server', ['code' => $data[$_key]['server']]);
            if (!empty($server)){
                $data[$_key]['require_character'] = $server['require_character'];
            }
        }

        return $this->response->setJSON(array_reverse($data));
    }

    /**
     * 建立推廣資料
     * @return void
     */
    public function create()
    {
        $postData = $this->request->getJson(True);
        $promotion = array(
            'user_id' => $postData['user'],
            'server' => $postData['server'],
        );        

        $M_Promotion = new M_Promotion();
        $promotionId = $M_Promotion->create($promotion);

        $result = array(
            'success' => True,
            'msg' => '上傳成功',
            'promotionId' => $promotionId,
        );

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    /**
     * 刪除推廣資料
     * @return void
     */
    public function delete()
    {
        $postData = $this->request->getJson(True);
        $promotionId = $postData['id'];

        $M_Promotion = new M_Promotion();
        $M_Promotion->deleteData($promotionId);

        $result = array(
            'success' => True,
            'msg' => '刪除成功',
        );

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    /**
     * 批次審核
     * @return void
     */
    public function batchAudit()
    {
        $postData = $this->request->getJson(True);
        $promotionId = $postData['id'];
        $status = $postData['status'];

        $M_Promotion = new M_Promotion();
        $temp = $M_Promotion->batchAudit($promotionId, $status);

        $result = array(
            'success' => True,
            'msg' => '批次審核成功',
            'temp' => $temp,
        );

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
}