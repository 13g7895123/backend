<?
namespace App\Controllers\Promotion;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\Response;
use CodeIgniter\API\ResponseTrait;
use App\Models\Promotion\M_Promotion;

class Promotion extends BaseController
{
    use ResponseTrait;

    public $M_Promotion;

    public function __construct()
    {
        // error_reporting(E_ALL);
        // ini_set('display_errors', 1);

        $this->M_Promotion = new M_Promotion();
    }

    public function index()
    {
        $join = array(
            array(
                'table' => 'users',
                'field' => 'id',
                'source_field' => 'user_id',
            ),
        );
        $data = $this->M_Promotion->getData([], ['*', 'promotions.id'], True, $join);

        foreach ($data as $_key => $_val){
            
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