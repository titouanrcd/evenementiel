<?php
/**
 * ============================================================
 * CONTRÔLEUR API - NOVA Événements
 * ============================================================
 */

namespace App\Controllers;

use App\Core\Security;

class ApiController extends Controller
{
    /**
     * API Météo (proxy)
     */
    public function weather(): void
    {
        // Rate limiting
        if (!Security::checkRateLimit('weather_api', 30, 60)) {
            $this->json(['error' => true, 'message' => 'Trop de requêtes'], 429);
            return;
        }
        
        $city = sanitizeString($_GET['city'] ?? '', 100);
        $type = in_array($_GET['type'] ?? '', ['weather', 'forecast']) ? $_GET['type'] : 'weather';
        
        // Validation du nom de ville
        $city = preg_replace('/[^\p{L}\s\-\'\.]/u', '', $city);
        
        if (empty($city) || mb_strlen($city) < 2) {
            $this->json(['error' => true, 'message' => 'Ville non spécifiée ou invalide'], 400);
            return;
        }
        
        // Cache
        $cacheDir = ROOT_PATH . '/logs/weather_cache/';
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        
        $cacheKey = md5($city . '_' . $type);
        $cacheFile = $cacheDir . $cacheKey . '.json';
        $cacheDuration = 600; // 10 minutes
        
        // Vérifier le cache
        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheDuration) {
            $data = file_get_contents($cacheFile);
            header('Content-Type: application/json; charset=utf-8');
            header('X-Cache: HIT');
            echo $data;
            exit;
        }
        
        // Appel API
        $apiKey = OPENWEATHER_API_KEY;
        $baseUrl = $type === 'forecast' 
            ? 'https://api.openweathermap.org/data/2.5/forecast'
            : 'https://api.openweathermap.org/data/2.5/weather';
        
        $url = sprintf(
            '%s?q=%s&appid=%s&units=metric&lang=fr',
            $baseUrl,
            urlencode($city),
            $apiKey
        );
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'NOVA-Events/1.0'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            $this->json(['error' => true, 'message' => 'Erreur lors de la récupération des données météo'], 503);
            return;
        }
        
        // Valider la réponse JSON
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->json(['error' => true, 'message' => 'Réponse API invalide'], 500);
            return;
        }
        
        // Sauvegarder en cache
        file_put_contents($cacheFile, $response, LOCK_EX);
        
        header('Content-Type: application/json; charset=utf-8');
        header('X-Cache: MISS');
        echo $response;
        exit;
    }
}
