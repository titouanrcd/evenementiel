<?php 
/**
 * ============================================================
 * PAGE DE CONNEXION / INSCRIPTION - NOVA Événements
 * ============================================================
 * Sécurité implémentée:
 * - Protection CSRF
 * - Validation des entrées
 * - Protection contre la force brute
 * - Régénération de session après connexion
 * ============================================================
 */

require_once 'security.php';  // Inclure la sécurité EN PREMIER
require_once 'db.php'; 

$erreurs = []; 
$active_tab = 'login'; 

// Vérifier si l'IP est bloquée (trop de tentatives)
$clientIp = getClientIp();
if (isIpBlocked($pdo, $clientIp)) {
    $erreurs[] = "Trop de tentatives de connexion. Veuillez réessayer dans 15 minutes.";
}

// =========================================================
// 1. TRAITEMENT DE L'INSCRIPTION
// =========================================================
if (isset($_POST['action']) && $_POST['action'] == 'register' && empty($erreurs)) {
    // Vérification du token CSRF
    if (!verifyCsrfToken()) {
        $erreurs[] = "Erreur de sécurité. Veuillez rafraîchir la page et réessayer.";
    } else {
        $active_tab = 'register'; 

        // Récupération et validation des données
        $user = sanitizeString($_POST['user'] ?? '', 100);
        $date_of_birth = sanitizeDate($_POST['date_of_birth'] ?? '');
        $sexe = in_array($_POST['sexe'] ?? '', ['H', 'F', 'A']) ? $_POST['sexe'] : '';
        $number = sanitizePhone($_POST['number'] ?? '') ?: '';
        $email = sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validation 
        if (empty($user)) $erreurs[] = "Le nom d'utilisateur est obligatoire.";
        if (strlen($user) < 3) $erreurs[] = "Le nom d'utilisateur doit faire au moins 3 caractères.";
        if (!$email) $erreurs[] = "L'email n'est pas valide.";
        if (!$date_of_birth) $erreurs[] = "La date de naissance n'est pas valide.";
        if ($password !== $confirm_password) $erreurs[] = "Les mots de passe ne correspondent pas.";
        
        // Validation renforcée du mot de passe
        $passwordCheck = validatePassword($password);
        if (!$passwordCheck['valid']) {
            $erreurs = array_merge($erreurs, $passwordCheck['errors']);
        }

        // Insertion avec systeme de sécurité 
        if (empty($erreurs)) {
            try {
                // Vérif doublon email
                $check = $pdo->prepare("SELECT email FROM users WHERE email = ?");
                $check->execute([$email]);

                if ($check->rowCount() > 0) {
                    $erreurs[] = "Cet email est déjà utilisé.";
                } else {
                    // Hashage avec coût par défaut (actuellement 10)
                    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

                    // INSERTION SÉCURISÉE
                    $sql = "INSERT INTO users (user, email, date_of_birth, sexe, number, password) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$user, $email, $date_of_birth, $sexe, $number, $hash]);

                    // Connexion auto avec régénération de session
                    regenerateSession();
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_name'] = $user;
                    $_SESSION['user_role'] = 'user';
                    
                    header('Location: index.php');
                    exit();
                }
            } catch (PDOException $e) {
                error_log("Erreur inscription: " . $e->getMessage());
                $erreurs[] = "Une erreur technique est survenue. Veuillez réessayer.";
            }
        }
    }
}

