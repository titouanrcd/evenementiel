<?php
require_once 'db.php';
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_email'])) {
    header('Location: connexion.php');
    exit();
}

$user_email = $_SESSION['user_email'];
$message = '';
$message_type = '';

// Récupérer les informations de l'utilisateur
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$user_email]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    die("Erreur lors de la récupération des données utilisateur.");
}

// =========================================================
// TRAITEMENT DES ACTIONS
// =========================================================

// Déconnexion
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Annuler une inscription
if (isset($_POST['action']) && $_POST['action'] == 'cancel_inscription') {
    $id_inscription = intval($_POST['id_inscription']);
    
    try {
        $stmt = $pdo->prepare("UPDATE inscriptions SET statut = 'annulé' WHERE id_inscription = ? AND user_email = ?");
        $stmt->execute([$id_inscription, $user_email]);
        
        if ($stmt->rowCount() > 0) {
            $message = "Votre inscription a été annulée avec succès.";
            $message_type = "success";
        } else {
            $message = "Impossible d'annuler cette inscription.";
            $message_type = "error";
        }
    } catch (PDOException $e) {
        $message = "Erreur lors de l'annulation.";
        $message_type = "error";
    }
}

// Mise à jour du profil
if (isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $new_user = htmlspecialchars(trim($_POST['user'] ?? ''));
    $new_number = htmlspecialchars(trim($_POST['number'] ?? ''));
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET user = ?, number = ? WHERE email = ?");
        $stmt->execute([$new_user, $new_number, $user_email]);
        
        $_SESSION['user_name'] = $new_user;
        $user['user'] = $new_user;
        $user['number'] = $new_number;
        
        $message = "Profil mis à jour avec succès !";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Erreur lors de la mise à jour.";
        $message_type = "error";
    }
}

