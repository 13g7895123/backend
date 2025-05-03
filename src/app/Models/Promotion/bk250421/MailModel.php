<?php

namespace App\Models\Promotion;

use CodeIgniter\Model;

class MailModel extends Model
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect('promotion');  // 預設資料庫
    }

    public function test()
    {
        print_r(APPPATH); die();
        print_r(12345); die();
    }

    public function sendMail()
    {
        // 服务账户密钥文件路径
        // $keyFilePath = APPPATH . 'Config/service-account.json'; // 确保路径正确

        // // 读取服务账户密钥
        // $key = json_decode(file_get_contents($keyFilePath), true);

        // // 获取访问令牌
        // $accessToken = $this->getAccessToken($key);

        // // 发送邮件
        // $to = '13g7895123@gmail.com';
        // $subject = 'Test Email from CodeIgniter 4';
        // $messageText = 'This is a test email sent using Gmail API.';

        // $this->sendEmail($accessToken, $to, $subject, $messageText);
        $this->mailJet();   
    }

    private function getTokenInfo($token)
    {
        $url = "https://www.googleapis.com/oauth2/v1/tokeninfo";

        // 初始化 cURL
        $ch = curl_init();

        // 設定 cURL 選項
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token"
        ]);

        // 執行 cURL 並獲取回應
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        // 關閉 cURL
        curl_close($ch);

        // 如果有錯誤，回傳錯誤訊息
        if ($error) {
            return $this->respond(["error" => $error], 500);
        }

        // 回傳 API 回應
        return $this->respond(json_decode($response, true), $httpCode);
    }

    private function getAccessToken($key)
    {
        $url = 'https://oauth2.googleapis.com/token';
        $data = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $this->createJWT($key),
        ];

        $options = [
            'http' => [
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ],
        ];
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $tokenInfo = json_decode($result, true);
        return $tokenInfo['access_token'];
    }

    private function createJWT($key)
    {
        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $claims = [
            'iss' => $key['client_email'],
            'scope' => 'https://www.googleapis.com/auth/gmail.send',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => time() + 3600, // 1 hour
            'iat' => time(),
        ];
        $payload = json_encode($claims);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = '';
        openssl_sign("$base64UrlHeader.$base64UrlPayload", $signature, $key['private_key'], OPENSSL_ALGO_SHA256);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
    }

    private function sendEmail($accessToken, $to, $subject, $messageText)
    {
        $url = 'https://www.googleapis.com/gmail/v1/users/me/messages/send';

        // 创建邮件内容
        $rawMessage = "To: $to\r\n";
        $rawMessage .= "Subject: $subject\r\n";
        $rawMessage .= "MIME-Version: 1.0\r\n";
        $rawMessage .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        $rawMessage .= $messageText;

        // 将邮件内容进行 Base64 编码
        $rawMessage = base64_encode($rawMessage);
        $rawMessage = str_replace(['+', '/', '='], ['-', '_', ''], $rawMessage); // URL 安全编码

        // 准备请求数据
        $data = json_encode(['raw' => $rawMessage]);

        // 使用 curl 发送请求
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json",
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // 执行请求
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        } else {
            echo 'Email sent successfully: ' . $response;
        }

        // 关闭 curl
        curl_close($ch);
    }

    private function mailJet()
    {
        $apiKey = '03b32dd6951a42dd27c5fe910ae35e74'; // 替換為您的 Mailjet API 金鑰
        $apiSecret = '00a995790399e00755c6f75a1b74ef97'; // 替換為您的 Mailjet API 密鑰

        $emailData = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => 'cs@pcgame.tw', // 寄件者電子郵件
                        // 'Email' => '13gt7895123@gmail.com', // 寄件者電子郵件
                        'Name' => '推廣系統' // 寄件者名稱
                    ],
                    'To' => [
                        [
                            'Email' => '13g7895123@gmail.com', // 收件者電子郵件
                            'Name' => 'Jarvis' // 收件者名稱
                        ]
                    ],
                    'Subject' => 'Hello from Mailjet!',
                    'TextPart' => 'This is a test email sent using Mailjet API.',
                    'HTMLPart' => '<h3>This is a test email sent using Mailjet API.</h3>'
                ]
            ]
        ];

        $ch = curl_init('https://api.mailjet.com/v3.1/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$apiKey:$apiSecret");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // 增加錯誤處理
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        } else {
            if ($httpCode == 200) {
                echo 'Email sent successfully: ' . $response;
            } else {
                echo 'Error sending email. HTTP Code: ' . $httpCode . ' Response: ' . $response;
            }
        }

        curl_close($ch);
    }
}