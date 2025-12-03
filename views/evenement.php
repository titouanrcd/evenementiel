<?php
/**
 * ============================================================
 * PAGE DES ÉVÉNEMENTS - NOVA Événements
 * ============================================================
 */

require_once 'security.php';  // Sécurité EN PREMIER
require_once 'db.php';

$is_logged_in = isLoggedIn();
$user_name = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';
$user_role = $_SESSION['user_role'] ?? 'user';

// Messages
$message = '';
$message_type = '';

// Inscription à un événement
if (isset($_POST['action']) && $_POST['action'] == 'inscription' && $is_logged_in) {
    if (!verifyCsrfToken()) {
        $message = "Erreur de sécurité. Veuillez rafraîchir la page.";
        $message_type = "error";
    } else {
        $id_event = sanitizeInt($_POST['id_event'] ?? 0);
        
        if (!$id_event) {
            $message = "Événement invalide.";
            $message_type = "error";
        } else {
            try {
                // Vérifier si déjà inscrit
                $check = $pdo->prepare("SELECT * FROM inscriptions WHERE user_email = ? AND id_event = ? AND statut = 'confirmé'");
                $check->execute([$user_email, $id_event]);
                
                if ($check->rowCount() > 0) {
                    $message = "Vous êtes déjà inscrit à cet événement.";
                    $message_type = "error";
                } else {
                    // Vérifier la capacité
                    $cap = $pdo->prepare("SELECT e.capacite, COUNT(i.id_inscription) as inscrits 
                                          FROM event e 
                                          LEFT JOIN inscriptions i ON e.id_event = i.id_event AND i.statut = 'confirmé'
                                          WHERE e.id_event = ?
                                          GROUP BY e.id_event");
                    $cap->execute([$id_event]);
                    $event_cap = $cap->fetch();
                    
                    if ($event_cap && $event_cap['inscrits'] >= $event_cap['capacite']) {
                        $message = "Désolé, cet événement est complet.";
                        $message_type = "error";
                    } else {
                        // Réactiver une inscription annulée ou en créer une nouvelle
                        $reactivate = $pdo->prepare("UPDATE inscriptions SET statut = 'confirmé', date_inscription = NOW() WHERE user_email = ? AND id_event = ?");
                        $reactivate->execute([$user_email, $id_event]);
                        
                        if ($reactivate->rowCount() == 0) {
                            $stmt = $pdo->prepare("INSERT INTO inscriptions (user_email, id_event) VALUES (?, ?)");
                            $stmt->execute([$user_email, $id_event]);
                        }
                        
                        $message = "Inscription réussie ! Rendez-vous dans votre profil pour voir vos inscriptions.";
                        $message_type = "success";
                    }
                }
            } catch (PDOException $e) {
                error_log("Erreur inscription événement: " . $e->getMessage());
                $message = "Erreur lors de l'inscription.";
                $message_type = "error";
            }
        }
    }
}

// Récupérer les filtres avec sanitization
$search = sanitizeString($_GET['search'] ?? '', 100);
$tag = isset($_GET['tag']) && in_array($_GET['tag'], array_keys(['sport' => 1, 'culture' => 1, 'soiree' => 1, 'conference' => 1, 'festival' => 1, 'autre' => 1])) ? $_GET['tag'] : '';
$lieu = sanitizeString($_GET['lieu'] ?? '', 255);
$date_from = sanitizeDate($_GET['date_from'] ?? '') ?: '';
$date_to = sanitizeDate($_GET['date_to'] ?? '') ?: '';
$prix_max = sanitizeInt($_GET['prix_max'] ?? 200, 0, 10000) ?: 200;

// Construire la requête SQL avec filtres
$sql = "SELECT e.*, 
        (SELECT COUNT(*) FROM inscriptions i WHERE i.id_event = e.id_event AND i.statut = 'confirmé') as nb_inscrits
        FROM event e 
        WHERE e.status = 'publié'";
$params = [];

if (!empty($search)) {
    // Échapper les caractères spéciaux LIKE
    $searchEscaped = escapeLike($search);
    $sql .= " AND (e.name LIKE ? OR e.description LIKE ? OR e.lieu LIKE ?)";
    $searchParam = "%$searchEscaped%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($tag)) {
    $sql .= " AND e.tag = ?";
    $params[] = $tag;
}

if (!empty($lieu)) {
    $lieuEscaped = escapeLike($lieu);
    $sql .= " AND e.lieu LIKE ?";
    $params[] = "%$lieuEscaped%";
}

if (!empty($date_from)) {
    $sql .= " AND e.event_date >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $sql .= " AND e.event_date <= ?";
    $params[] = $date_to;
}

if ($prix_max < 200) {
    $sql .= " AND e.prix <= ?";
    $params[] = $prix_max;
}

$sql .= " ORDER BY e.event_date ASC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erreur récupération événements: " . $e->getMessage());
    $events = [];
}

