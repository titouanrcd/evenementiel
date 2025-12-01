<?php
/**
 * ============================================================
 * PANEL ORGANISATEUR - NOVA Événements
 * ============================================================
 */

require_once 'security.php';  // Sécurité EN PREMIER
require_once 'db.php';

// Vérifier si l'utilisateur est connecté
requireLogin();

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
    error_log("Erreur vérification droits organisateur: " . $e->getMessage());
    die("Erreur de vérification des droits.");
}

$message = '';
$message_type = '';

// Créer le dossier uploads s'il n'existe pas (avec permissions sécurisées)
$upload_dir = '../uploads/events/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$tags = [
    'sport' => 'Sport',
    'culture' => 'Culture',
    'soiree' => 'Soirée',
    'conference' => 'Conférence',
    'festival' => 'Festival',
    'autre' => 'Autre'
];

// =========================================================
// TRAITEMENT : Créer un événement
// =========================================================
if (isset($_POST['action']) && $_POST['action'] == 'create_event') {
    if (!verifyCsrfToken()) {
        $message = "Erreur de sécurité. Veuillez rafraîchir la page.";
        $message_type = "error";
    } else {
        $name = sanitizeString($_POST['name'] ?? '', 255);
        $description = sanitizeString($_POST['description'] ?? '', 5000);
        $event_date = sanitizeDate($_POST['event_date'] ?? '');
        $hour = $_POST['hour'] ?? '';
        $lieu = sanitizeString($_POST['lieu'] ?? '', 255);
        $capacite = sanitizeInt($_POST['capacite'] ?? 0, 1, 100000);
        $prix = sanitizeInt($_POST['prix'] ?? 0, 0, 10000);
        $tag = in_array($_POST['tag'] ?? '', array_keys($tags)) ? $_POST['tag'] : 'autre';
        $image_url = '';
        
        // Gestion sécurisée de l'image
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $uploadResult = secureFileUpload($_FILES['image'], $upload_dir);
            if ($uploadResult['success']) {
                $image_url = 'uploads/events/' . $uploadResult['filename'];
            } else {
                $message = $uploadResult['error'];
                $message_type = "error";
            }
        } elseif (!empty($_POST['image_url'])) {
            $image_url = filter_var($_POST['image_url'], FILTER_VALIDATE_URL) ? $_POST['image_url'] : '';
        }
        
        // Validation
        if (empty($name) || !$event_date || empty($lieu) || !$capacite) {
            $message = "Veuillez remplir tous les champs obligatoires.";
            $message_type = "error";
        } elseif (empty($message)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO event (name, description, event_date, hour, lieu, capacite, prix, tag, image, owner_email, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en attente')");
                $stmt->execute([$name, $description, $event_date, $hour, $lieu, $capacite, $prix ?: 0, $tag, $image_url, $user_email]);
                
                $message = "Événement créé avec succès ! Il sera visible après validation par un administrateur.";
                $message_type = "success";
            } catch (PDOException $e) {
                error_log("Erreur création événement: " . $e->getMessage());
                $message = "Erreur lors de la création de l'événement.";
                $message_type = "error";
            }
        }
    }
}

// =========================================================
// TRAITEMENT : Modifier un événement
// =========================================================
if (isset($_POST['action']) && $_POST['action'] == 'edit_event') {
    if (!verifyCsrfToken()) {
        $message = "Erreur de sécurité. Veuillez rafraîchir la page.";
        $message_type = "error";
    } else {
        $id_event = sanitizeInt($_POST['id_event'] ?? 0);
        $name = sanitizeString($_POST['name'] ?? '', 255);
        $description = sanitizeString($_POST['description'] ?? '', 5000);
        $event_date = sanitizeDate($_POST['event_date'] ?? '');
        $hour = $_POST['hour'] ?? '';
        $lieu = sanitizeString($_POST['lieu'] ?? '', 255);
        $capacite = sanitizeInt($_POST['capacite'] ?? 0, 1, 100000);
        $prix = sanitizeInt($_POST['prix'] ?? 0, 0, 10000);
        $tag = in_array($_POST['tag'] ?? '', array_keys($tags)) ? $_POST['tag'] : 'autre';
        
        if (!$id_event) {
            $message = "Événement invalide.";
            $message_type = "error";
        } else {
            // Récupérer l'image actuelle
            $current = $pdo->prepare("SELECT image FROM event WHERE id_event = ? AND owner_email = ?");
            $current->execute([$id_event, $user_email]);
            $current_event = $current->fetch();
            $image_url = $current_event['image'] ?? '';
            
            // Nouvelle image ?
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $uploadResult = secureFileUpload($_FILES['image'], $upload_dir);
                if ($uploadResult['success']) {
                    $image_url = 'uploads/events/' . $uploadResult['filename'];
                }
            } elseif (!empty($_POST['image_url'])) {
                $image_url = filter_var($_POST['image_url'], FILTER_VALIDATE_URL) ? $_POST['image_url'] : $image_url;
            }
            
            try {
                $stmt = $pdo->prepare("UPDATE event SET name = ?, description = ?, event_date = ?, hour = ?, lieu = ?, capacite = ?, prix = ?, tag = ?, image = ? WHERE id_event = ? AND owner_email = ?");
                $stmt->execute([$name, $description, $event_date, $hour, $lieu, $capacite, $prix ?: 0, $tag, $image_url, $id_event, $user_email]);
                
                $message = "Événement modifié avec succès !";
                $message_type = "success";
            } catch (PDOException $e) {
                error_log("Erreur modification événement: " . $e->getMessage());
                $message = "Erreur lors de la modification.";
                $message_type = "error";
            }
        }
    }
}

