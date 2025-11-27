<?php
session_start();
require_once 'db.php';

$is_logged_in = isset($_SESSION['user_email']);
$user_name = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';
$user_role = $_SESSION['user_role'] ?? 'user';

// Messages
$message = '';
$message_type = '';

// Inscription à un événement
if (isset($_POST['action']) && $_POST['action'] == 'inscription' && $is_logged_in) {
    $id_event = intval($_POST['id_event']);
    
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
        $message = "Erreur lors de l'inscription.";
        $message_type = "error";
    }
}

// Récupérer les filtres
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tag = isset($_GET['tag']) ? $_GET['tag'] : '';
$lieu = isset($_GET['lieu']) ? $_GET['lieu'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$prix_max = isset($_GET['prix_max']) ? intval($_GET['prix_max']) : 200;

// Construire la requête SQL avec filtres
$sql = "SELECT e.*, 
        (SELECT COUNT(*) FROM inscriptions i WHERE i.id_event = e.id_event AND i.statut = 'confirmé') as nb_inscrits
        FROM event e 
        WHERE e.status = 'publié'";
$params = [];

if (!empty($search)) {
    $sql .= " AND (e.name LIKE ? OR e.description LIKE ? OR e.lieu LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($tag)) {
    $sql .= " AND e.tag = ?";
    $params[] = $tag;
}

if (!empty($lieu)) {
    $sql .= " AND e.lieu LIKE ?";
    $params[] = "%$lieu%";
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
    $events = [];
}

// Récupérer les lieux uniques pour le filtre
try {
    $lieux_stmt = $pdo->query("SELECT DISTINCT lieu FROM event WHERE status = 'publié' ORDER BY lieu");
    $lieux = $lieux_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $lieux = [];
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
</head>
<body>
    <header>
        <nav>
            <div class="logo header-logo">NOVA<span>.</span></div>
            <ul class="nav-links">
                <li><a href="index.php">Accueil</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="evenement.php">Evenement</a></li>
                <li><a href="#contact">Contact</a></li>
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
                <a href="profil.php" class="btn-gradient">👤 <?php echo htmlspecialchars($user_name); ?></a>
            <?php else: ?>
                <a href="connexion.php" class="btn-gradient">Connexion</a>
            <?php endif; ?>
        </nav>
    </header>

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
                                                    <span class="btn-registered">✓ Inscrit</span>
                                                <?php elseif ($is_full): ?>
                                                    <span class="btn-full-event">Complet</span>
                                                <?php else: ?>
                                                    <form method="POST" class="inscription-form">
                                                        <input type="hidden" name="action" value="inscription">
                                                        <input type="hidden" name="id_event" value="<?php echo $event['id_event']; ?>">
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
</body>
</html>