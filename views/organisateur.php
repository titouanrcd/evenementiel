<?php
session_start();
require_once 'db.php';

// Vérifier si l'utilisateur est connecté et est organisateur
if (!isset($_SESSION['user_email'])) {
    header('Location: connexion.php');
    exit();
}

$user_email = $_SESSION['user_email'];
$user_name = $_SESSION['user_name'] ?? '';

// Vérifier le rôle
try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE email = ?");
    $stmt->execute([$user_email]);
    $user = $stmt->fetch();
    
    if (!$user || ($user['role'] != 'organisateur' && $user['role'] != 'admin')) {
        header('Location: profil.php');
        exit();
    }
} catch (PDOException $e) {
    die("Erreur de vérification des droits.");
}

$message = '';
$message_type = '';

// Créer le dossier uploads s'il n'existe pas
$upload_dir = '../uploads/events/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

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
// TRAITEMENT : Créer un événement
// =========================================================
if (isset($_POST['action']) && $_POST['action'] == 'create_event') {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $description = htmlspecialchars(trim($_POST['description'] ?? ''));
    $event_date = $_POST['event_date'] ?? '';
    $hour = $_POST['hour'] ?? '';
    $lieu = htmlspecialchars(trim($_POST['lieu'] ?? ''));
    $capacite = intval($_POST['capacite'] ?? 0);
    $prix = intval($_POST['prix'] ?? 0);
    $tag = $_POST['tag'] ?? 'autre';
    $image_url = '';
    
    // Gestion de l'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid('event_') . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = 'uploads/events/' . $new_filename;
            }
        }
    } elseif (!empty($_POST['image_url'])) {
        $image_url = filter_var($_POST['image_url'], FILTER_VALIDATE_URL) ? $_POST['image_url'] : '';
    }
    
    // Validation
    if (empty($name) || empty($event_date) || empty($lieu) || $capacite <= 0) {
        $message = "Veuillez remplir tous les champs obligatoires.";
        $message_type = "error";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO event (name, description, event_date, hour, lieu, capacite, prix, tag, image, owner_email, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en attente')");
            $stmt->execute([$name, $description, $event_date, $hour, $lieu, $capacite, $prix, $tag, $image_url, $user_email]);
            
            $message = "Événement créé avec succès ! Il sera visible après validation par un administrateur.";
            $message_type = "success";
        } catch (PDOException $e) {
            $message = "Erreur lors de la création de l'événement.";
            $message_type = "error";
        }
    }
}

// =========================================================
// TRAITEMENT : Modifier un événement
// =========================================================
if (isset($_POST['action']) && $_POST['action'] == 'edit_event') {
    $id_event = intval($_POST['id_event']);
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $description = htmlspecialchars(trim($_POST['description'] ?? ''));
    $event_date = $_POST['event_date'] ?? '';
    $hour = $_POST['hour'] ?? '';
    $lieu = htmlspecialchars(trim($_POST['lieu'] ?? ''));
    $capacite = intval($_POST['capacite'] ?? 0);
    $prix = intval($_POST['prix'] ?? 0);
    $tag = $_POST['tag'] ?? 'autre';
    
    // Récupérer l'image actuelle
    $current = $pdo->prepare("SELECT image FROM event WHERE id_event = ? AND owner_email = ?");
    $current->execute([$id_event, $user_email]);
    $current_event = $current->fetch();
    $image_url = $current_event['image'] ?? '';
    
    // Nouvelle image ?
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid('event_') . '.' . $ext;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = 'uploads/events/' . $new_filename;
            }
        }
    } elseif (!empty($_POST['image_url'])) {
        $image_url = filter_var($_POST['image_url'], FILTER_VALIDATE_URL) ? $_POST['image_url'] : $image_url;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE event SET name = ?, description = ?, event_date = ?, hour = ?, lieu = ?, capacite = ?, prix = ?, tag = ?, image = ? WHERE id_event = ? AND owner_email = ?");
        $stmt->execute([$name, $description, $event_date, $hour, $lieu, $capacite, $prix, $tag, $image_url, $id_event, $user_email]);
        
        $message = "Événement modifié avec succès !";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Erreur lors de la modification.";
        $message_type = "error";
    }
}

