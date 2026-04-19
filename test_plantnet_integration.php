#!/usr/bin/env php
<?php

/**
 * Vérifications rapides : PlantNet + route d'analyse culture.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║  TEST INTÉGRATION PLANTNET / API CULTURE                   ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$checks = [
    'PLANTNET_API_KEY' => $_ENV['PLANTNET_API_KEY'] ?? null,
];

echo "1️⃣  Clé API PlantNet\n";
if ($checks['PLANTNET_API_KEY'] && $checks['PLANTNET_API_KEY'] !== 'YOUR_PLANTNET_API_KEY') {
    echo "   ✅ PLANTNET_API_KEY: " . substr($checks['PLANTNET_API_KEY'], 0, 10) . "...\n";
} else {
    echo "   ❌ PLANTNET_API_KEY manquante ou valeur par défaut\n";
}

echo "\n2️⃣  Fichiers\n";
$files = [
    'src/Service/PlantNetService.php',
    'src/Controller/CultureImageAnalysisController.php',
];
foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    echo file_exists($path) ? "   ✅ $file\n" : "   ❌ $file\n";
}

echo "\n3️⃣  config/services.yaml (PlantNetService)\n";
$servicesContent = file_get_contents(__DIR__ . '/config/services.yaml');
echo strpos($servicesContent, 'PlantNetService') !== false && strpos($servicesContent, 'PLANTNET_API_KEY') !== false
    ? "   ✅ PlantNetService + env PLANTNET_API_KEY\n"
    : "   ❌ Configuration incomplète\n";

echo "\n4️⃣  Route api_analyse_image\n";
$routesOutput = [];
exec('php bin/console debug:router 2>&1', $routesOutput);
$hasAnalyse = false;
foreach ($routesOutput as $line) {
    if (str_contains($line, 'api_analyse_image')) {
        $hasAnalyse = true;
        echo '   ✅ ' . trim($line) . "\n";
        break;
    }
}
if (!$hasAnalyse) {
    echo "   ❌ Route api_analyse_image non trouvée\n";
}

echo "\n5️⃣  Syntaxe PHP\n";
foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    exec('php -l "' . $path . '" 2>&1', $out, $code);
    echo $code === 0 ? "   ✅ $file\n" : "   ❌ $file\n";
}

echo "\n📌 Exemple d’appel:\n";
echo "  curl -X POST http://127.0.0.1:8000/api/culture/analyse-image \\\n";
echo "    -F \"image=@public/uploads/images/tomate_test.jpg\" \\\n";
echo "    -F \"idCulture=1\"\n\n";
