<?
namespace App\Controllers\Promotion;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\Response;
use CodeIgniter\API\ResponseTrait;
use App\Models\Promotion\M_Promotion;
use App\Models\M_Common;
use App\Models\M_Common;

class Promotion extends BaseController
{
    use ResponseTrait;

    public $M_Promotion;
    public $M_Common;

    public function __construct()
    {
        // error_reporting(E_ALL);
        // ini_set('display_errors', 1);

        $this->M_Common = new M_Common();
        $this->M_Promotion = new M_Promotion();        
    }

    public function index()
    {
        $join = array(
            array(
                'table' => 'player',
                'field' => 'id',
                'source_field' => 'user_id',
            ),
        );
        $data = $this->M_Promotion->getData([], ['*', 'promotions.id'], True, $join);

        foreach ($data as $_key => $_val){
            unset($data[$_key]['password']);

            $promotionDetail = $this->M_Common->getData('promotion_items', ['promotion_id' => $_val['id']], [], True);

            $firstLink = $firstImage = '';
            foreach ($promotionDetail as $d_key => $d_val){
                // 取得第一個連結
                if ($d_val['type'] == 'text' && $firstLink == ''){
                    $firstLink = $d_val['content'];
                }
                // 取得第一個圖片
                if ($d_val['type'] == 'image' && $firstImage == ''){
                    $firstImage = $d_val['content'];
                }
            }

            $data[$_key]['promotion_detail']['link'] = (isset($firstLink) && $firstLink != '') ? $firstLink : '';
            $data[$_key]['promotion_detail']['image'] = (isset($firstImage) && $firstImage != '') ? $firstImage : '';
        }

        return $this->response->setJSON($data);
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
    public function delete($promotionId)
    {
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
}