<?
namespace App\Controllers\Casino;

use App\Controllers\BaseController;
use App\Models\Casino\ArticleModel;
use App\Models\Casino\FileModel;
use App\Models\M_Common as M_Model_Common;

class Article extends BaseController
{
    protected $db;
    protected $M_Model_Common;

    public function __construct()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->M_Model_Common = new M_Model_Common();
        $this->db = \Config\Database::connect('casino');
    }

    /**
     * 顯示文章列表
     * @return json
     */
    public function index()
    {
        $result = array('success' => false);
        $postData = $this->request->getJSON(true);

        $articleModel = new ArticleModel();
        $articleData = $articleModel->fetchArticleList($postData['type']);

        if (!empty($articleData)) {
            foreach ($articleData as $key => $value) {
                $articleData[$key]['image'] = base_url() . 'api/casino/image/show/' . $value['image-id'];
            }
        }

        $result['success'] = true;
        $result['data'] = $articleData;

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    /**
     * 新增文章
     * @return json
     */
    public function create()
    {
        $result = array('success' => false);

        $data = $this->request->getJSON(true);

        $articleModel = new ArticleModel();
        $insertData = $data;
        $insertData['sort'] = 0;
        $insertData['html'] = $data['content'];
        unset($insertData['content']);
        $articleId = $articleModel->create($insertData);
        
        if ($articleId) {
            $result['success'] = true;
            $result['message'] = '新增成功';
            $result['articleId'] = $articleId;
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    /** 
     * 更新文章
     * @return json
     */
    public function update()
    {
        $result = array('success' => false);

        $data = $this->request->getJSON(true);

        $articleModel = new ArticleModel();
        $updateResult = $articleModel->updateData($data['id'], $data);
        
        if ($updateResult) {
            $result['success'] = true;
            $result['message'] = '更新成功';
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    /**
     * 刪除文章
     * @return json
     */
    public function delete()
    {
        $result = array('success' => false);
        $postData = $this->request->getJSON(true);

        $articleModel = new ArticleModel();
        $articleData = $articleModel->fetchArticle($postData['id']);
        $deleteResult = $articleModel->deleteData($postData['id']); 
        
        if ($deleteResult) {
            // 更新排序
            $articleModel->resetSort($articleData['type']);

            $result['success'] = true;
            $result['msg'] = '刪除成功';
        }
        
        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    /**
     * 顯示圖片
     * @param int $fileId 圖片 ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function showFile($fileId)
    {
        $fileModel = new FileModel();
        $fileData = $fileModel->fetchFile($fileId);

        $path = WRITEPATH . $fileData['path'];

        if (!is_file($path)) {
            return $this->response->setStatusCode(404)->setBody('File not found.');
        }

        $mimeType = mime_content_type($path);

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setBody(file_get_contents($path));
    }

    /**
     * 顯示文章
     * @param int $articleId 文章 ID
     * @return json
     */
    public function show($articleId)
    {
        $result = array('success' => false);

        $articleModel = new ArticleModel();
        $articleData = $articleModel->fetchArticle($articleId);
        
        $articleType = (!empty($articleData)) ? $articleData['type'] : '';

        $result['success'] = true;
        $result['data'] = $articleData;
        $result['html'] = $articleModel->formatArticle($articleData);
        $result['latest'] = $articleModel->fetchLatest($articleType);
        $result['category'] = $articleModel->fetchCategory($articleType);

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    /**
     * 上傳圖片
     * @return json
     */
    public function uploadImage()
    {
        $result = array('success' => false);

        $file = $this->request->getFile('file');
        $articleModel = new ArticleModel();
        $fileId = $articleModel->uploadImage($file);
        
        if ($fileId) {
            $postData = $this->request->getPost();
            $id = $postData['id'];
            $articleModel->updateData($id, ['image-id' => $fileId]);

            $result['success'] = true;
            $result['message'] = '上傳成功';
            $result['fileId'] = $fileId;
            $result['url'] = base_url('api/casino/admin/article/image/show/' . $fileId);
        }

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }

    /**
     * 搜尋文章
     * @return json
     */
    public function search()
    {
        $result = array('success' => false);
        $data = $this->request->getJSON(true);
        $keyword = $data['keyword'];
        $type = $data['type'];

        $articleModel = new ArticleModel();
        $articleData = $articleModel->search($keyword, $type);

        foreach ($articleData as $key => $value) {
            $articleData[$key]['image'] = base_url('api/casino/admin/article/image/show/' . $value['image-id']);
        }

        $result['success'] = true;
        $result['data'] = $articleData;

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
    
    /**
     * 排序
     * @return json
     */
    public function sort()
    {
        $result = array('success' => false);
        $postData = $this->request->getJSON(true);

        $articleModel = new ArticleModel();
        $articleModel->updateSort($postData['id'], $postData['operation']);

        $result['success'] = true;
        $result['message'] = '排序成功';

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
}