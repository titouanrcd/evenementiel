// Navigation Sidebar avec Hamburger Menu
document.addEventListener('DOMContentLoaded', function() {
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const sidebarLinks = sidebar ? sidebar.querySelectorAll('a') : [];

    // Ouvrir/Fermer la sidebar
    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', toggleSidebar);
    }

    // Fermer la sidebar en cliquant sur l'overlay
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Fermer la sidebar en cliquant sur un lien
    sidebarLinks.forEach(link => {
        link.addEventListener('click', closeSidebar);
    });

    function toggleSidebar() {
        if (!sidebar || !overlay) return;
        
        const isActive = sidebar.classList.contains('active');
        if (isActive) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    function openSidebar() {
        if (!sidebar || !overlay || !hamburgerBtn) return;
        sidebar.classList.add('active');
        overlay.classList.add('active');
        hamburgerBtn.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (!sidebar || !overlay || !hamburgerBtn) return;
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        hamburgerBtn.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Déterminer le lien actif
    const currentPage = window.location.pathname.split('/').pop() || 'index.php';
    sidebarLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage || (currentPage === '' && href === 'index.php')) {
            link.classList.add('active');
        } else if (currentPage === 'index.php' && href === 'index.php') {
            link.classList.add('active');
        }
    });
});
