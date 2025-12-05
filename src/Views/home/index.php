<?php
/**
 * PAGE D'ACCUEIL - NOVA Événements
 */

$title = 'NOVA ÉVÉNEMENTS - Agence 360°';
$nonce = cspNonce();
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
</head>
<body>
    <?php partial('header', ['isLoggedIn' => $isLoggedIn, 'userName' => $userName, 'userRole' => $userRole]); ?>

    <main>
        <?php if (!empty($flash)): ?>
            <div class="alert alert-<?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <section class="hero">
            <div class="hero-text">
                <h1>L'étincelle de vos <br><span class="text-gradient">plus grands moments</span></h1>
                <p class="hero-desc">
                    Agence événementielle 360°. Nous transformons vos idées en expériences spectaculaires, du concept à la réalité. Corporate, Privé, Festival.
                </p>
                <div class="hero-buttons">
                    <a class="btn-gradient" href="<?= url('/evenements') ?>">Voir les évènements</a>
                </div>
            </div>
            
            <div class="hero-visuals">
                <img src="<?= asset('img/hero.png') ?>" class="arch-img" alt="Concert Crowd">
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
                    <a href="<?= url('/evenements') ?>" class="btn-gradient btn-small">Découvrir</a>
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
                <h2 class="section-title">Nos Services</h2>
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
            
            <div class="carousel-wrapper">
                <div class="artist-row" id="categoriesCarousel">
                    <a href="<?= url('/evenements?tag=sport') ?>" class="artist-card artist-card-link">
                        <div class="artist-img-container">
                            <img src="https://images.unsplash.com/photo-1461896836934- voices-of-the-stadium?w=600" alt="Sport">
                        </div>
                        <h3>SPORT</h3>
                        <p class="sub-title">Compétitions & Tournois</p>
                    </a>

                    <a href="<?= url('/evenements?tag=culture') ?>" class="artist-card artist-card-link">
                        <div class="artist-img-container">
                            <img src="https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=600" alt="Culture">
                        </div>
                        <h3>CULTURE</h3>
                        <p class="sub-title">Arts & Expositions</p>
                    </a>

                    <a href="<?= url('/evenements?tag=soiree') ?>" class="artist-card artist-card-link">
                        <div class="artist-img-container">
                            <img src="https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?w=600" alt="Soirée">
                        </div>
                        <h3>SOIRÉE</h3>
                        <p class="sub-title">Fêtes & Galas</p>
                    </a>

                    <a href="<?= url('/evenements?tag=conference') ?>" class="artist-card artist-card-link">
                        <div class="artist-img-container">
                            <img src="https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=600" alt="Conférence">
                        </div>
                        <h3>CONFÉRENCE</h3>
                        <p class="sub-title">Séminaires & Talks</p>
                    </a>

                    <a href="<?= url('/evenements?tag=festival') ?>" class="artist-card artist-card-link">
                        <div class="artist-img-container">
                            <img src="https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=600" alt="Festival">
                        </div>
                        <h3>FESTIVAL</h3>
                        <p class="sub-title">Concerts & DJ Sets</p>
                    </a>

                    <a href="<?= url('/evenements?tag=autre') ?>" class="artist-card artist-card-link">
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
                <h2 class="section-title gallery-title">Réalisation <?= date('Y') ?></h2>
            </div>

            <div class="gallery-grid">
                <div class="gallery-item item-main">
                    <img src="https://media4.giphy.com/media/v1.Y2lkPTc5MGI3NjExZWlsdmp2ZDU5ZndwMjBqOXdmaThpNWVhb2U4bGFuenhhdTUxMW1wZyZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/l3UcmWzwnN5DafNOo/giphy.gif" alt="Festival">
                    <div class="gallery-item-info">
                        <h3 class="gallery-item-title">Festival</h3>
                        <p class="gallery-item-location">5000 Personnes</p>
                    </div>
                    <div class="gallery-item-overlay"></div>
                </div>

                <div class="gallery-item item-sub1">
                    <img src="https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExYXJpaWRhbXBxbXdjY3A4MHB6NTNxM3gwbmJ6cDZpaTI1bGF2NXBzbCZlcD12MV9naWZzX3NlYXJjaCZjdD1n/JUXtbHuixcZKeGJEro/giphy.gif" alt="Séminaire">
                    <div class="gallery-item-label">SÉMINAIRE</div>
                    <div class="gallery-item-overlay gallery-item-overlay-light"></div>
                </div>

                <div class="gallery-item item-sub2">
                    <img src="https://media.giphy.com/media/v1.Y2lkPWVjZjA1ZTQ3NHVlYWdyY2xucXNzOGdsNHlyN3g3eHBka2JocDJoeXk5dWhucWY4MiZlcD12MV9naWZzX3NlYXJjaCZjdD1n/un0j2CfCo3BhrDSZPO/giphy.gif" alt="Fête">
                    <div class="gallery-item-label">FÊTE</div>
                    <div class="gallery-item-overlay gallery-item-overlay-light"></div>
                </div>
            </div>
        </section>
    </main>

    <?php partial('footer'); ?>

    <script nonce="<?= $nonce ?>">
        // Carousel des catégories
        const carousel = document.getElementById('categoriesCarousel');
        const leftArrow = document.getElementById('carouselLeft');
        const rightArrow = document.getElementById('carouselRight');
        
        if (carousel && leftArrow && rightArrow) {
            let isScrolling = false;
            
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
                isScrolling = true;
                carousel.scrollBy({ left: -getCardWidth(), behavior: 'smooth' });
                setTimeout(() => isScrolling = false, 500);
            });
            
            rightArrow.addEventListener('click', function() {
                if (isScrolling) return;
                isScrolling = true;
                carousel.scrollBy({ left: getCardWidth(), behavior: 'smooth' });
                setTimeout(() => isScrolling = false, 500);
            });
        }
    </script>
    <script src="<?= asset('js/navbar.js') ?>" nonce="<?= $nonce ?>"></script>
</body>
</html>
