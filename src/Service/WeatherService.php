<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service d'intégration météo multi-usages.
 * Combine les fonctions de prévisions (fatma) et les fonctions de géolocalisation/mock (baya).
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
     * Récupère les données météo pour une ville/localisation donnée (Version Fatma).
     * @return array<string, mixed>|null
     */
    public function getWeather(string $location): ?array
    {
        if (empty($this->apiKey) || $this->apiKey === 'YOUR_OPENWEATHER_API_KEY' || $this->apiKey === 'votre_cle_ici') {
            return $this->getMockData($location);
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

            if ($response->getStatusCode() !== 200) {
                if ($location !== self::DEFAULT_CITY) {
                    return $this->getWeather(self::DEFAULT_CITY);
                }
                return null;
            }

            return $this->formatWeatherData($response->toArray(), $location);

        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Récupère la météo actuelle pour une ville donnée (Version Baya avec Mock automatique).
     * @return array<string, mixed>|null
     */
    public function getWeatherForCity(string $city): ?array
    {
        if (empty($this->apiKey) || $this->apiKey === 'votre_cle_ici' || $this->apiKey === 'YOUR_OPENWEATHER_API_KEY') {
            return $this->getMockData($city);
        }

        if (empty($city)) return null;

        try {
            $response = $this->httpClient->request('GET', self::BASE_URL, [
                'query' => [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'fr',
                ],
            ]);

            if ($response->getStatusCode() !== 200) return $this->getMockData($city);

            $data = $response->toArray();
            return [
                'temperature' => round($data['main']['temp']),
                'feels_like' => round($data['main']['feels_like']),
                'humidity' => $data['main']['humidity'],
                'description' => ucfirst($data['weather'][0]['description']),
                'icon' => $data['weather'][0]['icon'],
                'icon_url' => 'https://openweathermap.org/img/wn/' . $data['weather'][0]['icon'] . '@2x.png',
                'wind_speed' => round($data['wind']['speed'] * 3.6),
                'city_name' => $data['name'],
                'country' => $data['sys']['country'] ?? '',
            ];
        } catch (\Exception $e) {
            return $this->getMockData($city);
        }
    }

    /**
     * Récupère les prévisions météo sur 5 jours (Fatma).
     * @return array<string, mixed>|null
     */
    public function getForecast(string $location): ?array
    {
        if (empty($this->apiKey) || $this->apiKey === 'YOUR_OPENWEATHER_API_KEY') return null;

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

            return $response->getStatusCode() === 200 ? $response->toArray() : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Récupère les coordonnées GPS d'une ville via Nominatim (Baya).
     * @return array<string, mixed>|null
     */
    public function getCoordinatesForCity(string $city): ?array
    {
        if (empty($city)) return null;

        try {
            $response = $this->httpClient->request('GET', 'https://nominatim.openstreetmap.org/search', [
                'query' => [
                    'q' => $city,
                    'format' => 'json',
                    'limit' => 1,
                ],
                'headers' => [
                    'User-Agent' => 'AgriConnectApp/1.0 (contact@agriconnect.com)',
                ],
            ]);

            $data = $response->toArray();

            if (empty($data)) {
                return [
                    'lat' => 36.8065,
                    'lon' => 10.1815,
                    'display_name' => 'Tunis, Tunisie (Par défaut)',
                ];
            }

            return [
                'lat' => (float) $data[0]['lat'],
                'lon' => (float) $data[0]['lon'],
                'display_name' => $data[0]['display_name'],
            ];
        } catch (\Exception $e) {
             return [
                'lat' => 36.8065,
                'lon' => 10.1815,
                'display_name' => 'Tunis, Tunisie (Mode Hors-ligne)',
            ];
        }
    }

    /**
     * Vérifie si une ville est reconnue par OpenWeatherMap.
     */
    public function isValidLocation(string $location): bool
    {
        if (empty($this->apiKey) || $this->apiKey === 'YOUR_OPENWEATHER_API_KEY') return true; // Pour la démo

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

    public function getIconUrl(string $iconCode): string
    {
        return "https://openweathermap.org/img/wn/{$iconCode}@2x.png";
    }

    /** @return array<string, mixed> */
    private function getMockData(string $city): array
    {
        return [
            'temperature' => 25,
            'feels_like' => 27,
            'humidity' => 50,
            'description' => 'Ensoleillé (Mock)',
            'icon' => '01d',
            'icon_url' => 'https://openweathermap.org/img/wn/01d@2x.png',
            'wind_speed' => 15,
            'city_name' => ucfirst($city),
            'country' => 'TN',
            'ville' => ucfirst($city), // pour compatibilité formatWeatherData
            'pays' => 'TN',
            'ressenti' => 27,
            'humidite' => 50,
            'icone' => '01d',
            'vent' => 15,
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
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
