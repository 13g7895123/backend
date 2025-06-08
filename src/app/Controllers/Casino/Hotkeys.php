<?php
namespace App\Controllers\Casino;

use App\Controllers\Casino\Common\CommonBaseController;

class HotKeys extends CommonBaseController
{
    public function __construct()
    {
        $this->setTable('hotkeys');   
    }
}