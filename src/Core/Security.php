<?php
/**
 * ============================================================
 * SÉCURITÉ - NOVA Événements
 * ============================================================
 * Classe centrale de sécurité avec CSP nonce
 * ============================================================
 */

namespace App\Core;

class Security
{
    private static ?string $nonce = null;
    
    /**
     * Initialiser la sécurité
     */
    public function init(): void
    {
        $this->initSession();
        $this->setSecurityHeaders();
        $this->sanitizeGlobals();
    }
    
    /**
     * Générer un nonce unique pour CSP
     */
    public static function generateNonce(): string
    {
        if (self::$nonce === null) {
            self::$nonce = base64_encode(random_bytes(16));
        }
        return self::$nonce;
    }
    
    /**
     * Obtenir le nonce actuel
     */
    public static function getNonce(): string
    {
        if (self::$nonce === null) {
            self::generateNonce();
        }
        return self::$nonce;
    }
    
    /**
     * Initialiser la session sécurisée
     */
    private function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $cookieParams = [
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Strict'
            ];
            
            session_set_cookie_params($cookieParams);
            session_name('NOVA_SID');
            session_start();
            
            // Vérifier l'intégrité de la session
            $this->verifySessionIntegrity();
            
            // Régénérer l'ID périodiquement
            $this->regenerateSessionPeriodically();
        }
    }
    
    /**
     * Vérifier l'intégrité de la session
     */
    private function verifySessionIntegrity(): void
    {
        $fingerprint = $this->generateFingerprint();
        
        if (!isset($_SESSION['_fingerprint'])) {
            $_SESSION['_fingerprint'] = $fingerprint;
        } elseif (!hash_equals($_SESSION['_fingerprint'], $fingerprint)) {
            // Possible session hijacking
            $this->logSecurityEvent('session_hijacking', 'Fingerprint mismatch');
            $this->destroySession();
            $_SESSION['_fingerprint'] = $fingerprint;
        }
    }
    
    /**
     * Générer une empreinte de session
     */
    private function generateFingerprint(): string
    {
        return hash('sha256', 
            ($_SERVER['HTTP_USER_AGENT'] ?? '') . 
            ($_SERVER['REMOTE_ADDR'] ?? '')
        );
    }
    
    /**
     * Régénérer l'ID de session périodiquement
     */
    private function regenerateSessionPeriodically(): void
    {
        if (!isset($_SESSION['_last_regeneration'])) {
            $_SESSION['_last_regeneration'] = time();
        } elseif (time() - $_SESSION['_last_regeneration'] > 300) {
            session_regenerate_id(true);
            $_SESSION['_last_regeneration'] = time();
        }
    }
    
    /**
     * Définir les headers de sécurité HTTP avec CSP nonce
     */
    private function setSecurityHeaders(): void
    {
        // Générer le nonce
        $nonce = self::generateNonce();
        
        // Protection contre le clickjacking
        header('X-Frame-Options: DENY');
        
        // Protection XSS du navigateur
        header('X-XSS-Protection: 1; mode=block');
        
        // Empêche le navigateur de deviner le type MIME
        header('X-Content-Type-Options: nosniff');
        
        // Politique de référent
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy avec nonce
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'",
            "style-src 'self' 'nonce-{$nonce}' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "img-src 'self' https: data:",
            "connect-src 'self' https://api.openweathermap.org https://maps.googleapis.com",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "upgrade-insecure-requests"
        ];
        header('Content-Security-Policy: ' . implode('; ', $csp));
        
        // Permissions Policy
        header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
        
        // HSTS (uniquement en HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
    
    /**
     * Nettoyer les superglobales
     */
    private function sanitizeGlobals(): void
    {
        // Nettoyer $_GET
        if (!empty($_GET)) {
            $_GET = $this->sanitizeArray($_GET);
        }
        
        // Supprimer $_REQUEST (potentiellement dangereux)
        $_REQUEST = [];
    }
    
    /**
     * Nettoyer un tableau de manière récursive
     */
    private function sanitizeArray(array $data): array
    {
        $cleaned = [];
        foreach ($data as $key => $value) {
            $key = $this->sanitizeKey($key);
            if (is_array($value)) {
                $cleaned[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $cleaned[$key] = strip_tags(trim($value));
            } else {
                $cleaned[$key] = $value;
            }
        }
        return $cleaned;
    }
    
    /**
     * Nettoyer une clé
     */
    private function sanitizeKey(string $key): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $key);
    }
    
    /**
     * Générer un token CSRF
     */
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Vérifier le token CSRF
     */
    public static function verifyCsrfToken(?string $token = null): bool
    {
        $token = $token ?? ($_POST['csrf_token'] ?? '');
        
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Exiger un token CSRF valide
     */
    public static function requireCsrfToken(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !self::verifyCsrfToken()) {
            http_response_code(403);
            die('Token CSRF invalide');
        }
    }
    
    /**
     * Générer le champ HTML CSRF
     */
    public static function csrfField(): string
    {
        $token = self::generateCsrfToken();
        return sprintf('<input type="hidden" name="csrf_token" value="%s">', e($token));
    }
    
    /**
     * Vérifier si l'utilisateur est connecté
     */
    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['user_email']);
    }
    
    /**
     * Exiger une connexion
     */
    public static function requireLogin(string $redirect = '/connexion'): void
    {
        if (!self::isLoggedIn()) {
            header('Location: ' . $redirect);
            exit;
        }
    }
    
    /**
     * Vérifier le rôle de l'utilisateur
     */
    public static function hasRole(string $role): bool
    {
        return ($_SESSION['user_role'] ?? '') === $role;
    }
    
    /**
     * Vérifier si l'utilisateur a l'un des rôles
     */
    public static function hasAnyRole(array $roles): bool
    {
        return in_array($_SESSION['user_role'] ?? '', $roles, true);
    }
    
    /**
     * Exiger un rôle spécifique
     */
    public static function requireRole(string $role, string $redirect = '/profil'): void
    {
        self::requireLogin();
        if (!self::hasRole($role)) {
            header('Location: ' . $redirect);
            exit;
        }
    }
    
    /**
     * Déconnexion sécurisée
     */
    public static function logout(): void
    {
        $_SESSION = [];
        
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        session_destroy();
    }
    
    /**
     * Régénérer l'ID de session
     */
    public static function regenerateSession(): void
    {
        session_regenerate_id(true);
        $_SESSION['_last_regeneration'] = time();
    }
    
    /**
     * Détruire la session
     */
    private function destroySession(): void
    {
        session_destroy();
        session_start();
    }
    
    /**
     * Logger un événement de sécurité
     */
    public static function logSecurityEvent(string $type, string $message, array $data = []): void
    {
        $logDir = ROOT_PATH . '/logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logEntry = sprintf(
            "[%s] [%s] [IP: %s] [URI: %s] %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($type),
            self::getClientIp(),
            $_SERVER['REQUEST_URI'] ?? 'Unknown',
            $message,
            !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : ''
        );
        
        file_put_contents(
            $logDir . 'security.log',
            $logEntry,
            FILE_APPEND | LOCK_EX
        );
    }
    
    /**
     * Obtenir l'adresse IP du client
     */
    public static function getClientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        // Si derrière un proxy de confiance
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        }
        
        return filter_var($ip, FILTER_VALIDATE_IP) ?: '0.0.0.0';
    }
    
    /**
     * Vérifier si l'IP est bloquée
     */
    public static function isIpBlocked(\PDO $pdo, ?string $ip = null): bool
    {
        $ip = $ip ?? self::getClientIp();
        
        try {
            $stmt = $pdo->prepare(
                "SELECT COUNT(*) as attempts FROM login_attempts 
                 WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
            );
            $stmt->execute([$ip]);
            $result = $stmt->fetch();
            return ($result['attempts'] ?? 0) >= MAX_LOGIN_ATTEMPTS;
        } catch (\PDOException $e) {
            return false;
        }
    }
    
    /**
     * Enregistrer une tentative de connexion échouée
     */
    public static function recordFailedAttempt(\PDO $pdo, ?string $ip = null): void
    {
        $ip = $ip ?? self::getClientIp();
        
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO login_attempts (ip_address, attempt_time) VALUES (?, NOW())"
            );
            $stmt->execute([$ip]);
        } catch (\PDOException $e) {
            // Table peut ne pas exister
        }
    }
    
    /**
     * Nettoyer les anciennes tentatives
     */
    public static function cleanOldAttempts(\PDO $pdo): void
    {
        try {
            $pdo->exec("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        } catch (\PDOException $e) {
            // Ignorer
        }
    }
    
    /**
     * Rate limiting basé sur la session
     */
    public static function checkRateLimit(string $key, int $maxRequests = 100, int $windowSeconds = 60): bool
    {
        $rateLimitKey = 'rate_limit_' . $key;
        
        if (!isset($_SESSION[$rateLimitKey])) {
            $_SESSION[$rateLimitKey] = ['count' => 0, 'time' => time()];
        }
        
        if (time() - $_SESSION[$rateLimitKey]['time'] > $windowSeconds) {
            $_SESSION[$rateLimitKey] = ['count' => 0, 'time' => time()];
        }
        
        if ($_SESSION[$rateLimitKey]['count'] >= $maxRequests) {
            self::logSecurityEvent('rate_limit', "Rate limit exceeded for key: {$key}");
            return false;
        }
        
        $_SESSION[$rateLimitKey]['count']++;
        return true;
    }
}
