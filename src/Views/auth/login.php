<?php
/**
 * PAGE CONNEXION/INSCRIPTION - NOVA Événements
 */

$title = 'Connexion - NOVA';
$nonce = cspNonce();
$activeTab = $activeTab ?? 'login';
$errors = $errors ?? [];
$old = $old ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
    <?php partial('header', ['isLoggedIn' => false, 'userName' => '', 'userRole' => '']); ?>

    <main>
        <section class="auth-section">
            <div class="auth-bg-gradient"></div>

            <div class="auth-container">
                <input type="radio" id="tab-login" name="auth-tab" class="tab-radio" <?= $activeTab === 'login' ? 'checked' : '' ?>>
                <input type="radio" id="tab-register" name="auth-tab" class="tab-radio" <?= $activeTab === 'register' ? 'checked' : '' ?>>
                
                <div class="auth-tabs">
                    <label for="tab-login" class="auth-tab">Connexion</label>
                    <label for="tab-register" class="auth-tab">Inscription</label>
                </div>

                <!-- Formulaire Connexion -->
                <div id="login-form" class="auth-form">
                    <form action="<?= url('/connexion') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <?php if (!empty($errors) && $activeTab === 'login'): ?>
                            <div class="error-message">
                                <?php foreach($errors as $e): ?>
                                    <p><?= e($e) ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="input-group">
                            <label for="login-identifier">Email ou Nom d'utilisateur</label>
                            <input type="text" id="login-identifier" name="identifier" required placeholder="votre@email.com" maxlength="255" autocomplete="username">
                        </div>
                        <div class="input-group">
                            <label for="login-password">Mot de passe</label>
                            <input type="password" id="login-password" name="password" required placeholder="••••••••" autocomplete="current-password">
                        </div>
                        <button type="submit" class="btn-full">Se connecter</button>
                    </form>
                </div>

                <!-- Formulaire Inscription -->
                <div id="register-form" class="auth-form">
                    <form action="<?= url('/inscription') ?>" method="POST">
                        <?= csrf_field() ?>

                        <?php if (!empty($errors) && $activeTab === 'register'): ?>
                            <div class="error-message">
                                <?php foreach($errors as $e): ?>
                                    <p><?= e($e) ?></p>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div class="input-group">
                            <label for="reg-user">Nom d'utilisateur *</label>
                            <input type="text" id="reg-user" name="user" required placeholder="Pseudo ou Nom complet" 
                                   value="<?= e($old['user'] ?? '') ?>" minlength="3" maxlength="100">
                        </div>

                        <div class="input-group">
                            <label for="reg-dob">Date de naissance *</label>
                            <input type="date" id="reg-dob" name="date_of_birth" required 
                                   value="<?= e($old['date_of_birth'] ?? '') ?>">
                        </div>

                        <div class="input-group">
                            <label for="reg-sexe">Sexe *</label>
                            <select id="reg-sexe" name="sexe" required>
                                <option value="" disabled <?= empty($old['sexe']) ? 'selected' : '' ?>>Sélectionnez</option>
                                <option value="H" <?= ($old['sexe'] ?? '') === 'H' ? 'selected' : '' ?>>Homme</option>
                                <option value="F" <?= ($old['sexe'] ?? '') === 'F' ? 'selected' : '' ?>>Femme</option>
                                <option value="A" <?= ($old['sexe'] ?? '') === 'A' ? 'selected' : '' ?>>Autre</option>
                            </select>
                        </div>

                        <div class="input-group">
                            <label for="reg-phone">Téléphone</label>
                            <input type="tel" id="reg-phone" name="number" placeholder="06 12 34 56 78" 
                                   value="<?= e($old['number'] ?? '') ?>">
                        </div>

                        <div class="input-group">
                            <label for="reg-email">Email *</label>
                            <input type="email" id="reg-email" name="email" required placeholder="votre@email.com" 
                                   value="<?= e($old['email'] ?? '') ?>" autocomplete="email">
                        </div>

                        <div class="input-group">
                            <label for="reg-password">Mot de passe *</label>
                            <input type="password" id="reg-password" name="password" required placeholder="8 caractères min" 
                                   minlength="8" autocomplete="new-password">
                        </div>

                        <div class="input-group">
                            <label for="reg-confirm">Confirmer le mot de passe *</label>
                            <input type="password" id="reg-confirm" name="confirm_password" required 
                                   autocomplete="new-password">
                        </div>

                        <button type="submit" class="btn-full">Créer mon compte</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-bottom">
            © <?= date('Y') ?> NOVA ÉVÉNEMENTS.
        </div>
    </footer>

    <script src="<?= asset('js/navbar.js') ?>" nonce="<?= $nonce ?>"></script>
</body>
</html>
