<?php
session_start();
require_once 'db.php';

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_email'])) {
    header('Location: connexion.php');
    exit();
}

$user_email = $_SESSION['user_email'];
$user_name = $_SESSION['user_name'] ?? '';

// Vérifier le rôle admin
try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE email = ?");
    $stmt->execute([$user_email]);
    $user = $stmt->fetch();
    
    if (!$user || $user['role'] != 'admin') {
        header('Location: profil.php');
        exit();
    }
} catch (PDOException $e) {
    die("Erreur de vérification des droits.");
}

$message = '';
$message_type = '';

// Tags disponibles
$tags = [
    'sport' => '🏀 Sport',
    'culture' => '🎭 Culture',
    'soiree' => '🎉 Soirée',
    'conference' => '🎤 Conférence',
    'festival' => '🎪 Festival',
    'autre' => '📌 Autre'
];

// =========================================================
// TRAITEMENT : Changer le rôle d'un utilisateur
// =========================================================
if (isset($_POST['action']) && $_POST['action'] == 'change_role') {
    $target_email = $_POST['user_email'] ?? '';
    $new_role = $_POST['new_role'] ?? '';
    
    if ($target_email != $user_email && in_array($new_role, ['user', 'organisateur', 'admin'])) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE email = ?");
            $stmt->execute([$new_role, $target_email]);
            $message = "Rôle mis à jour avec succès.";
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Erreur lors de la mise à jour du rôle.";
            $message_type = "error";
        }
    }
}

// =========================================================
// TRAITEMENT : Changer le statut d'un événement
// =========================================================
if (isset($_POST['action']) && $_POST['action'] == 'change_event_status') {
    $id_event = intval($_POST['id_event']);
    $new_status = $_POST['new_status'] ?? '';
    
    if (in_array($new_status, ['publié', 'en attente'])) {
        try {
            $stmt = $pdo->prepare("UPDATE event SET status = ? WHERE id_event = ?");
            $stmt->execute([$new_status, $id_event]);
            $message = "Statut de l'événement mis à jour.";
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Erreur lors de la mise à jour.";
            $message_type = "error";
        }
    }
}

// =========================================================
// TRAITEMENT : Supprimer un utilisateur
// =========================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_user') {
    $target_email = $_POST['user_email'] ?? '';
    
    if ($target_email != $user_email) {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
            $stmt->execute([$target_email]);
            $message = "Utilisateur supprimé.";
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Erreur lors de la suppression.";
            $message_type = "error";
        }
    }
}

// =========================================================
// TRAITEMENT : Supprimer un événement
// =========================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_event') {
    $id_event = intval($_POST['id_event']);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM event WHERE id_event = ?");
        $stmt->execute([$id_event]);
        $message = "Événement supprimé.";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Erreur lors de la suppression.";
        $message_type = "error";
    }
}

// Récupérer tous les utilisateurs
try {
    $users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
} catch (PDOException $e) {
    $users = [];
}

