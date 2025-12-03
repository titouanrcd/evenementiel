<?php
/**
 * ============================================================
 * PAGE D'ACCUEIL - NOVA Événements
 * ============================================================
 */

require_once 'security.php';  // Sécurité EN PREMIER

$is_logged_in = isLoggedIn();
$user_name = $_SESSION['user_name'] ?? '';
$user_role = $_SESSION['user_role'] ?? 'user';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOVA ÉVÉNEMENTS - Agence 360°</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
                        <li><a href="#accueil">Accueil</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="evenement.php">Événements</a></li>
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
            <section class="hero">
            <div class="hero-text">
                <h1>L'étincelle de vos <br><span class="text-gradient">plus grands moments</span></h1>
                <p class="hero-desc">
                    Agence événementielle 360°. Nous transformons vos idées en expériences spectaculaires, du concept à la réalité. Corporate, Privé, Festival.
                </p>
                <div class="hero-buttons">
                    <button class="btn-gradient">Organiser un événement</button>
                </div>
            </div>
            
            <div class="hero-visuals">
                <img src="../img/hero.png" class="arch-img" alt="Concert Crowd">
            </div>
        </section>

        <section class="grid-section">
            <div class="grid-item grid-item-corporate">
                <img src="https://i.pinimg.com/1200x/7e/b0/25/7eb025e89eb45d2e7c36178af2c0a4a6.jpg" class="grid-item-bg" alt="Tech">
                <div class="grid-item-content">
                    <h3>CORPORATE</h3>
                    <p class="sub-title">Séminaires & Tech</p>
                </div>
            </div>
            
            <div class="grid-item item-large grid-item-nova">
                <div class="grid-item-content">
                    <p class="sub-title nova-subtitle">NOVA AGENCY</p>
                    <h3>CRÉATIVITÉ <br>EXPLOSIVE</h3>
                </div>
                <div class="nova-circle">
                    <div class="nova-circle-glow text-gradient"></div>
                </div>
            </div>

            <div class="grid-item item-large grid-item-services">
                <div class="grid-item-content">
                    <h3 class="text-gradient">NOS SERVICES</h3>
                    <p class="services-description">Une logistique militaire pour une créativité sans limite. Son, lumière, scénographie et gestion des invités.</p>
                    <button class="btn-gradient btn-small">Découvrir l'offre</button>
                </div>
            </div>

            <div class="grid-item grid-item-prive">
                <img src="https://i.pinimg.com/736x/2c/54/b8/2c54b81808f9a6e40ec3ac213b8b3aca.jpg" class="grid-item-bg" alt="Party">
                <div class="grid-item-content">
                    <h3>PRIVÉ</h3>
                    <p class="sub-title">Galas & Soirées</p>
                </div>
            </div>
        </section>

        <section class="artists-section">
            <div class="artists-header">
                <h2 class="section-title">Nos Services</h2>
            </div>
            
            <div class="artist-row">
                <div class="artist-card">
                    <div class="artist-img-container">
                        <img src="https://i.pinimg.com/1200x/ab/d7/64/abd7645999487483ab8799800f651cbe.jpg" alt="ELECTRO">
                    </div>
                    <h3>ELECTRO</h3>
                    <p class="sub-title">Festivals & DJ Sets</p>
                </div>

                <div class="artist-card">
                    <div class="artist-img-container">
                        <img src="https://i.pinimg.com/1200x/18/d8/b7/18d8b7b2d99b0c380b6c2bf813c05faa.jpg" alt="BRANDING">
                    </div>
                    <h3>BRANDING</h3>
                        
                    <p class="sub-title">Lancements de marque</p>
                </div>

                <div class="artist-card">
                    <div class="artist-img-container">
                        <img src="https://i.pinimg.com/1200x/78/ba/f4/78baf4fbbeeadc2e6510efab605d6d9a.jpg" alt="LUXE">
                    </div>
                    <h3>LUXE</h3>
                    <p class="sub-title">Mariages & VIP</p>
                </div>

                <div class="artist-card">
                    <div class="artist-img-container">
                        <img src="https://i.pinimg.com/736x/a5/75/af/a575afb24edbbc5812f54c02ad12a4df.jpg" alt="SCÉNOGRAPHIE">
                    </div>
                    <h3>SCÉNOGRAPHIE</h3>
                    <p class="sub-title">Immersion Totale</p>
                </div>
            </div>
        </section>

        <section class="gallery-section">
            <div class="gallery-header">
                <h2 class="section-title gallery-title">Réatisation 2025</h2>
                <a href="evenement.html" class="gallery-link">Voir tous les evenement</a>
            </div>

            <div class="gallery-grid">
                <div class="gallery-item gallery-item-main">
                    <img src="https://media4.giphy.com/media/v1.Y2lkPTc5MGI3NjExZWlsdmp2ZDU5ZndwMjBqOXdmaThpNWVhb2U4bGFuenhhdTUxMW1wZyZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/l3UcmWzwnN5DafNOo/giphy.gif" alt="Festival">
                    <div class="gallery-item-info">
                        <h3 class="gallery-item-title">Festival</h3>
                        <p class="gallery-item-location">5000 Personnes</p>
                    </div>
                    <div class="gallery-item-overlay"></div>
                </div>

                <div class="stacked-items">
                    <div class="gallery-item gallery-item-small">
                        <img src="https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExYXJpaWRhbXBxbXdjY3A4MHB6NTNxM3gwbmJ6cDZpaTI1bGF2NXBzbCZlcD12MV9naWZzX3NlYXJjaCZjdD1n/JUXtbHuixcZKeGJEro/giphy.gif" alt="SEMINAIRE">
                        <div class="gallery-item-label">SEMINAIRE</div>
                        <div class="gallery-item-overlay gallery-item-overlay-light"></div>
                    </div>
                    <div class="gallery-item gallery-item-small">
                        <img src="https://media.giphy.com/media/v1.Y2lkPWVjZjA1ZTQ3NHVlYWdyY2xucXNzOGdsNHlyN3g3eHBka2JocDJoeXk5dWhucWY4MiZlcD12MV9naWZzX3NlYXJjaCZjdD1n/un0j2CfCo3BhrDSZPO/giphy.gif" alt="FETE">
                        <div class="gallery-item-label">FETE</div>
                        <div class="gallery-item-overlay gallery-item-overlay-light"></div>
                    </div>
                </div>
            </div>
        </section>

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
    <script src="../js/navbar.js"></script>
</body>
</html>