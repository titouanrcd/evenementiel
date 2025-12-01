# 🚀 Guide CI/CD - NOVA Événements

## 📋 Introduction

Ce projet utilise **GitHub Actions** et **SonarCloud** pour automatiser les vérifications de sécurité, l'analyse de qualité, les tests et le déploiement. Chaque push déclenche automatiquement les workflows.

---

## 🔧 Workflows Disponibles

### 1. 🛡️ Security Check (`security.yml`)

**Déclencheur:** Push sur `main`/`develop` ou Pull Request

**Ce qu'il fait:**
- ✅ Vérifie la syntaxe PHP
- ✅ Analyse de sécurité avec Semgrep (SAST)
- ✅ Détection de secrets/credentials
- ✅ Vérification du code (PSR-12, PHPStan)
- ✅ Vérification CSRF, SQL injection, XSS
- ✅ Contrôle des headers de sécurité
- ✅ Vérification de la sécurité des uploads

### 2. � SonarCloud (`sonarcloud.yml`) - NOUVEAU!

**Déclencheur:** Push sur `main`/`develop` ou Pull Request

**Ce qu'il fait:**
- ✅ Analyse statique du code (SAST)
- ✅ Détection des bugs potentiels
- ✅ Détection des vulnérabilités de sécurité
- ✅ Identification des "code smells"
- ✅ Mesure de la dette technique
- ✅ Détection du code dupliqué
- ✅ Rapport de couverture de tests
- ✅ Quality Gate automatique

