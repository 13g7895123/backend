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

        $articleModel = new ArticleModel();
        $articleData = $articleModel->fetchArticleList();

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
            $result['articleId'] = $articleId;
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

        $result['success'] = true;
        $result['html'] = $articleModel->formatArticle($articleData);

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
            $result['success'] = true;
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

        $articleModel = new ArticleModel();
        $articleData = $articleModel->search($keyword);

        foreach ($articleData as $key => $value) {
            $articleData[$key]['image'] = base_url('api/casino/admin/article/image/show/' . $value['image-id']);
        }

        $result['success'] = true;
        $result['data'] = $articleData;

        $this->response->noCache();
        $this->response->setContentType('application/json');
        return $this->response->setJSON($result);
    }
    
}