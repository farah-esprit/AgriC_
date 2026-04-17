<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class IaService
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function predict($symptomes, $culture)
    {
        $response = $this->client->request(
            'POST',
            'http://127.0.0.1:5001/predict',
            [
                'json' => [
                    'symptomes' => $symptomes,
                    'culture' => $culture
                ]
            ]
        );

        return $response->toArray();
    }
}