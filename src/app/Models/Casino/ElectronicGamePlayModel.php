<?php

namespace App\Models\Casino;
use CodeIgniter\Model;
use App\Models\M_Common as M_Model_Common;

class ElectronicGamePlayModel extends Model
{
    protected $db;
    protected $M_Model_Common;

    public function __construct()
    {
        $this->db = \Config\Database::connect('casino');  // 預設資料庫
        $this->M_Model_Common = new M_Model_Common();
    }

    public function fetchData($id=null)
    {
        $builder = $this->db->table('electronic-game-play');

        if ($id !== null) {
            $builder->where('id', $id);
        }

        $builder->orderBy('sort', 'ASC');
        $query = $builder->get();

        return ($id !== null) ? $query->getRowArray() : $query->getResultArray();
    }
        
    public function createData(array $data)
    {
        // 取得最後一筆資料的sort
        $lastSort = $this->fetchLastSort();
        $data['sort'] = $lastSort + 1;

        // 新增資料
        $this->db->table('electronic-game-play')
            ->insert($data);

        return $this->db->insertID();
    }

    public function updateData(array $data)
    {
        $updateData = $data;
        unset($updateData['id']);

        return $this->db->table('electronic-game-play')
            ->where('id', $data['id'])
            ->update($updateData);
    }

    private function fetchLastSort()
    {
        $data = $this->db->table('electronic-game-play')
            ->select('MAX(sort) as last_sort')
            ->get()
            ->getRowArray();

        return $data['last_sort'] ?? 0;
    }
}