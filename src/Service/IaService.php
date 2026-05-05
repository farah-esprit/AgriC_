<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class IaService
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /** @return array<string, mixed> */
    public function predict(string $symptomes, string $culture): array
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