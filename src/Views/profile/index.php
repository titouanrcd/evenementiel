<?php
/**
 * PAGE PROFIL - NOVA Événements
 */

$title = 'Mon Profil - NOVA';
$nonce = cspNonce();
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
    <?php partial('header', ['isLoggedIn' => $isLoggedIn, 'userName' => $userName, 'userRole' => $userRole]); ?>

    <main>
        <section class="profil-section">
            <div class="profil-bg-gradient"></div>
            
            <?php if (!empty($flash)): ?>
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="profil-container">
                <!-- Sidebar Profil -->
                <aside class="profil-sidebar">
                    <div class="profil-avatar">
                        <div class="avatar-circle">
                            <?= e(strtoupper(substr($user['user'], 0, 1))) ?>
                        </div>
                        <h2 class="profil-name"><?= e($user['user']) ?></h2>
                        <p class="profil-email"><?= e($user['email']) ?></p>
                    </div>
                    
                    <div class="profil-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?= count(array_filter($inscriptions, fn($i) => $i['statut'] === 'confirmé')) ?></span>
                            <span class="stat-label">Inscriptions actives</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= count($inscriptions) ?></span>
                            <span class="stat-label">Total événements</span>
                        </div>
                    </div>

                    <nav class="profil-nav">
                        <button class="profil-nav-btn active" data-tab="inscriptions">
                            Mes Inscriptions
                        </button>
                        <button class="profil-nav-btn" data-tab="settings">
                            Paramètres
                        </button>
                    </nav>
                </aside>

                <!-- Contenu principal -->
                <div class="profil-content">
                    <!-- Tab Inscriptions -->
                    <div class="profil-tab active" id="tab-inscriptions">
                        <div class="tab-header">
                            <h2>Mes Inscriptions</h2>
                            <p>Gérez vos inscriptions aux événements</p>
                        </div>

                        <?php if (empty($inscriptions)): ?>
                            <div class="empty-state">
                                <h3>Aucune inscription</h3>
                                <p>Vous n'êtes inscrit à aucun événement pour le moment.</p>
                                <a href="<?= url('/evenements') ?>" class="btn-gradient">Découvrir les événements</a>
                            </div>
                        <?php else: ?>
                            <div class="inscriptions-list">
                                <?php foreach ($inscriptions as $inscription): ?>
                                    <div class="inscription-card <?= e($inscription['statut']) ?>">
                                        <div class="inscription-date-badge">
                                            <span class="day"><?= date('d', strtotime($inscription['event_date'])) ?></span>
                                            <span class="month"><?= strtoupper(date('M', strtotime($inscription['event_date']))) ?></span>
                                        </div>
                                        
                                        <div class="inscription-info">
                                            <div class="inscription-header">
                                                <h3><?= e($inscription['name']) ?></h3>
                                                <span class="inscription-status status-<?= e($inscription['statut']) ?>">
                                                    <?= e(ucfirst($inscription['statut'])) ?>
                                                </span>
                                            </div>
                                            
                                            <p class="inscription-details">
                                                <span><?= e($inscription['lieu']) ?></span>
                                                <span><?= e(substr($inscription['hour'] ?? '00:00', 0, 5)) ?></span>
                                            </p>
                                            
                                            <?php if ($inscription['statut'] === 'confirmé'): ?>
                                                <form action="<?= url('/evenement/desinscription') ?>" method="POST" class="inscription-actions">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id_inscription" value="<?= e($inscription['id_inscription']) ?>">
                                                    <button type="submit" class="btn-cancel" 
                                                            onclick="return confirm('Êtes-vous sûr de vouloir annuler cette inscription ?')">
                                                        Annuler l'inscription
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab Paramètres -->
                    <div class="profil-tab" id="tab-settings">
                        <div class="tab-header">
                            <h2>Paramètres du compte</h2>
                            <p>Modifiez vos informations personnelles</p>
                        </div>

                        <form action="<?= url('/profil/update') ?>" method="POST" class="settings-form">
                            <?= csrf_field() ?>
                            
                            <div class="input-group">
                                <label for="user">Nom d'utilisateur</label>
                                <input type="text" id="user" name="user" value="<?= e($user['user']) ?>" 
                                       required minlength="3" maxlength="100">
                            </div>
                            
                            <div class="input-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" value="<?= e($user['email']) ?>" disabled>
                                <small>L'email ne peut pas être modifié</small>
                            </div>
                            
                            <div class="input-group">
                                <label for="number">Téléphone</label>
                                <input type="tel" id="number" name="number" value="<?= e($user['number'] ?? '') ?>" 
                                       placeholder="06 12 34 56 78">
                            </div>
                            
                            <button type="submit" class="btn-gradient">Enregistrer les modifications</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php partial('footer'); ?>
    
    <script nonce="<?= $nonce ?>">
        // Gestion des onglets
        document.querySelectorAll('.profil-nav-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const tab = this.dataset.tab;
                
                // Activer le bouton
                document.querySelectorAll('.profil-nav-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Afficher l'onglet
                document.querySelectorAll('.profil-tab').forEach(t => t.classList.remove('active'));
                document.getElementById('tab-' + tab).classList.add('active');
            });
        });
    </script>
    <script src="<?= asset('js/navbar.js') ?>" nonce="<?= $nonce ?>"></script>
</body>
</html>
