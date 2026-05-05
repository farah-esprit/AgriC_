<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Service de diagnostic IA — utilise un modèle Python local
 * pour analyser les symptômes des cultures agricoles.
 */
class DiagnosticAiService
{
    private const TEXT_AI_URL  = 'http://127.0.0.1:5001/predict';
    private const IMAGE_AI_URL = 'http://127.0.0.1:5001/predict-image';
    private const TIMEOUT      = 20;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {}

    /** @return array<string, mixed>|null */
    public function analyserSymptomes(
        string  $symptomes,
        ?string $nomCulture = null,
        ?string $informationsComplementaires = null
    ): ?array {

        try {
            $this->logger->info('Appel Modèle IA Local (Texte)', ['url' => self::TEXT_AI_URL]);

            $response = $this->httpClient->request('POST', self::TEXT_AI_URL, [
                'json' => [
                    'symptomes' => $symptomes,
                    'culture'   => $nomCulture,
                    'infos'     => $informationsComplementaires
                ],
                'timeout' => self::TIMEOUT,
            ]);

            if ($response->getStatusCode() !== 200) {
                return $this->resultatErreur("Erreur serveur IA (Texte) : " . $response->getStatusCode());
            }

            return $response->toArray();

        } catch (\Throwable $e) {
            $this->logger->error('Erreur DiagnosticAiService (Texte)', ['message' => $e->getMessage()]);
            return $this->resultatErreur('Serveur de diagnostic local injoignable (Texte).');
        }
    }

    /** @return array<string, mixed>|null */
    public function analyserImage(
        string $imagePath,
        ?string $nomCulture = null,
        ?string $symptomes = null
    ): ?array {
        try {
            if (!file_exists($imagePath)) {
                return $this->resultatErreur("Fichier image introuvable pour l'analyse.");
            }

            $this->logger->info('Appel Modèle IA Local (Image)', ['url' => self::IMAGE_AI_URL]);

            // Utiliser DataPart pour le multipart
            $formData = [
                'image'     => fopen($imagePath, 'r'),
                'culture'   => $nomCulture ?? '',
                'symptomes' => $symptomes ?? '',
            ];

            $response = $this->httpClient->request('POST', self::IMAGE_AI_URL, [
                'body'    => $formData,
                'timeout' => self::TIMEOUT,
            ]);

            if ($response->getStatusCode() !== 200) {
                return $this->resultatErreur("Erreur serveur IA (Image) : " . $response->getStatusCode());
            }

            return $response->toArray();

        } catch (\Throwable $e) {
            $this->logger->error('Erreur DiagnosticAiService (Image)', ['message' => $e->getMessage()]);
            return $this->resultatErreur('Serveur de diagnostic local injoignable (Image).');
        }
    }

    /** @return array<string, mixed> */
    private function resultatErreur(string $message): array
    {
        return [
            'suggestion'  => $message,
            'maladies'    => [],
            'traitements' => [
                'Veuillez démarrer le serveur Python local',
                'Lancez le fichier python_ai/start.bat',
                'Vérifiez que Python est installé sur votre machine'
            ],
            'urgence'     => 'inconnue',
            'conseil'     => 'Le service d\'analyse IA local est hors ligne.',
            'confiance'   => 0,
        ];
    }
}