<?php
/**
 * ============================================================
 * FICHIER DE SÉCURITÉ CENTRALISÉ - NOVA Événements
 * ============================================================
 * Ce fichier contient toutes les fonctions et configurations
 * de sécurité du site. À inclure en premier dans chaque page.
 * VERSION: 2.0 - Pentest Ready
 * ============================================================
 */

// Définir la constante pour l'accès sécurisé aux fichiers config
define('NOVA_APP', true);

// ============================================================
// 0. FONCTIONS DE LOGGING SÉCURISÉ
// ============================================================

/**
 * Log sécurisé des événements de sécurité
 */
function securityLog($type, $message, $data = []) {
    $logDir = __DIR__ . '/../logs/';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . 'security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = getClientIp();
    $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 200);
    $uri = $_SERVER['REQUEST_URI'] ?? 'Unknown';
    
    $logEntry = sprintf(
        "[%s] [%s] [IP: %s] [URI: %s] %s %s\n",
        $timestamp,
        strtoupper($type),
        $ip,
        $uri,
        $message,
        !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : ''
    );
    
    // Écriture atomique
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// ============================================================
// 1. CONFIGURATION SÉCURISÉE DES SESSIONS
// ============================================================
function initSecureSession() {
    // Configurer les cookies de session AVANT session_start()
    if (session_status() === PHP_SESSION_NONE) {
        // Configuration sécurisée des cookies de session
        $cookieParams = [
            'lifetime' => 0,                    // Expire à la fermeture du navigateur
            'path' => '/',                      // Disponible sur tout le site
            'domain' => '',                     // Domaine actuel
            'secure' => isset($_SERVER['HTTPS']),  // HTTPS uniquement si disponible
            'httponly' => true,                 // Pas accessible via JavaScript
            'samesite' => 'Strict'              // Protection CSRF
        ];
        
        session_set_cookie_params($cookieParams);
        
        // Nom de session personnalisé (moins prévisible)
        session_name('NOVA_SID');
        
        session_start();
        
        // Vérifier l'intégrité de la session (fingerprint)
        $fingerprint = md5($_SERVER['HTTP_USER_AGENT'] ?? '' . $_SERVER['REMOTE_ADDR'] ?? '');
        if (!isset($_SESSION['_fingerprint'])) {
            $_SESSION['_fingerprint'] = $fingerprint;
        } elseif ($_SESSION['_fingerprint'] !== $fingerprint) {
            // Possible session hijacking - détruire la session
            securityLog('warning', 'Session fingerprint mismatch - possible hijacking');
            session_destroy();
            session_start();
            $_SESSION['_fingerprint'] = $fingerprint;
        }
        
        // Régénérer l'ID de session périodiquement pour éviter le fixation
        if (!isset($_SESSION['_last_regeneration'])) {
            $_SESSION['_last_regeneration'] = time();
        } elseif (time() - $_SESSION['_last_regeneration'] > 300) { // 5 minutes
            session_regenerate_id(true);
            $_SESSION['_last_regeneration'] = time();
        }
    }
}

// ============================================================
// 2. PROTECTION CSRF (Cross-Site Request Forgery)
// ============================================================

/**
 * Génère un token CSRF unique par session
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Retourne le champ HTML caché contenant le token CSRF
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

/**
 * Vérifie si le token CSRF est valide
 */
function verifyCsrfToken() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/**
 * Vérifie le token CSRF et arrête l'exécution si invalide
 */
function requireCsrfToken() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken()) {
            http_response_code(403);
            die('Erreur de sécurité : Token CSRF invalide. Veuillez rafraîchir la page et réessayer.');
        }
    }
}

// ============================================================
// 3. HEADERS DE SÉCURITÉ HTTP
// ============================================================
function setSecurityHeaders() {
    // Protection contre le clickjacking
    header('X-Frame-Options: DENY');
    
    // Protection XSS du navigateur
    header('X-XSS-Protection: 1; mode=block');
    
    // Empêche le navigateur de deviner le type MIME
    header('X-Content-Type-Options: nosniff');
    
    // Politique de référent
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy (basique - à adapter selon vos besoins)
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' https: data:;");
    
    // Permissions Policy
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
}

// ============================================================
// 4. FONCTIONS DE VALIDATION ET SANITIZATION
// ============================================================

/**
 * Nettoie et valide une chaîne de texte
 */
function sanitizeString($input, $maxLength = 255) {
    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return mb_substr($input, 0, $maxLength);
}

/**
 * Valide et nettoie un email
 */
function sanitizeEmail($email) {
    $email = trim($email);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

/**
 * Valide un entier positif
 */
function sanitizeInt($input, $min = 0, $max = PHP_INT_MAX) {
    $input = filter_var($input, FILTER_VALIDATE_INT);
    if ($input === false || $input < $min || $input > $max) {
        return false;
    }
    return $input;
}

/**
 * Valide une date au format Y-m-d
 */
function sanitizeDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return ($d && $d->format('Y-m-d') === $date) ? $date : false;
}

