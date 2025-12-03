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
                        <div class="logo" style="font-size: 32px;">caca<span>.</span></div>
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

        <section class="artists-section" id="services">
            <div class="artists-header">
                <h2 class="section-title">Nos Catégories</h2>
                <!-- Flèches navigation dynamiques -->
                <div class="carousel-nav">
                    <button class="nav-arrow" id="carouselLeft" aria-label="Précédent">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 12H5"></path>
                            <path d="M12 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <button class="nav-arrow" id="carouselRight" aria-label="Suivant">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"></path>
                            <path d="M12 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Conteneur du carousel -->
            <div class="carousel-wrapper">
                <div class="artist-row" id="categoriesCarousel">
                    <!-- Sport -->
                    <a href="evenement.php?tag=sport" class="artist-card artist-card-link">
                        <div class="artist-img-container">
                            <img src="https://images.unsplash.com/photo-1461896836934- voices-of-the-stadium?w=600" alt="Sport">
                        </div>
                        <h3>SPORT</h3>
                        <p class="sub-title">Compétitions & Tournois</p>
                    </a>

                    <!-- Culture -->
                    <a href="evenement.php?tag=culture" class="artist-card artist-card-link">
                        <div class="artist-img-container">
                            <img src="https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=600" alt="Culture">
                        </div>
                        <h3>CULTURE</h3>
                        <p class="sub-title">Arts & Expositions</p>
                    </a>

                    <!-- Soirée -->
                    <a href="evenement.php?tag=soiree" class="artist-card artist-card-link">
                        <div class="artist-img-container">
                            <img src="https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=600" alt="Soirée">
                        </div>
                        <h3>SOIRÉE</h3>
                        <p class="sub-title">Fêtes & Galas</p>
                    </a>

                    <!-- Conférence -->
                    <a href="evenement.php?tag=conference" class="artist-card artist-card-link">
                        <div class="artist-img-container">
                            <img src="https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=600" alt="Conférence">
                        </div>
                        <h3>CONFÉRENCE</h3>
                        <p class="sub-title">Séminaires & Talks</p>
                    </a>

                    <!-- Festival -->
                    <a href="evenement.php?tag=festival" class="artist-card artist-card-link">
                        <div class="artist-img-container">
                            <img src="https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=600" alt="Festival">
                        </div>
                        <h3>FESTIVAL</h3>
                        <p class="sub-title">Concerts & DJ Sets</p>
                    </a>

                    <!-- Autre -->
                    <a href="evenement.php?tag=autre" class="artist-card artist-card-link">
                        <div class="artist-img-container">
                            <img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?w=600" alt="Autre">
                        </div>
                        <h3>AUTRE</h3>
                        <p class="sub-title">Événements Divers</p>
                    </a>
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

        // Carousel des catégories - Défilement fluide
        const carousel = document.getElementById('categoriesCarousel');
        const leftArrow = document.getElementById('carouselLeft');
        const rightArrow = document.getElementById('carouselRight');
        
        if (carousel && leftArrow && rightArrow) {
            let isScrolling = false;
            let scrollTarget = 0;
            let animationId = null;
            
            // Fonction de défilement fluide personnalisée
            function smoothScrollTo(target) {
                if (animationId) {
                    cancelAnimationFrame(animationId);
                }
                
                const start = carousel.scrollLeft;
                const distance = target - start;
                const duration = 600; // Durée en ms
                let startTime = null;
                
                // Fonction d'easing (ease-out cubic)
                function easeOutCubic(t) {
                    return 1 - Math.pow(1 - t, 3);
                }
                
                function animate(currentTime) {
                    if (!startTime) startTime = currentTime;
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    
                    carousel.scrollLeft = start + distance * easeOutCubic(progress);
                    
                    if (progress < 1) {
                        animationId = requestAnimationFrame(animate);
                    } else {
                        isScrolling = false;
                        updateArrows();
                    }
                }
                
                isScrolling = true;
                animationId = requestAnimationFrame(animate);
            }
            
            // Calculer la largeur d'une carte
            function getCardWidth() {
                const card = carousel.querySelector('.artist-card');
                if (card) {
                    const style = window.getComputedStyle(carousel);
                    const gap = parseInt(style.gap) || 30;
                    return card.offsetWidth + gap;
                }
                return 310;
            }
            
            leftArrow.addEventListener('click', function() {
                if (isScrolling) return;
                const cardWidth = getCardWidth();
                scrollTarget = Math.max(0, carousel.scrollLeft - cardWidth);
                smoothScrollTo(scrollTarget);
            });
            
            rightArrow.addEventListener('click', function() {
                if (isScrolling) return;
                const cardWidth = getCardWidth();
                const maxScroll = carousel.scrollWidth - carousel.clientWidth;
                scrollTarget = Math.min(maxScroll, carousel.scrollLeft + cardWidth);
                smoothScrollTo(scrollTarget);
            });
            
            // Mettre à jour l'état des flèches
            function updateArrows() {
                const maxScroll = carousel.scrollWidth - carousel.clientWidth;
                
                if (carousel.scrollLeft <= 5) {
                    leftArrow.classList.add('disabled');
                } else {
                    leftArrow.classList.remove('disabled');
                }
                
                if (carousel.scrollLeft >= maxScroll - 5) {
                    rightArrow.classList.add('disabled');
                } else {
                    rightArrow.classList.remove('disabled');
                }
            }
            
            // Écouter le scroll manuel (drag)
            carousel.addEventListener('scroll', function() {
                if (!isScrolling) {
                    updateArrows();
                }
            });
            
            window.addEventListener('resize', updateArrows);
            
            // Initialiser
            setTimeout(updateArrows, 100);
        }
    </script>
    <script src="../js/navbar.js"></script>


</body>
</html>