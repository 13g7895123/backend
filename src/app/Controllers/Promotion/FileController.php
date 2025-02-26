<?
namespace App\Controllers\Promotion;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\Response;
use CodeIgniter\API\ResponseTrait;
use App\Models\Promotion\M_File;
use App\Models\Promotion\M_Promotion;
use App\Models\Promotion\M_PromotionItem;

class FileController extends BaseController
{
    use ResponseTrait;

    private $M_File;

    public function __construct()
    {
        $this->M_File = new M_File();
    }

    // 上傳檔案
    public function upload()
    {
        $file = $this->request->getFile('file');
        $fileId = $this->M_File->saveFile($file, 'images');
        $result = array('success' => False);

        if ($fileId === false) {
            $result['msg'] = '上傳失敗';

            $this->response->noCache();
            $this->response->setContentType('application/json');
            return $this->response->setJSON($result);
        }

        // $postData = $this->request->getJson(True);
        $postData = $this->request->getPost();
        $promotionId = $postData['promotionId'];

        $insertData = array(
            'promotion_id' => $promotionId,
            'type' => 'image',
            'content' => $fileId,
        );        

        $M_PromotionItem = new M_PromotionItem();
        $M_PromotionItem->create($insertData);

        $result['success'] = True;
        $result['msg'] = '上傳成功';

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
}