// Récupérer les lieux uniques pour le filtre
try {
    $lieux_stmt = $pdo->query("SELECT DISTINCT lieu FROM event WHERE status = 'publié' ORDER BY lieu");
    $lieux = $lieux_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $lieux = [];
}

$tags = [
    'sport' => 'Sport',
    'culture' => 'Culture',
    'soiree' => 'Soirée',
    'conference' => 'Conférence',
    'festival' => 'Festival',
    'autre' => 'Autre'
];

// Vérifier si l'utilisateur est inscrit à un événement
function isUserRegistered($pdo, $user_email, $id_event) {
    if (empty($user_email)) return false;
    $stmt = $pdo->prepare("SELECT id_inscription FROM inscriptions WHERE user_email = ? AND id_event = ? AND statut = 'confirmé'");
    $stmt->execute([$user_email, $id_event]);
    return $stmt->rowCount() > 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Événements - NOVA ÉVÉNEMENTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    
    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=VOTRE_CLE_API&libraries=places"></script>
    
    <style>
        /* Modal Itinéraire */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .modal-overlay.active {
            display: flex;
        }
        .modal-content {
            background: #1a1a2e;
            border-radius: 16px;
            width: 90%;
            max-width: 900px;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .modal-header h3 {
            color: #fff;
            margin: 0;
        }
        .modal-close {
            background: none;
            border: none;
            color: #fff;
            font-size: 28px;
            cursor: pointer;
            padding: 5px;
        }
        .modal-close:hover {
            color: #ff6b6b;
        }
        .modal-body {
            padding: 20px;
        }
        #map {
            width: 100%;
            height: 400px;
            border-radius: 12px;
            margin-bottom: 15px;
        }
        .directions-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }
        .direction-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }
        .direction-card h4 {
            color: #888;
            font-size: 12px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .direction-card p {
            color: #fff;
            font-size: 18px;
            margin: 0;
            font-weight: 600;
        }
        .btn-directions {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #4285f4, #34a853);
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            margin-top: 10px;
            transition: transform 0.2s;
        }
        .btn-directions:hover {
            transform: scale(1.05);
        }
        .btn-itineraire {
            background: linear-gradient(135deg, #4285f4, #34a853);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 10px;
            transition: transform 0.2s, opacity 0.2s;
        }
        .btn-itineraire:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }
        .transport-modes {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .transport-mode {
            flex: 1;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid transparent;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
        }
        .transport-mode:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .transport-mode.active {
            border-color: #4285f4;
            background: rgba(66, 133, 244, 0.2);
        }
        .transport-mode svg {
            width: 24px;
            height: 24px;
            margin-bottom: 5px;
        }
        .transport-mode span {
            display: block;
            color: #fff;
            font-size: 12px;
        }
        @media (max-width: 768px) {
            .directions-info {
                grid-template-columns: 1fr;
            }
            .transport-modes {
                flex-wrap: wrap;
            }
            .transport-mode {
                flex: 1 1 45%;
            }
        }
    </style>
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
                    <?php if ($is_logged_in): ?>
                    <li><a href="profil.php">Mon Profil</a></li>
                    <?php if ($user_role === 'organisateur' || $user_role === 'admin'): ?>
                    <li><a href="organisateur.php">Panel Orga</a></li>
                    <?php endif; ?>
                    <?php if ($user_role === 'admin'): ?>
                    <li><a href="admin.php">Panel Admin</a></li>
                    <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <?php if ($is_logged_in): ?>
                    <div class="sidebar-footer">
                        <div class="sidebar-user">
                            <div class="sidebar-user-avatar">U</div>
                            <div class="sidebar-user-info">
                                <h4><?php echo htmlspecialchars($user_name); ?></h4>
                                <p><?php echo ucfirst($user_role); ?></p>
                            </div>
                        </div>
                        <div class="sidebar-actions">
                            <a href="profil.php">Mon Profil</a>
                            <a href="profil.php?action=logout">Déconnexion</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="sidebar-footer">
                        <div class="sidebar-actions">
                            <a href="connexion.php">Connexion</a>
                        </div>
                    </div>
                <?php endif; ?>
            </aside>
        </nav>
    </header>
    
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <main>
        <section class="events-hero">
            <div class="events-hero-content">
                <h1>Trouvez votre événement</h1>
                <p>Concerts, vie associative, soirées étudiantes... tout est là.</p>
            </div>
        </section>

        <?php if ($message): ?>
            <div class="alert-container">
                <div class="alert alert-<?php echo $message_type; ?>">
                    <?php echo $message; ?>
                </div>
            </div>
        <?php endif; ?>

        <section class="events-filter-section">
            <div class="events-container">
                <aside class="events-filters">
                    <form method="GET" class="filters-form">
                        <div class="filter-header">
                            <h3>Rechercher un événement</h3>
                            <a href="evenement.php" class="filter-reset">Réinitialiser</a>
                        </div>

                        <div class="filter-group">
                            <label for="searchInput" class="filter-label">Nom de l'événement</label>
                            <input type="text" name="search" id="searchInput" class="filter-input" 
                                   placeholder="Rechercher un événement..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Catégorie</label>
                            <div class="filter-tags">
                                <?php foreach ($tags as $key => $label): ?>
                                    <label class="tag-option <?php echo ($tag == $key) ? 'active' : ''; ?>">
                                        <input type="radio" name="tag" value="<?php echo $key; ?>" 
                                               <?php echo ($tag == $key) ? 'checked' : ''; ?>>
                                        <span><?php echo $label; ?></span>
                                    </label>
                                <?php endforeach; ?>
                                <label class="tag-option <?php echo empty($tag) ? 'active' : ''; ?>">
                                    <input type="radio" name="tag" value="" <?php echo empty($tag) ? 'checked' : ''; ?>>
                                    <span>🔄 Tous</span>
                                </label>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label for="lieuSelect" class="filter-label">Lieu</label>
                            <select name="lieu" id="lieuSelect" class="filter-select">
                                <option value="">Toutes les villes</option>
                                <?php foreach ($lieux as $l): ?>
                                    <option value="<?php echo htmlspecialchars($l); ?>" 
                                            <?php echo ($lieu == $l) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($l); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Période</label>
                            <div class="date-range">
                                <div class="date-input-group">
                                    <label for="dateFrom" class="date-label">Du</label>
                                    <input type="date" name="date_from" id="dateFrom" class="filter-input" 
                                           value="<?php echo $date_from; ?>">
                                </div>
                                <div class="date-input-group">
                                    <label for="dateTo" class="date-label">Au</label>
                                    <input type="date" name="date_to" id="dateTo" class="filter-input" 
                                           value="<?php echo $date_to; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Prix Maximum</label>
                            <div class="price-range">
                                <input type="range" name="prix_max" id="priceRange" class="price-slider" 
                                       min="0" max="200" value="<?php echo $prix_max; ?>">
                                <div class="price-labels">
                                    <span>Gratuit</span>
                                    <span id="priceDisplay" class="price-value"><?php echo $prix_max >= 200 ? '200€+' : $prix_max . '€'; ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="filter-group">
                            <button type="submit" class="btn-gradient filter-apply-btn">
                                Rechercher
                            </button>
                        </div>
                    </form>
                </aside>

                <div class="events-content">
                    <div class="events-header-info">
                        <p class="events-count"><?php echo count($events); ?> événement(s) trouvé(s)</p>
                    </div>

                    <?php if (empty($events)): ?>
                        <div class="no-events">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <h3>Aucun événement trouvé</h3>
                            <p>Essayez de modifier vos critères de recherche.</p>
                        </div>
                    <?php else: ?>
                        <div class="events-grid">
                            <?php foreach ($events as $index => $event): 
                                $is_registered = isUserRegistered($pdo, $user_email, $event['id_event']);
                                $is_full = $event['nb_inscrits'] >= $event['capacite'];
                                $places_restantes = $event['capacite'] - $event['nb_inscrits'];
                            ?>
                                <article class="event-card <?php echo ($index === 0) ? 'featured' : ''; ?>">
                                    <div class="event-image">
                                        <?php if ($event['image']): ?>
                                            <img src="<?php echo htmlspecialchars($event['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($event['name']); ?>"
                                                 onerror="this.src='https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800'">
                                        <?php else: ?>
                                            <img src="https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800" 
                                                 alt="<?php echo htmlspecialchars($event['name']); ?>">
                                        <?php endif; ?>
                                        
                                        <div class="event-tag tag-<?php echo $event['tag']; ?>">
                                            <?php echo $tags[$event['tag']] ?? $event['tag']; ?>
                                        </div>
                                        
                                        <?php if ($event['prix'] == 0): ?>
                                            <div class="event-status">Gratuit</div>
                                        <?php endif; ?>
                                        
                                        <div class="event-date-badge">
                                            <span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                                            <span class="month"><?php echo strtoupper(date('M', strtotime($event['event_date']))); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="event-info">
                                        <h3 class="event-title"><?php echo htmlspecialchars($event['name']); ?></h3>
                                        <p class="event-location">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                                <circle cx="12" cy="10" r="3"></circle>
                                            </svg>
                                            <?php echo htmlspecialchars($event['lieu']); ?>
                                            <button type="button" class="btn-itineraire" 
                                                    onclick="openDirections('<?php echo htmlspecialchars(addslashes($event['lieu'])); ?>', '<?php echo htmlspecialchars(addslashes($event['name'])); ?>')">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
                                                </svg>
                                                Itinéraire
                                            </button>
                                        </p>
                                        <p class="event-time">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polyline points="12 6 12 12 16 14"></polyline>
                                            </svg>
                                            <?php echo date('H:i', strtotime($event['hour'])); ?>
                                        </p>
                                        <p class="event-description">
                                            <?php echo htmlspecialchars(substr($event['description'], 0, 120)); ?>...
                                        </p>
                                        
                                        <div class="event-meta">
                                            <span class="event-capacity <?php echo $is_full ? 'full' : ''; ?>">
                                                <?php if ($is_full): ?>
                                                    <span class="capacity-full">COMPLET</span>
                                                <?php else: ?>
                                                    <?php echo $places_restantes; ?> place(s) restante(s)
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        
                                        <div class="event-details">
                                            <span class="event-price">
                                                <?php echo $event['prix'] > 0 ? $event['prix'] . '€' : 'Gratuit'; ?>
                                            </span>
                                            
                                            <?php if ($is_logged_in): ?>
                                                <?php if ($is_registered): ?>
                                                    <span class="btn-registered">Inscrit</span>
                                                <?php elseif ($is_full): ?>
                                                    <span class="btn-full-event">Complet</span>
                                                <?php else: ?>
                                                    <form method="POST" class="inscription-form">
                                                        <?php echo csrfField(); ?>
                                                        <input type="hidden" name="action" value="inscription">
                                                        <input type="hidden" name="id_event" value="<?php echo intval($event['id_event']); ?>">
                                                        <button type="submit" class="event-btn">S'inscrire</button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="connexion.php" class="event-btn">Connexion</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-col footer-col-main">
                <div class="logo footer-logo">NOVA<span>.</span></div>
                <p class="footer-description">
                    Agence événementielle nouvelle génération. Nous créons l'inattendu, nous gérons l'impossible, nous illuminons vos instants.
                </p>
            </div>
            
            <div class="footer-col">
                <h5>AGENCE</h5>
                <ul>
                    <li><a href="index.html">Accueil</a></li>
                    <li><a href="#">L'Équipe</a></li>
                    <li><a href="#">Carrières</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h5>EXPERTISE</h5>
                <ul>
                    <li><a href="#">Corporate</a></li>
                    <li><a href="#">Festivals</a></li>
                    <li><a href="#">Mariages</a></li>
                    <li><a href="#">Scénographie</a></li>
                </ul>
            </div>

             <div class="social-links">
    <a href="https://www.instagram.com/" class="social-link instagram" aria-label="Instagram">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="24" height="24">
        <path fill="currentColor" d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.5 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/>
        </svg>
    </a>

    <a href="https://www.linkedin.com/feed/" class="social-link linkedin" aria-label="LinkedIn">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="24" height="24">
            <path fill="currentColor" d="M100.28 448H7.4V148.9h92.88zM53.79 108.1C24.09 108.1 0 83.5 0 53.8a53.79 53.79 0 0 1 107.58 0c0 29.7-24.1 54.3-53.79 54.3zM447.9 448h-92.68V302.4c0-34.7-.7-79.2-48.29-79.2-48.29 0-55.69 37.7-55.69 76.7V448h-92.78V148.9h89.08v40.8h1.3c12.4-23.5 42.69-48.3 87.88-48.3 94 0 111.28 61.9 111.28 142.3V448z"/>
        </svg>
    </a>

    <a href="https://www.tiktok.com/" class="social-link tiktok" aria-label="TikTok">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="24" height="24">
        <path fill="#FFFFFF" d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z"/>
        </svg>
    </a>
</div>
        </div>
        <div class="footer-bottom">
            © 2025 NOVA ÉVÉNEMENTS. Tous droits réservés.
        </div>
    </footer>

    <script>
        // Mise à jour du prix en temps réel
        const priceSlider = document.getElementById('priceRange');
        const priceDisplay = document.getElementById('priceDisplay');
        
        if (priceSlider && priceDisplay) {
            priceSlider.addEventListener('input', function() {
                const value = parseInt(this.value);
                priceDisplay.textContent = value >= 200 ? '200€+' : value + '€';
            });
        }
    </script>
    
    <!-- Modal Itinéraire -->
    <div class="modal-overlay" id="directionsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Itinéraire vers l'événement</h3>
                <button class="modal-close" onclick="closeDirections()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="transport-modes">
                    <div class="transport-mode active" data-mode="DRIVING" onclick="changeTransportMode('DRIVING', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                        </svg>
                        <span>Voiture</span>
                    </div>
                    <div class="transport-mode" data-mode="TRANSIT" onclick="changeTransportMode('TRANSIT', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2c-4.42 0-8 .5-8 4v9.5C4 17.43 5.57 19 7.5 19L6 20.5v.5h12v-.5L16.5 19c1.93 0 3.5-1.57 3.5-3.5V6c0-3.5-3.58-4-8-4zM7.5 17c-.83 0-1.5-.67-1.5-1.5S6.67 14 7.5 14s1.5.67 1.5 1.5S8.33 17 7.5 17zm3.5-6H6V6h5v5zm2 0V6h5v5h-5zm3.5 6c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/>
                        </svg>
                        <span>Transports</span>
                    </div>
                    <div class="transport-mode" data-mode="WALKING" onclick="changeTransportMode('WALKING', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M13.5 5.5c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zM9.8 8.9L7 23h2.1l1.8-8 2.1 2v6h2v-7.5l-2.1-2 .6-3C14.8 12 16.8 13 19 13v-2c-1.9 0-3.5-1-4.3-2.4l-1-1.6c-.4-.6-1-1-1.7-1-.3 0-.5.1-.8.1L6 8.3V13h2V9.6l1.8-.7"/>
                        </svg>
                        <span>À pied</span>
                    </div>
                    <div class="transport-mode" data-mode="BICYCLING" onclick="changeTransportMode('BICYCLING', this)">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M15.5 5.5c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zM5 12c-2.8 0-5 2.2-5 5s2.2 5 5 5 5-2.2 5-5-2.2-5-5-5zm0 8.5c-1.9 0-3.5-1.6-3.5-3.5s1.6-3.5 3.5-3.5 3.5 1.6 3.5 3.5-1.6 3.5-3.5 3.5zm5.8-10l2.4-2.4.8.8c1.3 1.3 3 2.1 5.1 2.1V9c-1.5 0-2.7-.6-3.6-1.5l-1.9-1.9c-.5-.4-1-.6-1.6-.6s-1.1.2-1.4.6L7.8 8.4c-.4.4-.6.9-.6 1.4 0 .6.2 1.1.6 1.4L11 14v5h2v-6.2l-2.2-2.3zM19 12c-2.8 0-5 2.2-5 5s2.2 5 5 5 5-2.2 5-5-2.2-5-5-5zm0 8.5c-1.9 0-3.5-1.6-3.5-3.5s1.6-3.5 3.5-3.5 3.5 1.6 3.5 3.5-1.6 3.5-3.5 3.5z"/>
                        </svg>
                        <span>Vélo</span>
                    </div>
                </div>
                
                <div id="map"></div>
                
                <div class="directions-info">
                    <div class="direction-card">
                        <h4>Distance</h4>
                        <p id="distanceInfo">--</p>
                    </div>
                    <div class="direction-card">
                        <h4>Durée estimée</h4>
                        <p id="durationInfo">--</p>
                    </div>
                    <div class="direction-card">
                        <h4>Destination</h4>
                        <p id="destinationInfo">--</p>
                    </div>
                </div>
                
                <a id="googleMapsLink" href="#" target="_blank" class="btn-directions">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                    </svg>
                    Ouvrir dans Google Maps
                </a>
            </div>
        </div>
    </div>
    
    <script>
        let map;
        let directionsService;
        let directionsRenderer;
        let currentDestination = '';
        let currentMode = 'DRIVING';
        let userLocation = null;
        
        // Initialiser la carte
        function initMap() {
            // Position par défaut (Paris)
            const defaultLocation = { lat: 48.8566, lng: 2.3522 };
            
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 12,
                center: defaultLocation,
                styles: [
                    { elementType: "geometry", stylers: [{ color: "#1d2c4d" }] },
                    { elementType: "labels.text.stroke", stylers: [{ color: "#1a1a2e" }] },
                    { elementType: "labels.text.fill", stylers: [{ color: "#8ec3b9" }] },
                    { featureType: "road", elementType: "geometry", stylers: [{ color: "#304a7d" }] },
                    { featureType: "road", elementType: "geometry.stroke", stylers: [{ color: "#255763" }] },
                    { featureType: "water", elementType: "geometry", stylers: [{ color: "#0e1626" }] },
                ]
            });
            
            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
                map: map,
                polylineOptions: {
                    strokeColor: '#4285f4',
                    strokeWeight: 5
                }
            });
        }
        
        // Ouvrir le modal et calculer l'itinéraire
        function openDirections(destination, eventName) {
            currentDestination = destination;
            document.getElementById('modalTitle').textContent = 'Itinéraire vers ' + eventName;
            document.getElementById('destinationInfo').textContent = destination;
            document.getElementById('directionsModal').classList.add('active');
            
            // Initialiser la carte si pas encore fait
            if (!map) {
                initMap();
            }
            
            // Demander la géolocalisation
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        userLocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        calculateRoute();
                    },
                    function(error) {
                        console.log('Géolocalisation refusée, utilisation de Paris par défaut');
                        userLocation = { lat: 48.8566, lng: 2.3522 };
                        calculateRoute();
                    }
                );
            } else {
                userLocation = { lat: 48.8566, lng: 2.3522 };
                calculateRoute();
            }
            
            // Mettre à jour le lien Google Maps
            const encodedDest = encodeURIComponent(destination);
            document.getElementById('googleMapsLink').href = 
                `https://www.google.com/maps/dir/?api=1&destination=${encodedDest}&travelmode=${currentMode.toLowerCase()}`;
        }
        
        // Calculer l'itinéraire
        function calculateRoute() {
            if (!userLocation || !currentDestination) return;
            
            const request = {
                origin: userLocation,
                destination: currentDestination,
                travelMode: google.maps.TravelMode[currentMode]
            };
            
            directionsService.route(request, function(result, status) {
                if (status === 'OK') {
                    directionsRenderer.setDirections(result);
                    
                    const route = result.routes[0].legs[0];
                    document.getElementById('distanceInfo').textContent = route.distance.text;
                    document.getElementById('durationInfo').textContent = route.duration.text;
                } else {
                    document.getElementById('distanceInfo').textContent = 'Non disponible';
                    document.getElementById('durationInfo').textContent = 'Non disponible';
                    console.error('Erreur itinéraire:', status);
                }
            });
        }
        
        // Changer le mode de transport
        function changeTransportMode(mode, element) {
            currentMode = mode;
            
            // Mettre à jour l'UI
            document.querySelectorAll('.transport-mode').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
            
            // Mettre à jour le lien Google Maps
            const encodedDest = encodeURIComponent(currentDestination);
            document.getElementById('googleMapsLink').href = 
                `https://www.google.com/maps/dir/?api=1&destination=${encodedDest}&travelmode=${mode.toLowerCase()}`;
            
            // Recalculer l'itinéraire
            calculateRoute();
        }
        
        // Fermer le modal
        function closeDirections() {
            document.getElementById('directionsModal').classList.remove('active');
        }
        
        // Fermer le modal en cliquant à l'extérieur
        document.getElementById('directionsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDirections();
            }
        });
        
        // Fermer avec Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDirections();
            }
        });
    </script>
    
    <script src="../js/navbar.js"></script>
</body>
</html>