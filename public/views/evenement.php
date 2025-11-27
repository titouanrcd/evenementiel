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
            </ul>
            <a href="connexion.html" class="btn-gradient">Connexion</a>
        </nav>
    </header>

    <main>
        <section class="events-hero">
            <div class="events-hero-content">
                <h1>Trouvez votre événement</h1>
                <p>Concerts, vie associative, soirées étudiantes... tout est là.</p>
                <div class="events-cta">
                    <button class="btn-gradient">Démarrer un projet</button>
                </div>
            </div>
        </section>

        <section class="events-filter-section">
            <div class="events-container">
                <aside class="events-filters">
                    <form id="filtersForm" class="filters-form">
                        <div class="filter-header">
                            <h3>Rechercher un événement</h3>
                            <button type="button" id="resetFilters" class="filter-reset">Réinitialiser</button>
                        </div>

                        <div class="filter-group">
                            <label for="searchInput" class="filter-label">Nom de l'événement</label>
                            <input type="text" id="searchInput" class="filter-input" placeholder="Rechercher un événement...">
                        </div>

                        <div class="filter-group">
                            <label for="locationSelect" class="filter-label">Lieu</label>
                            <select id="locationSelect" class="filter-select">
                                <option value="">Toutes les villes</option>
                                <option value="Paris">Paris</option>
                                <option value="Lyon">Lyon</option>
                                <option value="Marseille">Marseille</option>
                                <option value="Bordeaux">Bordeaux</option>
                                <option value="Lille">Lille</option>
                                <option value="Toulouse">Toulouse</option>
                                <option value="Nice">Nice</option>
                                <option value="Nantes">Nantes</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Période</label>
                            <div class="date-range">
                                <div class="date-input-group">
                                    <label for="dateFrom" class="date-label">Du</label>
                                    <input type="date" id="dateFrom" class="filter-input">
                                </div>
                                <div class="date-input-group">
                                    <label for="dateTo" class="date-label">Au</label>
                                    <input type="date" id="dateTo" class="filter-input">
                                </div>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Prix Maximum</label>
                            <div class="price-range">
                                <input type="range" id="priceRange" class="price-slider" min="0" max="200" value="200">
                                <div class="price-labels">
                                    <span>Gratuit</span>
                                    <span id="priceDisplay" class="price-value">200€+</span>
                                </div>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Catégories</label>
                            <div class="filter-options">
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="concert">
                                    <span class="checkmark"></span>
                                    Concerts & Spectacles
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="conference">
                                    <span class="checkmark"></span>
                                    Conférences & Séminaires
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="festival">
                                    <span class="checkmark"></span>
                                    Festivals
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="exposition">
                                    <span class="checkmark"></span>
                                    Expositions
                                </label>
                                <label class="filter-option">
                                    <input type="checkbox" name="category" value="sport">
                                    <span class="checkmark"></span>
                                    Sports & Loisirs
                                </label>
                            </div>
                        </div>

                        <div class="filter-group">
                            <button type="button" class="btn-gradient filter-apply-btn" onclick="NOVA_EVENTS.applyFilters()">
                                Rechercher
                            </button>
                        </div>
                    </form>
                </aside>

                <main class="events-content">
                    <div class="loading" id="loadingSpinner">
                        <p>Chargement des événements...</p>
                    </div>
                    <div class="events-grid" id="eventsContainer">
                        <!-- Les événements seront chargés ici dynamiquement -->
                    </div>
                </main>
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

    <script src="../js/api.js"></script>
</body>
</html>