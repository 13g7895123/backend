<?
namespace App\Controllers\Casino;

use App\Controllers\BaseController;
use App\Models\Casino\FileModel;
use App\Models\Casino\ChessAndCardsModel;
use App\Models\M_Common as M_Model_Common;

/**
 * 首頁棋牌遊戲
 * 說明: 實際引用的是chess-and-cards-play-detail
 */
class ChessAndCards extends BaseController
{
    protected $db;
    protected $FileModel;
    protected $ChessAndCardsModel;
    protected $M_Model_Common;

    public function __construct()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->ChessAndCardsModel = new ChessAndCardsModel();
        $this->FileModel = new FileModel();
        $this->M_Model_Common = new M_Model_Common();
        $this->M_Model_Common->setDatabase('casino');
    }

    public function index($id=null)
    {
        $result = array('success' => false);
        $where = [];
        $multiple = true;
        $sort = array('show-home' => 'DESC', 'home-sort' => 'ASC');

        // 編輯
        if ($id != null) {
            $where['id'] = $id;
            $multiple = false;
        }

        $data = $this->M_Model_Common->getData('chess-and-cards-play-detail', $where, [], $multiple, [], [], $sort);

        if (!empty($data) && $multiple === true) {
            foreach ($data as $_key => $_val) {
                $data[$_key]['image'] = base_url() . 'api/casino/image/show/' . $_val['image-id'];
                $data[$_key]['view-image'] = base_url() . 'api/casino/image/show/' . $_val['view-image-id'];
            }
        }

        // 單筆資料不會有圖片
        $result['success'] = true;
        $result['data'] = $data;

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    public function create()
    {
        $result = array('success' => false);
        $postData = $this->request->getJSON(true);

        $insertId = $this->ChessAndCardsModel->createData($postData);

        if ($insertId > 0) {
            $result['success'] = true;
            $result['msg'] = '新增成功';
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    public function update()
    {
        $result = array('success' => false);
        $postData = $this->request->getJSON(true);

        $updateResult = $this->ChessAndCardsModel->updateData($postData);
        // $updateResult = $this->ChessAndCardsModel->updateData($postData, true);

        if ($updateResult) {
            $result['success'] = true;
            $result['msg'] = '更新成功';
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    public function delete()
    {
        $result = array('success' => false);
        $postData = $this->request->getJSON(true);
        
        $deleteResult = $this->ChessAndCardsModel->deleteData($postData['id']);

        if ($deleteResult) {
            // 更新排序
            $this->ChessAndCardsModel->resetSort();

            $result['success'] = true;
            $result['msg'] = '刪除成功';
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    public function upload()
    {
        $result = array('success' => false);
        $file = $this->request->getFile('file');
        $fileId = $this->FileModel->saveFile($file, 'images/casino');

        if ($fileId === false) {
            $result['msg'] = '上傳失敗';

            $this->response->noCache();
            $this->response->setContentType('application/json');
            return $this->response->setJSON($result);
        }

        $postData = $this->request->getPost();
        $imageField = 'image-id';
        $imageField = isset($postData['image-type']) && $postData['image-type'] == 'view' ? 'view-image-id' : 'image-id';

        $updateData = array(
            'id' => $postData['id'],
            $imageField => $fileId,
        );
        $updateResult = $this->ChessAndCardsModel->updateData($updateData);

        if ($updateResult) {
            $result['success'] = true;
            $result['msg'] = '上傳成功';
        }
        
        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    public function sort()
    {
        $result = array('success' => false);
        $postData = $this->request->getJSON(true);
        
        $sortResult = $this->ChessAndCardsModel->updateSort($postData['id'], $postData['type'], 'home-sort');

        if ($sortResult) {
            $result['success'] = true;
            $result['msg'] = '排序成功';
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
}