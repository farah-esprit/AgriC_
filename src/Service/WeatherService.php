<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service d'intégration avec l'API OpenWeatherMap.
 * Utilisé par le module Culture pour afficher la météo de chaque localisation.
 */
class WeatherService
{
    private const BASE_URL     = 'https://api.openweathermap.org/data/2.5/weather';
    private const FORECAST_URL = 'https://api.openweathermap.org/data/2.5/forecast';
    private const DEFAULT_CITY = 'Tunis';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiKey
    ) {}

    /**
     * Récupère les données météo pour une ville/localisation donnée.
     * Si la ville est inconnue, retourne la météo de Tunis par défaut.
     */
    public function getWeather(string $location): ?array
    {
        dump('=== WeatherService ===');
        dump('apiKey: ' . substr($this->apiKey, 0, 10) . '...');
        dump('location: ' . $location);

        if (empty($this->apiKey) || $this->apiKey === 'YOUR_OPENWEATHER_API_KEY') {
            dump('❌ Clé API vide ou placeholder !');
            return null;
        }

        try {
            $response = $this->httpClient->request('GET', self::BASE_URL, [
                'query' => [
                    'q'     => $location,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang'  => 'fr',
                ],
                'timeout' => 5,
            ]);

            $statusCode = $response->getStatusCode();
            dump('statusCode: ' . $statusCode);

            if ($statusCode !== 200) {
                dump('❌ Erreur HTTP ' . $statusCode . ' → fallback Tunis');
                if ($location !== self::DEFAULT_CITY) {
                    return $this->getWeather(self::DEFAULT_CITY);
                }
                return null;
            }

            $data = $response->toArray();
            dump('✅ Données reçues pour: ' . $data['name']);

            return $this->formatWeatherData($data, $location);

        } catch (\Throwable $e) {
            dump('❌ Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère les prévisions météo sur 5 jours.
     */
    public function getForecast(string $location): ?array
    {
        if (empty($this->apiKey) || $this->apiKey === 'YOUR_OPENWEATHER_API_KEY') {
            return null;
        }

        try {
            $response = $this->httpClient->request('GET', self::FORECAST_URL, [
                'query' => [
                    'q'     => $location,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang'  => 'fr',
                ],
                'timeout' => 5,
            ]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            return $response->toArray();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Vérifie si une ville est reconnue par OpenWeatherMap.
     */
    public function isValidLocation(string $location): bool
    {
        if (empty($this->apiKey) || $this->apiKey === 'YOUR_OPENWEATHER_API_KEY') {
            return false;
        }

        try {
            $response = $this->httpClient->request('GET', self::BASE_URL, [
                'query' => [
                    'q'     => $location,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                ],
                'timeout' => 5,
            ]);

            return $response->getStatusCode() === 200;

        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Retourne l'URL de l'icône météo OpenWeatherMap.
     */
    public function getIconUrl(string $iconCode): string
    {
        return "https://openweathermap.org/img/wn/{$iconCode}@2x.png";
    }

    /**
     * Formate les données brutes de l'API en tableau structuré.
     */
    private function formatWeatherData(array $data, string $location): array
    {
        return [
            'ville'       => $data['name'] ?? $location,
            'pays'        => $data['sys']['country'] ?? '',
            'temperature' => round($data['main']['temp'] ?? 0, 1),
            'ressenti'    => round($data['main']['feels_like'] ?? 0, 1),
            'humidite'    => $data['main']['humidity'] ?? 0,
            'description' => ucfirst($data['weather'][0]['description'] ?? ''),
            'icone'       => $data['weather'][0]['icon'] ?? '01d',
            'vent'        => round(($data['wind']['speed'] ?? 0) * 3.6, 1),
            'fallback'    => ($data['name'] ?? $location) === self::DEFAULT_CITY
                                && strtolower($location) !== strtolower(self::DEFAULT_CITY),
        ];
    }
}