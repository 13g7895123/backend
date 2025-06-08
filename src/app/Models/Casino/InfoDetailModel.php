<?php

namespace App\Models\Casino;
use App\Models\Casino\Common\BaseDetailModel;

class InfoDetailModel extends BaseDetailModel
{
    public function __construct()
    {
        parent::__construct();
        $this->setTable('info_detail');
    }
}