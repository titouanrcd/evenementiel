<?php
/**
 * ============================================================
 * PAGE DES ÉVÉNEMENTS - NOVA Événements
 * ============================================================
 */

$title = 'Événements - NOVA';
$nonce = cspNonce();

$tags = [
    'sport' => 'Sport',
    'culture' => 'Culture',
    'soiree' => 'Soirée',
    'conference' => 'Conférence',
    'festival' => 'Festival',
    'autre' => 'Autre'
];

// Configuration API Météo OpenWeatherMap
$weatherApiKey = defined('OPENWEATHER_API_KEY') ? OPENWEATHER_API_KEY : 'e9b37b97190dcabd2eb2b9256b76ffeb';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;900&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    
    <style nonce="<?= $nonce ?>">
        .events-hero {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.3) 0%, rgba(118, 75, 162, 0.3) 50%, rgba(255, 0, 150, 0.2) 100%), 
                        radial-gradient(ellipse at center, rgba(102, 126, 234, 0.15) 0%, transparent 70%),
                        #0a0a0a;
            padding: 120px 5% 100px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .events-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 50%, rgba(255, 0, 150, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 70% 50%, rgba(102, 126, 234, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        .events-hero-content {
            position: relative;
            z-index: 1;
        }
        .events-hero-content h1 {
            font-size: 4rem;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #fff 0%, #667eea 50%, #ff0096 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmer 3s ease infinite;
            text-shadow: 0 0 60px rgba(102, 126, 234, 0.5);
        }
        @keyframes shimmer {
            0%, 100% { background-position: 0% center; }
            50% { background-position: 100% center; }
        }
        .events-hero-content p {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.8);
            max-width: 600px;
            margin: 0 auto;
        }

        .location-link {
            color: inherit;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }
        .location-link:hover {
            color: #667eea;
        }
        .location-link svg {
            transition: stroke 0.2s;
        }
        .location-link:hover svg {
            stroke: #667eea;
        }
        
        .weather-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(8px);
            border-radius: 8px;
            padding: 6px 10px;
            display: flex;
            align-items: center;
            gap: 6px;
            z-index: 10;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .weather-badge:hover {
            background: rgba(0, 0, 0, 0.85);
            transform: scale(1.05);
            border-color: rgba(102, 126, 234, 0.5);
        }
        .weather-badge.loading {
            min-width: 50px;
            padding: 8px 12px;
        }
        .weather-badge.loading::after {
            content: '';
            width: 8px;
            height: 8px;
            border: 1px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .weather-icon {
            width: 28px;
            height: 28px;
        }
        .weather-info {
            display: flex;
            align-items: center;
        }
        .weather-temp {
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            line-height: 1;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        .weather-desc {
            display: none;
        }
        
        /* Popup détails météo */
        .weather-popup {
            display: none;
            position: absolute;
            top: 28px;
            left: 8px;
            background: rgba(10, 10, 25, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 6px;
            padding: 6px 10px;
            z-index: 20;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 5px 15px rgba(0,0,0,0.4);
            white-space: nowrap;
        }
        .weather-badge.active + .weather-popup,
        .weather-popup:hover {
            display: flex;
            align-items: center;
            gap: 8px;
            animation: fadeInUp 0.2s ease;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .weather-popup-header {
            display: flex;
            align-items: center;
            gap: 4px;
            padding-right: 8px;
            border-right: 1px solid rgba(255,255,255,0.1);
        }
        .weather-popup-icon {
            width: 18px;
            height: 18px;
        }
        .weather-popup-main {
            display: flex;
            flex-direction: column;
        }
        .weather-popup-temp {
            font-size: 11px;
            font-weight: 700;
            color: #fff;
            line-height: 1.1;
        }
        .weather-popup-desc {
            color: rgba(255,255,255,0.6);
            text-transform: capitalize;
            font-size: 8px;
        }
        .weather-popup-details {
            display: flex;
            gap: 8px;
        }
        .weather-detail {
            display: flex;
            align-items: center;
            gap: 2px;
            color: rgba(255,255,255,0.8);
            font-size: 9px;
        }
        .weather-detail svg {
            display: none;
        }
        .weather-detail-value {
            font-weight: 600;
            color: #fff;
        }
        
        /* Couleurs selon la météo */
        .weather-sunny { background: rgba(255,193,7,0.15) !important; }
        .weather-cloudy { background: rgba(158,158,158,0.15) !important; }
        .weather-rainy { background: rgba(33,150,243,0.15) !important; }
        .weather-snowy { background: rgba(200,200,220,0.15) !important; }
        .weather-stormy { background: rgba(69,90,100,0.2) !important; }
        
        /* Tag inline à côté du titre */
        .event-title-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .event-tag-inline {
            font-size: 9px;
            padding: 2px 8px;
            border-radius: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .event-tag-inline.tag-sport { background: #e91e63; color: #fff; }
        .event-tag-inline.tag-culture { background: #9c27b0; color: #fff; }
        .event-tag-inline.tag-soiree { background: #ff9800; color: #fff; }
        .event-tag-inline.tag-conference { background: #2196f3; color: #fff; }
        .event-tag-inline.tag-festival { background: #4caf50; color: #fff; }
        .event-tag-inline.tag-autre { background: #607d8b; color: #fff; }

        .events-header-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sort-quick {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: rgba(255, 255, 255, 0.6);
        }
        .sort-link {
            padding: 6px 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .sort-link:hover {
            background: rgba(102, 126, 234, 0.2);
            border-color: #667eea;
            color: #fff;
        }
        .sort-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            color: #fff;
        }

        .event-info {
            padding: 20px !important;
        }
        .event-title {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }
        .event-description {
            margin: 15px 0;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.7);
        }
        .event-meta {
            margin: 15px 0;
        }
        .event-details {
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <?php partial('header', ['isLoggedIn' => $isLoggedIn, 'userName' => $userName, 'userRole' => $userRole]); ?>

    <main>
        <section class="events-hero">
            <div class="events-hero-content">
                <h1>ÉVÉNEMENTS</h1>
                <p>Découvrez nos événements à venir</p>
            </div>
        </section>

        <?php if (!empty($flash)): ?>
            <div class="alert-container">
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
            </div>
        <?php endif; ?>

        <section class="events-filter-section">
            <div class="events-container">
                <!-- FILTRES À GAUCHE -->
                <aside class="events-filters">
                    <form method="GET" action="<?= url('/evenements') ?>" class="filters-form">
                        <div class="filter-header">
                            <h3>Rechercher un événement</h3>
                            <a href="<?= url('/evenements') ?>" class="filter-reset">Réinitialiser</a>
                        </div>

                        <div class="filter-group">
                            <label for="searchInput" class="filter-label">Nom de l'événement</label>
                            <input type="text" name="search" id="searchInput" class="filter-input" 
                                   placeholder="Rechercher un événement..." 
                                   value="<?= e($filters['search'] ?? '') ?>">
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Catégorie</label>
                            <div class="filter-tags">
                                <label class="tag-option <?= empty($filters['tag'] ?? '') ? 'active' : '' ?>">
                                    <input type="radio" name="tag" value="" <?= empty($filters['tag'] ?? '') ? 'checked' : '' ?>>
                                    <span>🔄 Tous</span>
                                </label>
                                <?php foreach ($tags as $key => $label): ?>
                                    <label class="tag-option <?= (($filters['tag'] ?? '') == $key) ? 'active' : '' ?>">
                                        <input type="radio" name="tag" value="<?= e($key) ?>" 
                                               <?= (($filters['tag'] ?? '') == $key) ? 'checked' : '' ?>>
                                        <span><?= e($label) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label for="lieuSelect" class="filter-label">Lieu</label>
                            <select name="lieu" id="lieuSelect" class="filter-select">
                                <option value="">Toutes les villes</option>
                                <?php foreach ($lieux as $l): ?>
                                    <option value="<?= e($l) ?>" 
                                            <?= (($filters['lieu'] ?? '') == $l) ? 'selected' : '' ?>>
                                        <?= e($l) ?>
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
                                           value="<?= e($filters['date_from'] ?? '') ?>">
                                </div>
                                <div class="date-input-group">
                                    <label for="dateTo" class="date-label">Au</label>
                                    <input type="date" name="date_to" id="dateTo" class="filter-input" 
                                           value="<?= e($filters['date_to'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Prix Maximum</label>
                            <div class="price-range">
                                <input type="range" name="prix_max" id="priceRange" class="price-slider" 
                                       min="0" max="200" value="<?= e($filters['prix_max'] ?? 200) ?>">
                                <div class="price-labels">
                                    <span>Gratuit</span>
                                    <span id="priceDisplay" class="price-value"><?= ($filters['prix_max'] ?? 200) >= 200 ? '200€+' : ($filters['prix_max'] ?? 200) . '€' ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label for="sortSelect" class="filter-label">Trier par</label>
                            <select name="sort" id="sortSelect" class="filter-select">
                                <option value="date" <?= (($_GET['sort'] ?? 'date') == 'date') ? 'selected' : '' ?>>Date (plus proche)</option>
                                <option value="prix_asc" <?= (($_GET['sort'] ?? '') == 'prix_asc') ? 'selected' : '' ?>>Prix croissant ↑</option>
                                <option value="prix_desc" <?= (($_GET['sort'] ?? '') == 'prix_desc') ? 'selected' : '' ?>>Prix décroissant ↓</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <button type="submit" class="btn-gradient filter-apply-btn">
                                Rechercher
                            </button>
                        </div>
                    </form>
                </aside>

                <!-- CONTENU À DROITE -->
                <div class="events-content">
                    <div class="events-header-info">
                        <p class="events-count"><?= count($events) ?> événement(s) trouvé(s)</p>
                        <div class="sort-quick">
                            <span>Tri rapide:</span>
                            <a href="<?= url('/evenements') ?>?sort=prix_asc<?= !empty($filters['tag']) ? '&tag=' . e($filters['tag']) : '' ?>" 
                               class="sort-link <?= (($_GET['sort'] ?? '') == 'prix_asc') ? 'active' : '' ?>">€↑</a>
                            <a href="<?= url('/evenements') ?>?sort=prix_desc<?= !empty($filters['tag']) ? '&tag=' . e($filters['tag']) : '' ?>" 
                               class="sort-link <?= (($_GET['sort'] ?? '') == 'prix_desc') ? 'active' : '' ?>">€↓</a>
                        </div>
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
                                $is_registered = in_array($event['id_event'], $userRegistrations ?? []);
                                $is_full = $event['nb_inscrits'] >= $event['capacite'];
                                $places_restantes = $event['capacite'] - $event['nb_inscrits'];
                                $event_hour = date('H', strtotime($event['hour'] ?? '12:00'));
                            ?>
                                <article class="event-card <?= ($index === 0) ? 'featured' : '' ?>">
                                    <div class="event-image">
                                        <?php if (!empty($event['image'])): ?>
                                            <img src="<?= e($event['image']) ?>" 
                                                 alt="<?= e($event['name']) ?>"
                                                 onerror="this.src='https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800'">
                                        <?php else: ?>
                                            <img src="https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=800" 
                                                 alt="<?= e($event['name']) ?>">
                                        <?php endif; ?>
                                        
                                        <!-- Badge Météo dynamique -->
                                        <div class="weather-badge loading" 
                                             data-city="<?= e($event['lieu']) ?>"
                                             data-date="<?= e($event['event_date']) ?>"
                                             data-hour="<?= e($event_hour) ?>"
                                             data-event-id="<?= e($event['id_event']) ?>"
                                             onclick="this.classList.toggle('active')">
                                        </div>
                                        <div class="weather-popup" id="weather-popup-<?= e($event['id_event']) ?>"></div>
                                        
                                        <?php if ($event['prix'] == 0): ?>
                                            <div class="event-status">Gratuit</div>
                                        <?php endif; ?>
                                        
                                        <div class="event-date-badge">
                                            <span class="day"><?= date('d', strtotime($event['event_date'])) ?></span>
                                            <span class="month"><?= strtoupper(date('M', strtotime($event['event_date']))) ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="event-info">
                                        <div class="event-title-row">
                                            <h3 class="event-title"><?= e($event['name']) ?></h3>
                                            <span class="event-tag-inline tag-<?= e($event['tag']) ?>">
                                                <?= e($tags[$event['tag']] ?? $event['tag']) ?>
                                            </span>
                                        </div>
                                        <p class="event-location">
                                            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($event['lieu']) ?>" 
                                               target="_blank" 
                                               class="location-link"
                                               title="Voir sur Google Maps">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                                    <circle cx="12" cy="10" r="3"></circle>
                                                </svg>
                                                <?= e($event['lieu']) ?>
                                            </a>
                                        </p>
                                        <p class="event-time">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <polyline points="12 6 12 12 16 14"></polyline>
                                            </svg>
                                            <?= date('H:i', strtotime($event['hour'] ?? '00:00')) ?>
                                        </p>
                                        <p class="event-description">
                                            <?= e(substr($event['description'] ?? '', 0, 120)) ?>...
                                        </p>
                                        
                                        <div class="event-meta">
                                            <span class="event-capacity <?= $is_full ? 'full' : '' ?>">
                                                <?php if ($is_full): ?>
                                                    <span class="capacity-full">COMPLET</span>
                                                <?php else: ?>
                                                    <?= e($places_restantes) ?> place(s) restante(s)
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        
                                        <div class="event-details">
                                            <span class="event-price">
                                                <?= $event['prix'] > 0 ? e($event['prix']) . '€' : 'Gratuit' ?>
                                            </span>
                                            
                                            <?php if ($isLoggedIn): ?>
                                                <?php if ($is_registered): ?>
                                                    <span class="btn-registered">Inscrit</span>
                                                <?php elseif ($is_full): ?>
                                                    <span class="btn-full-event">Complet</span>
                                                <?php else: ?>
                                                    <form method="POST" action="<?= url('/evenement/inscription') ?>" class="inscription-form">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="id_event" value="<?= intval($event['id_event']) ?>">
                                                        <button type="submit" class="event-btn">S'inscrire</button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="<?= url('/connexion') ?>" class="event-btn">Connexion</a>
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

    <?php partial('footer'); ?>

    <script nonce="<?= $nonce ?>">
        const priceSlider = document.getElementById('priceRange');
        const priceDisplay = document.getElementById('priceDisplay');
        
        if (priceSlider && priceDisplay) {
            priceSlider.addEventListener('input', function() {
                const value = parseInt(this.value);
                priceDisplay.textContent = value >= 200 ? '200€+' : value + '€';
            });
        }
    </script>
    
    <script nonce="<?= $nonce ?>">
        const WEATHER_API_KEY = '<?= e($weatherApiKey) ?>';
        const weatherCache = new Map();
        
        function getWeatherIcon(iconCode) {
            return `https://openweathermap.org/img/wn/${iconCode}@2x.png`;
        }
        
        function getWeatherClass(weatherId) {
            if (weatherId >= 200 && weatherId < 300) return 'weather-stormy';
            if (weatherId >= 300 && weatherId < 600) return 'weather-rainy';
            if (weatherId >= 600 && weatherId < 700) return 'weather-snowy';
            if (weatherId >= 700 && weatherId < 800) return 'weather-cloudy';
            if (weatherId === 800) return 'weather-sunny';
            return 'weather-cloudy';
        }
        
        const frenchCities = ['Paris', 'Lyon', 'Marseille', 'Toulouse', 'Nice', 'Nantes', 'Strasbourg', 'Montpellier', 'Bordeaux', 'Lille', 'Rennes', 'Reims', 'Grenoble', 'Dijon', 'Angers', 'Nîmes', 'Tours', 'Amiens', 'Limoges', 'Annecy', 'Perpignan', 'Metz', 'Besançon', 'Orléans', 'Rouen', 'Mulhouse', 'Caen', 'Nancy', 'Avignon', 'Poitiers', 'Versailles', 'Pau', 'Calais', 'Cannes', 'Béziers', 'Bourges', 'La Rochelle', 'Colmar', 'Valence', 'Quimper', 'Troyes', 'Lorient', 'Niort', 'Chambéry', 'Cholet', 'Ajaccio'];
        
        function extractCity(lieu) {
            const cleanLieu = lieu.trim();
            for (const city of frenchCities) {
                if (cleanLieu.toLowerCase().includes(city.toLowerCase())) {
                    return city;
                }
            }
            const parts = cleanLieu.split(',').map(p => p.trim());
            if (parts.length >= 2) {
                const firstPart = parts[0];
                if (firstPart.length < 20 && !/\d/.test(firstPart) && !firstPart.toLowerCase().includes('salle') && !firstPart.toLowerCase().includes('rue') && !firstPart.toLowerCase().includes('avenue')) {
                    return firstPart;
                }
                return parts[parts.length - 1];
            }
            const words = cleanLieu.split(' ');
            if (words.length > 0) {
                return words[0];
            }
            return cleanLieu;
        }
        
        async function fetchWeather(city, date, hour, eventId) {
            const cacheKey = `${city}-${date}-${hour}`;
            
            if (weatherCache.has(cacheKey)) {
                return weatherCache.get(cacheKey);
            }
            
            try {
                const response = await fetch(`<?= url('/api/weather') ?>?city=${encodeURIComponent(city)}&type=weather`);
                const data = await response.json();
                
                if (data.error) throw new Error(data.message || 'Erreur API');
                
                const weatherData = {
                    temp: Math.round(data.main.temp),
                    feels_like: Math.round(data.main.feels_like),
                    humidity: data.main.humidity,
                    wind: Math.round(data.wind.speed * 3.6),
                    description: data.weather[0].description,
                    icon: data.weather[0].icon,
                    weatherId: data.weather[0].id,
                    city: data.name
                };
                
                weatherCache.set(cacheKey, weatherData);
                return weatherData;
                
            } catch (error) {
                console.error('❌ Erreur météo:', city, error.message);
                return { error: true, message: error.message, city: city };
            }
        }
        
        function updateWeatherBadge(badge, weatherData, eventId) {
            badge.classList.remove('loading');
            
            if (!weatherData || weatherData.error) {
                badge.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity:0.5">
                        <path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"></path>
                    </svg>
                `;
                return;
            }
            
            const weatherClass = getWeatherClass(weatherData.weatherId);
            badge.classList.add(weatherClass);
            
            badge.innerHTML = `
                <img src="${getWeatherIcon(weatherData.icon)}" alt="${weatherData.description}" class="weather-icon">
                <div class="weather-info">
                    <span class="weather-temp">${weatherData.temp}°C</span>
                </div>
            `;
            
            const popup = document.getElementById(`weather-popup-${eventId}`);
            if (popup) {
                popup.innerHTML = `
                    <div class="weather-popup-header">
                        <img src="${getWeatherIcon(weatherData.icon)}" alt="${weatherData.description}" class="weather-popup-icon">
                        <div class="weather-popup-main">
                            <div class="weather-popup-temp">${weatherData.temp}°C</div>
                            <div class="weather-popup-desc">${weatherData.description}</div>
                        </div>
                    </div>
                    <div class="weather-popup-details">
                        <div class="weather-detail">Ressenti <span class="weather-detail-value">${weatherData.feels_like}°C</span></div>
                        <div class="weather-detail">Humidité <span class="weather-detail-value">${weatherData.humidity}%</span></div>
                        <div class="weather-detail">Vent <span class="weather-detail-value">${weatherData.wind} km/h</span></div>
                    </div>
                `;
            }
        }
        
        async function initWeather() {
            const weatherBadges = document.querySelectorAll('.weather-badge');
            
            for (const badge of weatherBadges) {
                const lieu = badge.dataset.city;
                const date = badge.dataset.date;
                const hour = badge.dataset.hour || '12';
                const eventId = badge.dataset.eventId;
                
                const city = extractCity(lieu);
                const weatherData = await fetchWeather(city, date, hour, eventId);
                updateWeatherBadge(badge, weatherData, eventId);
                
                await new Promise(resolve => setTimeout(resolve, 100));
            }
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            if (WEATHER_API_KEY && WEATHER_API_KEY !== 'VOTRE_CLE_API_OPENWEATHER') {
                initWeather();
            } else {
                document.querySelectorAll('.weather-badge').forEach(badge => {
                    badge.classList.remove('loading');
                    badge.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity:0.4">
                            <path d="M17.5 19H9a7 7 0 1 1 6.71-9h1.79a4.5 4.5 0 1 1 0 9Z"></path>
                        </svg>
                    `;
                });
            }
        });
    </script>

    <script src="<?= asset('js/navbar.js') ?>" nonce="<?= $nonce ?>"></script>
</body>
</html>
