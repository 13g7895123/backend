<?php

namespace App\Models\Casino;
use CodeIgniter\Model;
use App\Models\M_Common as M_Model_Common;

class SportsScoresModel extends Model
{
    protected $db;
    protected $M_Model_Common;

    public function __construct()
    {
        $this->db = \Config\Database::connect('casino');  // 預設資料庫
        $this->M_Model_Common = new M_Model_Common();
        $this->M_Model_Common->setDatabase('casino');
    }

    public function createData(array $data)
    {
        // 最新排序
        $scoreData = $this->M_Model_Common->getData('sports-scores', [], [], true);
        $maxSort = (empty($scoreData)) ? 0 : max(array_column($scoreData, 'sort'));
        $newSort = $maxSort + 1;

        $data['sort'] = $newSort;
        $data['image-id'] = 0;          // 沒有圖片預設ID給零
        $this->db->table('sports-scores')->insert($data);

        return $this->db->insertID();
    }

    public function updateData(array $data)
    {
        // 確認資料存在
        $scoreData = $this->M_Model_Common->getData('sports-scores', ['id' => $data['id']], [], false);

        if (empty($scoreData)) {
            return false;
        }

        // 更新資料
        $updateData = $data;
        unset($updateData['id']);
        $this->db->table('sports-scores')->where('id', $data['id'])->update($updateData);

        return true;
    }

    public function deleteData($id)
    {
        // 確認資料存在
        $multiple = (is_array($id)) ? true : false;
        $scoreData = $this->M_Model_Common->getData('sports-scores', ['id' => $id], [], $multiple);

        if (empty($scoreData)) {
            return false;
        }

        // 刪除資料
        $builder = $this->db->table('sports-scores');

        if (is_array($id)) {
            $builder->whereIn('id', $id);
        } else {
            $builder->where('id', $id);
        }

        $builder->delete();

        return true;
    }
    
    public function updateSort($id, $operation)
    {
        $sort = array(
            'field' => 'sort',
            'direction' => 'ASC',
        );
        $scoreData = $this->M_Model_Common->getData('sports-scores', [], [], true, [], $sort);

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
        foreach ($scoreData as $_val) {
            if ($_val['id'] == $id) {
                $nowSort = $_val['sort'];
                $newSort = ($operation == 'plus') ? $nowSort + 1 : $nowSort - 1;
                $target['target']['sort'] = $newSort;
            }
        }

        // 計算另一筆資料的排序
        $target['another']['sort'] = ($operation == 'minus') ? $target['target']['sort'] + 1 : $target['target']['sort'] - 1;

        // 找出另一筆資料的ID
        foreach ($scoreData as $_val) {
            if ($_val['sort'] == $target['target']['sort']) {
                $target['another']['id'] = $_val['id'];
            }
        }

        // 更新排序
        $this->db->table('sports-scores')
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

    /**
     * 重置排序
     */
    public function resetSort()
    {
        $sort = array(
            'field' => 'sort',
            'direction' => 'ASC',
        );
        $scoreData = $this->M_Model_Common->getData('sports-scores', [], [], true, [], $sort);

        $sort = 1;
        $updateData = array();
        foreach ($scoreData as $_val) {
            $updateData[] = [
                'id' => $_val['id'],
                'sort' => $sort
            ];
            $sort++;

            // 每30筆更新一次
            if (count($updateData) >= 30) {
                $this->db->table('sports-scores')->updateBatch($updateData, 'id');
                $updateData = array();
            }
        }

        // 更新剩餘的資料
        if (!empty($updateData)) {
            $this->db->table('sports-scores')->updateBatch($updateData, 'id');
        }

        return true;
    }
}