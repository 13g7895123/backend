<?php
namespace App\Controllers\Casino;

use App\Controllers\Casino\Common\CommonBaseController;

class Advertisement extends CommonBaseController
{
    public function __construct()
    {
        $this->setTable('advertisement');   
    }
}