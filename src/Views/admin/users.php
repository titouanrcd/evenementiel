<?php
/**
 * ADMIN - LISTE DES UTILISATEURS - NOVA Événements
 */

$title = 'Gestion des utilisateurs - NOVA';
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
        <section class="admin-section">
            <div class="admin-bg-gradient"></div>
            
            <?php if (!empty($flash)): ?>
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="admin-container">
                <aside class="admin-sidebar">
                    <div class="admin-profile">
                        <div class="admin-avatar">A</div>
                        <h3><?= e($userName) ?></h3>
                        <span class="role-badge role-admin">Administrateur</span>
                    </div>
                    
                    <nav class="admin-nav">
                        <a href="<?= url('/admin') ?>" class="admin-nav-link">Dashboard</a>
                        <a href="<?= url('/admin/utilisateurs') ?>" class="admin-nav-link active">Utilisateurs</a>
                        <a href="<?= url('/admin/evenements') ?>" class="admin-nav-link">Événements</a>
                    </nav>
                </aside>

                <div class="admin-content">
                    <div class="admin-card">
                        <div class="card-header">
                            <h2>Gestion des utilisateurs</h2>
                        </div>
                        
                        <div class="users-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Utilisateur</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th>Inscription</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= e($user['user']) ?></td>
                                            <td><?= e($user['email']) ?></td>
                                            <td>
                                                <form action="<?= url('/admin/utilisateur/role') ?>" method="POST" style="display: inline;">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="user_email" value="<?= e($user['email']) ?>">
                                                    <select name="new_role" onchange="this.form.submit()" 
                                                            <?= $user['email'] === $userEmail ? 'disabled' : '' ?>>
                                                        <option value="user" <?= ($user['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>User</option>
                                                        <option value="organisateur" <?= ($user['role'] ?? '') === 'organisateur' ? 'selected' : '' ?>>Organisateur</option>
                                                        <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($user['created_at'] ?? 'now')) ?></td>
                                            <td>
                                                <?php if ($user['email'] !== $userEmail): ?>
                                                    <form action="<?= url('/admin/utilisateur/supprimer') ?>" method="POST" style="display: inline;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="user_email" value="<?= e($user['email']) ?>">
                                                        <button type="submit" class="btn-small btn-danger" 
                                                                onclick="return confirm('Supprimer cet utilisateur ?')">
                                                            Supprimer
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php partial('footer'); ?>
    <script src="<?= asset('js/navbar.js') ?>" nonce="<?= $nonce ?>"></script>
</body>
</html>
