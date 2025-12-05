<?php
/**
 * ADMIN - LISTE DES ÉVÉNEMENTS - NOVA Événements
 */

$title = 'Gestion des événements - NOVA';
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
                        <a href="<?= url('/admin/utilisateurs') ?>" class="admin-nav-link">Utilisateurs</a>
                        <a href="<?= url('/admin/evenements') ?>" class="admin-nav-link active">Événements</a>
                    </nav>
                </aside>

                <div class="admin-content">
                    <div class="admin-card">
                        <div class="card-header">
                            <h2>Gestion des événements</h2>
                            <div class="card-header-filters">
                                <a href="<?= url('/admin/evenements') ?>" class="filter-tab <?= empty($currentStatus) ? 'active' : '' ?>">Tous</a>
                                <a href="<?= url('/admin/evenements?status=publié') ?>" class="filter-tab <?= ($currentStatus ?? '') === 'publié' ? 'active' : '' ?>">Publiés</a>
                                <a href="<?= url('/admin/evenements?status=en+attente') ?>" class="filter-tab <?= ($currentStatus ?? '') === 'en attente' ? 'active' : '' ?>">En attente</a>
                            </div>
                        </div>
                        
                        <div class="events-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Événement</th>
                                        <th>Organisateur</th>
                                        <th>Date</th>
                                        <th>Inscrits</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($events as $event): ?>
                                        <tr>
                                            <td>
                                                <div class="event-name-cell">
                                                    <strong><?= e($event['name']) ?></strong>
                                                    <span class="event-tag-small tag-<?= e($event['tag']) ?>"><?= e($tags[$event['tag']] ?? $event['tag']) ?></span>
                                                </div>
                                            </td>
                                            <td><?= e($event['owner_name'] ?? 'N/A') ?></td>
                                            <td><?= date('d/m/Y', strtotime($event['event_date'])) ?></td>
                                            <td><?= e($event['nb_inscrits']) ?>/<?= e($event['capacite']) ?></td>
                                            <td>
                                                <form action="<?= url('/admin/evenement/statut') ?>" method="POST" class="status-form">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id_event" value="<?= e($event['id_event']) ?>">
                                                    <select name="new_status" onchange="this.form.submit()">
                                                        <option value="publié" <?= $event['status'] === 'publié' ? 'selected' : '' ?>>Publié</option>
                                                        <option value="en attente" <?= $event['status'] === 'en attente' ? 'selected' : '' ?>>En attente</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td>
                                                <form action="<?= url('/admin/evenement/supprimer') ?>" method="POST" class="delete-form">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="id_event" value="<?= e($event['id_event']) ?>">
                                                    <button type="submit" class="btn-small btn-danger" 
                                                            onclick="return confirm('Supprimer cet événement ?')">
                                                        Supprimer
                                                    </button>
                                                </form>
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
