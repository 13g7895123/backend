<?php

namespace App\Models\Casino;
use CodeIgniter\Model;
use App\Models\M_Common as M_Model_Common;

// 文章
class ArticleModel extends Model
{
    protected $db;
    protected $M_Model_Common;

    public function __construct()
    {
        $this->db = \Config\Database::connect('casino');  // 預設資料庫
        $this->M_Model_Common = new M_Model_Common();
    }

    /**
     * 新增文章
     * @param array $data 文章資料
     * @return int|false 新增的圖片 ID 或 false
     */
    public function create(array $data)
    {
        $this->db->table('article_new')
            ->insert($data);

        return $this->db->insertID();
    }

    /**
     * 顯示文章列表
     * @return array 文章資料
     */
    public function fetchArticleList()
    {
        $query = $this->db->table('article_new')
            ->get();
        return $query->getResultArray();
    }

    /**
     * 顯示文章
     * @param int $articleId 文章 ID
     * @return array 文章資料
     */
    public function fetchArticle($articleId)
    {
        $query = $this->db->table('article_new')
            ->where('id', $articleId)
            ->get();
        return $query->getRowArray();
    }
    

    /**
     * 上傳圖片
     * @param object $file 圖片檔案
     * @return int|false 新增的圖片 ID 或 false
     */
    public function uploadImage(object $file)
    {
        $path = 'images/casino/post';
        $fileModel = new FileModel();
        $fileId = $fileModel->saveFile($file, $path);
        
        if ($fileId) {
            return $fileId;
        }

        return false;
    }
}