-- ============================================================
-- MISE À JOUR DE SÉCURITÉ v2.0 - NOVA Événements
-- ============================================================
-- Ce fichier contient les modifications de sécurité à appliquer
-- à la base de données existante.
-- EXÉCUTER AVANT LE PENTEST
-- ============================================================

-- Table pour la protection contre les attaques par force brute
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_agent` varchar(255) DEFAULT NULL,
  `attempted_user` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ip_time` (`ip_address`, `attempt_time`),
  KEY `idx_attempt_time` (`attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Table pour les logs de sécurité
-- ============================================================
CREATE TABLE IF NOT EXISTS `security_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_type` enum('login_success','login_failed','logout','password_change','role_change','suspicious_activity','csrf_failed','rate_limit') NOT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_type` (`event_type`),
  KEY `idx_user_email` (`user_email`),
  KEY `idx_ip_address` (`ip_address`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Table pour les tokens de réinitialisation de mot de passe
-- ============================================================
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_token` (`token`),
  KEY `idx_email` (`email`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Table pour les sessions actives (optionnel - pour tracking)
-- ============================================================
CREATE TABLE IF NOT EXISTS `active_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `last_activity` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_session` (`session_id`),
  KEY `idx_user_email` (`user_email`),
  KEY `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- Ajout de colonnes de sécurité à la table users
-- ============================================================
-- Ajouter les colonnes si elles n'existent pas
ALTER TABLE `users` 
  ADD COLUMN IF NOT EXISTS `last_login` datetime DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `login_count` int(11) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `password_changed_at` datetime DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `account_locked` tinyint(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `lock_reason` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `email_verified` tinyint(1) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `two_factor_enabled` tinyint(1) DEFAULT 0;

-- ============================================================
-- Nettoyage automatique (EVENTS - nécessite EVENT SCHEDULER)
-- ============================================================
-- Activer le planificateur d'événements (si vous avez les droits)
-- SET GLOBAL event_scheduler = ON;

-- Nettoyage des tentatives de connexion anciennes
DROP EVENT IF EXISTS clean_login_attempts;
DELIMITER //
CREATE EVENT IF NOT EXISTS clean_login_attempts
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
    DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 24 HOUR);
END//
DELIMITER ;

-- Nettoyage des tokens de réinitialisation expirés
DROP EVENT IF EXISTS clean_password_resets;
DELIMITER //
CREATE EVENT IF NOT EXISTS clean_password_resets
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
    DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1;
END//
DELIMITER ;

-- Nettoyage des sessions inactives
DROP EVENT IF EXISTS clean_inactive_sessions;
DELIMITER //
CREATE EVENT IF NOT EXISTS clean_inactive_sessions
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
    DELETE FROM active_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 24 HOUR);
END//
DELIMITER ;

-- ============================================================
-- PROCÉDURES STOCKÉES SÉCURISÉES
-- ============================================================

-- Procédure pour enregistrer un log de sécurité
DROP PROCEDURE IF EXISTS log_security_event;
DELIMITER //
CREATE PROCEDURE log_security_event(
    IN p_event_type VARCHAR(50),
    IN p_user_email VARCHAR(255),
    IN p_ip_address VARCHAR(45),
    IN p_user_agent VARCHAR(255),
    IN p_details TEXT
)
BEGIN
    INSERT INTO security_logs (event_type, user_email, ip_address, user_agent, details)
    VALUES (p_event_type, p_user_email, p_ip_address, p_user_agent, p_details);
END//
DELIMITER ;

-- Procédure pour vérifier si une IP est bloquée
DROP PROCEDURE IF EXISTS check_ip_blocked;
DELIMITER //
CREATE PROCEDURE check_ip_blocked(
    IN p_ip_address VARCHAR(45),
    OUT p_is_blocked BOOLEAN
)
BEGIN
    DECLARE attempt_count INT;
    
    SELECT COUNT(*) INTO attempt_count
    FROM login_attempts
    WHERE ip_address = p_ip_address 
    AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE);
    
    SET p_is_blocked = (attempt_count >= 5);
END//
DELIMITER ;

-- ============================================================
-- VUES SÉCURISÉES
-- ============================================================

-- Vue pour les statistiques de sécurité (sans données sensibles)
CREATE OR REPLACE VIEW security_stats AS
SELECT 
    DATE(created_at) as date,
    event_type,
    COUNT(*) as count
FROM security_logs
GROUP BY DATE(created_at), event_type
ORDER BY date DESC, count DESC;

-- Vue pour les tentatives de connexion récentes
CREATE OR REPLACE VIEW recent_login_attempts AS
SELECT 
    ip_address,
    COUNT(*) as attempt_count,
    MAX(attempt_time) as last_attempt
FROM login_attempts
WHERE attempt_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY ip_address
HAVING attempt_count >= 3
ORDER BY attempt_count DESC;

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
-- UPDATE users SET password = 'NOUVEAU_HASH_ICI', password_changed_at = NOW() WHERE email = 'admin@nova.com';
-- ============================================================

-- Marquer que les mots de passe par défaut doivent être changés
UPDATE users 
SET password_changed_at = NULL 
WHERE email IN ('admin@nova.com', 'orga@nova.com');

-- ============================================================
-- INDEXES POUR LA PERFORMANCE DE SÉCURITÉ
-- ============================================================
-- Index composite pour les requêtes de sécurité fréquentes
CREATE INDEX IF NOT EXISTS idx_users_email_role ON users(email, role);
CREATE INDEX IF NOT EXISTS idx_inscriptions_user_event ON inscriptions(user_email, id_event);
CREATE INDEX IF NOT EXISTS idx_event_status_date ON event(status, event_date);

