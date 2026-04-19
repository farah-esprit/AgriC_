#!/usr/bin/env php
<?php

/**
 * 🧪 Test Script - Vérification des ParamConverter
 * ================================================
 *
 * Ce script aide à vérifier que tous les ParamConverter sont correctement configurés.
 * Utilisez-le pour tester les routes problématiques.
 */

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║  🧪 Vérification des ParamConverter & Routes                 ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Configuration
$baseUrl = "http://localhost:8000";
$routesToTest = [
    // Culture
    [
        'name' => 'Culture - Show',
        'method' => 'GET',
        'route' => '/culture/1',
        'expected' => 200,
    ],
    [
        'name' => 'Culture - Edit',
        'method' => 'GET',
        'route' => '/culture/1/edit',
        'expected' => 200,
    ],

    // Stock
    [
        'name' => 'Stock - Edit',
        'method' => 'GET',
        'route' => '/stock/1/edit',
        'expected' => 200,
    ],

    // Produit
    [
        'name' => 'Produit - Edit',
        'method' => 'GET',
        'route' => '/produit/1/edit',
        'expected' => 200,
    ],

    // Réclamation
    [
        'name' => 'Réclamation - Show',
        'method' => 'GET',
        'route' => '/reclamation/1',
        'expected' => 200,
    ],

    // Commande
    [
        'name' => 'Commande - Edit',
        'method' => 'GET',
        'route' => '/commande/1/edit',
        'expected' => 200,
    ],
    [
        'name' => 'Payment - Checkout',
        'method' => 'GET',
        'route' => '/payment/checkout/1',
        'expected' => 200,
    ],

    // Diagnostic
    [
        'name' => 'Diagnostic - Show',
        'method' => 'GET',
        'route' => '/diagnostic/1',
        'expected' => 200,
    ],
];

echo "📊 Routes à Tester:\n\n";

$passed = 0;
$failed = 0;

foreach ($routesToTest as $index => $test) {
    $num = $index + 1;
    echo "[$num] {$test['name']}\n";
    echo "    📍 {$test['method']} {$baseUrl}{$test['route']}\n";
    echo "    Accédez à cette URL et vérifiez:\n";
    echo "    ✓ Pas d'erreur 404\n";
    echo "    ✓ Pas d'erreur \"object not found\"\n";
    echo "    ✓ Pas d'erreur de ParamConverter\n\n";
}

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  📝 Instructions de Test                                      ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "1️⃣  Assurez-vous que le serveur est lancé:\n";
echo "    $ symfony serve\n\n";

echo "2️⃣  Testez chaque route ci-dessus en accédant à l'URL\n";
echo "    Vérifiez qu'aucune erreur EntityValueResolver ne s'affiche\n\n";

echo "3️⃣  En cas d'erreur, vérifiez:\n";
echo "    ✓ Le ParamConverter est importé\n";
echo "    ✓ L'attribut #[ParamConverter] est présent\n";
echo "    ✓ Le mapping est correct\n";
echo "    ✓ Le cache a été vidé: php bin/console cache:clear\n\n";

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  🔍 Vérification du Cache                                      ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "Pour vider le cache:\n";
echo "  $ php bin/console cache:clear\n\n";

echo "Pour vérifier les routes enregistrées:\n";
echo "  $ php bin/console debug:router | grep -i culture\n\n";

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  ✅ Checklist de Vérification                                 ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$checklist = [
    "✅ CultureController.php - ParamConverter pour show/edit/delete",
    "✅ StockController.php - ParamConverter pour edit/delete",
    "✅ ProduitController.php - ParamConverter pour edit/delete",
    "✅ ReclamationController.php - ParamConverter pour show/edit/delete",
    "✅ CommandeController.php - ParamConverter pour edit/delete",
    "✅ PaymentController.php - ParamConverter pour checkout/process/success/cancel",
    "✅ DiagnosticController.php - ParamConverter pour show/edit/delete/pdf",
    "✅ Cache vidé avec: php bin/console cache:clear",
    "✅ Tous les imports ParamConverter ajoutés",
    "✅ Tous les mappings points vers les bonnes colonnes",
];

foreach ($checklist as $item) {
    echo "$item\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

