<?php

namespace App\Models\Casino;
use App\Models\Casino\Common\BaseDetailModel;

class ChessAndCardsPlayDetailModel extends BaseDetailModel
{
    public function __construct()
    {
        parent::__construct();
        $this->setTable('chess-and-cards-play-detail');
    }
}