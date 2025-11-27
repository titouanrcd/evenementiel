<?php 
require_once 'db.php'; 
session_start(); 

$erreurs = []; 
$active_tab = 'login'; 

// =========================================================
// 1. TRAITEMENT DE L'INSCRIPTION
// =========================================================
if (isset($_POST['action']) && $_POST['action'] == 'register') {
    $active_tab = 'register'; 

    // Récupération des données (Alignées sur vos colonnes DB)
    $user = htmlspecialchars(trim($_POST['user'] ?? '')); // Colonne 'user'
    $date_of_birth = $_POST['date_of_birth'] ?? '';       // Colonne 'date_of_birth'
    $sexe = $_POST['sexe'] ?? '';                         // Colonne 'sexe'
    $number = htmlspecialchars(trim($_POST['number'] ?? '')); // Colonne 'number'
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL); // Colonne 'email'
    $password = $_POST['password'] ?? '';                 // Colonne 'password'
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation 
    if (empty($user)) $erreurs[] = "Le nom d'utilisateur est obligatoire.";
    if (!$email) $erreurs[] = "L'email n'est pas valide.";
    if ($password !== $confirm_password) $erreurs[] = "Les mots de passe ne correspondent pas.";
    if (strlen($password) < 8) $erreurs[] = "Le mot de passe doit faire au moins 8 caractères.";

    // Insertion avec systeme de sécurité basique 
    if (empty($erreurs)) {
        try {
            // Vérif doublon email
            $check = $pdo->prepare("SELECT email FROM users WHERE email = ?");
            $check->execute([$email]);

            if ($check->rowCount() > 0) {
                $erreurs[] = "Cet email est déjà utilisé.";
            } else {
                // Hashage
                $hash = password_hash($password, PASSWORD_BCRYPT);

                // INSERTION EXACTE SELON VOTRE DB
                $sql = "INSERT INTO users (user, email, date_of_birth, sexe, number, password) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user, $email, $date_of_birth, $sexe, $number, $hash]);

                // Connexion auto
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $user;
                
                header('Location: index.php');
                exit();
            }
        } catch (PDOException $e) {
            $erreurs[] = "Erreur technique : " . $e->getMessage();
        }
    }
}

// =========================================================
// 2. TRAITEMENT DE LA CONNEXION
// =========================================================
if (isset($_POST['action']) && $_POST['action'] == 'login') {
    $active_tab = 'login';

    $identifier = trim($_POST['identifier'] ?? ''); // Email ou user ou number
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
                $_SESSION['user_email'] = $user_data['email'];
                $_SESSION['user_name'] = $user_data['user']; // La colonne s'appelle 'user'
                $_SESSION['user_role'] = $user_data['role'] ?? 'user';

                header('Location: index.php');
                exit();
            } else {
                $erreurs[] = "Identifiants incorrects.";
            }
        } catch (PDOException $e) {
            $erreurs[] = "Erreur de connexion.";
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
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="evenement.php">Événements</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            <a href="index.php" class="btn-gradient">Retour</a>
        </nav>
    </header>

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
                        
                        <?php if (!empty($erreurs) && $active_tab == 'login'): ?>
                            <div class="error-message" style="background: #ff4d4d; color: white; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
                                <?php foreach($erreurs as $e) echo "<p>$e</p>"; ?>
                            </div>
                        <?php endif; ?>

                        <div class="input-group">
                            <label>Email ou Nom d'utilisateur</label>
                            <input type="text" name="identifier" required placeholder="votre@email.com">
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

                        <?php if (!empty($erreurs) && $active_tab == 'register'): ?>
                            <div class="error-message" style="background: #ff4d4d; color: white; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
                                <?php foreach($erreurs as $e) echo "<p>$e</p>"; ?>
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

</body>
</html>