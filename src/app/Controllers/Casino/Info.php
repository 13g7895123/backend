<?php
namespace App\Controllers\Casino;

use App\Controllers\Casino\Common\CommonBaseController;

class Info extends CommonBaseController
{
    public function __construct()
    {
        $this->setTable('info');   
    }
}