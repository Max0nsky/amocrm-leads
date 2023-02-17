<?php

class AmoCRM
{
    private string $accessToken;
    private string $baseUrl;
    private int $refreshTokenUpdateTime;

    // Данные из раздела интеграции AmoCRM
    const CLIENT_SECRET = 'YOUR_CLIENT_SECRET';
    const CLIENT_ID = 'YOUR_CLIENT_ID';
    const SUBDOMAIN = 'YOUR_SUBDOMAIN';

    // Идентификаторы свойств AmoCRM
    const FIELDS = [
        'good_name' => 111111,
        'good_code' => 222222,
    ];

    public function __construct()
    {
        $tokenData = $this->getAccessToken();

        $this->accessToken = $tokenData['access_token'];
        $this->refreshTokenUpdateTime =  $tokenData['end_token_time'];
        $this->baseUrl = "https://" . self::SUBDOMAIN . ".amocrm.ru/api/v4/";
    }

    /**
     * Добавление сделки. Метод принимает имя и стоимость сделки в качестве параметров, 
     * также дополнительные поля, создает массив данных и отправляет запрос на API.
     * Метод возвращает массив данных ответа.
     */
    public function addDeal(string $name, int $price, $good_name, $good_code): array
    {
        $url = $this->baseUrl . 'leads';

        $data = [
            "name" => $name,
            "price" => $price,
            "custom_fields_values" => [
                [
                    "field_id" => self::FIELDS['good_name'],
                    "values" => [
                        [
                            "value" => $good_name
                        ],
                    ]
                ],
                [
                    "field_id" => self::FIELDS['good_code'],
                    "values" => [
                        [
                            "value" => $good_code
                        ],
                    ]
                ],
            ]
        ];

        return $this->sendCurlRequest($url, $data);
    }

    /**
     * Извлекает токен доступа из файла JSON и обновляет его, если он истек. 
     * Метод возвращает массив данных токена доступа
     */
    private function getAccessToken(): array
    {
        $filename = 'token.json';
        $tokenData = json_decode(file_get_contents($filename), true);

        if (time() >= $this->refreshTokenUpdateTime) {
            $tokenData = $this->refreshAccessToken($tokenData, $filename);
        }

        return $tokenData;
    }

    /**
     * Обновляет токен доступа, отправляя запрос к API с использованием токена обновления. 
     * Метод возвращает массив новых данных токена доступа.
     */
    private function refreshAccessToken(array $tokenData, string $filename): array
    {
        $data = [
            'client_id' => self::CLIENT_ID,
            'client_secret' => self::CLIENT_SECRET,
            'grant_type' => 'refresh_token',
            'refresh_token' =>  $tokenData['refresh_token'],
        ];

        $url = "https://" . self::SUBDOMAIN . ".amocrm.ru/oauth2/access_token";
        $response = $this->sendCurlRequest($url, $data);

        $tokenData = [
            'access_token' => $response['access_token'],
            'refresh_token' => $response['refresh_token'],
            'end_token_time' => time() + $response['expires_in'],
        ];
        file_put_contents($filename, json_encode($tokenData));

        $this->refreshTokenUpdateTime = $tokenData['end_token_time'];
        $this->accessToken = $tokenData['access_token'];

        return $tokenData;
    }

    /**
     * Отправляет запрос curl к API AmoCRM с указанным URL и данными. 
     * Метод возвращает массив данных ответа.
     */
    private function sendCurlRequest(string $url, array $data): array
    {
        if (time() >= $this->refreshTokenUpdateTime) {
            $this->getAccessToken();
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->accessToken,
            ],
        ]);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new Exception("Request error: $httpCode - $response");
        }

        return json_decode($response, true);
    }
}
