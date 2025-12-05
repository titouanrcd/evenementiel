# 📋 RAPPORT DE PROJET - NOVA Événements
## Plateforme de Gestion d'Événements Étudiants

**Niveau** : BAC+1 Informatique  
**Date** : Décembre 2025  
**Auteur** : [Votre Nom]

---

# 📑 SOMMAIRE

1. [Introduction](#1-introduction)
2. [Frontend - Interface Utilisateur](#2-frontend---interface-utilisateur)
3. [Backend - Serveur et Base de Données](#3-backend---serveur-et-base-de-données)
4. [Sécurisation](#4-sécurisation)
5. [Conclusion](#5-conclusion)

---

# 1. INTRODUCTION

## 1.1 Présentation du Projet

**NOVA Événements** est une plateforme web de gestion d'événements destinée aux étudiants. Elle permet de :

- 🎫 **Découvrir** des événements (concerts, conférences, festivals...)
- 📝 **S'inscrire** aux événements
- 🎭 **Créer** et gérer ses propres événements (pour les organisateurs)
- 👤 **Gérer** son profil utilisateur
- ☁️ **Consulter** la météo des lieux d'événements

## 1.2 Technologies Utilisées

| Catégorie | Technologies |
|-----------|--------------|
| **Frontend** | HTML5, CSS3, JavaScript (ES6+) |
| **Backend** | PHP 8.2 (Architecture MVC) |
| **Base de données** | MySQL/MariaDB |
| **Serveur** | Apache (XAMPP) |
| **APIs externes** | OpenWeatherMap (météo) |

## 1.3 Architecture du Projet

```
evenementiel/
├── public/           # Point d'entrée (accessible au web)
│   ├── index.php     # Front Controller
│   ├── css/          # Feuilles de style
│   ├── js/           # Scripts JavaScript
│   └── .htaccess     # Règles Apache
├── src/              # Code source (non accessible)
│   ├── Controllers/  # Contrôleurs MVC
│   ├── Core/         # Classes système
│   └── Views/        # Vues (templates)
├── config/           # Configuration
├── uploads/          # Fichiers uploadés
└── logs/             # Journaux d'erreurs
```

---

# 2. FRONTEND - Interface Utilisateur

## 2.1 HTML5 - Structure des Pages

Le HTML5 structure le contenu de chaque page. Nous utilisons des balises sémantiques pour une meilleure accessibilité.

### Exemple - Structure d'une carte d'événement :
```html
<article class="event-card">
    <div class="event-image">
        <img src="image.jpg" alt="Nom événement">
        <div class="event-date-badge">
            <span class="day">25</span>
            <span class="month">DEC</span>
        </div>
    </div>
    <div class="event-info">
        <h3 class="event-title">Concert Rock</h3>
        <p class="event-location">Paris, Olympia</p>
        <p class="event-price">25€</p>
    </div>
</article>
```

### Formulaires avec validation :
```html
<form method="POST" action="/connexion">
    <input type="hidden" name="csrf_token" value="...">
    <input type="email" name="email" required>
    <input type="password" name="password" minlength="8" required>
    <button type="submit">Connexion</button>
</form>
```

## 2.2 CSS3 - Mise en Forme

### Variables CSS (Design System) :
```css
:root {
    --nova-pink: #ff00cc;
    --nova-orange: #ff9900;
    --nova-gradient: linear-gradient(135deg, #ff00cc, #ff9900);
    --bg-dark: #0a0a0a;
    --text-gray: #b0b0b0;
}
```

### Flexbox et Grid pour les layouts :
```css
/* Grille d'événements responsive */
.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
}

/* Centrage avec Flexbox */
.event-card {
    display: flex;
    flex-direction: column;
}
```

### Animations CSS :
```css
/* Animation de dégradé sur le titre */
@keyframes shimmer {
    0%, 100% { background-position: 0% center; }
    50% { background-position: 100% center; }
}

.title {
    background: linear-gradient(135deg, #fff, #667eea, #ff0096);
    animation: shimmer 3s ease infinite;
}
```

### Media Queries (Responsive) :
```css
/* Mobile */
@media (max-width: 768px) {
    .events-container {
        grid-template-columns: 1fr;
    }
    .events-filters {
        display: none; /* Menu burger sur mobile */
    }
}
```

## 2.3 JavaScript - Interactivité

### Appel API Météo avec Fetch :
```javascript
async function fetchWeather(city) {
    const response = await fetch(`/api/weather?city=${city}`);
    const data = await response.json();
    
    return {
        temp: Math.round(data.main.temp),
        description: data.weather[0].description,
        icon: data.weather[0].icon
    };
}
```

### Mise à jour dynamique du DOM :
```javascript
function updateWeatherBadge(badge, weatherData) {
    badge.innerHTML = `
        <img src="${getWeatherIcon(weatherData.icon)}" class="weather-icon">
        <span class="weather-temp">${weatherData.temp}°C</span>
    `;
}
```

### Validation de formulaire :
```javascript
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    
    if (password.length < 8) {
        e.preventDefault();
        alert('Le mot de passe doit contenir au moins 8 caractères');
    }
});
```

---

# 3. BACKEND - Serveur et Base de Données

## 3.1 XAMPP - Environnement de Développement

**XAMPP** est un package qui inclut :
- **Apache** : Serveur web (traite les requêtes HTTP)
- **MySQL/MariaDB** : Base de données relationnelle
- **PHP** : Langage de programmation côté serveur
- **phpMyAdmin** : Interface web pour gérer la BDD

### Configuration Apache (.htaccess) :
```apache
# Activer la réécriture d'URL
RewriteEngine On

# Rediriger tout vers index.php
RewriteRule ^(.*)$ index.php [QSA,L]

# Bloquer l'accès aux fichiers sensibles
<FilesMatch "\.(env|log|sql)$">
    Deny from all
</FilesMatch>
```

## 3.2 Base de Données MySQL

### Schéma de la base :

```
┌─────────────────┐       ┌─────────────────┐
│     users       │       │     event       │
├─────────────────┤       ├─────────────────┤
│ email (PK)      │       │ id_event (PK)   │
│ user            │       │ name            │
│ password        │◄──────│ owner_email (FK)│
│ role            │       │ event_date      │
│ date_of_birth   │       │ lieu            │
│ created_at      │       │ prix            │
└─────────────────┘       │ capacite        │
        │                 │ status          │
        │                 │ tag             │
        │                 └─────────────────┘
        │                         │
        │    ┌─────────────────┐  │
        │    │  inscriptions   │  │
        │    ├─────────────────┤  │
        └───►│ user_email (FK) │◄─┘
             │ id_event (FK)   │
             │ statut          │
             │ date_inscription│
             └─────────────────┘
```


## 3.3 PHP - Architecture MVC

### Pattern MVC (Modèle-Vue-Contrôleur) :

```
Requête → Router → Controller → Model (BDD)
                        ↓
              Réponse ← View
```

### Connexion PDO sécurisée :
```php
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false  // Sécurité!
]);
```

### Requête préparée (anti-injection SQL) :
```php
// ❌ DANGEREUX - Injection SQL possible
$sql = "SELECT * FROM users WHERE email = '$email'";

// ✅ SÉCURISÉ - Requête préparée
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();
```

### Exemple de Contrôleur :
```php
class EventController extends Controller
{
    public function index(): void
    {
        // Récupérer les événements publiés
        $events = $this->db->fetchAll(
            "SELECT * FROM event WHERE status = 'publié'"
        );
        
        // Afficher la vue avec les données
        $this->render('events/index', [
            'events' => $events
        ]);
    }
}
```

## 3.4 API OpenWeatherMap

### Appel à l'API externe :
```php
public function weather(): void
{
    $city = $_GET['city'];
    $apiKey = OPENWEATHER_API_KEY;
    
    $url = "https://api.openweathermap.org/data/2.5/weather";
    $url .= "?q={$city}&appid={$apiKey}&units=metric&lang=fr";
    
    $response = file_get_contents($url);
    $this->json(json_decode($response, true));
}
```

---

# 4. SÉCURISATION

## 4.1 Protection XSS (Cross-Site Scripting)

**Problème** : Un attaquant peut injecter du code JavaScript malveillant.

**Solution** : Échapper toutes les sorties HTML avec `htmlspecialchars()`.

```php
// Fonction d'échappement
function e(string $string): string {
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// Utilisation dans les vues
<h1><?= e($event['name']) ?></h1>  <!-- Sécurisé -->
<h1><?= $event['name'] ?></h1>     <!-- ❌ Dangereux -->
```

## 4.2 Protection CSRF (Cross-Site Request Forgery)

**Problème** : Un site malveillant peut faire exécuter des actions à l'utilisateur connecté.

**Solution** : Token CSRF unique par session.

```php
// Génération du token
public static function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Vérification
public static function verifyCsrfToken(?string $token): bool {
    return hash_equals($_SESSION['csrf_token'], $token);
}
```

```html
<form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
</form>
```

## 4.3 Protection Injection SQL

**Problème** : Un attaquant peut modifier les requêtes SQL.

**Solution** : Requêtes préparées avec paramètres liés.

```php
// ❌ VULNÉRABLE
$email = $_POST['email'];
$sql = "SELECT * FROM users WHERE email = '$email'";
// Attaque: ' OR '1'='1

// ✅ SÉCURISÉ
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$_POST['email']]);
```

## 4.4 Hashage des Mots de Passe

**Problème** : Si la BDD est volée, les mots de passe seraient visibles.

**Solution** : Hashage bcrypt (irréversible).

```php
// Enregistrement
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Vérification (connexion)
if (password_verify($password, $user['password'])) {
    // Mot de passe correct
}
```

## 4.5 Content Security Policy (CSP)

**Problème** : Exécution de scripts non autorisés.

**Solution** : En-tête CSP avec nonce.

```php
$nonce = base64_encode(random_bytes(16));

$csp = [
    "default-src 'self'",
    "script-src 'self' 'nonce-{$nonce}'",
    "style-src 'self' 'nonce-{$nonce}' https://fonts.googleapis.com"
];
header('Content-Security-Policy: ' . implode('; ', $csp));
```

```html
<script nonce="<?= $nonce ?>">
    // Ce script est autorisé
</script>
```

## 4.6 Protection Brute Force

**Problème** : Attaquant teste des milliers de mots de passe.

**Solution** : Bloquer l'IP après 5 tentatives.

```php
// Vérifier si l'IP est bloquée
public static function isIpBlocked(PDO $pdo, string $ip): bool {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM login_attempts 
         WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
    );
    $stmt->execute([$ip]);
    return $stmt->fetchColumn() >= 5;
}
```

## 4.7 Upload Sécurisé

**Problème** : Upload de fichiers malveillants (ex: PHP).

**Solution** : Validation du type MIME réel.

```php
// Vérifier le type MIME réel (pas l'extension!)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($mimeType, $allowedTypes)) {
    throw new Exception('Type de fichier non autorisé');
}

// Nom de fichier aléatoire (évite les conflits et attaques)
$newFilename = bin2hex(random_bytes(16)) . '.jpg';
```

## 4.8 Sessions Sécurisées

```php
session_set_cookie_params([
    'lifetime' => 0,           // Expire à la fermeture du navigateur
    'path' => '/',
    'secure' => true,          // HTTPS uniquement
    'httponly' => true,        // Pas accessible en JavaScript
    'samesite' => 'Strict'     // Protection CSRF
]);
```

## 4.9 En-têtes HTTP de Sécurité

```php
header('X-Frame-Options: DENY');           // Anti-clickjacking
header('X-Content-Type-Options: nosniff'); // Pas de sniffing MIME
header('X-XSS-Protection: 1; mode=block'); // Protection XSS navigateur
header('Referrer-Policy: strict-origin');  // Contrôle du referer
```

## 4.10 Tableau Récapitulatif des Protections

| Menace | Protection | Implémentation |
|--------|------------|----------------|
| **XSS** | Échappement HTML | `htmlspecialchars()` |
| **CSRF** | Token unique | `$_SESSION['csrf_token']` |
| **SQL Injection** | Requêtes préparées | `PDO::prepare()` |
| **Vol de MDP** | Hashage bcrypt | `password_hash()` |
| **Brute Force** | Blocage IP | Table `login_attempts` |
| **Upload malveillant** | Validation MIME | `finfo_file()` |
| **Clickjacking** | X-Frame-Options | Header HTTP |
| **Scripts non autorisés** | CSP + Nonce | Header CSP |

---

# 5. CONCLUSION

## 5.1 Résumé Technique

Ce projet démontre la mise en œuvre d'une application web complète avec :

✅ **Frontend moderne** : HTML5 sémantique, CSS3 avec Flexbox/Grid, JavaScript ES6+  
✅ **Backend robuste** : Architecture MVC, PHP 8.2, PDO  
✅ **Base de données** : MySQL avec relations et contraintes d'intégrité  
✅ **Sécurité multicouche** : 10+ mécanismes de protection  

## 5.2 Compétences Acquises

| Domaine | Compétences |
|---------|-------------|
| **Frontend** | Responsive design, animations CSS, appels API asynchrones |
| **Backend** | Architecture MVC, gestion sessions, requêtes SQL sécurisées |
| **Sécurité** | Protection OWASP Top 10, CSP, hashage cryptographique |
| **DevOps** | Configuration serveur Apache, gestion des logs |

## 5.3 Améliorations Futures

- 🔐 Authentification à deux facteurs (2FA)
- 📧 Système de notifications par email
- 💳 Intégration paiement (Stripe)
- 📱 Application mobile (React Native)
- 🔍 Recherche full-text avec Elasticsearch

---

**Document rédigé dans le cadre du projet NOVA Événements**  
**Décembre 2025**
