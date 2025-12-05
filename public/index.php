<?php
/**
 * ============================================================
 * POINT D'ENTRÉE UNIQUE - NOVA Événements
 * ============================================================
 * Toutes les requêtes passent par ce fichier (Front Controller)
 * ============================================================
 */

// Définir le chemin racine de l'application
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

// Charger l'autoloader et la configuration
require_once ROOT_PATH . '/src/Core/Bootstrap.php';

// Initialiser l'application
$app = new \App\Core\Application();

// Exécuter le routeur
$app->run();
