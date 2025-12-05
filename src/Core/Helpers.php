<?php
/**
 * ============================================================
 * HELPERS - NOVA Événements
 * ============================================================
 * Fonctions utilitaires globales
 * ============================================================
 */

use App\Core\Security;

/**
 * Échapper pour HTML (protection XSS)
 */
function e(?string $string): string
{
    return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Échapper pour attribut HTML
 */
function eAttr(?string $string): string
{
    return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Échapper pour JavaScript
 */
function eJs($value): string
{
    return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
}

/**
 * Obtenir le nonce CSP
 */
function cspNonce(): string
{
    return Security::getNonce();
}

/**
 * Générer le champ CSRF
 */
function csrf_field(): string
{
    return Security::csrfField();
}

/**
 * Vérifier si l'utilisateur est connecté
 */
function isLoggedIn(): bool
{
    return Security::isLoggedIn();
}

/**
 * Obtenir l'URL de base
 */
function baseUrl(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Générer une URL interne (pour les liens)
 */
function url(string $path = ''): string
{
    $basePath = defined('BASE_PATH') ? BASE_PATH : '';
    return rtrim($basePath, '/') . '/' . ltrim($path, '/');
}

/**
 * Obtenir le chemin d'un asset
 */
function asset(string $path): string
{
    return baseUrl($path);
}

/**
 * Redirection sécurisée
 */
function redirect(string $url): void
{
    // Liste blanche des chemins autorisés
    $allowedPrefixes = ['/', '/accueil', '/connexion', '/profil', '/evenements', '/admin', '/organisateur', '/api'];
    
    $isAllowed = false;
    foreach ($allowedPrefixes as $prefix) {
        if (strpos($url, $prefix) === 0) {
            $isAllowed = true;
            break;
        }
    }
    
    if (!$isAllowed) {
        $url = '/';
    }
    
    // Nettoyer l'URL
    $url = str_replace(["\r", "\n", "\0"], '', $url);
    
    // Ajouter le BASE_PATH si nécessaire
    $basePath = defined('BASE_PATH') ? BASE_PATH : '';
    $fullUrl = rtrim($basePath, '/') . '/' . ltrim($url, '/');
    
    header('Location: ' . $fullUrl);
    exit;
}

/**
 * Nettoyer et valider une chaîne
 */
function sanitizeString(?string $input, int $maxLength = 255): string
{
    if ($input === null) {
        return '';
    }
    $input = trim($input);
    $input = strip_tags($input);
    return mb_substr($input, 0, $maxLength);
}

/**
 * Valider et nettoyer un email
 */
function sanitizeEmail(?string $email): ?string
{
    if ($email === null) {
        return null;
    }
    $email = trim($email);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
}

/**
 * Valider un entier
 */
function sanitizeInt($input, int $min = 0, int $max = PHP_INT_MAX): ?int
{
    $input = filter_var($input, FILTER_VALIDATE_INT);
    if ($input === false || $input < $min || $input > $max) {
        return null;
    }
    return $input;
}

/**
 * Valider une date
 */
function sanitizeDate(?string $date): ?string
{
    if ($date === null) {
        return null;
    }
    $d = \DateTime::createFromFormat('Y-m-d', $date);
    return ($d && $d->format('Y-m-d') === $date) ? $date : null;
}

/**
 * Valider un numéro de téléphone français
 */
function sanitizePhone(?string $phone): ?string
{
    if ($phone === null) {
        return null;
    }
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    if (preg_match('/^(\+33|0)[1-9][0-9]{8}$/', $phone)) {
        return $phone;
    }
    return null;
}

/**
 * Valider la force d'un mot de passe
 */
function validatePassword(string $password): array
{
    $errors = [];
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Le mot de passe doit contenir au moins ' . PASSWORD_MIN_LENGTH . ' caractères.';
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

/**
 * Hasher un mot de passe de manière sécurisée
 */
function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_COST]);
}

/**
 * Vérifier un mot de passe
 */
function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

/**
 * Formater une date en français
 */
function formatDateFr(string $date): string
{
    $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 
               'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $d = new \DateTime($date);
    return $d->format('d') . ' ' . $months[$d->format('n') - 1] . ' ' . $d->format('Y');
}

/**
 * Formater l'heure
 */
function formatTime(string $time): string
{
    return substr($time, 0, 5);
}

/**
 * Calculer l'âge
 */
function calculateAge(string $birthDate): int
{
    $birth = new \DateTime($birthDate);
    $today = new \DateTime('today');
    return $birth->diff($today)->y;
}

/**
 * Inclure une vue
 */
function view(string $name, array $data = []): void
{
    extract($data);
    $nonce = cspNonce();
    include ROOT_PATH . '/src/Views/' . $name . '.php';
}

/**
 * Inclure un template partiel
 */
function partial(string $name, array $data = []): void
{
    extract($data);
    $nonce = cspNonce();
    include ROOT_PATH . '/src/Views/partials/' . $name . '.php';
}
