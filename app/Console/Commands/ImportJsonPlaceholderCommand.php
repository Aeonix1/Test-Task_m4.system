<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use Illuminate\Console\Command;

class ImportJsonPlaceholderCommand extends Command
{
    protected $signature = 'import:jsonplaceholder';

    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = new Client(
            [
                'base_uri' => 'https://developer-api.m4.systems:4443',
                'headers' => [
                    'Authorization' => 'Bearer ' . env('MY_ACCESS_TOKEN'),
                    'Content-Type' => 'application/json',
                ]
            ]
        );

        // Задание 1 (Авторизация через API)
        $response = $client->get('/api_auth/login_check', ['auth' => [env("MY_LOGIN"), env("MY_PASSWORD")]]);
//        dd(json_decode($response->getBody()->getContents()));

        $data = json_decode($response->getBody()->getContents(), true);


        // Задание 2 (Получение URL сервиса SD из ответа авторизации)
        $services = $data['services'];
        foreach ($services as $service) {
            if ($service['code'] === "SD") {
                $urlSD = $service['apiUrl'];
                echo($service['apiUrl'] . PHP_EOL);          // вывод url-адреса на экран (выполнение задания 2)
            }
        }


        // Задание 3 (Получение списка заявок, изменённых за последние 3 дня)
        $params = [
            "method" => "M4GetTasks",
            "params" => ["status" => [0, 1, 4, 5, 6], "lastUpdate" => date('Y-m-d H:i:s', strtotime('-600 day'))],
            "id" => "1",
            "jsonrpc" => "2.0",
        ];

            $response = $client->post('/api_web/api.php',
                [
                    'json' => $params
                ]);
            $applications = json_decode($response->getBody()->getContents(), true);

        // Задание 4

        try
        {
            if (count($applications['result']) < 2)
            {
                $application2 = $applications['result'][1];
                echo 'applicationId => ' . $application2['applicationId'] . ', req => ' . $application2['req'] .', caption => '. $application2['caption']. ', status/statusName => ' . $application2['status']['name'] . PHP_EOL;
            }
        } catch (\Exception $e)
        {
            echo 'Недостаточно заявок для выполнения тестового сценария';
            exit(0);
        }


        // Задание 5
        if (count($applications['result']) > 2) {
            $application2 = $applications['result'][1];
            echo 'applicationId => ' . $application2['taskId'] . ', req => ' . $application2['req'] . ', caption => ' . $application2['caption'] . ', status/statusName => ' . $application2['status']['name'] . PHP_EOL;
        }

        // Задание 6

        // Добавил изображения по адресу /public/storage/images/

        // Задание 7

        $image1 = './././public/storage/images/Image1.jpg';
        $image2 = './././public/storage/images/Image2.jpg';
//        $application2['image1'] = $image1;
//        $application2['image2'] = $image2;

        $params2 = [
            "method" => "M4AddTaskAttach",
            "params" => [
                    "taskId" => $application2['taskId'],
                    "files" => [
                         "guid" => "830b187315ab36c7c79ee155768d38b0",
                         "typeAttachId" => 5
                ],
            ],
            "id" => 1,
            "jsonrpc" => "2.0",
        ];

        $client->request('POST', '/api_web/api.php', [
            'json' => $params2,
        ]);


        // Задание 8

        $fio = 'Вержбовский Олег Вячеславович';
        $datetime = "06.05.2026 17:06";
//        $application2['public_comment'] = "Тестовый комментарий от кандидата: " . $fio . ", " . $datetime;

        $params3 = [
            "method" => "M4AddTaskComment",
            "params" => [
                "taskId" => $application2['taskId'],
                "comment" => "$fio, $datetime",
                "isPublic" => true
            ],
            "id" => 1,
            "jsonrpc" => "2.0"
        ];

        $client->request('POST', '/api_web/api.php', [
            'json' => $params3,
        ]);

        // Задание 9 (logout)

        $params4 = [
            "method" => "logout",
            "id" => "1",
            "jsonrpc" => "2.0",
        ];

        $response = $client->post('/api_auth',
            [
                'json' => $params4,
            ]);
        json_decode($response->getBody()->getContents());
    }
}
