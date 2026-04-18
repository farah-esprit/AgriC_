<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class WeatherService
{
    private HttpClientInterface $httpClient;
    private string $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $openWeatherApiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $openWeatherApiKey;
    }

    /**
     * Récupère la météo actuelle pour une ville donnée
     */
    public function getWeatherForCity(string $city): ?array
    {
        // Si la clé API n'est pas configurée, on retourne des données fictives pour la démonstration (très utile pour la soutenance !)
        if (empty($this->apiKey) || $this->apiKey === 'votre_cle_ici') {
            return [
                'temperature' => random_int(20, 35),
                'feels_like' => random_int(22, 38),
                'humidity' => random_int(40, 80),
                'description' => 'Ensoleillé (Mock)',
                'icon_url' => 'https://openweathermap.org/img/wn/01d@2x.png',
                'wind_speed' => random_int(10, 30),
                'city_name' => ucfirst($city),
                'country' => 'TN',
            ];
        }

        if (empty($city)) {
            return null;
        }

        try {
            $response = $this->httpClient->request('GET', 'https://api.openweathermap.org/data/2.5/weather', [
                'query' => [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                    'lang' => 'fr',
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

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
            return null;
        }
    }

    /**
     * Récupère les coordonnées GPS d'une ville via Nominatim (OpenStreetMap)
     */
    public function getCoordinatesForCity(string $city): ?array
    {
        if (empty($city)) {
            return null;
        }

        try {
            $response = $this->httpClient->request('GET', 'https://nominatim.openstreetmap.org/search', [
                'query' => [
                    'q' => $city,
                    'format' => 'json',
                    'limit' => 1,
                ],
                'headers' => [
                    // Il est important d'avoir un User-Agent valide pour éviter l'erreur 403 Forbidden de Nominatim
                    'User-Agent' => 'AgriConnectApp/1.0 (contact@agriconnect.com)',
                ],
            ]);

            $data = $response->toArray();

            // Fallback: Si Nominatim ne trouve rien, on donne des coordonnées par défaut (Tunis) pour la démo
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
}
