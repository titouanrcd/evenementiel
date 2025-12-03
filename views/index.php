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