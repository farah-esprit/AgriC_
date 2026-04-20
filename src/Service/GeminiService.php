<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GeminiService
{
    private $httpClient;
    private $apiKey;

    public function __construct(HttpClientInterface $httpClient, ParameterBagInterface $params)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $params->get('app.gemini_api_key');
    }

    /**
     * Analyse le contenu pour détecter des propos inappropriés.
     * Retourne true si le contenu est sûr, false sinon.
     */
    public function isContentSafe(string $text): bool
    {
        if (empty($text) || $this->apiKey === 'votre_cle_gemini_ici') {
            return true;
        }

        $prompt = "TU ES UN MODÉRATEUR DE FORUM. Réponds UNIQUEMENT par 'SAFE' ou 'UNSAFE'.
        Considère 'UNSAFE' si le texte contient : insultes, agressivité, harcèlement, haine, ou contenu sexuel.
        Texte à analyser : " . $text;

        try {
            $response = $this->callGemini($prompt, true); // true = mode modération
            return trim(strtoupper($response)) === 'SAFE';
        } catch (\Exception $e) {
            // Si l'erreur vient d'un blocage de sécurité de Gemini lui-même, c'est que c'est UNSAFE
            if (str_contains($e->getMessage(), 'SAFETY') || str_contains($e->getMessage(), 'blocked')) {
                return false;
            }
            return true; 
        }
    }

    /**
     * Traduit le texte vers la langue cible.
     */
    public function translate(string $text, string $targetLanguage = 'français'): string
    {
        if (empty($text) || $this->apiKey === 'votre_cle_gemini_ici') {
            return $text;
        }

        $prompt = "Traduis le texte suivant en " . $targetLanguage . ". Ne fournis que la traduction, rien d'autre. Texte : " . $text;

        try {
            return $this->callGemini($prompt);
        } catch (\Exception $e) {
            return "Erreur de traduction : " . $e->getMessage();
        }
    }

    /**
     * Analyse la qualité du contenu et attribue une note d'XP (0 à 5).
     */
    public function calculateXpReward(string $text): int
    {
        if (empty($text) || $this->apiKey === 'votre_cle_gemini_ici') {
            return 0;
        }

        $prompt = "TU ES UN EXPERT EN AGRICULTURE ET MODÉRATEUR DE FORUM. 
        Analyse la qualité technique, la pertinence et la richesse du texte suivant. 
        Attribue une note de 0 à 5 points d'expérience (XP) selon l'utilité pour la communauté (0 = inutile/très court, 5 = guide expert détaillé).
        Réponds UNIQUEMENT par le chiffre (ex: 3).
        Texte : " . $text;

        try {
            $response = $this->callGemini($prompt);
            $xp = (int) filter_var($response, FILTER_SANITIZE_NUMBER_INT);
            return max(0, min(5, $xp)); // Clamp entre 0 et 5
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function callGemini(string $prompt, bool $isModeration = false): string
    {
        // Utilisation du modèle Gemini 3.1 Flash Lite (Le plus stable pour le Free Tier en Avril 2026)
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite-preview:generateContent?key=" . $this->apiKey;

        $response = $this->httpClient->request('POST', $url, [
            'json' => [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'safetySettings' => [
                    ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                    ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_ONLY_HIGH'],
                    ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                    ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_ONLY_HIGH']
                ]
            ]
        ]);

        $data = $response->toArray(false);

        // Debug: Si la réponse est vide ou contient une erreur explicite
        if (isset($data['error'])) {
            throw new \Exception("Erreur API : " . ($data['error']['message'] ?? 'Inconnue'));
        }

        // Vérification du blocage par les filtres de sécurité
        if (isset($data['promptFeedback']['blockReason'])) {
            throw new \Exception("Contenu bloqué par les filtres de sécurité Google (" . $data['promptFeedback']['blockReason'] . ")");
        }

        if (isset($data['candidates'][0]['finishReason']) && $data['candidates'][0]['finishReason'] === 'SAFETY') {
            throw new \Exception("Réponse bloquée par les filtres de sécurité Google (SAFETY)");
        }

        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }

        return "Erreur : Structure API invalide ou réponse vide.";
    }
}
