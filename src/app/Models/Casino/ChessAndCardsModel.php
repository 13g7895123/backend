<?php

namespace App\Models\Casino;
use App\Models\Casino\Common\BaseModel;

class ChessAndCardsModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->setTable('chess-and-cards-play-detail');
    }

    // 更新資料
    public function updateData(array $data, bool $isTest = false)
    {
        // 有show-home欄位，需要處理首頁排序
        if (isset($data['show-home'])){
            $beforeData = $this->M_Model_Common->getData($this->table, ['id' => $data['id']]);

            // 新建立項目需要加入首頁排序
            if ($beforeData['show-home'] == 0 && $data['show-home'] == 1){
                $maxHomeSort = $this->fetchMaxHomeSort();
                $updateData = $data;
                $updateData['home-sort'] = $maxHomeSort + 1;

                return parent::updateData($updateData, $isTest);
            }

            // 移除首頁排序，其他項目需要重新排序
            if ($beforeData['show-home'] == 1 && $data['show-home'] == 0){
                $updateData = $data;
                $updateData['home-sort'] = 0;
                parent::updateData($updateData, $isTest);
                $this->resetSort();
            }
        }

        return parent::updateData($data, $isTest);
    }

    // 更新排序
    public function updateSort($id, $operation, $field = 'sort')
    {
        return parent::updateSort($id, $operation, $field);
    }

    // 當前最大首頁排序
    public function fetchMaxHomeSort()
    {
        $sort = array(
            'field' => 'home-sort',
            'direction' => 'ASC'
        );
        $sortData = $this->M_Model_Common->getData($this->table, [], [], true, [], $sort);

        $maxSort = array_column($sortData, 'home-sort');
        $maxSort = max($maxSort);

        return $maxSort;
    }

    // 重製排序
    public function resetSort()
    {
        $data = $this->M_Model_Common->getData($this->table, ['show-home' => 1], [], true);

        if (!empty($data)){
            $sort = 1;
            foreach ($data as $_val){
                $updateData = [
                    'id' => $_val['id'],
                    'home-sort' => $sort
                ];
                parent::updateData($updateData);
                $sort++;
            }

            return true;
        }

        return false;
    }
}