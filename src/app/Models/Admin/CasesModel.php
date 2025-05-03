<?php

namespace App\Models\Admin;
use CodeIgniter\Model;
use App\Models\M_Common as M_Model_Common;

class CasesModel extends Model
{
    protected $db;
    protected $M_Model_Common;

    public function __construct()
    {
        $this->db = \Config\Database::connect('admin');  // 預設資料庫
        $this->M_Model_Common = new M_Model_Common();
    }

    /**
     * 取得電子遊戲詳細資料
     * @param int|null $playId 遊戲ID
     * @param int|null $id 詳細資料ID
     * @return array|object 查詢結果
     */
    public function fetchData($playId=null, $id=null)
    {
        $builder = $this->db->table('electronic-game-play-detail');

        if ($playId !== null) {
            $builder->where('play_id', $playId);
        }

        if ($id !== null) {
            $builder->where('id', $id);
        }

        $builder->orderBy('sort', 'ASC');
        $query = $builder->get();

        return ($playId !== null && $id !== null) ? $query->getRowArray() : $query->getResultArray();
    }
        
    public function createData(array $data)
    {
        // 新增資料
        // print_r($data); die();
        $this->db->table('cases')
            ->insert($data);

        return $this->db->insertID();
    }

    public function updateData(array $data)
    {
        $updateData = $data;
        unset($updateData['id']);

        return $this->db->table('electronic-game-play-detail')
            ->where('id', $data['id'])
            ->update($updateData);
    }

    public function deleteData(array $data)
    {
        return $this->db->table('electronic-game-play-detail')
            ->where('id', $data['id'])
            ->delete();
    }

    private function fetchLastSort($playId)
    {
        $data = $this->db->table('electronic-game-play-detail')
            ->where('play_id', $playId)
            ->select('MAX(sort) as last_sort')
            ->get()
            ->getRowArray();

        return $data['last_sort'] ?? 0;
    }
}