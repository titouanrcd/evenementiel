-- ============================================================
-- MISE À JOUR DE SÉCURITÉ - NOVA Événements
-- ============================================================
-- Ce fichier contient les modifications de sécurité à appliquer
-- à la base de données existante.
-- ============================================================

-- Table pour la protection contre les attaques par force brute
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_time` (`ip_address`, `attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Nettoyage automatique des anciennes tentatives (optionnel - à exécuter via CRON)
-- DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 1 HOUR);

-- ============================================================
-- IMPORTANT: Changer les mots de passe par défaut!
-- ============================================================
-- Les comptes admin par défaut doivent être sécurisés.
-- Exécutez cette requête APRÈS avoir créé un nouveau mot de passe hashé:
-- 
-- Pour générer un nouveau hash en PHP:
-- echo password_hash('VotreNouveauMotDePasse', PASSWORD_BCRYPT, ['cost' => 12]);
--
-- Puis mettez à jour:
-- UPDATE users SET password = 'NOUVEAU_HASH_ICI' WHERE email = 'admin@nova.com';
-- ============================================================
