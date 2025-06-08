<?php

namespace App\Models\Casino;
use App\Models\Casino\Common\BaseModel;

class BaccaratModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->setTable('baccarat-play');
    }
}