// =========================================================
// TRAITEMENT : Supprimer un événement
// =========================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_event') {
    $id_event = intval($_POST['id_event']);
    
    try {
        $stmt = $pdo->prepare("DELETE FROM event WHERE id_event = ? AND owner_email = ?");
        $stmt->execute([$id_event, $user_email]);
        
        $message = "Événement supprimé.";
        $message_type = "success";
    } catch (PDOException $e) {
        $message = "Erreur lors de la suppression.";
        $message_type = "error";
    }
}

// Récupérer les événements de l'organisateur
try {
    $stmt = $pdo->prepare("
        SELECT e.*, 
        (SELECT COUNT(*) FROM inscriptions i WHERE i.id_event = e.id_event AND i.statut = 'confirmé') as nb_inscrits
        FROM event e 
        WHERE e.owner_email = ?
        ORDER BY e.event_date DESC
    ");
    $stmt->execute([$user_email]);
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $events = [];
}

// Mode édition
$edit_event = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    foreach ($events as $e) {
        if ($e['id_event'] == $edit_id) {
            $edit_event = $e;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Organisateur - NOVA</title>
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
                <li><a href="organisateur.php" class="active">Panel Orga</a></li>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <li><a href="admin.php">Panel Admin</a></li>
                <?php endif; ?>
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
                        <div class="admin-avatar">🎭</div>
                        <h3><?php echo htmlspecialchars($user_name); ?></h3>
                        <span class="role-badge">Organisateur</span>
                    </div>
                    
                    <nav class="admin-nav">
                        <a href="organisateur.php" class="admin-nav-link active">
                            <span class="nav-icon">📅</span>
                            Mes Événements
                        </a>
                        <a href="organisateur.php#create" class="admin-nav-link">
                            <span class="nav-icon">➕</span>
                            Créer un événement
                        </a>
                        <a href="profil.php" class="admin-nav-link">
                            <span class="nav-icon">👤</span>
                            Mon Profil
                        </a>
                    </nav>
                    
                    <div class="admin-stats">
                        <div class="stat-box">
                            <span class="stat-value"><?php echo count($events); ?></span>
                            <span class="stat-label">Événements</span>
                        </div>
                        <div class="stat-box">
                            <span class="stat-value"><?php echo array_sum(array_column($events, 'nb_inscrits')); ?></span>
                            <span class="stat-label">Inscrits total</span>
                        </div>
                    </div>
                </aside>

                <!-- Contenu principal -->
                <div class="admin-content">
                    <!-- Formulaire de création/édition -->
                    <div class="admin-card" id="create">
                        <div class="card-header">
                            <h2><?php echo $edit_event ? 'Modifier l\'événement' : 'Créer un événement'; ?></h2>
                        </div>
                        
                        <form method="POST" enctype="multipart/form-data" class="event-form">
                            <input type="hidden" name="action" value="<?php echo $edit_event ? 'edit_event' : 'create_event'; ?>">
                            <?php if ($edit_event): ?>
                                <input type="hidden" name="id_event" value="<?php echo $edit_event['id_event']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-row">
                                <div class="input-group">
                                    <label>Nom de l'événement *</label>
                                    <input type="text" name="name" required 
                                           value="<?php echo $edit_event ? htmlspecialchars($edit_event['name']) : ''; ?>"
                                           placeholder="Ex: Festival Electro 2025">
                                </div>
                                
                                <div class="input-group">
                                    <label>Catégorie *</label>
                                    <select name="tag" required>
                                        <?php foreach ($tags as $key => $label): ?>
                                            <option value="<?php echo $key; ?>" 
                                                    <?php echo ($edit_event && $edit_event['tag'] == $key) ? 'selected' : ''; ?>>
                                                <?php echo $label; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="input-group">
                                <label>Description</label>
                                <textarea name="description" rows="4" 
                                          placeholder="Décrivez votre événement..."><?php echo $edit_event ? htmlspecialchars($edit_event['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="input-group">
                                    <label>Date *</label>
                                    <input type="date" name="event_date" required 
                                           value="<?php echo $edit_event ? $edit_event['event_date'] : ''; ?>">
                                </div>
                                
                                <div class="input-group">
                                    <label>Heure *</label>
                                    <input type="time" name="hour" required 
                                           value="<?php echo $edit_event ? substr($edit_event['hour'], 0, 5) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="input-group">
                                <label>Lieu *</label>
                                <input type="text" name="lieu" required 
                                       value="<?php echo $edit_event ? htmlspecialchars($edit_event['lieu']) : ''; ?>"
                                       placeholder="Ex: Paris, Palais des Congrès">
                            </div>
                            
                            <div class="form-row">
                                <div class="input-group">
                                    <label>Capacité *</label>
                                    <input type="number" name="capacite" required min="1" 
                                           value="<?php echo $edit_event ? $edit_event['capacite'] : ''; ?>"
                                           placeholder="Nombre de places">
                                </div>
                                
                                <div class="input-group">
                                    <label>Prix (€)</label>
                                    <input type="number" name="prix" min="0" 
                                           value="<?php echo $edit_event ? $edit_event['prix'] : '0'; ?>"
                                           placeholder="0 = Gratuit">
                                </div>
                            </div>
                            
                            <div class="input-group">
                                <label>Image de l'événement</label>
                                <div class="image-upload-container">
                                    <div class="upload-option">
                                        <label class="upload-label">
                                            <input type="file" name="image" accept="image/*" class="file-input">
                                            <span class="upload-btn">📁 Choisir un fichier</span>
                                        </label>
                                    </div>
                                    <div class="upload-divider">ou</div>
                                    <div class="upload-option">
                                        <input type="text" name="image_url" 
                                               placeholder="URL de l'image (https://...)"
                                               value="<?php echo ($edit_event && strpos($edit_event['image'], 'http') === 0) ? htmlspecialchars($edit_event['image']) : ''; ?>">
                                    </div>
                                </div>
                                <?php if ($edit_event && $edit_event['image']): ?>
                                    <div class="current-image">
                                        <p>Image actuelle :</p>
                                        <img src="<?php echo (strpos($edit_event['image'], 'http') === 0) ? $edit_event['image'] : '../' . $edit_event['image']; ?>" 
                                             alt="Image actuelle" style="max-width: 200px; border-radius: 8px;">
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-actions">
                                <?php if ($edit_event): ?>
                                    <a href="organisateur.php" class="btn-secondary">Annuler</a>
                                <?php endif; ?>
                                <button type="submit" class="btn-gradient">
                                    <?php echo $edit_event ? 'Mettre à jour' : 'Créer l\'événement'; ?>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Liste des événements -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h2>Mes Événements</h2>
                            <span class="events-total"><?php echo count($events); ?> événement(s)</span>
                        </div>
                        
                        <?php if (empty($events)): ?>
                            <div class="empty-state">
                                <span class="empty-icon">📅</span>
                                <h3>Aucun événement</h3>
                                <p>Créez votre premier événement ci-dessus !</p>
                            </div>
                        <?php else: ?>
                            <div class="events-table-container">
                                <table class="events-table">
                                    <thead>
                                        <tr>
                                            <th>Événement</th>
                                            <th>Date</th>
                                            <th>Lieu</th>
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
                                                <td>
                                                    <?php echo date('d/m/Y', strtotime($event['event_date'])); ?><br>
                                                    <small><?php echo substr($event['hour'], 0, 5); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($event['lieu']); ?></td>
                                                <td>
                                                    <span class="inscrits-badge">
                                                        <?php echo $event['nb_inscrits']; ?> / <?php echo $event['capacite']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="status-badge status-<?php echo str_replace(' ', '-', $event['status']); ?>">
                                                        <?php echo $event['status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="organisateur.php?edit=<?php echo $event['id_event']; ?>#create" 
                                                           class="btn-action btn-edit" title="Modifier">✏️</a>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Supprimer cet événement ?');">
                                                            <input type="hidden" name="action" value="delete_event">
                                                            <input type="hidden" name="id_event" value="<?php echo $event['id_event']; ?>">
                                                            <button type="submit" class="btn-action btn-delete" title="Supprimer">🗑️</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
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
        // Preview du nom de fichier sélectionné
        document.querySelector('.file-input')?.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'Aucun fichier';
            const label = this.closest('.upload-label').querySelector('.upload-btn');
            label.textContent = '📁 ' + fileName;
        });
    </script>

</body>
</html>
