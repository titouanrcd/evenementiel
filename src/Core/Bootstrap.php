<?php
/**
 * ============================================================
 * BOOTSTRAP - NOVA Événements
 * ============================================================
 * Initialisation de l'application
 * ============================================================
 */

namespace App\Core;

// Empêcher l'accès direct
if (!defined('ROOT_PATH')) {
    die('Accès non autorisé');
}

// Configuration PHP
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', ROOT_PATH . '/logs/php_errors.log');

// Autoloader personnalisé
spl_autoload_register(function ($class) {
    // Convertir le namespace en chemin de fichier
    $prefix = 'App\\';
    $baseDir = ROOT_PATH . '/src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Charger la configuration
require_once ROOT_PATH . '/config/app.php';

// Charger les fonctions helpers
require_once ROOT_PATH . '/src/Core/Helpers.php';
