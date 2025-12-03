<?php
/**
 * ============================================================
 * CONFIGURATION CENTRALE - NOVA Événements
 * ============================================================
 * Ce fichier contient toutes les configurations sensibles.
 * EN PRODUCTION: Utilisez des variables d'environnement!
 * ============================================================
 */

// Empêcher l'accès direct
if (!defined('NOVA_APP')) {
    die('Accès non autorisé');
}

// ============================================================
// 1. ENVIRONNEMENT
// ============================================================
// Changer en 'production' sur le serveur
define('ENVIRONMENT', 'development');

// ============================================================
// 2. BASE DE DONNÉES
// ============================================================
// EN PRODUCTION: Utilisez getenv() ou un fichier .env
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'gestion_events_etudiants');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// ============================================================
// 3. CLÉS API
// ============================================================
// EN PRODUCTION: Utilisez getenv() ou un fichier .env
define('OPENWEATHER_API_KEY', getenv('OPENWEATHER_API_KEY') ?: '5758e5efd62dd49f94888a8acdc2525c');
define('GOOGLE_MAPS_API_KEY', getenv('GOOGLE_MAPS_API_KEY') ?: 'VOTRE_CLE_API');

// ============================================================
// 4. SÉCURITÉ
// ============================================================
// Clé secrète pour le chiffrement (générer une nouvelle en production!)
// Générer avec: bin2hex(random_bytes(32))
define('APP_SECRET_KEY', getenv('APP_SECRET_KEY') ?: 'CHANGEZ_MOI_EN_PRODUCTION_' . bin2hex(random_bytes(16)));

// Configuration des mots de passe
define('PASSWORD_COST', 12);
define('PASSWORD_MIN_LENGTH', 8);

// Configuration brute force
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 15); // minutes

// ============================================================
// 5. UPLOADS
// ============================================================
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5 Mo
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp'
]);

// ============================================================
// 6. SESSIONS
// ============================================================
define('SESSION_LIFETIME', 3600); // 1 heure
define('SESSION_NAME', 'NOVA_SESSION');

// ============================================================
// 7. RATE LIMITING
// ============================================================
define('RATE_LIMIT_REQUESTS', 100);
define('RATE_LIMIT_WINDOW', 60); // secondes

// ============================================================
// 8. LOGS
// ============================================================
define('LOG_DIR', __DIR__ . '/../logs/');
define('ERROR_LOG_FILE', LOG_DIR . 'errors.log');
define('SECURITY_LOG_FILE', LOG_DIR . 'security.log');
define('ACCESS_LOG_FILE', LOG_DIR . 'access.log');

// ============================================================
// 9. URLs & PATHS
// ============================================================
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . '://' . $host);
define('BASE_PATH', dirname(__DIR__));

// ============================================================
// 10. CONFIGURATION PRODUCTION
// ============================================================
if (ENVIRONMENT === 'production') {
    // Désactiver l'affichage des erreurs
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
    
    // Forcer HTTPS
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
        exit();
    }
} else {
    // Développement: afficher les erreurs
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}