/**
 * Échappe les caractères spéciaux pour LIKE SQL
 */
function escapeLike($string) {
    return addcslashes($string, '%_\\');
}

/**
 * Valide un numéro de téléphone français
 */
function sanitizePhone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    if (preg_match('/^(\+33|0)[1-9][0-9]{8}$/', $phone)) {
        return $phone;
    }
    return false;
}

// ============================================================
// 5. SÉCURITÉ DES UPLOADS
// ============================================================

/**
 * Valide et traite un fichier uploadé de manière sécurisée
 */
function secureFileUpload($file, $uploadDir, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], $maxSize = 5242880) {
    $result = [
        'success' => false,
        'error' => '',
        'filename' => ''
    ];
    
    // Vérifier les erreurs d'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'Erreur lors de l\'upload du fichier.';
        return $result;
    }
    
    // Vérifier la taille
    if ($file['size'] > $maxSize) {
        $result['error'] = 'Le fichier est trop volumineux (max: ' . ($maxSize / 1024 / 1024) . ' Mo).';
        return $result;
    }
    
    // Vérifier le type MIME réel (pas l'extension!)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    if (!in_array($mimeType, $allowedTypes)) {
        $result['error'] = 'Type de fichier non autorisé.';
        return $result;
    }
    
    // Générer un nom de fichier sécurisé
    $extension = array_search($mimeType, [
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp'
    ], true);
    
    if ($extension === false) {
        $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        $extension = $extensions[$mimeType] ?? 'jpg';
    }
    
    $newFilename = bin2hex(random_bytes(16)) . '.' . $extension;
    $uploadPath = rtrim($uploadDir, '/') . '/' . $newFilename;
    
    // Créer le dossier si nécessaire (avec permissions sécurisées)
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        // Définir des permissions sécurisées sur le fichier
        chmod($uploadPath, 0644);
        $result['success'] = true;
        $result['filename'] = $newFilename;
    } else {
        $result['error'] = 'Impossible de déplacer le fichier uploadé.';
    }
    
    return $result;
}

// ============================================================
// 6. PROTECTION CONTRE LES ATTAQUES PAR FORCE BRUTE
// ============================================================

/**
 * Vérifie si l'IP est bloquée (trop de tentatives)
 */
function isIpBlocked($pdo, $ip) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
        $stmt->execute([$ip]);
        $result = $stmt->fetch();
        return ($result['attempts'] >= 5);
    } catch (PDOException $e) {
        return false; // En cas d'erreur, on laisse passer
    }
}

/**
 * Enregistre une tentative de connexion échouée
 */
function recordFailedAttempt($pdo, $ip) {
    try {
        $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, attempt_time) VALUES (?, NOW())");
        $stmt->execute([$ip]);
    } catch (PDOException $e) {
        // Table peut ne pas exister, ignorer silencieusement
    }
}

/**
 * Nettoie les anciennes tentatives
 */
function cleanOldAttempts($pdo) {
    try {
        $pdo->exec("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    } catch (PDOException $e) {
        // Ignorer
    }
}

/**
 * Obtient l'adresse IP réelle du client
 */
function getClientIp() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    // Si derrière un proxy (attention: peut être falsifié!)
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
    }
    
    return filter_var($ip, FILTER_VALIDATE_IP) ?: '0.0.0.0';
}

// ============================================================
// 7. FONCTIONS D'AUTHENTIFICATION SÉCURISÉES
// ============================================================

/**
 * Vérifie si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['user_email']) && !empty($_SESSION['user_email']);
}

/**
 * Requiert une connexion utilisateur, sinon redirige
 */
function requireLogin($redirectUrl = 'connexion.php') {
    if (!isLoggedIn()) {
        header('Location: ' . $redirectUrl);
        exit();
    }
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Vérifie si l'utilisateur a l'un des rôles spécifiés
 */
function hasAnyRole(array $roles) {
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], $roles);
}

/**
 * Requiert un rôle spécifique, sinon redirige
 */
function requireRole($role, $redirectUrl = 'profil.php') {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: ' . $redirectUrl);
        exit();
    }
}

/**
 * Déconnecte l'utilisateur de manière sécurisée
 */
function secureLogout() {
    // Détruire toutes les données de session
    $_SESSION = [];
    
    // Supprimer le cookie de session
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
    
    // Détruire la session
    session_destroy();
}

/**
 * Régénère l'ID de session (à appeler après connexion)
 */
function regenerateSession() {
    session_regenerate_id(true);
    $_SESSION['_last_regeneration'] = time();
}

// ============================================================
// 8. GESTION SÉCURISÉE DES ERREURS
// ============================================================

/**
 * Gestionnaire d'erreurs personnalisé pour la production
 */
function secureErrorHandler($errno, $errstr, $errfile, $errline) {
    // Logger l'erreur (à adapter avec votre système de logs)
    $logDir = __DIR__ . '/../logs/';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logMessage = date('[Y-m-d H:i:s]') . " Erreur [$errno]: $errstr dans $errfile ligne $errline\n";
    error_log($logMessage, 3, $logDir . 'errors.log');
    
    // En production, ne pas afficher les détails
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
        return true; // Ne pas exécuter le gestionnaire PHP par défaut
    }
    
    return false; // En développement, laisser PHP gérer
}

