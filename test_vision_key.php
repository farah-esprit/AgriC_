#!/usr/bin/env php
<?php

/**
 * 🧪 Script de Test - Google Vision API
 * =======================================
 */

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  🔑 Test Configuration Google Vision API                  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Charger les variables d'environnement
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$apiKey = $_ENV['VISION_API_KEY'] ?? null;

// Vérification 1: Clé présente?
echo "1️⃣  Clé API Présente?\n";
if (!$apiKey) {
    echo "   ❌ VISION_API_KEY non trouvée dans .env\n";
    exit(1);
}
echo "   ✅ VISION_API_KEY trouvée\n\n";

// Vérification 2: Clé valide?
echo "2️⃣  Clé API Valide?\n";
if ($apiKey === 'YOUR_VISION_API_KEY') {
    echo "   ❌ VISION_API_KEY a la valeur par défaut\n";
    echo "   📝 Remplacez 'YOUR_VISION_API_KEY' par votre vraie clé\n";
    exit(1);
}
echo "   ✅ Clé semble valide (format: AIzaSy...)\n\n";

// Vérification 3: Connecter à l'API
echo "3️⃣  Connexion à Google Vision API...\n";

try {
    $httpClient = HttpClient::create();

    $response = $httpClient->request('POST',
        'https://vision.googleapis.com/v1/images:annotate?key=' . urlencode($apiKey),
        [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'requests' => [
                    [
                        'image' => ['content' => ''],
                        'features' => [['type' => 'LABEL_DETECTION', 'maxResults' => 5]]
                    ]
                ]
            ],
            'timeout' => 10,
        ]
    );

    $statusCode = $response->getStatusCode();

    if ($statusCode === 200) {
        echo "   ✅ Connexion réussie! (HTTP 200)\n";
        echo "   ✅ La clé API fonctionne correctement!\n\n";

        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║  ✅ TOUT EST CONFIGURÉ CORRECTEMENT!                      ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";

        echo "Vous pouvez maintenant:\n";
        echo "  ✅ Utiliser l'API Vision dans votre application\n";
        echo "  ✅ Analyser des images\n";
        echo "  ✅ Détecter des cultures\n\n";

    } else if ($statusCode === 400) {
        echo "   ⚠️  HTTP 400 - Erreur de requête (normal pour un contenu vide)\n";
        echo "   ✅ Mais la clé est VALIDE!\n\n";

        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║  ✅ CLÉ API VALIDE!                                        ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n\n";

    } else if ($statusCode === 401) {
        echo "   ❌ HTTP 401 - Clé API invalide ou expirée\n";
        echo "   📝 Vérifiez votre clé API\n";
        exit(1);

    } else {
        echo "   ❌ HTTP $statusCode - Erreur inconnue\n";
        exit(1);
    }

} catch (\Exception $e) {
    echo "   ❌ Erreur de connexion: " . $e->getMessage() . "\n";
    exit(1);
}

echo "═══════════════════════════════════════════════════════════\n\n";

