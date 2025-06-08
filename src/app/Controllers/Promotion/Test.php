<?php

namespace App\Controllers\Promotion;

use App\Controllers\BaseController;
use App\Models\Promotion\MailModel;

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
        // print_r(123); die();
        // $M_User = new M_User();
        // $M_User->checkAccessToken('5l6pbvbm4adgtz9rrzug');

        // $code = 'ga';
        // $M_CustomizedDb = new M_CustomizedDb($code);
        // $data = $M_CustomizedDb->fetchData();

        // if (empty($data)){
        //     return $this->response->setJSON([
        //         'success' => false,
        //         'msg' => 'No data found'
        //     ]);
        // }

        $db = \Config\Database::connect('promotion');

        $promotionData = $db->table('promotions')
            ->where('status', 'success')
            ->where('created_at >=', '2025-06-06 00:00:00')
            ->get()
            ->getResultArray();

        $wrongCount = 0;
        $temp = array();
        foreach ($promotionData as $_key => $_val) {
            $rewardData = $db->table('reward')
                ->join('player', 'player.id = reward.player_id')
                ->where('reward.player_id', $_val['user_id'])
                ->where('reward.server_code', $_val['server'])
                ->where('reward.created_at >=', '2025-06-06 00:00:00')
                ->orderBy('reward.created_at', 'DESC')
                ->get()
                ->getRowArray();

            if (empty($rewardData)) {
                // $temp[] = $_val;
                // $wrongCount ++;

                continue;
            }

            $promotionData[$_key]['reward_time'] = $rewardData['created_at'];
            $promotionData[$_key]['user'] = $rewardData['username'];

            if ($promotionData[$_key]['reward_time'] < $promotionData[$_key]['created_at']) {
                $temp[] = $promotionData[$_key];
            }
        }

        print_r($temp); die();
    }
}