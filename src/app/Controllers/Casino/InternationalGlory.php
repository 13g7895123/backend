<?
namespace App\Controllers\Casino;

use App\Controllers\BaseController;
use App\Models\M_Common as M_Model_Common;

class InternationalGlory extends BaseController
{
    protected $db;
    protected $M_Model_Common;

    public function __construct()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->M_Model_Common = new M_Model_Common();
        $this->db = \Config\Database::connect('casino');
    }
}