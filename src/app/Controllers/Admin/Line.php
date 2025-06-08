<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Admin\LineModel;
use App\Models\M_Common as M_Model_Common;

class Line extends BaseController
{
    protected $db;
    protected $LineModel;
    protected $M_Model_Common;
    // protected $accessToken = 'i/5rFtsUJH3wHvNlZqjunlc9YpPiRHdHjCR3tKpant5SnLMOXpM+Z9EQ7ZjhfT0nIoVpvtOK8RKBriQMuy4R4EIwfIIKDv2yCPvU4Hncn2cst1mSAlMzi7hKmNn+3QtzIvE+DFsYUnAzOhM5HKRBfQdB04t89/1O/w1cDnyilFU=';
    protected $accessToken = 'xUkKfJzG8NNIePbz+Y8YGi9tkMUCoStUAgUv6HLX6FRQIzPM2MOcN5OJXRFAcajci9AoaoGFDafVjaF9Z6B+9xWmDsUQuySdoARFAu9k7UPAarbSHzgEmQeMhtyRkSHeqc0nHrXQy35UPsXZPiq1+wdB04t89/1O/w1cDnyilFU=';

    public function __construct()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        // $this->LineModel = new LineModel();
        $this->M_Model_Common = new M_Model_Common();
        $this->db = \Config\Database::connect('admin');
    }

    public function webhook()
    {
        try {
            // 讀取 LINE 傳來的 JSON
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            // 記錄接收到的資料
            $this->db->table('line')->insert([
                'received_data' => $json,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // 確保事件存在
            if (!isset($data['events'][0])) {
                return $this->response->setJSON(['status' => 'no events']);
            }

            $event = $data['events'][0];

            // 只處理文字訊息
            if ($event['type'] === 'message' && $event['message']['type'] === 'text') {
                $replyToken = $event['replyToken'];
                $messageText = $event['message']['text'];
                $userId = $event['source']['userId'] ?? '未知使用者';
                $groupId = $event['source']['groupId'] ?? null;
                
                // 根據不同指令回傳不同訊息
                switch(strtolower($messageText)) {
                    case 'id':
                        $replyMessage = "您的 User ID 是：\n" . $userId;
                        break;
                        
                    case 'group':
                        if ($groupId) {
                            $replyMessage = "目前群組的 ID 是：\n" . $groupId;
                        } else {
                            $replyMessage = "這不是在群組中的對話";
                        }
                        break;
                        
                    default:
                        $replyMessage = $messageText;
                }
                
                // 回覆訊息
                $this->replyMessage($replyToken, $replyMessage);
                
                // 記錄回覆的訊息
                $this->db->table('line')->insert([
                    'reply_message' => $replyMessage,
                    'user_id' => $userId,
                    'group_id' => $groupId,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            return $this->response->setJSON(['status' => 'success']);

        } catch (\Exception $e) {
            // 記錄錯誤
            $this->db->table('line')->insert([
                'error' => $e->getMessage(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            return $this->response->setStatusCode(500)
                ->setJSON(['error' => $e->getMessage()]);
        }
    }

    private function replyMessage($replyToken, $message)
    {
        $url = 'https://api.line.me/v2/bot/message/reply';

        $postData = [
            'replyToken' => $replyToken,
            'messages' => [
                [
                    'type' => 'text',
                    'text' => $message
                ]
            ]
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        
        // 記錄 LINE API 回應
        $this->db->table('line')->insert([
            'line_response' => $result,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        curl_close($ch);
        return $result;
    }

    function sendMessageToGroup($groupId, $message) {
        $url = 'https://api.line.me/v2/bot/message/push';
    
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken
        ];
    
        $postData = [
            'to' => $groupId, // 指定群組 ID
            'messages' => [['type' => 'text', 'text' => $message]]
        ];
    
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        $result = curl_exec($ch);
        curl_close($ch);
    
        return $result;
    }
}