<?php

namespace App\Models\Casino;
use CodeIgniter\Model;
use App\Models\M_Common as M_Model_Common;
use PDO;

// 文章
class ArticleModel extends Model
{
    protected $db;
    protected $M_Model_Common;

    public function __construct()
    {
        $this->db = \Config\Database::connect('casino');  // 預設資料庫
        $this->M_Model_Common = new M_Model_Common();
        $this->M_Model_Common->setDatabase('casino');
    }

    /**
     * 新增文章
     * @param array $data 文章資料
     * @return int|false 新增的圖片 ID 或 false
     */
    public function create(array $data)
    {
        // 取得最新排序
        $latestSort = $this->fetachLatestSort($data['type']);
        $data['sort'] = $latestSort + 1;

        // 新增文章
        $this->db->table('article_new')
            ->insert($data);

        return $this->db->insertID();
    }

    /**
     * 更新文章
     * @param int $articleId 文章 ID
     * @param array $data 文章資料
     * @return bool 更新成功與否
     */
    public function updateData(int $articleId, array $data)
    {
        if (isset($data['id'])) {
            unset($data['id']);
        }

        // 欄位轉換
        if (isset($data['content'])) {
            $data['html'] = $data['content'];
            unset($data['content']);
        }

        return $this->db->table('article_new')
            ->where('id', $articleId)
            ->update($data);
    }

    /**
     * 刪除文章
     * @param int $id 文章 ID
     * @return bool 刪除成功與否
     */
    public function deleteData($id)
    {
        // 確認資料存在
        $multiple = (is_array($id)) ? true : false;
        $articleData = $this->M_Model_Common->getData('article_new', ['id' => $id], [], $multiple);

        if (empty($articleData)) {
            return false;
        }
        
        // 刪除資料
        $builder = $this->db->table('article_new');

        if (is_array($id)) {
            $builder->whereIn('id', $id);
        } else {
            $builder->where('id', $id);
        }

        $builder->delete();

        return true;
    }

    /**
     * 顯示文章列表
     * @return array 文章資料
     */
    public function fetchArticleList($type)
    {
        $query = $this->db->table('article_new')
            ->where('type', $type)
            ->orderBy('sort', 'ASC')
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

    /**
     * 搜尋文章
     * @param string $keyword 關鍵字
     * @param string $type 文章種類
     * @return array 文章資料   
     */
    public function search($keyword, $type)
    {
        return $this->db->table('article_new')
            ->where('type', $type)
            ->groupStart()
                ->like('title', $keyword)
                ->orLike('tag', $keyword) 
                ->orLike('html', $keyword)
            ->groupEnd()
            ->get()
            ->getResultArray();
    }

    /**
     * 格式化文章
     * @param array $article 文章資料
     * @return array 格式化後的文章資料
     */
    public function formatArticle($article)
    {
        $html = '<h1 style="text-align: left">';
        $html .= '<span style="font-size: 24px"><strong>'.$article['title'].'</strong></span>';
        $html .= '</h1>';
        $html .= '<img class="max-w-full h-auto" src="https://backend.mercylife.cc/api/casino/admin/article/image/show/'.$article['image-id'].'" alt="現金網體驗金.jpg">';
        $html .= $article['html'];
                
        return $html;
    }

    public function fetachLatestSort($type)
    {
        $article = $this->db->table('article_new')
            ->where('type', $type)
            ->orderBy('sort', 'DESC')
            ->get()
            ->getRowArray();

        return (!empty($article)) ? $article['sort'] : 0;
    }

    public function updateSort($id, $operation)
    {
        // 取得文章種類
        $where = array('id' => $id);
        $article = $this->M_Model_Common->getData('article_new', $where);
        $type = $article['type'];

        // 取得該種類文章
        $where = array('type' => $type);
        $fields = array('id', 'title', 'sort');
        $sort = array('field' => 'sort', 'direction' => 'ASC');
        $articleData = $this->M_Model_Common->getData('article_new', $where, $fields, true, [], $sort);

        // 要更新的項目
        $target = array(
            'target' => array(
                'id' => $id,
                'sort' => null
            ),
            'another' => array(
                'id' => null,
                'sort' => null
            )
        );

        // 找出目標排序
        foreach ($articleData as $_val) {
            if ($_val['id'] == $id) {
                $nowSort = $_val['sort'];
                $newSort = ($operation == 'plus') ? $nowSort + 1 : $nowSort - 1;
                $target['target']['sort'] = $newSort;
            }
        }

        // 計算另一筆資料的排序
        $target['another']['sort'] = ($operation == 'minus') ? $target['target']['sort'] + 1 : $target['target']['sort'] - 1;

        // 找出另一筆資料的ID
        foreach ($articleData as $_val) {
            if ($_val['sort'] == $target['target']['sort']) {
                $target['another']['id'] = $_val['id'];
            }
        }

        // 更新排序
        $this->db->table('article_new')
            ->updateBatch([
                [
                    'id' => $target['target']['id'],
                    'sort' => $target['target']['sort']
                ],
                [
                    'id' => $target['another']['id'], 
                    'sort' => $target['another']['sort']
                ]
            ], 'id');

        return true;
    }

    public function resetSort($type)
    {
        $where = array('type' => $type);
        $fields = array('id', 'title', 'sort');
        $sort = array('field' => 'sort', 'direction' => 'ASC');
        $articleData = $this->M_Model_Common->getData('article_new', $where, $fields, true, [], $sort);

        $sort = 1;
        $updateData = array();
        foreach ($articleData as $_val) {
            $updateData[] = [
                'id' => $_val['id'],
                'sort' => $sort
            ];
            $sort++;

            // 每30筆更新一次
            if (count($updateData) >= 30) { 
                $this->db->table('article_new')
                    ->updateBatch($updateData, 'id');
                $updateData = array();
            }
        }

        // 更新剩餘的資料
        if (!empty($updateData)) {
            $this->db->table('article_new')
                ->updateBatch($updateData, 'id');
        }

        return true;
    }

    public function fetchLatest($type)
    {
        $data = $this->db->table('article_new')
            ->where('type', $type)
            ->select('id, title, image-id, created_at')
            ->orderBy('created_at', 'DESC')
            ->limit(3)
            ->get()
            ->getResultArray();

        if (empty($data)) {
            return array();
        }

        foreach ($data as $key => $value) {
            $data[$key]['image'] = base_url() . 'api/casino/image/show/' . $value['image-id'];
        }

        return $data;
    }

    public function fetchCategory($type)
    {
        $categories = $this->db->table('article_new')
            ->select('category, COUNT(*) as count')
            ->where('type', $type)
            ->where('category !=', '')
            ->groupBy('category')
            ->get()
            ->getResultArray();

        if (empty($categories)) {
            return array();
        }

        $categoryData = array_column($categories, 'category');

        $articleData = $this->db->table('article_new')
            ->select('id, title, image-id, tag, star, html, category, created_at')
            ->where('type', $type)
            ->whereIn('category', $categoryData)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        if (!empty($articleData)) {
            foreach ($categories as $_key => $_val) {
                $categories[$_key]['articles'] = array_values(array_filter($articleData, function($item) use ($_val) {
                    return $item['category'] === $_val['category'];
                }));
                
                foreach ($categories[$_key]['articles'] as $a_key => $a_val) {
                    $categories[$_key]['articles'][$a_key]['image'] = base_url() . 'api/casino/image/show/' . $a_val['image-id'];
                }
            }
        }

        return $categories;
    }
}