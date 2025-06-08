<?php

namespace App\Models\Casino;
use App\Models\Casino\Common\BaseModel;

class ChessAndCardsPlayModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->setTable('chess-and-cards-play');
    }
}