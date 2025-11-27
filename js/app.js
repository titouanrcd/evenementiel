// NOVA Événements - App JavaScript

document.addEventListener('DOMContentLoaded', function() {
    
    // Smooth scrolling pour les liens d'ancrage
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Animation au scroll pour les éléments
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observer les sections principales
    document.querySelectorAll('.grid-section, .artists-section, .gallery-section, .events-section').forEach(section => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(30px)';
        section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(section);
    });

    // Gestion des boutons de navigation (artistes)
    const prevBtn = document.querySelector('.nav-btn-prev');
    const nextBtn = document.querySelector('.nav-btn-next');
    const artistRow = document.querySelector('.artist-row');

    if (prevBtn && nextBtn && artistRow) {
        prevBtn.addEventListener('click', () => {
            artistRow.scrollBy({ left: -300, behavior: 'smooth' });
        });

        nextBtn.addEventListener('click', () => {
            artistRow.scrollBy({ left: 300, behavior: 'smooth' });
        });
    }

    // Effet parallax léger sur l'image hero
    const heroImg = document.querySelector('.arch-img');
    if (heroImg) {
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            heroImg.style.transform = `translateY(${rate}px)`;
        });
    }

    // Animation des boutons au hover
    document.querySelectorAll('.btn-gradient').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.style.backgroundPosition = 'right center';
        });
        
        btn.addEventListener('mouseleave', function() {
            this.style.backgroundPosition = 'left center';
        });
    });

    console.log('🚀 NOVA Événements - Application chargée avec succès');
});