**Dashboard:** [sonarcloud.io](https://sonarcloud.io/project/overview?id=titouanrcd_evenementiel)

### 3. �📊 Tests (`tests.yml`)

**Déclencheur:** Push sur `main`/`develop` ou Pull Request

**Ce qu'il fait:**
- ✅ Tests de syntaxe PHP
- ✅ Vérification des fonctions de sécurité
- ✅ Validation CSS
- ✅ Validation JavaScript
- ✅ Vérification du responsive design
- ✅ Contrôle d'accessibilité basique

### 4. 🚀 Deploy (`deploy.yml`)

**Déclencheur:** Push sur `main` ou déclenchement manuel

**Ce qu'il fait:**
- ✅ Vérifications pré-déploiement
- ✅ Tests de sécurité critiques
- ✅ Déploiement FTP (optionnel)
- ✅ Déploiement SSH (optionnel)
- ✅ Notification du résultat

---

## ⚙️ Configuration

### Étape 1: Configurer SonarCloud (IMPORTANT!)

1. **Créer un compte SonarCloud**
   - Va sur [sonarcloud.io](https://sonarcloud.io)
   - Connecte-toi avec ton compte GitHub

2. **Importer le projet**
   - Clique sur "+" puis "Analyze new project"
   - Sélectionne ton repository `evenementiel`
   - Choisis "GitHub Actions" comme méthode d'analyse

3. **Récupérer le token SONAR_TOKEN**
   - Va dans "My Account" > "Security"
   - Génère un nouveau token
   - Copie-le précieusement

4. **Ajouter le secret dans GitHub**
   - Va dans ton repo GitHub > Settings > Secrets and variables > Actions
   - Clique "New repository secret"
   - Nom: `SONAR_TOKEN`
   - Valeur: colle le token

### Étape 2: Créer le dépôt GitHub

```bash
# Initialiser Git si ce n'est pas fait
git init

# Ajouter tous les fichiers
git add .

# Premier commit
git commit -m "Initial commit - NOVA Événements"

# Ajouter le remote (remplacer par votre URL)
git remote add origin https://github.com/VOTRE_USERNAME/nova-evenements.git

# Pousser le code
git push -u origin main
```

### Étape 3: Configurer les Secrets GitHub

Allez dans **Settings > Secrets and variables > Actions** de votre dépôt.

#### Pour SonarCloud (OBLIGATOIRE):
| Secret | Description | Comment l'obtenir |
|--------|-------------|-------------------|
| `SONAR_TOKEN` | Token d'authentification | sonarcloud.io > My Account > Security |

#### Pour le déploiement FTP:
| Secret | Description | Exemple |
|--------|-------------|---------|
| `FTP_SERVER` | Adresse du serveur FTP | `ftp.monsite.com` |
| `FTP_USERNAME` | Nom d'utilisateur FTP | `user@monsite.com` |
| `FTP_PASSWORD` | Mot de passe FTP | `********` |

#### Pour le déploiement SSH:
| Secret | Description | Exemple |
|--------|-------------|---------|
| `SSH_HOST` | Adresse du serveur | `123.45.67.89` |
| `SSH_USER` | Utilisateur SSH | `www-data` |
| `SSH_PRIVATE_KEY` | Clé privée SSH | `-----BEGIN RSA PRIVATE KEY-----...` |
| `SSH_PATH` | Chemin sur le serveur | `/var/www/html/nova` |

### Étape 4: Activer le Déploiement

Dans `.github/workflows/deploy.yml`, modifiez la ligne `if: false` en `if: true` pour le type de déploiement souhaité:

```yaml
# Pour FTP
deploy-ftp:
  if: true  # Changer de false à true

# OU pour SSH
deploy-ssh:
  if: true  # Changer de false à true
```

---

## 📊 Comprendre les Rapports

### Voir les résultats

1. Allez dans l'onglet **Actions** de votre dépôt GitHub
2. Cliquez sur le workflow exécuté
3. Consultez le **Summary** pour un aperçu rapide
4. Cliquez sur chaque job pour les détails

### Dashboard SonarCloud

Accédez à votre dashboard SonarCloud pour voir:
- **Quality Gate**: Statut global (Passed/Failed)
- **Bugs**: Erreurs potentielles détectées
- **Vulnerabilities**: Failles de sécurité
- **Code Smells**: Problèmes de maintenabilité
- **Coverage**: Pourcentage de code couvert par les tests
- **Duplications**: Code dupliqué à refactoriser

👉 [Voir le Dashboard](https://sonarcloud.io/project/overview?id=titouanrcd_evenementiel)

### Badges de statut

Ajoutez ces badges dans votre README:

```markdown
![Security](https://github.com/titouanrcd/evenementiel/workflows/🛡️%20Security%20Check/badge.svg)
![SonarCloud](https://sonarcloud.io/api/project_badges/measure?project=titouanrcd_evenementiel&metric=alert_status)
![Tests](https://github.com/titouanrcd/evenementiel/workflows/📊%20Tests/badge.svg)
![Deploy](https://github.com/titouanrcd/evenementiel/workflows/🚀%20Deploy%20to%20Production/badge.svg)
```

### Badges SonarCloud supplémentaires

```markdown
<!-- Quality Gate -->
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=titouanrcd_evenementiel&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=titouanrcd_evenementiel)

<!-- Bugs -->
[![Bugs](https://sonarcloud.io/api/project_badges/measure?project=titouanrcd_evenementiel&metric=bugs)](https://sonarcloud.io/summary/new_code?id=titouanrcd_evenementiel)

<!-- Vulnerabilities -->
[![Vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=titouanrcd_evenementiel&metric=vulnerabilities)](https://sonarcloud.io/summary/new_code?id=titouanrcd_evenementiel)

<!-- Code Smells -->
[![Code Smells](https://sonarcloud.io/api/project_badges/measure?project=titouanrcd_evenementiel&metric=code_smells)](https://sonarcloud.io/summary/new_code?id=titouanrcd_evenementiel)

<!-- Coverage -->
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=titouanrcd_evenementiel&metric=coverage)](https://sonarcloud.io/summary/new_code?id=titouanrcd_evenementiel)

<!-- Duplications -->
[![Duplicated Lines (%)](https://sonarcloud.io/api/project_badges/measure?project=titouanrcd_evenementiel&metric=duplicated_lines_density)](https://sonarcloud.io/summary/new_code?id=titouanrcd_evenementiel)
```

---

## 🚨 Que Faire en Cas d'Échec ?

### 1. Erreur de syntaxe PHP
```
❌ Erreur de syntaxe: views/fichier.php
```
**Solution:** Vérifiez le fichier indiqué avec un IDE ou `php -l fichier.php`

### 2. Échec CSRF
```
❌ Protection CSRF manquante
```
**Solution:** Ajoutez `<?php require_once 'security.php'; ?>` et les tokens CSRF dans vos formulaires

### 3. SQL Injection détectée
```
⚠️ Requête SQL non sécurisée
```
**Solution:** Utilisez toujours les requêtes préparées PDO

### 4. Credentials en dur
```
❌ Credentials hardcodés détectés
```
**Solution:** Utilisez des variables d'environnement ou un fichier `.env` (non versionné)

### 5. Échec SonarCloud Quality Gate
```
Quality Gate failed
```
**Solution:** Consultez le dashboard SonarCloud pour voir les problèmes détectés et corrigez-les

---

## 🔄 Workflow de Développement Recommandé

```
1. Créer une branche feature
   git checkout -b feature/ma-fonctionnalite

2. Développer et committer
   git add .
   git commit -m "Ajout de ma fonctionnalité"

3. Pousser et créer une Pull Request
   git push origin feature/ma-fonctionnalite
   → Les tests s'exécutent automatiquement

4. Vérifier les résultats
   → Corriger si nécessaire

5. Merger dans main
   → Le déploiement s'exécute automatiquement
```

---

## 📝 Fichier .gitignore Recommandé

Créez un fichier `.gitignore` à la racine:

```gitignore
# Environnement local
.env
.env.local
config.local.php

# Logs
logs/*.log
*.log

# IDE
.idea/
.vscode/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db

# Uploads utilisateurs (optionnel)
# uploads/*
# !uploads/.htaccess

# Dépendances
/vendor/
/node_modules/
```

---

## 🛠️ Commandes Utiles

```bash
# Vérifier la syntaxe PHP localement
find views -name "*.php" -exec php -l {} \;

# Voir l'historique des workflows
gh run list

# Relancer le dernier workflow échoué
gh run rerun [run-id]

# Déclencher un déploiement manuel
gh workflow run deploy.yml
```

---

## 📚 Ressources

- [Documentation GitHub Actions](https://docs.github.com/en/actions)
- [Marketplace des Actions](https://github.com/marketplace?type=actions)
- [Syntaxe des Workflows](https://docs.github.com/en/actions/reference/workflow-syntax-for-github-actions)

---

## ❓ FAQ

**Q: Les workflows sont-ils gratuits?**
R: Oui, pour les dépôts publics. Pour les privés, GitHub offre 2000 minutes/mois gratuitement.

**Q: Puis-je exécuter les workflows localement?**
R: Oui, avec [act](https://github.com/nektos/act): `act -j php-security`

**Q: Comment désactiver temporairement un workflow?**
R: Renommez le fichier `.yml` en `.yml.disabled` ou supprimez-le.

---

*Guide créé pour le projet NOVA Événements - 2024*
