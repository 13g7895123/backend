<?php

namespace App\Controllers\Promotion;

use App\Controllers\BaseController;
use App\Models\Promotion\M_CustomizedDb;
use App\Models\Promotion\M_User;

class Test extends BaseController
{
    public function __construct()
    {

    }

    public function index()
    {
        $dbInfo = array(
            'id' => 2,
            'server_code' => 'tdb',
            'name' => 'maple-db',
            'host' => '139.162.15.125',
            'port' => 9903,
            'account' => 'maple_user',
            'password' => 'v94176w6',
            'table_name' => 'promotion'
        );

        $db = \Config\Database::connect([
            'DSN'      => '',
            'hostname' => $dbInfo['host'],
            'username' => $dbInfo['account'],
            'password' => $dbInfo['password'],
            'database' => $dbInfo['name'],
            'port'     => $dbInfo['port'],
            'DBDriver' => 'MySQLi',
            'charset'  => 'utf8mb4',  
        ]);

        // 檢查連線是否成功
        try {
            $db->connect();
            
            $db->table('promotion')->insert([
                'user_id' => 1,
                'product_id' => 2,
                'number' => '3',
            ]);
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    public function test()
    {
        // $M_User = new M_User();
        // $M_User->checkAccessToken('5l6pbvbm4adgtz9rrzug');

        $code = 'tdb';
        $M_CustomizedDb = new M_CustomizedDb($code);
        print_r($M_CustomizedDb); die();
        // // $insertData = array(
        // //     'server_code' => $code,
        // //     'table_name' => $M_CustomizedDb->getTable(),
        // // );
        


        // foreach ($M_CustomizedDb->getDbField() as $_val){
        //     $insertData[$_val['field']] = $_val['value'];
        // }
        // $M_CustomizedDb->insertData($insertData);  
    }
}