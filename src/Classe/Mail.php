<?php

namespace App\Classe;

use \Mailjet\Client;
use \Mailjet\Resources;


class Mail
{
    private $api_key = 'f76b8573c75c1ae8961f56b512ff8f91';

    public function send($secret_api_key, $to_email, $to_name, $subject, $content) 
    {

        $mj = new Client($this->api_key, $secret_api_key,true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "lemondeduchocolat@proton.me",
                        'Name' => "Le monde du chocolat"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'Subject' => $subject,
                    'TemplateId' => 4119425,
                    'TemplateLanguage' => true,
                    'Variables' => [
                        'content' => $content
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success() && dd($response->getData());
    }
}