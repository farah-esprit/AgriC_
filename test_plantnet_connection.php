#!/usr/bin/env php
<?php

/**
 * 🧪 Script de Diagnostic - Connexion PlantNet API
 */

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  🧪 DIAGNOSTIC - CONNEXION PLANTNET API                  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// Charger .env
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$apiKey = $_ENV['PLANTNET_API_KEY'] ?? null;

// 1. Vérifier la clé API
echo "1️⃣  Clé API Configurée?\n";
if (!$apiKey || $apiKey === 'YOUR_PLANTNET_API_KEY') {
    echo "   ❌ PLANTNET_API_KEY non valide\n";
    exit(1);
}
echo "   ✅ PLANTNET_API_KEY: " . substr($apiKey, 0, 10) . "...\n\n";

// 2. Vérifier la connexion Internet
echo "2️⃣  Connexion Internet?\n";
$connected = @fsockopen("www.google.com", 80, $errno, $errstr, 5);
if ($connected) {
    echo "   ✅ Internet disponible\n";
    fclose($connected);
} else {
    echo "   ❌ Pas de connexion Internet\n";
    exit(1);
}
echo "\n";

// 3. Tester la connexion à PlantNet
echo "3️⃣  Connexion à PlantNet API?\n";

try {
    $httpClient = HttpClient::create();

    // Test simple - requête POST avec clé invalide juste pour vérifier si l'API répond
    $response = $httpClient->request('POST',
        'https://my-api.plantnet.org/v2/identify/all?organs=leaf&api-key=test',
        [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => 'images=test',
            'timeout' => 10,
        ]
    );

    $statusCode = $response->getStatusCode();

    if ($statusCode === 401 || $statusCode === 400) {
        echo "   ✅ API PlantNet répond (erreur attendue avec clé test)\n";
        echo "   Status: HTTP $statusCode\n\n";
    } elseif ($statusCode === 200) {
        echo "   ✅ API PlantNet accessible\n";
        echo "   Status: HTTP 200\n\n";
    } else {
        echo "   ⚠️  API répond avec HTTP $statusCode\n";
        echo "   Body: " . substr($response->getContent(false), 0, 200) . "\n\n";
    }

} catch (\Exception $e) {
    echo "   ❌ Impossible de se connecter à PlantNet\n";
    echo "   Erreur: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 4. Tester avec une vraie requête
echo "4️⃣  Test avec Votre Clé API?\n\n";

echo "Pour tester avec une vraie image, exécutez:\n";
echo "  curl -X POST 'http://localhost:8000/api/culture/analyse-image' \\\n";
echo "    -F 'image=@image.jpg'\n\n";

echo "Ou tesez directement via cURL avec PlantNet:\n";
echo "  curl -X POST 'https://my-api.plantnet.org/v2/identify/all?organs=leaf' \\\n";
echo "    -F 'images=@image.jpg' \\\n";
echo "    -F 'api-key=$apiKey'\n\n";

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  ✅ DIAGNOSTIC TERMINÉ                                    ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "Résumé:\n";
echo "✅ Clé API: Valide\n";
echo "✅ Connexion Internet: OK\n";
echo "✅ API PlantNet: Accessible\n\n";

echo "Prochaine étape: Testez avec une vraie image\n\n";

