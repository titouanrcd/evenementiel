# RAPPORT TECHNIQUE
## Projet NOVA Événements

---

| | |
|---|---|
| **Projet** | Application Web de Gestion d'Événements |
| **Version** | 1.0 |
| **Date** | 04 Décembre 2025 |
| **Auteur** | [Votre Nom] |
| **Classification** | Document Technique |

---

## SOMMAIRE

1. [INTRODUCTION](#1-introduction)
   - 1.1 Contexte
   - 1.2 Objectifs
   - 1.3 Stack Technique

2. [FRONT-END](#2-front-end)
   - 2.1 Architecture CSS
   - 2.2 Composants UI
   - 2.3 Responsive Design
   - 2.4 Interactions JavaScript

3. [BACK-END](#3-back-end)
   - 3.1 Architecture Applicative
   - 3.2 Connexion Base de Données
   - 3.3 Gestion des Sessions
   - 3.4 Traitement des Formulaires
   - 3.5 Requêtes SQL

4. [DEVSECOPS](#4-devsecops)
   - 4.1 Protection CSRF
   - 4.2 Protection XSS
   - 4.3 Protection SQL Injection
   - 4.4 Sanitization des Entrées
   - 4.5 Gestion des Mots de Passe
   - 4.6 Protection Brute Force
   - 4.7 Sécurité Apache
   - 4.8 Matrice des Risques

5. [CONCLUSION](#5-conclusion)

---

## 1. INTRODUCTION

### 1.1 Contexte

Le projet **NOVA Événements** répond au besoin de digitaliser la gestion d'événements étudiants. L'application permet aux utilisateurs de consulter, s'inscrire et gérer des événements, tout en offrant des outils d'administration aux organisateurs.

### 1.2 Objectifs

- Développer une interface utilisateur moderne et responsive
- Implémenter un système d'authentification sécurisé
- Assurer la protection des données utilisateurs
- Respecter les bonnes pratiques de développement sécurisé

### 1.3 Stack Technique

| Couche | Technologie | Version |
|--------|-------------|---------|
| Serveur | Apache (XAMPP) | 2.4 |
| Backend | PHP | 8.2.12 |
| Base de données | MariaDB | 10.4.32 |
| Frontend | HTML5, CSS3, JS | - |
| APIs | OpenWeatherMap, Google Maps | - |

---

## 2. FRONT-END

### 2.1 Architecture CSS

L'architecture CSS adopte une approche **modulaire** permettant une meilleure maintenabilité du code. Le fichier principal `style.css` centralise les imports :

```css
@import url("base/variables.css");
@import url("components/ui-elements.css");
@import url("layout/navigation.css");
@import url("sections/events.css");
```

Cette organisation sépare les préoccupations :
- `base/` : Variables globales et reset
- `components/` : Éléments réutilisables
- `layout/` : Structure de page
- `sections/` : Styles spécifiques par page

Les **variables CSS** centralisent la charte graphique :

```css
:root {
  --nova-gradient: linear-gradient(135deg, #ff00cc 0%, #ff9900 100%);
  --nova-pink: #ff00cc;
  --card-bg: #121212;
}
```

### 2.2 Composants UI

Le système de **popup flottant** pour l'affichage des itinéraires utilise un positionnement absolu avec animation d'apparition :

```css
.location-popup {
    position: absolute;
    bottom: calc(100% + 8px);
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    animation: popupFadeIn 0.2s ease;
}
```

L'animation `popupFadeIn` assure une transition fluide :

```css
@keyframes popupFadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
```

### 2.3 Responsive Design

L'approche **mobile-first** adapte l'interface selon les breakpoints :

```css
@media (max-width: 768px) {
    .events-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 480px) {
    .events-grid { grid-template-columns: 1fr; }
}
```

### 2.4 Interactions JavaScript

La gestion des popups utilise la **délégation d'événements** pour optimiser les performances :

```javascript
document.querySelectorAll('.location-clickable').forEach(function(el) {
    el.addEventListener('click', function(e) {
        e.stopPropagation();
        document.querySelectorAll('.location-popup.active')
            .forEach(p => p.classList.remove('active'));
        this.nextElementSibling?.classList.add('active');
    });
});
```

La fermeture automatique au clic extérieur améliore l'UX :

```javascript
document.addEventListener('click', function(e) {
    if (!e.target.closest('.event-location')) {
        document.querySelectorAll('.location-popup.active')
            .forEach(p => p.classList.remove('active'));
    }
});
```

---

## 3. BACK-END

### 3.1 Architecture Applicative

L'application suit une architecture **MVC simplifiée** où chaque fichier PHP combine la logique métier et la vue. L'ordre d'inclusion est standardisé :

```php
require_once 'security.php';  // Sécurité EN PREMIER
require_once 'db.php';        // Connexion BDD
```

### 3.2 Connexion Base de Données

La connexion PDO est configurée avec des **options de sécurité renforcées** :

```php
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_DIRECT_QUERY => false,
];
```

Points clés :
- `EMULATE_PREPARES => false` : Active les vraies requêtes préparées côté serveur MySQL
- `ERRMODE_EXCEPTION` : Lève des exceptions en cas d'erreur SQL
- Charset `utf8mb4` : Support complet Unicode

### 3.3 Gestion des Sessions

L'initialisation des sessions applique des paramètres de sécurité stricts :

```php
session_set_cookie_params([
    'lifetime' => 0,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_name('NOVA_SID');
```

Un système de **fingerprinting** détecte le vol de session :

```php
$_SESSION['_fingerprint'] = hash('sha256', 
    $_SERVER['HTTP_USER_AGENT'] . $_SERVER['HTTP_ACCEPT_LANGUAGE']
);
```

La régénération périodique de l'ID (toutes les 30 minutes) prévient la fixation de session :

```php
if (time() - $_SESSION['_last_regeneration'] > 1800) {
    session_regenerate_id(true);
}
```

### 3.4 Traitement des Formulaires

Le traitement des inscriptions suit un workflow sécurisé :

1. **Validation CSRF** obligatoire
2. **Sanitization** des entrées
3. **Vérification métier** (capacité, doublon)
4. **Exécution** de la requête

```php
if (!verifyCsrfToken()) {
    $message = "Erreur de sécurité.";
} else {
    $id_event = sanitizeInt($_POST['id_event'] ?? 0);
    // Suite du traitement...
}
```

### 3.5 Requêtes SQL

Les requêtes sont construites dynamiquement avec des **paramètres liés** :

```php
$sql = "SELECT * FROM event WHERE status = 'publié'";
$params = [];

if (!empty($tag)) {
    $sql .= " AND tag = ?";
    $params[] = $tag;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
```

---

## 4. DEVSECOPS

### 4.1 Protection CSRF

Chaque formulaire inclut un **token CSRF** unique généré cryptographiquement :

```php
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
```

La vérification utilise `hash_equals()` pour prévenir les attaques timing :

```php
return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
```

### 4.2 Protection XSS

Toute donnée affichée est **échappée** via une fonction dédiée :

```php
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
```

Utilisation systématique dans les vues :

```php
<p><?= e($event['description']) ?></p>
```

### 4.3 Protection SQL Injection

Les **requêtes préparées** avec paramètres liés neutralisent les injections :

```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

Les caractères spéciaux LIKE sont échappés :

```php
function escapeLike($string) {
    return str_replace(['%', '_'], ['\\%', '\\_'], $string);
}
```

### 4.4 Sanitization des Entrées

Chaque type de donnée dispose d'une fonction de nettoyage :

| Fonction | Traitement |
|----------|------------|
| `sanitizeString()` | `trim()`, `strip_tags()`, limite longueur |
| `sanitizeEmail()` | `FILTER_SANITIZE_EMAIL` |
| `sanitizeInt()` | `FILTER_VALIDATE_INT` + bornes min/max |
| `sanitizeDate()` | Validation format `Y-m-d` |
| `sanitizePhone()` | Suppression caractères non numériques |

Exemple d'implémentation :

```php
function sanitizeInt($value, $min = 0, $max = PHP_INT_MAX) {
    $int = filter_var($value, FILTER_VALIDATE_INT);
    return ($int === false) ? 0 : max($min, min($max, $int));
}
```

### 4.5 Gestion des Mots de Passe

Le hashage utilise **bcrypt** avec un coût de 12 :

```php
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
```

La validation impose des règles de complexité :

```php
if (!preg_match('/[A-Z]/', $password)) {
    $errors[] = 'Au moins une majuscule requise.';
}
```

### 4.6 Protection Brute Force

Les tentatives échouées sont enregistrées par IP :

```php
$stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address) VALUES (?)");
```

Le blocage s'active après 5 tentatives en 1 heure :

```php
$stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts 
    WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
```

### 4.7 Sécurité Apache

Le fichier `.htaccess` implémente plusieurs couches de protection :

**Headers de sécurité :**
```apache
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-Content-Type-Options "nosniff"
Header always set Content-Security-Policy "default-src 'self'..."
```

**Restriction des méthodes HTTP :**
```apache
RewriteCond %{REQUEST_METHOD} !^(GET|POST|HEAD)$ [NC]
RewriteRule .* - [F,L]
```

**Blocage des fichiers sensibles :**
```apache
<FilesMatch "^(db\.php|security\.php|\.env|.*\.sql)$">
    Require all denied
</FilesMatch>
```

**Détection des scanners :**
```apache
RewriteCond %{HTTP_USER_AGENT} (sqlmap|nikto|burp) [NC]
RewriteRule .* - [F,L]
```

### 4.8 Matrice des Risques

| Risque | Criticité | Mesure | Statut |
|--------|-----------|--------|--------|
| SQL Injection | Critique | Requêtes préparées PDO | ✅ |
| XSS | Haute | `htmlspecialchars()` + CSP | ✅ |
| CSRF | Haute | Tokens + `hash_equals()` | ✅ |
| Brute Force | Moyenne | Blocage IP temporaire | ✅ |
| Session Hijacking | Haute | Fingerprint + `httponly` | ✅ |
| Clickjacking | Moyenne | `X-Frame-Options` | ✅ |
| Information Disclosure | Moyenne | `.htaccess` + logs | ✅ |

---

## 5. CONCLUSION

### Synthèse Technique

Le projet NOVA Événements implémente une architecture web sécurisée répondant aux standards actuels :

- **Front-End** : Architecture CSS modulaire, design responsive, interactions optimisées
- **Back-End** : PDO sécurisé, sessions renforcées, validation stricte des entrées
- **DevSecOps** : Protection multicouche contre les vulnérabilités OWASP Top 10

### Axes d'Amélioration

| Priorité | Amélioration |
|----------|--------------|
| P1 | Migration HTTPS (Let's Encrypt) |
| P2 | Implémentation tests unitaires (PHPUnit) |
| P2 | Pipeline CI/CD (GitHub Actions) |
| P3 | Migration vers framework (Laravel) |
| P3 | Containerisation Docker |

---

**Document généré le 04/12/2025**  
**© NOVA Événements - Confidentiel**
