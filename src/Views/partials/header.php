<?php
/**
 * HEADER - NOVA Événements
 */
?>
<header>
    <nav>
        <a href="<?= url('/') ?>" class="logo header-logo">NOVA<span>.</span></a>
        <button class="hamburger-btn" id="hamburger-btn" aria-label="Menu">
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
                <li><a href="<?= url('/') ?>">Accueil</a></li>
                <li><a href="<?= url('/evenements') ?>">Événements</a></li>
                <?php if ($isLoggedIn ?? false): ?>
                    <li><a href="<?= url('/profil') ?>">Mon Profil</a></li>
                    <?php if (($userRole ?? '') === 'organisateur' || ($userRole ?? '') === 'admin'): ?>
                        <li><a href="<?= url('/organisateur') ?>">Panel Orga</a></li>
                    <?php endif; ?>
                    <?php if (($userRole ?? '') === 'admin'): ?>
                        <li><a href="<?= url('/admin') ?>">Panel Admin</a></li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            
            <div class="sidebar-footer">
                <?php if ($isLoggedIn ?? false): ?>
                    <div class="sidebar-user">
                        <div class="sidebar-user-avatar"><?= e(strtoupper(substr($userName ?? 'U', 0, 1))) ?></div>
                        <div class="sidebar-user-info">
                            <h4><?= e($userName ?? 'Utilisateur') ?></h4>
                            <p><?= e(ucfirst($userRole ?? 'user')) ?></p>
                        </div>
                    </div>
                    <div class="sidebar-actions">
                        <a href="<?= url('/profil') ?>">Mon Profil</a>
                        <a href="<?= url('/deconnexion') ?>">Déconnexion</a>
                    </div>
                <?php else: ?>
                    <div class="sidebar-actions">
                        <a href="<?= url('/connexion') ?>">Connexion</a>
                    </div>
                <?php endif; ?>
            </div>
        </aside>
    </nav>
</header>

<div class="sidebar-overlay" id="sidebar-overlay"></div>
