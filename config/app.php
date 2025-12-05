<?php
/**
 * ============================================================
 * CONFIGURATION CENTRALE - NOVA Événements
 * ============================================================
 * Configuration de l'application avec sécurité renforcée
 * EN PRODUCTION: Utilisez des variables d'environnement!
 * ============================================================
 */

// Empêcher l'accès direct
if (!defined('ROOT_PATH')) {
    die('Accès non autorisé');
}

// ============================================================
// 1. ENVIRONNEMENT
// ============================================================
define('ENVIRONMENT', getenv('APP_ENV') ?: 'development');

// ============================================================
// 2. BASE DE DONNÉES
// ============================================================
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'gestion_events_etudiants');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', 'utf8mb4');

// ============================================================
// 3. CLÉS API
// ============================================================
define('OPENWEATHER_API_KEY', getenv('OPENWEATHER_API_KEY') ?: '5758e5efd62dd49f94888a8acdc2525c');
define('GOOGLE_MAPS_API_KEY', getenv('GOOGLE_MAPS_API_KEY') ?: '');

// ============================================================
// 4. SÉCURITÉ
// ============================================================
// Clé secrète pour le chiffrement (CHANGER EN PRODUCTION!)
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
define('UPLOAD_DIR', ROOT_PATH . '/uploads/');
define('ALLOWED_MIME_TYPES', [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/gif' => 'gif',
    'image/webp' => 'webp'
]);

// ============================================================
// 6. SESSIONS
// ============================================================
define('SESSION_LIFETIME', 3600); // 1 heure
define('SESSION_NAME', 'NOVA_SID');

// ============================================================
// 7. RATE LIMITING
// ============================================================
define('RATE_LIMIT_REQUESTS', 100);
define('RATE_LIMIT_WINDOW', 60); // secondes

// ============================================================
// 8. LOGS
// ============================================================
define('LOG_DIR', ROOT_PATH . '/logs/');

// ============================================================
// 9. URLs & PATHS
// ============================================================
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Détecter automatiquement le sous-dossier (pour XAMPP/WAMP)
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$basePath = str_replace('/index.php', '', $scriptName);
define('BASE_PATH', $basePath); // Ex: /evenementiel/public

define('BASE_URL', $protocol . '://' . $host . BASE_PATH);

// ============================================================
// 10. CONFIGURATION PRODUCTION
// ============================================================
if (ENVIRONMENT === 'production') {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// ============================================================
// 11. TIMEZONE
// ============================================================
date_default_timezone_set('Europe/Paris');

// ============================================================
// 12. ENCODAGE
// ============================================================
mb_internal_encoding('UTF-8');
