<?php
/**
 * Proxy API Météo - Contourne les restrictions CORS
 * Appelle OpenWeatherMap côté serveur
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Clé API OpenWeatherMap
define('OPENWEATHER_API_KEY', '5758e5efd62dd49f94888a8acdc2525c');

// Récupérer les paramètres
$city = isset($_GET['city']) ? trim($_GET['city']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'weather'; // 'weather' ou 'forecast'

if (empty($city)) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Ville non spécifiée']);
    exit;
}

// Construire l'URL de l'API
$endpoint = ($type === 'forecast') ? 'forecast' : 'weather';
$url = "https://api.openweathermap.org/data/2.5/{$endpoint}?q=" . urlencode($city) . ",FR&appid=" . OPENWEATHER_API_KEY . "&units=metric&lang=fr";

// Faire la requête avec cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => 'Erreur cURL: ' . $error]);
    exit;
}

if ($httpCode !== 200) {
    http_response_code($httpCode);
    $data = json_decode($response, true);
    echo json_encode([
        'error' => true, 
        'message' => $data['message'] ?? 'Erreur API OpenWeatherMap',
        'code' => $httpCode
    ]);
    exit;
}

// Renvoyer la réponse
echo $response;
