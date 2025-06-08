<?php

namespace App\Models\Casino;
use App\Models\Casino\Common\BaseModel;

class QuestionsModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->setTable('questions');
    }
}