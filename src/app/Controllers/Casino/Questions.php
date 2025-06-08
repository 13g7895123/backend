<?php
namespace App\Controllers\Casino;

use App\Controllers\Casino\Common\CommonBaseController;

class Questions extends CommonBaseController
{
    public function __construct()
    {
        $this->setTable('questions');   
    }
}