// =========================================================
// TRAITEMENT : Supprimer un événement
// =========================================================
if (isset($_POST['action']) && $_POST['action'] == 'delete_event') {
    if (!verifyCsrfToken()) {
        $message = "Erreur de sécurité. Veuillez rafraîchir la page.";
        $message_type = "error";
    } else {
        $id_event = sanitizeInt($_POST['id_event'] ?? 0);
        
        if ($id_event) {
            try {
                $stmt = $pdo->prepare("DELETE FROM event WHERE id_event = ? AND owner_email = ?");
                $stmt->execute([$id_event, $user_email]);
                
                $message = "Événement supprimé.";
                $message_type = "success";
            } catch (PDOException $e) {
                error_log("Erreur suppression événement: " . $e->getMessage());
                $message = "Erreur lors de la suppression.";
                $message_type = "error";
            }
        }
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
                    <li><a href="organisateur.php">Panel Orga</a></li>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <li><a href="admin.php">Panel Admin</a></li>
                    <?php endif; ?>
                </ul>
                
                <div class="sidebar-footer">
                    <div class="sidebar-user">
                        <div class="sidebar-user-avatar">O</div>
                        <div class="sidebar-user-info">
                            <h4><?php echo htmlspecialchars($user_name); ?></h4>
                            <p>Organisateur</p>
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
                        <div class="admin-avatar">O</div>
                        <h3><?php echo htmlspecialchars($user_name); ?></h3>
                        <span class="role-badge">Organisateur</span>
                    </div>
                    
                    <nav class="admin-nav">
                        <a href="organisateur.php" class="admin-nav-link active">
                            <span class="nav-icon">-</span>
                            Mes Événements
                        </a>
                        <a href="organisateur.php#create" class="admin-nav-link">
                            <span class="nav-icon">+</span>
                            Créer un événement
                        </a>
                        <a href="profil.php" class="admin-nav-link">
                            <span class="nav-icon">-</span>
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
                    <!-- Formulaire de création/édition avec WIZARD -->
                    <div class="admin-card wizard-card-container" id="create">
                        <div class="wizard-header">
                            <h3 style="margin-bottom: 20px; font-size: 1.2rem;">
                                <?php echo $edit_event ? 'Modifier votre événement' : 'Initialisation de l\'Événement'; ?>
                            </h3>
                            <div class="progress-container">
                                <div class="progress-line-bg"></div>
                                <div class="progress-line-fill" id="progress-fill"></div>
                                
                                <div class="step-item active" id="step-indicator-1">
                                    <div class="step-circle">1</div>
                                    <div class="step-label">Type</div>
                                </div>
                                <div class="step-item" id="step-indicator-2">
                                    <div class="step-circle">2</div>
                                    <div class="step-label">Détails</div>
                                </div>
                                <div class="step-item" id="step-indicator-3">
                                    <div class="step-circle">3</div>
                                    <div class="step-label">Image</div>
                                </div>
                            </div>
                        </div>

                        <form method="POST" enctype="multipart/form-data" class="wizard-body-form" id="event-wizard-form">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="<?php echo $edit_event ? 'edit_event' : 'create_event'; ?>">
                            <?php if ($edit_event): ?>
                                <input type="hidden" name="id_event" value="<?php echo intval($edit_event['id_event']); ?>">
                            <?php endif; ?>

                            <!-- STEP 1: TYPE D'ÉVÉNEMENT -->
                            <div class="step-content active" id="step-1">
                                <h2>Quel est le type de votre événement ?</h2>
                                <p class="step-desc">Sélectionnez la catégorie qui correspond à votre événement.</p>
                                
                                <div class="type-grid">
                                    <label>
                                        <input type="radio" name="tag" value="sport" class="type-radio" hidden <?php echo (!$edit_event || $edit_event['tag'] == 'sport') ? 'checked' : ''; ?>>
                                        <div class="type-card">
                                            <span class="type-icon">S</span>
                                            <h4>Sport</h4>
                                            <p style="font-size:0.8rem; color:#666; margin-top:5px;">Compétitions, matchs, tournois.</p>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="tag" value="culture" class="type-radio" hidden <?php echo ($edit_event && $edit_event['tag'] == 'culture') ? 'checked' : ''; ?>>
                                        <div class="type-card">
                                            <span class="type-icon">C</span>
                                            <h4>Culture</h4>
                                            <p style="font-size:0.8rem; color:#666; margin-top:5px;">Spectacles, expositions, théâtre.</p>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="tag" value="soiree" class="type-radio" hidden <?php echo ($edit_event && $edit_event['tag'] == 'soiree') ? 'checked' : ''; ?>>
                                        <div class="type-card">
                                            <span class="type-icon">G</span>
                                            <h4>Soirée / Gala</h4>
                                            <p style="font-size:0.8rem; color:#666; margin-top:5px;">Cocktails, dîners, célébrations.</p>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="tag" value="conference" class="type-radio" hidden <?php echo ($edit_event && $edit_event['tag'] == 'conference') ? 'checked' : ''; ?>>
                                        <div class="type-card">
                                            <span class="type-icon">K</span>
                                            <h4>Conférence</h4>
                                            <p style="font-size:0.8rem; color:#666; margin-top:5px;">Séminaires, présentations, talks.</p>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="tag" value="festival" class="type-radio" hidden <?php echo ($edit_event && $edit_event['tag'] == 'festival') ? 'checked' : ''; ?>>
                                        <div class="type-card">
                                            <span class="type-icon">F</span>
                                            <h4>Festival</h4>
                                            <p style="font-size:0.8rem; color:#666; margin-top:5px;">Concerts, public, outdoor.</p>
                                        </div>
                                    </label>

                                    <label>
                                        <input type="radio" name="tag" value="autre" class="type-radio" hidden <?php echo ($edit_event && $edit_event['tag'] == 'autre') ? 'checked' : ''; ?>>
                                        <div class="type-card">
                                            <span class="type-icon">+</span>
                                            <h4>Autre</h4>
                                            <p style="font-size:0.8rem; color:#666; margin-top:5px;">Autre type d'événement.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- STEP 2: DÉTAILS LOGISTIQUES -->
                            <div class="step-content" id="step-2">
                                <h2>Logistique & Détails</h2>
                                <p class="step-desc">Donnez-nous les informations essentielles de votre événement.</p>

                                <div class="form-row">
                                    <div class="input-group">
                                        <label>Nom de l'événement *</label>
                                        <input type="text" name="name" required placeholder="Ex: Festival Electro 2025"
                                               value="<?php echo $edit_event ? htmlspecialchars($edit_event['name']) : ''; ?>">
                                    </div>
                                    <div class="input-group">
                                        <label>Date *</label>
                                        <input type="date" name="event_date" required
                                               value="<?php echo $edit_event ? $edit_event['event_date'] : ''; ?>">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="input-group">
                                        <label>Heure *</label>
                                        <input type="time" name="hour" required
                                               value="<?php echo $edit_event ? substr($edit_event['hour'], 0, 5) : ''; ?>">
                                    </div>
                                    <div class="input-group">
                                        <label>Lieu *</label>
                                        <input type="text" name="lieu" required placeholder="Ex: Paris, Palais des Congrès"
                                               value="<?php echo $edit_event ? htmlspecialchars($edit_event['lieu']) : ''; ?>">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="input-group">
                                        <label>Capacité *</label>
                                        <input type="number" name="capacite" required min="1" placeholder="Nombre de places"
                                               value="<?php echo $edit_event ? $edit_event['capacite'] : ''; ?>">
                                    </div>
                                    <div class="input-group">
                                        <label>Prix (€)</label>
                                        <input type="number" name="prix" min="0" placeholder="0 = Gratuit"
                                               value="<?php echo $edit_event ? $edit_event['prix'] : '0'; ?>">
                                    </div>
                                </div>

                                <div class="input-group">
                                    <label>Description</label>
                                    <textarea name="description" rows="5" placeholder="Décrivez votre événement, l'atmosphère, les détails...">
                                        <?php echo $edit_event ? htmlspecialchars($edit_event['description']) : ''; ?>
                                    </textarea>
                                </div>
                            </div>

                            <!-- STEP 3: IMAGE & FINITIONS -->
                            <div class="step-content" id="step-3">
                                <h2>Image & Finitions</h2>
                                <p class="step-desc">Ajoutez une image représentative de votre événement.</p>

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
                                            <input type="text" name="image_url" placeholder="URL de l'image (https://...)"
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
                            </div>

                        </form>

                        <!-- FOOTER NAVIGATION -->
                        <div class="wizard-footer">
                            <button type="button" class="btn-text" id="btn-prev" style="visibility: hidden;">← Retour</button>
                            <button type="button" class="btn-next" id="btn-next">Suivant →</button>
                        </div>

                    </div>

                    <!-- Liste des événements -->
                    <div class="admin-card">
                        <div class="card-header">
                            <h2>Mes Événements</h2>
                            <span class="events-total"><?php echo count($events); ?> événement(s)</span>
                        </div>
                        
                        <?php if (empty($events)): ?>
                            <div class="empty-state">
                                <span class="empty-icon">-</span>
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
                                                        <a href="organisateur.php?edit=<?php echo intval($event['id_event']); ?>#create" 
                                                           class="btn-action btn-edit" title="Modifier">Modifier</a>
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Supprimer cet événement ?');">
                                                            <?php echo csrfField(); ?>
                                                            <input type="hidden" name="action" value="delete_event">
                                                            <input type="hidden" name="id_event" value="<?php echo intval($event['id_event']); ?>">
                                                            <button type="submit" class="btn-action btn-delete" title="Supprimer">X</button>
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
        // ========== WIZARD LOGIC ==========
        let currentStep = 1;
        const totalSteps = 3;

        const btnNext = document.getElementById('btn-next');
        const btnPrev = document.getElementById('btn-prev');
        const progressFill = document.getElementById('progress-fill');
        const form = document.getElementById('event-wizard-form');

        function updateUI() {
            // Update Form Visibility
            document.querySelectorAll('.step-content').forEach(el => el.classList.remove('active'));
            document.getElementById(`step-${currentStep}`)?.classList.add('active');

            // Update Header Indicators
            for(let i=1; i<=totalSteps; i++) {
                const indicator = document.getElementById(`step-indicator-${i}`);
                if (!indicator) continue;
                indicator.classList.remove('active', 'completed');
                if(i < currentStep) indicator.classList.add('completed');
                if(i === currentStep) indicator.classList.add('active');
            }

            // Update Progress Bar Line
            const percentage = ((currentStep - 1) / (totalSteps - 1)) * 100;
            progressFill.style.width = `${percentage}%`;

            // Update Buttons
            btnPrev.style.visibility = currentStep === 1 ? 'hidden' : 'visible';
            
            if(currentStep === totalSteps) {
                btnNext.textContent = 'Créer l\'événement →';
                btnNext.style.background = '#FF9900';
            } else {
                btnNext.textContent = 'Suivant →';
                btnNext.style.background = 'linear-gradient(135deg, #FF00CC 0%, #FF9900 100%)';
            }
        }

        function validateStep(stepNum) {
            const step = document.getElementById(`step-${stepNum}`);
            if (!step) return true;

            // Étape 1: Valider la sélection du type
            if (stepNum === 1) {
                const tagSelected = form.querySelector('input[name="tag"]:checked');
                if (!tagSelected) {
                    alert('Veuillez sélectionner un type d\'événement');
                    return false;
                }
            }

            // Étape 2: Valider les champs obligatoires
            if (stepNum === 2) {
                const name = form.querySelector('input[name="name"]').value.trim();
                const date = form.querySelector('input[name="event_date"]').value;
                const hour = form.querySelector('input[name="hour"]').value;
                const lieu = form.querySelector('input[name="lieu"]').value.trim();
                const capacite = form.querySelector('input[name="capacite"]').value;

                if (!name || !date || !hour || !lieu || !capacite) {
                    alert('Veuillez remplir tous les champs obligatoires (nom, date, heure, lieu, capacité)');
                    return false;
                }
                if (capacite <= 0) {
                    alert('La capacité doit être supérieure à 0');
                    return false;
                }
            }

            return true;
        }

        btnNext.addEventListener('click', () => {
            if (validateStep(currentStep)) {
                if(currentStep < totalSteps) {
                    currentStep++;
                    updateUI();
                } else {
                    // Soumettre le formulaire
                    form.submit();
                }
            }
        });

        btnPrev.addEventListener('click', () => {
            if(currentStep > 1) {
                currentStep--;
                updateUI();
            }
        });

        // Preview du nom de fichier sélectionné
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name || 'Aucun fichier';
                const label = this.closest('.upload-label').querySelector('.upload-btn');
                label.textContent = '📁 ' + fileName;
            });
        });

        // Initialiser l'UI du wizard
        updateUI();
    </script>

<script src="../js/navbar.js"></script>
</body>
</html>