// Récupérer tous les événements
try {
    $events = $pdo->query("
        SELECT e.*, u.user as owner_name,
        (SELECT COUNT(*) FROM inscriptions i WHERE i.id_event = e.id_event AND i.statut = 'confirmé') as nb_inscrits
        FROM event e 
        LEFT JOIN users u ON e.owner_email = u.email
        ORDER BY e.event_date DESC
    ")->fetchAll();
} catch (PDOException $e) {
    $events = [];
}

// Statistiques
$stats = [
    'users' => count($users),
    'events' => count($events),
    'events_published' => count(array_filter($events, fn($e) => $e['status'] == 'publié')),
    'events_pending' => count(array_filter($events, fn($e) => $e['status'] == 'en attente')),
    'inscriptions' => array_sum(array_column($events, 'nb_inscrits'))
];

// Onglet actif
$active_tab = $_GET['tab'] ?? 'dashboard';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - NOVA</title>
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
                <li><a href="profil.php">Mon Profil</a></li>
                <li><a href="organisateur.php">Panel Orga</a></li>
                <li><a href="admin.php" class="active">Panel Admin</a></li>
            </ul>
            <a href="profil.php?action=logout" class="btn-gradient btn-logout">Déconnexion</a>
        </nav>
    </header>

    <main>
        <section class="admin-section">
            <div class="admin-bg-gradient"></div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="admin-container">
                <!-- Sidebar -->
                <aside class="admin-sidebar">
                    <div class="admin-profile">
                        <div class="admin-avatar">👑</div>
                        <h3><?php echo htmlspecialchars($user_name); ?></h3>
                        <span class="role-badge role-admin">Administrateur</span>
                    </div>
                    
                    <nav class="admin-nav">
                        <a href="admin.php?tab=dashboard" class="admin-nav-link <?php echo $active_tab == 'dashboard' ? 'active' : ''; ?>">
                            <span class="nav-icon">📊</span>
                            Dashboard
                        </a>
                        <a href="admin.php?tab=users" class="admin-nav-link <?php echo $active_tab == 'users' ? 'active' : ''; ?>">
                            <span class="nav-icon">👥</span>
                            Utilisateurs
                        </a>
                        <a href="admin.php?tab=events" class="admin-nav-link <?php echo $active_tab == 'events' ? 'active' : ''; ?>">
                            <span class="nav-icon">📅</span>
                            Événements
                        </a>
                        <a href="admin.php?tab=pending" class="admin-nav-link <?php echo $active_tab == 'pending' ? 'active' : ''; ?>">
                            <span class="nav-icon">⏳</span>
                            En attente
                            <?php if ($stats['events_pending'] > 0): ?>
                                <span class="badge-count"><?php echo $stats['events_pending']; ?></span>
                            <?php endif; ?>
                        </a>
                    </nav>
                </aside>

                <!-- Contenu principal -->
                <div class="admin-content">
                    
                    <!-- DASHBOARD -->
                    <?php if ($active_tab == 'dashboard'): ?>
                    <div class="admin-card">
                        <div class="card-header">
                            <h2>Dashboard</h2>
                        </div>
                        
                        <div class="dashboard-stats">
                            <div class="dashboard-stat-card">
                                <div class="stat-icon">👥</div>
                                <div class="stat-info">
                                    <span class="stat-value"><?php echo $stats['users']; ?></span>
                                    <span class="stat-label">Utilisateurs</span>
                                </div>
                            </div>
                            <div class="dashboard-stat-card">
                                <div class="stat-icon">📅</div>
                                <div class="stat-info">
                                    <span class="stat-value"><?php echo $stats['events']; ?></span>
                                    <span class="stat-label">Événements</span>
                                </div>
                            </div>
                            <div class="dashboard-stat-card">
                                <div class="stat-icon">✅</div>
                                <div class="stat-info">
                                    <span class="stat-value"><?php echo $stats['events_published']; ?></span>
                                    <span class="stat-label">Publiés</span>
                                </div>
                            </div>
                            <div class="dashboard-stat-card">
                                <div class="stat-icon">⏳</div>
                                <div class="stat-info">
                                    <span class="stat-value"><?php echo $stats['events_pending']; ?></span>
                                    <span class="stat-label">En attente</span>
                                </div>
                            </div>
                            <div class="dashboard-stat-card">
                                <div class="stat-icon">🎫</div>
                                <div class="stat-info">
                                    <span class="stat-value"><?php echo $stats['inscriptions']; ?></span>
                                    <span class="stat-label">Inscriptions</span>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($stats['events_pending'] > 0): ?>
                        <div class="alert-info">
                            <strong>⚠️ Attention :</strong> Vous avez <?php echo $stats['events_pending']; ?> événement(s) en attente de validation.
                            <a href="admin.php?tab=pending">Voir les événements en attente →</a>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- UTILISATEURS -->
                    <?php if ($active_tab == 'users'): ?>
                    <div class="admin-card">
                        <div class="card-header">
                            <h2>Gestion des Utilisateurs</h2>
                            <span class="events-total"><?php echo count($users); ?> utilisateur(s)</span>
                        </div>
                        
                        <div class="events-table-container">
                            <table class="events-table">
                                <thead>
                                    <tr>
                                        <th>Utilisateur</th>
                                        <th>Email</th>
                                        <th>Rôle</th>
                                        <th>Inscrit le</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($u['user']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                                            <td>
                                                <form method="POST" class="role-form">
                                                    <input type="hidden" name="action" value="change_role">
                                                    <input type="hidden" name="user_email" value="<?php echo $u['email']; ?>">
                                                    <select name="new_role" onchange="this.form.submit()" 
                                                            <?php echo $u['email'] == $user_email ? 'disabled' : ''; ?>>
                                                        <option value="user" <?php echo $u['role'] == 'user' ? 'selected' : ''; ?>>👤 User</option>
                                                        <option value="organisateur" <?php echo $u['role'] == 'organisateur' ? 'selected' : ''; ?>>🎭 Organisateur</option>
                                                        <option value="admin" <?php echo $u['role'] == 'admin' ? 'selected' : ''; ?>>👑 Admin</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($u['created_at'] ?? 'now')); ?></td>
                                            <td>
                                                <?php if ($u['email'] != $user_email): ?>
                                                    <form method="POST" style="display: inline;" 
                                                          onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                                        <input type="hidden" name="action" value="delete_user">
                                                        <input type="hidden" name="user_email" value="<?php echo $u['email']; ?>">
                                                        <button type="submit" class="btn-action btn-delete">🗑️</button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted">Vous</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- ÉVÉNEMENTS -->
                    <?php if ($active_tab == 'events'): ?>
                    <div class="admin-card">
                        <div class="card-header">
                            <h2>Tous les Événements</h2>
                            <span class="events-total"><?php echo count($events); ?> événement(s)</span>
                        </div>
                        
                        <div class="events-table-container">
                            <table class="events-table">
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
                                                <div class="event-cell">
                                                    <?php if ($event['image']): ?>
                                                        <img src="<?php echo (strpos($event['image'], 'http') === 0) ? $event['image'] : '../' . $event['image']; ?>" 
                                                             alt="" class="event-thumb">
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($event['name']); ?></strong>
                                                        <span class="tag-small tag-<?php echo $event['tag']; ?>">
                                                            <?php echo $tags[$event['tag']] ?? $event['tag']; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($event['owner_name'] ?? $event['owner_email']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($event['event_date'])); ?></td>
                                            <td>
                                                <span class="inscrits-badge">
                                                    <?php echo $event['nb_inscrits']; ?> / <?php echo $event['capacite']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form method="POST" class="status-form">
                                                    <input type="hidden" name="action" value="change_event_status">
                                                    <input type="hidden" name="id_event" value="<?php echo $event['id_event']; ?>">
                                                    <select name="new_status" onchange="this.form.submit()">
                                                        <option value="publié" <?php echo $event['status'] == 'publié' ? 'selected' : ''; ?>>✅ Publié</option>
                                                        <option value="en attente" <?php echo $event['status'] == 'en attente' ? 'selected' : ''; ?>>⏳ En attente</option>
                                                    </select>
                                                </form>
                                            </td>
                                            <td>
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Supprimer cet événement ?');">
                                                    <input type="hidden" name="action" value="delete_event">
                                                    <input type="hidden" name="id_event" value="<?php echo $event['id_event']; ?>">
                                                    <button type="submit" class="btn-action btn-delete">🗑️</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- EN ATTENTE -->
                    <?php if ($active_tab == 'pending'): ?>
                    <div class="admin-card">
                        <div class="card-header">
                            <h2>Événements en Attente de Validation</h2>
                        </div>
                        
                        <?php 
                        $pending_events = array_filter($events, fn($e) => $e['status'] == 'en attente');
                        ?>
                        
                        <?php if (empty($pending_events)): ?>
                            <div class="empty-state">
                                <span class="empty-icon">✅</span>
                                <h3>Aucun événement en attente</h3>
                                <p>Tous les événements ont été validés.</p>
                            </div>
                        <?php else: ?>
                            <div class="pending-events">
                                <?php foreach ($pending_events as $event): ?>
                                    <div class="pending-card">
                                        <div class="pending-image">
                                            <?php if ($event['image']): ?>
                                                <img src="<?php echo (strpos($event['image'], 'http') === 0) ? $event['image'] : '../' . $event['image']; ?>" alt="">
                                            <?php else: ?>
                                                <div class="no-image">📅</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="pending-info">
                                            <h3><?php echo htmlspecialchars($event['name']); ?></h3>
                                            <p class="pending-meta">
                                                <span>📅 <?php echo date('d/m/Y', strtotime($event['event_date'])); ?></span>
                                                <span>📍 <?php echo htmlspecialchars($event['lieu']); ?></span>
                                                <span>👤 <?php echo htmlspecialchars($event['owner_name'] ?? $event['owner_email']); ?></span>
                                            </p>
                                            <p class="pending-desc"><?php echo htmlspecialchars(substr($event['description'], 0, 200)); ?>...</p>
                                        </div>
                                        <div class="pending-actions">
                                            <form method="POST">
                                                <input type="hidden" name="action" value="change_event_status">
                                                <input type="hidden" name="id_event" value="<?php echo $event['id_event']; ?>">
                                                <input type="hidden" name="new_status" value="publié">
                                                <button type="submit" class="btn-approve">✅ Approuver</button>
                                            </form>
                                            <form method="POST" onsubmit="return confirm('Supprimer cet événement ?');">
                                                <input type="hidden" name="action" value="delete_event">
                                                <input type="hidden" name="id_event" value="<?php echo $event['id_event']; ?>">
                                                <button type="submit" class="btn-reject">❌ Rejeter</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
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
