<?php
namespace App\Controllers\Casino;

use App\Controllers\Casino\Common\CommonBaseController;

class Marquee extends CommonBaseController
{
    public function __construct()
    {
        $this->setTable('marquee');
    }
}