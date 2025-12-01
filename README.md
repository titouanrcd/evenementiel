# 🎉 NOVA Événements

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=titouanrcd_evenementiel&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=titouanrcd_evenementiel)
[![Bugs](https://sonarcloud.io/api/project_badges/measure?project=titouanrcd_evenementiel&metric=bugs)](https://sonarcloud.io/summary/new_code?id=titouanrcd_evenementiel)
[![Vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=titouanrcd_evenementiel&metric=vulnerabilities)](https://sonarcloud.io/summary/new_code?id=titouanrcd_evenementiel)
[![Code Smells](https://sonarcloud.io/api/project_badges/measure?project=titouanrcd_evenementiel&metric=code_smells)](https://sonarcloud.io/summary/new_code?id=titouanrcd_evenementiel)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=titouanrcd_evenementiel&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=titouanrcd_evenementiel)

> Plateforme de gestion d'événements étudiants - Projet sécurisé avec CI/CD

---

## 📋 Description

**NOVA Événements** est une application web de gestion d'événements destinée aux étudiants. Elle permet de :

- 🎫 Créer et gérer des événements
- 👥 Gérer les inscriptions des participants
- 🎨 Présenter les artistes
- 📸 Afficher une galerie photos
- 👤 Gérer son profil utilisateur
- 🔐 Administration sécurisée

---

## 🛡️ Sécurité

Ce projet implémente de nombreuses mesures de sécurité :

| Mesure | Status |
|--------|--------|
| Protection CSRF | ✅ |
| Sessions sécurisées | ✅ |
| Protection XSS | ✅ |
| Requêtes préparées (SQL Injection) | ✅ |
| Hashage bcrypt | ✅ |
| Headers de sécurité | ✅ |
| Protection brute force | ✅ |
| Upload sécurisé | ✅ |

📄 Voir le [Rapport de Sécurité Détaillé](RAPPORT_SECURITE_DETAILLE.md)

---

## 🚀 CI/CD

Le projet utilise une pipeline CI/CD complète :

### Workflows GitHub Actions

| Workflow | Description | Status |
|----------|-------------|--------|
| 🛡️ Security Check | Analyse de sécurité automatique | ![Security](https://github.com/titouanrcd/evenementiel/workflows/🛡️%20Security%20Check/badge.svg) |
| 🔬 SonarCloud | Analyse qualité du code | [![Quality Gate](https://sonarcloud.io/api/project_badges/measure?project=titouanrcd_evenementiel&metric=alert_status)](https://sonarcloud.io/dashboard?id=titouanrcd_evenementiel) |
| 📊 Tests | Tests automatisés | ![Tests](https://github.com/titouanrcd/evenementiel/workflows/📊%20Tests/badge.svg) |
| 🚀 Deploy | Déploiement automatique | ![Deploy](https://github.com/titouanrcd/evenementiel/workflows/🚀%20Deploy%20to%20Production/badge.svg) |

📄 Voir le [Guide CI/CD](CI_CD_GUIDE.md)

---

## 🛠️ Technologies

- **Backend:** PHP 8.x
- **Base de données:** MySQL / MariaDB
- **Frontend:** HTML5, CSS3, JavaScript
- **Serveur:** Apache (XAMPP)
- **CI/CD:** GitHub Actions
- **Qualité:** SonarCloud

---

## 📦 Installation

### Prérequis

- XAMPP (PHP 8.x + MySQL/MariaDB)
- Git

### Étapes

```bash
# 1. Cloner le projet
git clone https://github.com/titouanrcd/evenementiel.git

# 2. Placer dans le dossier htdocs de XAMPP
# Windows: C:\xampp\htdocs\evenementiel
# Mac: /Applications/XAMPP/htdocs/evenementiel

# 3. Importer la base de données
# Via phpMyAdmin, importer:
# - gestion_events_etudiants.sql
# - security_update.sql

# 4. Configurer la connexion
# Modifier views/db.php si nécessaire

# 5. Accéder au site
# http://localhost/evenementiel/views/
```

---

## 📁 Structure du Projet

```
evenementiel/
├── .github/
│   └── workflows/          # Pipelines CI/CD
│       ├── security.yml    # Vérifications sécurité
│       ├── sonarcloud.yml  # Analyse SonarCloud
│       ├── tests.yml       # Tests automatisés
│       └── deploy.yml      # Déploiement
├── css/
│   ├── base/               # Reset, variables
│   ├── components/         # Éléments UI
│   ├── layout/             # Navigation, footer
│   ├── sections/           # Styles par page
│   ├── style.css           # Style principal
│   └── responsive.css      # Responsive design
├── js/
│   ├── app.js              # JavaScript principal
│   └── navbar.js           # Navigation
├── views/
│   ├── security.php        # Module de sécurité
│   ├── db.php              # Connexion BDD
│   ├── index.php           # Page d'accueil
│   ├── connexion.php       # Login/Register
│   ├── evenement.php       # Liste événements
│   ├── profil.php          # Profil utilisateur
│   ├── admin.php           # Administration
│   ├── organisateur.php    # Gestion événements
│   └── navbar.php          # Barre de navigation
├── uploads/                # Fichiers uploadés
├── logs/                   # Logs d'erreurs
├── img/                    # Images statiques
├── .htaccess               # Configuration Apache
├── sonar-project.properties # Config SonarCloud
└── README.md
```

---

## 👥 Rôles Utilisateurs

| Rôle | Permissions |
|------|-------------|
| **Visiteur** | Voir événements, galerie |
| **Étudiant** | S'inscrire aux événements, profil |
| **Organisateur** | Créer/gérer ses événements |
| **Admin** | Gestion complète du site |

---

## 📊 Métriques SonarCloud

| Métrique | Description |
|----------|-------------|
| [![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=titouanrcd_evenementiel&metric=reliability_rating)](https://sonarcloud.io/summary/new_code?id=titouanrcd_evenementiel) | Fiabilité |
| [![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=titouanrcd_evenementiel&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=titouanrcd_evenementiel) | Maintenabilité |
| [![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=titouanrcd_evenementiel&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=titouanrcd_evenementiel) | Sécurité |

---

## 📝 Licence

Projet étudiant - Usage éducatif uniquement.

---

## 👤 Auteur

**Titouan Richard-Carrere**

- GitHub: [@titouanrcd](https://github.com/titouanrcd)

---

*Fait avec ❤️ pour un projet scolaire - 2024/2025*