// =========================================================
// 2. TRAITEMENT DE LA CONNEXION
// =========================================================
if (isset($_POST['action']) && $_POST['action'] == 'login' && empty($erreurs)) {
    // Vérification du token CSRF
    if (!verifyCsrfToken()) {
        $erreurs[] = "Erreur de sécurité. Veuillez rafraîchir la page et réessayer.";
    } else {
        $active_tab = 'login';

        $identifier = sanitizeString($_POST['identifier'] ?? '', 255);
        $password_login = $_POST['password'] ?? '';

        if (empty($identifier) || empty($password_login)) {
            $erreurs[] = "Veuillez remplir tous les champs.";
        } else {
            try {
                // On autorise la connexion par email ou par user
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR user = ?");
                $stmt->execute([$identifier, $identifier]);
                $user_data = $stmt->fetch();

                if ($user_data && password_verify($password_login, $user_data['password'])) {
                    // Connexion réussie - Régénérer la session
                    regenerateSession();
                    
                    $_SESSION['user_email'] = $user_data['email'];
                    $_SESSION['user_name'] = $user_data['user'];
                    $_SESSION['user_role'] = $user_data['role'] ?? 'user';

                    // Nettoyer les tentatives de connexion
                    cleanOldAttempts($pdo);

                    header('Location: index.php');
                    exit();
                } else {
                    // Enregistrer la tentative échouée
                    recordFailedAttempt($pdo, $clientIp);
                    $erreurs[] = "Identifiants incorrects.";
                }
            } catch (PDOException $e) {
                error_log("Erreur connexion: " . $e->getMessage());
                $erreurs[] = "Erreur de connexion.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - NOVA</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <header>
        <nav>
            <div class="logo header-logo">NOVA<span>.</span></div>
            <button class="hamburger-btn" id="hamburger-btn">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <aside class="sidebar" id="sidebar">
                <div class="sidebar-header">
                    <div class="logo" style="font-size: 32px;">NOVA<span>.</span></div>
                    <p>Événements Spectaculaires</p>
                </div>
                
                <ul class="nav-links">
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="evenement.php">Événements</a></li>
                </ul>
                
                <div class="sidebar-footer">
                    <div class="sidebar-actions">
                        <a href="index.php">Retour à l'accueil</a>
                    </div>
                </div>
            </aside>
        </nav>
    </header>
    
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <main>
       <section class="auth-section">
        <div class="auth-bg-gradient"></div>

        <div class="auth-container">
            <input type="radio" id="tab-login" name="auth-tab" class="tab-radio" <?php echo ($active_tab == 'login') ? 'checked' : ''; ?>>
            <input type="radio" id="tab-register" name="auth-tab" class="tab-radio" <?php echo ($active_tab == 'register') ? 'checked' : ''; ?>>
            
            <div class="auth-tabs">
                <label for="tab-login" class="auth-tab">Connexion</label>
                <label for="tab-register" class="auth-tab">Inscription</label>
            </div>

                <div id="login-form" class="auth-form">
                    <form action="connexion.php" method="POST">
                        <input type="hidden" name="action" value="login">
                        <?php echo csrfField(); ?>
                        
                        <?php if (!empty($erreurs) && $active_tab == 'login'): ?>
                            <div class="error-message" style="background: #ff4d4d; color: white; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
                                <?php foreach($erreurs as $e) echo "<p>" . htmlspecialchars($e) . "</p>"; ?>
                            </div>
                        <?php endif; ?>

                        <div class="input-group">
                            <label>Email ou Nom d'utilisateur</label>
                            <input type="text" name="identifier" required placeholder="votre@email.com" maxlength="255">
                        </div>
                        <div class="input-group">
                            <label>Mot de passe</label>
                            <input type="password" name="password" required placeholder="••••••••">
                        </div>
                        <button type="submit" class="btn-full">Se connecter</button>
                    </form>
                </div>

                <div id="register-form" class="auth-form">
                    <form action="connexion.php" method="POST">
                        <input type="hidden" name="action" value="register">
                        <?php echo csrfField(); ?>

                        <?php if (!empty($erreurs) && $active_tab == 'register'): ?>
                            <div class="error-message" style="background: #ff4d4d; color: white; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
                                <?php foreach($erreurs as $e) echo "<p>" . htmlspecialchars($e) . "</p>"; ?>
                            </div>
                        <?php endif; ?>

                        <div class="input-group">
                            <label>Nom d'utilisateur *</label>
                            <input type="text" name="user" required placeholder="Pseudo ou Nom complet" value="<?php echo isset($_POST['user']) ? htmlspecialchars($_POST['user']) : ''; ?>">
                        </div>

                        <div class="input-group">
                            <label>Date de naissance *</label>
                            <input type="date" name="date_of_birth" required value="<?php echo isset($_POST['date_of_birth']) ? $_POST['date_of_birth'] : ''; ?>">
                        </div>

                        <div class="input-group">
                            <label>Sexe *</label>
                            <select name="sexe" required>
                                <option value="" disabled selected>Sélectionnez</option>
                                <option value="H">Homme</option>
                                <option value="F">Femme</option>
                                <option value="A">Autre</option>
                            </select>
                        </div>

                        <div class="input-group">
                            <label>Téléphone</label>
                            <input type="tel" name="number" placeholder="06 12 34 56 78" value="<?php echo isset($_POST['number']) ? htmlspecialchars($_POST['number']) : ''; ?>">
                        </div>

                        <div class="input-group">
                            <label>Email *</label>
                            <input type="email" name="email" required placeholder="votre@email.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>

                        <div class="input-group">
                            <label>Mot de passe *</label>
                            <input type="password" name="password" required placeholder="8 caractères min">
                        </div>

                        <div class="input-group">
                            <label>Confirmer le mot de passe *</label>
                            <input type="password" name="confirm_password" required>
                        </div>

                        <button type="submit" class="btn-full">Créer mon compte</button>
                    </form>
                </div>

            </div>
        </section>
    </main>

    <footer>
        <div class="footer-bottom">
            © 2025 NOVA ÉVÉNEMENTS.
        </div>
    </footer>

<script src="../js/navbar.js"></script> 
</body>
</html>