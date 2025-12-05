# NOVA Événements

Bienvenue sur le projet **NOVA Événements**, une plateforme de gestion d'événements développée en PHP natif avec une architecture MVC.

## 📋 Prérequis

Pour faire tourner ce projet en local, vous avez besoin de :

*   **XAMPP** (ou WAMP/MAMP) avec :
    *   PHP 8.2 ou supérieur
    *   MySQL / MariaDB
    *   Apache

## 🚀 Installation

Suivez ces étapes pour installer le projet sur votre machine :

### 1. Cloner le projet
Placez-vous dans le dossier `htdocs` de XAMPP et clonez le dépôt :

```bash
cd c:\xampp\htdocs
git clone https://github.com/votre-repo/evenementiel.git
```

### 2. Configuration de la Base de Données

1.  Ouvrez **phpMyAdmin** (généralement `http://localhost/phpmyadmin`).
2.  Créez une nouvelle base de données nommée `gestion_events_etudiants`.
3.  Importez le fichier `database.sql` situé à la racine du projet.

### 3. Configuration de l'Application

1.  Ouvrez le fichier `config/app.php`.
2.  Vérifiez que les identifiants de connexion correspondent à votre configuration locale (par défaut sur XAMPP, user: `root`, password: vide).

```php
return [
    'db_host' => 'localhost',
    'db_name' => 'evenementiel',
    'db_user' => 'root',
    'db_pass' => '',
    // ...
];
```

### 4. Lancer le site

Ouvrez votre navigateur et accédez à :

`http://localhost/evenementiel/public/`

## 🛠️ Architecture

Le projet suit une architecture **MVC (Modèle-Vue-Contrôleur)** stricte :

*   `src/Controllers` : Logique métier.
*   `src/Models` : Accès aux données (SQL).
*   `src/Views` : Interface utilisateur (HTML/PHP).
*   `src/Core` : Noyau du framework maison (Router, Database, etc.).
*   `public/` : Point d'entrée unique (index.php) et fichiers statiques (CSS, JS, Images).

## 🔒 Sécurité

Le projet intègre plusieurs mesures de sécurité :
*   Protection CSRF sur tous les formulaires.
*   Échappement XSS automatique.
*   Requêtes SQL préparées (PDO).
*   Système d'authentification robuste.

## 👥 Auteurs

non divulgé