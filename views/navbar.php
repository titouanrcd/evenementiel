<?php
/**
 * ============================================================
 * NAVBAR - NOVA Événements
 * ============================================================
 * Barre de navigation réutilisable avec sécurité
 * ============================================================
 */

// Inclure la sécurité si pas déjà fait (vérifie si la session est déjà démarrée)
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/security.php';
}

$is_logged_in = isset($_SESSION['user_email']) && !empty($_SESSION['user_email']);
$user_name = htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8');
$user_role = htmlspecialchars($_SESSION['user_role'] ?? 'user', ENT_QUOTES, 'UTF-8');
?>

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
                <li><a href="index.php">Accueil</a></li>
                <li><a href="evenement.php">Événements</a></li>
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
