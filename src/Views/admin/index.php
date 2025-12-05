<?php
/**
 * PANEL ADMIN - NOVA Événements
 */

$title = 'Panel Admin - NOVA';
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
                        <a href="<?= url('/admin') ?>" class="admin-nav-link active">Dashboard</a>
                        <a href="<?= url('/admin/utilisateurs') ?>" class="admin-nav-link">Utilisateurs</a>
                        <a href="<?= url('/admin/evenements') ?>" class="admin-nav-link">Événements</a>
                        <a href="<?= url('/admin/evenements?status=en+attente') ?>" class="admin-nav-link">
                            En attente
                            <?php if ($stats['events_pending'] > 0): ?>
                                <span class="badge-count"><?= e($stats['events_pending']) ?></span>
                            <?php endif; ?>
                        </a>
                    </nav>
                </aside>

                <div class="admin-content">
                    <div class="admin-card">
                        <div class="card-header">
                            <h2>Dashboard</h2>
                        </div>
                        
                        <div class="dashboard-stats">
                            <div class="dashboard-stat-card">
                                <span class="stat-value"><?= e($stats['users']) ?></span>
                                <span class="stat-label">Utilisateurs</span>
                            </div>
                            <div class="dashboard-stat-card">
                                <span class="stat-value"><?= e($stats['events']) ?></span>
                                <span class="stat-label">Événements</span>
                            </div>
                            <div class="dashboard-stat-card">
                                <span class="stat-value"><?= e($stats['events_published']) ?></span>
                                <span class="stat-label">Publiés</span>
                            </div>
                            <div class="dashboard-stat-card">
                                <span class="stat-value"><?= e($stats['inscriptions']) ?></span>
                                <span class="stat-label">Inscriptions</span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($pendingEvents)): ?>
                    <div class="admin-card">
                        <div class="card-header">
                            <h3>Événements en attente</h3>
                            <a href="<?= url('/admin/evenements?status=en+attente') ?>" class="btn-small">Voir tout</a>
                        </div>
                        
                        <div class="events-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Événement</th>
                                        <th>Organisateur</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingEvents as $event): ?>
                                        <tr>
                                            <td><?= e($event['name']) ?></td>
                                            <td><?= e($event['owner_name'] ?? 'N/A') ?></td>
                                            <td><?= date('d/m/Y', strtotime($event['event_date'])) ?></td>
                                            <td>
                                                <form action="<?= url('/admin/evenement/statut') ?>" method="POST" style="display: inline;">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id_event" value="<?= e($event['id_event']) ?>">
                                                    <input type="hidden" name="new_status" value="publié">
                                                    <button type="submit" class="btn-small btn-success">Approuver</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="admin-card">
                        <div class="card-header">
                            <h3>Derniers utilisateurs</h3>
                            <a href="<?= url('/admin/utilisateurs') ?>" class="btn-small">Voir tout</a>
                        </div>
                        
                        <div class="users-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Utilisateur</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td><?= e($user['user']) ?></td>
                                            <td><?= e($user['email']) ?></td>
                                            <td>
                                                <span class="role-badge role-<?= e($user['role'] ?? 'user') ?>">
                                                    <?= e(ucfirst($user['role'] ?? 'user')) ?>
                                                </span>
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