// ============================================================
// 9. VALIDATION DU MOT DE PASSE
// ============================================================

/**
 * Vérifie la force d'un mot de passe
 * Retourne un tableau avec 'valid' (bool) et 'errors' (array)
 */
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Le mot de passe doit contenir au moins une majuscule.';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Le mot de passe doit contenir au moins une minuscule.';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Le mot de passe doit contenir au moins un chiffre.';
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

// ============================================================
// 10. PROTECTION XSS AVANCÉE
// ============================================================

/**
 * Échappe les données pour affichage HTML sécurisé
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Échappe les données pour les attributs HTML
 */
function eAttr($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Échappe les données pour JavaScript
 */
function eJs($string) {
    return json_encode($string, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
}

// ============================================================
// 11. PROTECTION RATE LIMITING
// ============================================================

/**
 * Rate limiting global basé sur la session
 */
function checkRateLimit($key, $maxRequests = 100, $windowSeconds = 60) {
    $rateLimitKey = 'rate_limit_' . $key;
    
    if (!isset($_SESSION[$rateLimitKey])) {
        $_SESSION[$rateLimitKey] = ['count' => 0, 'time' => time()];
    }
    
    // Réinitialiser après la fenêtre de temps
    if (time() - $_SESSION[$rateLimitKey]['time'] > $windowSeconds) {
        $_SESSION[$rateLimitKey] = ['count' => 0, 'time' => time()];
    }
    
    // Vérifier la limite
    if ($_SESSION[$rateLimitKey]['count'] >= $maxRequests) {
        securityLog('rate_limit', "Rate limit exceeded for key: $key");
        return false;
    }
    
    $_SESSION[$rateLimitKey]['count']++;
    return true;
}

// ============================================================
// 12. VALIDATION D'URL SÉCURISÉE
// ============================================================

/**
 * Valide et nettoie une URL
 */
function sanitizeUrl($url, $allowedHosts = []) {
    $url = trim($url);
    
    // Vérifier que c'est une URL valide
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    // Vérifier le protocole (HTTP/HTTPS uniquement)
    $parsed = parse_url($url);
    if (!in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
        return false;
    }
    
    // Vérifier l'hôte si une liste blanche est fournie
    if (!empty($allowedHosts)) {
        if (!in_array($parsed['host'] ?? '', $allowedHosts)) {
            return false;
        }
    }
    
    return $url;
}

// ============================================================
// 13. PROTECTION CONTRE L'INJECTION D'EN-TÊTES
// ============================================================

/**
 * Nettoie une valeur pour l'utilisation dans les en-têtes HTTP
 */
function sanitizeHeader($value) {
    // Supprimer les caractères de nouvelle ligne (injection d'en-têtes)
    return str_replace(["\r", "\n", "\0"], '', $value);
}

/**
 * Redirection sécurisée
 */
function secureRedirect($url) {
    // Liste blanche des URLs internes autorisées
    $allowedPaths = [
        'index.php', 'profil.php', 'connexion.php', 'evenement.php', 
        'admin.php', 'organisateur.php'
    ];
    
    // Vérifier si c'est une URL relative interne
    $cleanUrl = sanitizeHeader($url);
    $isAllowed = false;
    
    foreach ($allowedPaths as $path) {
        if (strpos($cleanUrl, $path) === 0 || $cleanUrl === $path) {
            $isAllowed = true;
            break;
        }
    }
    
    if (!$isAllowed) {
        $cleanUrl = 'index.php';
    }
    
    header('Location: ' . $cleanUrl);
    exit();
}

// ============================================================
// 14. PROTECTION CLICKJACKING (Frame Busting JS)
// ============================================================

/**
 * Génère le code JavaScript anti-clickjacking
 */
function antiClickjackingScript() {
    return '<script>if(self!==top){top.location=self.location;}</script>';
}

// ============================================================
// 15. NETTOYAGE DES DONNÉES D'ENTRÉE GLOBALES
// ============================================================

/**
 * Nettoie les superglobales dangereuses
 */
function sanitizeGlobals() {
    // Nettoyer $_GET
    if (!empty($_GET)) {
        foreach ($_GET as $key => $value) {
            if (is_string($value)) {
                $_GET[$key] = strip_tags(trim($value));
            }
        }
    }
    
    // Supprimer les variables GLOBALS potentiellement dangereuses
    unset($GLOBALS['_REQUEST']);
}

// ============================================================
// 16. INITIALISATION AUTOMATIQUE
// ============================================================

// Définir l'environnement (à changer en 'production' sur le serveur)
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

// Nettoyer les globales
sanitizeGlobals();

// Initialiser la session sécurisée
initSecureSession();

// Définir les headers de sécurité
setSecurityHeaders();

// En production, masquer les erreurs PHP
if (ENVIRONMENT === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
    set_error_handler('secureErrorHandler');
}
