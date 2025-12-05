# 🎭 NOVA Événements

**Plateforme de gestion d'événements étudiants** - Application web PHP moderne avec architecture MVC sécurisée.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white)
![Security](https://img.shields.io/badge/Security-CSP%20Nonce-green)
![License](https://img.shields.io/badge/License-MIT-blue)

---

## 📋 Table des matières

- [Présentation](#-présentation)
- [Architecture](#-architecture)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Sécurité](#-sécurité)
- [Fonctionnalités](#-fonctionnalités)
- [API](#-api)
- [Structure du projet](#-structure-du-projet)
- [Utilisation](#-utilisation)

---

## 🎯 Présentation

NOVA Événements est une plateforme complète de gestion d'événements permettant aux utilisateurs de :

- **Découvrir** des événements par catégorie, lieu et date
- **S'inscrire** aux événements de leur choix
- **Organiser** leurs propres événements (rôle organisateur)
- **Administrer** la plateforme (rôle admin)

### Types d'utilisateurs

| Rôle | Permissions |
|------|------------|
| **Visiteur** | Consultation des événements |
| **Utilisateur** | Inscription aux événements, gestion du profil |
| **Organisateur** | Création/gestion de ses événements |
| **Admin** | Gestion complète (utilisateurs, événements, modération) |

---

## 🏗 Architecture

L'application suit le pattern **MVC (Model-View-Controller)** avec un **Front Controller** :

```
┌─────────────────────────────────────────────────────────────┐
│                        CLIENT (Browser)                      │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                     public/index.php                        │
│                     (Front Controller)                       │
└─────────────────────────────────────────────────────────────┘
                              │
              ┌───────────────┼───────────────┐
              ▼               ▼               ▼
        ┌──────────┐   ┌──────────┐   ┌──────────────┐
        │ Bootstrap │   │  Router  │   │   Security   │
        │ (autoload)│   │ (routes) │   │ (CSP, CSRF)  │
        └──────────┘   └──────────┘   └──────────────┘
                              │
                              ▼
        ┌─────────────────────────────────────────────┐
        │              CONTROLLERS                     │
        │  Home | Auth | Event | Profile | Admin | API │
        └─────────────────────────────────────────────┘
              │                               │
              ▼                               ▼
        ┌──────────┐                   ┌──────────┐
        │ Database │                   │  Views   │
        │  (PDO)   │                   │ (*.php)  │
        └──────────┘                   └──────────┘
```

### Composants Core

| Composant | Description |
|-----------|-------------|
| `Bootstrap.php` | Autoloading, chargement de la config |
| `Application.php` | Enregistrement des routes, démarrage |
| `Router.php` | Routing avec paramètres dynamiques |
| `Database.php` | Singleton PDO avec prepared statements |
| `Security.php` | CSP Nonce, CSRF, sessions sécurisées |
| `Validator.php` | Validation des entrées utilisateur |
| `FileUpload.php` | Upload sécurisé avec validation MIME |
| `Helpers.php` | Fonctions globales (e(), asset(), etc.) |

---

## 🚀 Installation

### Prérequis

- PHP 8.0 ou supérieur
- MySQL 5.7+ ou MariaDB 10.2+
- Serveur Apache avec `mod_rewrite` activé
- Extensions PHP : `pdo`, `pdo_mysql`, `mbstring`, `fileinfo`

### Étapes

1. **Cloner le projet**
   ```bash
   git clone https://github.com/votre-repo/nova-evenements.git
   cd nova-evenements
   ```

2. **Configurer la base de données**
   ```sql
   CREATE DATABASE gestion_events_etudiants CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Importer le schéma**
   ```bash
   mysql -u root -p gestion_events_etudiants < database/schema.sql
   ```

4. **Configurer Apache**
   
   Pointer le DocumentRoot vers le dossier `public/` :
   ```apache
   <VirtualHost *:80>
       ServerName nova.local
       DocumentRoot /path/to/nova-evenements/public
       
       <Directory /path/to/nova-evenements/public>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

5. **Configurer l'application**
   
   Copier et modifier le fichier de configuration :
   ```bash
   cp config/app.php.example config/app.php
   ```

---

## ⚙️ Configuration

### Variables d'environnement (Production)

```bash
# Base de données
export DB_HOST="localhost"
export DB_NAME="gestion_events_etudiants"
export DB_USER="nova_user"
export DB_PASS="mot_de_passe_fort"

# Application
export APP_ENV="production"
export APP_SECRET_KEY="votre_cle_secrete_32_caracteres"

# APIs externes
export OPENWEATHER_API_KEY="votre_cle_openweather"
```

### config/app.php

```php
define('ENVIRONMENT', getenv('APP_ENV') ?: 'development');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'gestion_events_etudiants');
// ...
```

---

## 🔒 Sécurité

L'application intègre plusieurs couches de sécurité :

### 1. Content Security Policy (CSP) avec Nonce

Chaque page génère un **nonce unique** pour autoriser uniquement les scripts légitimes :

```php
// Génération automatique
$nonce = Security::generateNonce();

// Dans les vues
<script nonce="<?= $nonce ?>">
    // Code JavaScript autorisé
</script>
```

**En-tête CSP envoyé :**
```
Content-Security-Policy: 
  default-src 'self'; 
  script-src 'self' 'nonce-ABC123...'; 
  style-src 'self' 'nonce-ABC123...' fonts.googleapis.com;
  img-src 'self' data: https:;
```

### 2. Protection XSS

Toutes les sorties sont échappées avec la fonction helper `e()` :

```php
// Échappe automatiquement le HTML
<?= e($userInput) ?>

// Pour JavaScript
<script nonce="<?= $nonce ?>">
    var data = <?= eJs($data) ?>;
</script>
```

### 3. Protection CSRF

Tokens CSRF générés pour chaque session :

```php
// Dans les formulaires
<?= csrf_field() ?>

// Vérification côté serveur
Security::verifyCSRFToken($_POST['csrf_token']);
```

### 4. Sessions sécurisées

- **Fingerprinting** : Validation User-Agent + IP
- **Régénération** : ID de session régénéré périodiquement
- **Cookies sécurisés** : `HttpOnly`, `SameSite=Strict`, `Secure` (HTTPS)

### 5. Upload sécurisé

```php
// Validation du type MIME réel (pas l'extension)
$upload = new FileUpload($_FILES['image']);
$result = $upload
    ->allowedTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
    ->maxSize(5 * 1024 * 1024) // 5 Mo
    ->isImage(true)
    ->save('uploads/events/');
```

### 6. Protection base de données

- **Prepared Statements** : Toutes les requêtes utilisent PDO avec paramètres liés
- **ATTR_EMULATE_PREPARES = false** : Vrais prepared statements côté serveur

```php
$stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
```

### 7. Rate Limiting & Brute Force

```php
// Limitation de tentatives de connexion
if (Security::isRateLimited($email, 5, 15)) {
    // Compte bloqué pendant 15 minutes après 5 échecs
}
```

---

## ✨ Fonctionnalités

### Visiteurs
- 📅 Consulter la liste des événements
- 🔍 Filtrer par catégorie, lieu, date
- ℹ️ Voir les détails d'un événement

### Utilisateurs connectés
- 📝 S'inscrire aux événements
- 👤 Gérer son profil
- 📋 Voir ses inscriptions
- ❌ Annuler une inscription

### Organisateurs
- ➕ Créer des événements
- ✏️ Modifier ses événements
- 🗑️ Supprimer ses événements
- 📊 Voir les statistiques

### Administrateurs
- 👥 Gérer tous les utilisateurs
- 🎫 Modérer tous les événements
- ✅ Approuver/refuser les événements
- 📈 Dashboard avec statistiques

---

## 🔌 API

### Météo (OpenWeatherMap)

```http
GET /api/meteo?ville=Paris
```

**Réponse :**
```json
{
  "success": true,
  "data": {
    "temperature": 18.5,
    "description": "Partiellement nuageux",
    "humidity": 65,
    "icon": "02d"
  }
}
```

### Événements à venir

```http
GET /api/evenements?limit=5
```

---

## 📁 Structure du projet

```
evenementiel/
├── config/
│   └── app.php                 # Configuration centrale
├── logs/                       # Fichiers de logs
├── public/                     # DocumentRoot Apache
│   ├── index.php              # Front Controller (point d'entrée)
│   ├── .htaccess              # Réécriture URL + sécurité
│   ├── css/                   # Feuilles de style
│   │   ├── style.css
│   │   ├── responsive.css
│   │   ├── base/
│   │   ├── components/
│   │   ├── layout/
│   │   └── sections/
│   ├── js/                    # Scripts JavaScript
│   │   ├── app.js
│   │   └── navbar.js
│   ├── img/                   # Images statiques
│   └── uploads/               # Fichiers uploadés
├── src/
│   ├── Core/                  # Classes du framework
│   │   ├── Application.php
│   │   ├── Bootstrap.php
│   │   ├── Database.php
│   │   ├── FileUpload.php
│   │   ├── Helpers.php
│   │   ├── Router.php
│   │   ├── Security.php
│   │   └── Validator.php
│   ├── Controllers/           # Contrôleurs
│   │   ├── Controller.php     # Contrôleur de base
│   │   ├── AdminController.php
│   │   ├── ApiController.php
│   │   ├── AuthController.php
│   │   ├── EventController.php
│   │   ├── HomeController.php
│   │   ├── OrganizerController.php
│   │   └── ProfileController.php
│   └── Views/                 # Templates PHP
│       ├── layouts/
│       │   └── main.php
│       ├── partials/
│       │   ├── header.php
│       │   └── footer.php
│       ├── admin/
│       ├── auth/
│       ├── errors/
│       ├── events/
│       ├── home/
│       ├── organizer/
│       └── profile/
├── uploads/                   # Dossier uploads legacy
└── README.md                  # Ce fichier
```

---

## 📖 Utilisation

### Routes principales

| URL | Méthode | Description |
|-----|---------|-------------|
| `/` | GET | Page d'accueil |
| `/evenements` | GET | Liste des événements |
| `/connexion` | GET/POST | Connexion / Inscription |
| `/deconnexion` | GET | Déconnexion |
| `/profil` | GET | Profil utilisateur |
| `/organisateur` | GET | Panel organisateur |
| `/organisateur/creer` | GET/POST | Créer un événement |
| `/admin` | GET | Dashboard admin |
| `/admin/utilisateurs` | GET | Gestion utilisateurs |
| `/admin/evenements` | GET | Gestion événements |
| `/api/meteo` | GET | API météo |

### Démarrage en développement

```bash
cd public
php -S localhost:8000
```

Accéder à : http://localhost:8000

---

## 🧪 Tests

```bash
# Lancer les tests unitaires
./vendor/bin/phpunit

# Avec couverture
./vendor/bin/phpunit --coverage-html coverage/
```

---

## 🤝 Contribution

1. Forker le projet
2. Créer une branche (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commiter les changements (`git commit -am 'Ajout nouvelle fonctionnalité'`)
4. Pusher la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Ouvrir une Pull Request

---

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

---

## 👥 Auteurs

- **Équipe NOVA** - Développement initial

---

<p align="center">
  <strong>🎭 NOVA Événements</strong> - Créez, découvrez et participez !
</p>
