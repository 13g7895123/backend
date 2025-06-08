<?php
namespace App\Controllers\Casino;

use App\Controllers\Casino\Common\CommonBaseController;

class GameGuideTag extends CommonBaseController
{
    public function __construct()
    {
        $this->setTable('game-guide-tag');   
    }
}