<?php
namespace App\Controllers\Admin;

use App\Controllers\Admin\Common\LineBaseController;

class LineNew extends LineBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function webhook($botId = null)
    {
        if ($botId) {
            $this->accessToken = $this->fetchAccessToken($botId);
        }
        
        return parent::webhook();
    }

    private function fetchAccessToken($botId)
    {
        $data = array(
            'test' => 'i/5rFtsUJH3wHvNlZqjunlc9YpPiRHdHjCR3tKpant5SnLMOXpM+Z9EQ7ZjhfT0nIoVpvtOK8RKBriQMuy4R4EIwfIIKDv2yCPvU4Hncn2cst1mSAlMzi7hKmNn+3QtzIvE+DFsYUnAzOhM5HKRBfQdB04t89/1O/w1cDnyilFU=',
            'bot2' => 'xUkKfJzG8NNIePbz+Y8YGi9tkMUCoStUAgUv6HLX6FRQIzPM2MOcN5OJXRFAcajci9AoaoGFDafVjaF9Z6B+9xWmDsUQuySdoARFAu9k7UPAarbSHzgEmQeMhtyRkSHeqc0nHrXQy35UPsXZPiq1+wdB04t89/1O/w1cDnyilFU=',
            'bot3' => 'third_bot_token_here',
        );

        return $data[$botId];
    }
}