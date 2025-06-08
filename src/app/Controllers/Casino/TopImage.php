<?php
namespace App\Controllers\Casino;

use App\Controllers\Casino\Common\CommonBaseController;

class TopImage extends CommonBaseController
{
    public function __construct()
    {
        $this->setTable('top-image');   
    }
}