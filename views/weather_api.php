<?php
/**
 * ============================================================
 * Proxy API Météo - NOVA Événements
 * ============================================================
 * Contourne les restrictions CORS en appelant OpenWeatherMap côté serveur
 * SÉCURITÉ: Rate limiting, validation des entrées, cache
 * ============================================================
 */

// Démarrer la session pour le rate limiting par utilisateur
session_start();

// Headers de sécurité
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// CORS restreint - Autoriser seulement le même domaine
$allowedOrigins = [
    'http://localhost',
    'http://127.0.0.1',
    'https://localhost',
    // Ajoutez votre domaine de production ici
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
}

// Clé API OpenWeatherMap (idéalement dans un fichier .env)
define('OPENWEATHER_API_KEY', '5758e5efd62dd49f94888a8acdc2525c');

// ============================================================
// RATE LIMITING - Protection contre les abus
// ============================================================
$maxRequestsPerMinute = 30;
$rateLimitKey = 'weather_api_requests';

if (!isset($_SESSION[$rateLimitKey])) {
    $_SESSION[$rateLimitKey] = ['count' => 0, 'time' => time()];
}

// Réinitialiser le compteur après 1 minute
if (time() - $_SESSION[$rateLimitKey]['time'] > 60) {
    $_SESSION[$rateLimitKey] = ['count' => 0, 'time' => time()];
}

// Vérifier le rate limit
if ($_SESSION[$rateLimitKey]['count'] >= $maxRequestsPerMinute) {
    http_response_code(429);
    echo json_encode(['error' => true, 'message' => 'Trop de requêtes. Réessayez dans une minute.']);
    exit;
}
$_SESSION[$rateLimitKey]['count']++;

// ============================================================
// VALIDATION DES ENTRÉES
// ============================================================
$city = isset($_GET['city']) ? trim($_GET['city']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'weather';

// Validation stricte du nom de ville
// Autorise: lettres (y compris accentuées), espaces, tirets, apostrophes
if (empty($city)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Ville non spécifiée']);
    exit;
}

// Nettoyer et valider le nom de ville
$city = preg_replace('/[^\p{L}\s\-\'\.]/u', '', $city);
$city = mb_substr($city, 0, 100); // Limite de longueur

if (empty($city) || mb_strlen($city) < 2) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Nom de ville invalide']);
    exit;
}

// Validation stricte du type (whitelist)
$allowedTypes = ['weather', 'forecast'];
if (!in_array($type, $allowedTypes)) {
    $type = 'weather';
}

// ============================================================
// CACHE SIMPLE (Évite les appels API répétitifs)
// ============================================================
$cacheDir = __DIR__ . '/../logs/weather_cache/';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0755, true);
}

$cacheKey = md5($city . '_' . $type);
$cacheFile = $cacheDir . $cacheKey . '.json';
$cacheDuration = 600; // 10 minutes

// Vérifier le cache
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheDuration) {
    $cachedData = file_get_contents($cacheFile);
    if ($cachedData !== false) {
        echo $cachedData;
        exit;
    }
}

// ============================================================
// APPEL API OpenWeatherMap
// ============================================================
$endpoint = ($type === 'forecast') ? 'forecast' : 'weather';
$url = "https://api.openweathermap.org/data/2.5/{$endpoint}?" . http_build_query([
    'q' => $city . ',FR',
    'appid' => OPENWEATHER_API_KEY,
    'units' => 'metric',
    'lang' => 'fr'
]);

// Configuration cURL sécurisée
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_SSL_VERIFYPEER => true,  // IMPORTANT: Vérifier SSL en production
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_FOLLOWLOCATION => false, // Ne pas suivre les redirections
    CURLOPT_MAXREDIRS => 0,
    CURLOPT_USERAGENT => 'NOVA-Events/1.0',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    error_log("Weather API cURL error: " . $error);
    echo json_encode(['error' => true, 'message' => 'Erreur de connexion au service météo']);
    exit;
}

if ($httpCode !== 200) {
    http_response_code($httpCode);
    $data = json_decode($response, true);
    echo json_encode([
        'error' => true, 
        'message' => $data['message'] ?? 'Erreur du service météo',
        'code' => $httpCode
    ]);
    exit;
}

// Valider que la réponse est du JSON valide
$jsonData = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Réponse invalide du service météo']);
    exit;
}

// Sauvegarder en cache
@file_put_contents($cacheFile, $response);

// Renvoyer la réponse
echo $response;