// Récupérer les inscriptions de l'utilisateur
try {
    $stmt = $pdo->prepare("
        SELECT i.*, e.name, e.event_date, e.hour, e.lieu, e.description, e.prix, e.capacite, e.status
        FROM inscriptions i
        JOIN event e ON i.id_event = e.id_event
        WHERE i.user_email = ?
        ORDER BY e.event_date DESC
    ");
    $stmt->execute([$user_email]);
    $inscriptions = $stmt->fetchAll();
} catch (PDOException $e) {
    $inscriptions = [];
}

// Calculer l'âge à partir de la date de naissance
function calculateAge($birthDate) {
    $birth = new DateTime($birthDate);
    $today = new DateTime('today');
    return $birth->diff($today)->y;
}

// Formater la date
function formatDate($date) {
    $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    $d = new DateTime($date);
    return $d->format('d') . ' ' . $months[$d->format('n')-1] . ' ' . $d->format('Y');
}

// Formater l'heure
function formatTime($time) {
    return substr($time, 0, 5);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - NOVA</title>
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
                    <li><a href="profil.php">Mon Profil</a></li>
                    <?php if (($user['role'] ?? 'user') === 'organisateur' || ($user['role'] ?? 'user') === 'admin'): ?>
                    <li><a href="organisateur.php">Panel Orga</a></li>
                    <?php endif; ?>
                    <?php if (($user['role'] ?? 'user') === 'admin'): ?>
                    <li><a href="admin.php">Panel Admin</a></li>
                    <?php endif; ?>
                </ul>
                
                <div class="sidebar-footer">
                    <div class="sidebar-user">
                        <div class="sidebar-user-avatar">👤</div>
                        <div class="sidebar-user-info">
                            <h4><?php echo htmlspecialchars($user['user']); ?></h4>
                            <p><?php echo ucfirst($user['role'] ?? 'user'); ?></p>
                        </div>
                    </div>
                    <div class="sidebar-actions">
                        <a href="profil.php">Mon Profil</a>
                        <a href="profil.php?action=logout">Déconnexion</a>
                    </div>
                </div>
            </aside>
        </nav>
    </header>
    
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <main>
        <section class="profil-section">
            <div class="profil-bg-gradient"></div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="profil-container">
                <!-- Sidebar Profil -->
                <aside class="profil-sidebar">
                    <div class="profil-avatar">
                        <div class="avatar-circle">
                            <?php echo strtoupper(substr($user['user'], 0, 1)); ?>
                        </div>
                        <h2 class="profil-name"><?php echo htmlspecialchars($user['user']); ?></h2>
                        <p class="profil-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    
                    <div class="profil-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo count(array_filter($inscriptions, fn($i) => $i['statut'] == 'confirmé')); ?></span>
                            <span class="stat-label">Inscriptions actives</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?php echo count($inscriptions); ?></span>
                            <span class="stat-label">Total événements</span>
                        </div>
                    </div>

                    <nav class="profil-nav">
                        <button class="profil-nav-btn active" data-tab="inscriptions">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            Mes Inscriptions
                        </button>
                        <button class="profil-nav-btn" data-tab="settings">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="3"></circle>
                                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                            </svg>
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
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                <h3>Aucune inscription</h3>
                                <p>Vous n'êtes inscrit à aucun événement pour le moment.</p>
                                <a href="evenement.php" class="btn-gradient">Découvrir les événements</a>
                            </div>
                        <?php else: ?>
                            <div class="inscriptions-list">
                                <?php foreach ($inscriptions as $inscription): ?>
                                    <div class="inscription-card <?php echo $inscription['statut']; ?>">
                                        <div class="inscription-date-badge">
                                            <span class="day"><?php echo date('d', strtotime($inscription['event_date'])); ?></span>
                                            <span class="month"><?php echo strtoupper(date('M', strtotime($inscription['event_date']))); ?></span>
                                        </div>
                                        
                                        <div class="inscription-info">
                                            <div class="inscription-header">
                                                <h3><?php echo htmlspecialchars($inscription['name']); ?></h3>
                                                <span class="inscription-status status-<?php echo $inscription['statut']; ?>">
                                                    <?php 
                                                        switch($inscription['statut']) {
                                                            case 'confirmé': echo '✓ Confirmé'; break;
                                                            case 'annulé': echo '✗ Annulé'; break;
                                                            case 'en_attente': echo '⏳ En attente'; break;
                                                        }
                                                    ?>
                                                </span>
                                            </div>
                                            
                                            <div class="inscription-details">
                                                <span class="detail-item">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                                        <circle cx="12" cy="10" r="3"></circle>
                                                    </svg>
                                                    <?php echo htmlspecialchars($inscription['lieu']); ?>
                                                </span>
                                                <span class="detail-item">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="10"></circle>
                                                        <polyline points="12 6 12 12 16 14"></polyline>
                                                    </svg>
                                                    <?php echo formatTime($inscription['hour']); ?>
                                                </span>
                                                <span class="detail-item">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                                    </svg>
                                                    <?php echo $inscription['prix'] > 0 ? $inscription['prix'] . '€' : 'Gratuit'; ?>
                                                </span>
                                            </div>
                                            
                                            <p class="inscription-desc"><?php echo htmlspecialchars(substr($inscription['description'], 0, 150)); ?>...</p>
                                            
                                            <div class="inscription-meta">
                                                <span>Inscrit le <?php echo formatDate($inscription['date_inscription']); ?></span>
                                            </div>
                                        </div>
                                        
                                        <div class="inscription-actions">
                                            <?php if ($inscription['statut'] == 'confirmé'): ?>
                                                <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette inscription ?');">
                                                    <input type="hidden" name="action" value="cancel_inscription">
                                                    <input type="hidden" name="id_inscription" value="<?php echo $inscription['id_inscription']; ?>">
                                                    <button type="submit" class="btn-cancel">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <circle cx="12" cy="12" r="10"></circle>
                                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                                        </svg>
                                                        Annuler
                                                    </button>
                                                </form>
                                            <?php elseif ($inscription['statut'] == 'annulé'): ?>
                                                <span class="cancelled-badge">Inscription annulée</span>
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

                        <form method="POST" class="settings-form">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="form-grid">
                                <div class="input-group">
                                    <label>Nom d'utilisateur</label>
                                    <input type="text" name="user" value="<?php echo htmlspecialchars($user['user']); ?>" required>
                                </div>
                                
                                <div class="input-group">
                                    <label>Email</label>
                                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                    <small class="input-hint">L'email ne peut pas être modifié</small>
                                </div>
                                
                                <div class="input-group">
                                    <label>Date de naissance</label>
                                    <input type="date" value="<?php echo $user['date_of_birth']; ?>" disabled>
                                </div>
                                
                                <div class="input-group">
                                    <label>Sexe</label>
                                    <input type="text" value="<?php echo $user['sexe'] == 'H' ? 'Homme' : ($user['sexe'] == 'F' ? 'Femme' : 'Autre'); ?>" disabled>
                                </div>
                                
                                <div class="input-group">
                                    <label>Téléphone</label>
                                    <input type="tel" name="number" value="<?php echo htmlspecialchars($user['number']); ?>" placeholder="06 12 34 56 78">
                                </div>
                                
                                <div class="input-group">
                                    <label>Âge</label>
                                    <input type="text" value="<?php echo calculateAge($user['date_of_birth']); ?> ans" disabled>
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn-gradient">Sauvegarder les modifications</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-bottom">
            © 2025 NOVA ÉVÉNEMENTS.
        </div>
    </footer>

    <script>
        // Gestion des tabs
        document.querySelectorAll('.profil-nav-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Retirer la classe active de tous les boutons et tabs
                document.querySelectorAll('.profil-nav-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.profil-tab').forEach(t => t.classList.remove('active'));
                
                // Ajouter la classe active au bouton cliqué
                this.classList.add('active');
                
                // Afficher le tab correspondant
                const tabId = 'tab-' + this.dataset.tab;
                document.getElementById(tabId).classList.add('active');
            });
        });
    </script>

<script src="../js/navbar.js"></script>
</body>
</html>
