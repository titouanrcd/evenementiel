-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 27 nov. 2025
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gestion_events_etudiants`
--

-- --------------------------------------------------------

--
-- Structure de la table `event`
--

CREATE TABLE `event` (
  `id_event` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` enum('publié','en attente') NOT NULL DEFAULT 'en attente',
  `owner_email` varchar(255) NOT NULL,
  `event_date` date NOT NULL,
  `hour` time NOT NULL,
  `description` text NOT NULL,
  `capacite` int(11) NOT NULL,
  `prix` int(11) NOT NULL DEFAULT 0,
  `lieu` varchar(255) NOT NULL,
  `tag` enum('sport','culture','soiree','conference','festival','autre') NOT NULL DEFAULT 'autre',
  `image` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `user` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `date_of_birth` date NOT NULL,
  `sexe` enum('H','F','Other') DEFAULT NULL,
  `number` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','organisateur','admin') NOT NULL DEFAULT 'user',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Table user';

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id_event`,`name`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `event`
--
ALTER TABLE `event`
  MODIFY `id_event` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Structure de la table `inscriptions`
-- Table pour gérer les inscriptions des utilisateurs aux événements
--

CREATE TABLE `inscriptions` (
  `id_inscription` int(11) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `id_event` int(11) NOT NULL,
  `date_inscription` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `statut` enum('confirmé','annulé','en_attente') NOT NULL DEFAULT 'confirmé'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD PRIMARY KEY (`id_inscription`),
  ADD UNIQUE KEY `unique_inscription` (`user_email`, `id_event`),
  ADD KEY `fk_user_email` (`user_email`),
  ADD KEY `fk_event_id` (`id_event`);

--
-- AUTO_INCREMENT pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  MODIFY `id_inscription` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD CONSTRAINT `fk_user_email` FOREIGN KEY (`user_email`) REFERENCES `users` (`email`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_event_id` FOREIGN KEY (`id_event`) REFERENCES `event` (`id_event`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --------------------------------------------------------
--
-- Structure de la table `login_attempts` (Protection Brute Force)
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ip_time` (`ip_address`, `attempt_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
--
-- Insertion d'un compte admin par défaut
-- Email: admin@nova.com | Password: admin123
--
INSERT INTO `users` (`user`, `email`, `date_of_birth`, `sexe`, `number`, `password`, `role`, `created_at`) VALUES
('Admin NOVA', 'admin@nova.com', '1990-01-01', 'H', '0600000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NOW()),
('Organisateur Test', 'orga@nova.com', '1995-05-15', 'F', '0611111111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'organisateur', NOW());

-- --------------------------------------------------------
--
-- Insertion de 30 événements aléatoires
--
INSERT INTO `event` (`name`, `status`, `owner_email`, `event_date`, `hour`, `description`, `capacite`, `prix`, `lieu`, `tag`, `image`) VALUES
-- SPORT (6 événements)
('Marathon de Paris 2025', 'publié', 'orga@nova.com', '2025-12-15', '08:00:00', 'Le plus grand marathon de France avec plus de 50 000 participants. Parcours mythique passant par les Champs-Élysées, la Tour Eiffel et Notre-Dame.', 50000, 85, 'Paris, Champs-Élysées', 'sport', 'https://images.unsplash.com/photo-1513593771513-7b58b6c4af38?w=800'),
('Tournoi de Basketball 3x3', 'publié', 'orga@nova.com', '2025-12-20', '14:00:00', 'Tournoi amateur de basketball 3x3 ouvert à tous. Venez montrer vos skills sur le terrain!', 200, 15, 'Lyon, Parc de la Tête d''Or', 'sport', 'https://images.unsplash.com/photo-1546519638-68e109498ffc?w=800'),
('Course Cycliste des Alpes', 'publié', 'orga@nova.com', '2026-01-10', '07:30:00', 'Une course épique à travers les cols alpins. 120km de dénivelé et de paysages à couper le souffle.', 500, 45, 'Grenoble, Place Victor Hugo', 'sport', 'https://images.unsplash.com/photo-1517649763962-0c623066013b?w=800'),
('Compétition de Natation', 'publié', 'orga@nova.com', '2026-01-25', '09:00:00', 'Championnats régionaux de natation. Toutes catégories d''âge. Piscine olympique.', 300, 0, 'Marseille, Piscine Vallier', 'sport', 'https://images.unsplash.com/photo-1519315901367-f34ff9154487?w=800'),
('Match de Rugby Universitaire', 'publié', 'orga@nova.com', '2025-12-08', '15:00:00', 'Grande finale du championnat universitaire de rugby. Ambiance garantie!', 5000, 10, 'Toulouse, Stade Ernest Wallon', 'sport', 'https://images.unsplash.com/photo-1544298621-a28e5251dca0?w=800'),
('Tournoi de Tennis Amateur', 'en attente', 'orga@nova.com', '2026-02-15', '10:00:00', 'Tournoi de tennis simple et double. Tous niveaux acceptés.', 64, 25, 'Nice, Tennis Club', 'sport', 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?w=800'),

-- CULTURE (6 événements)
('Exposition Van Gogh Immersive', 'publié', 'orga@nova.com', '2025-12-01', '10:00:00', 'Plongez dans l''univers de Van Gogh avec cette exposition immersive à 360°. Une expérience sensorielle unique.', 200, 18, 'Paris, Atelier des Lumières', 'culture', 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800'),
('Concert Symphonique de Noël', 'publié', 'orga@nova.com', '2025-12-24', '20:00:00', 'L''Orchestre Philharmonique présente son concert annuel de Noël. Œuvres classiques et chants traditionnels.', 2000, 35, 'Bordeaux, Grand Théâtre', 'culture', 'https://images.unsplash.com/photo-1507838153414-b4b713384a76?w=800'),
('Festival de Théâtre d''Avignon', 'publié', 'orga@nova.com', '2026-01-15', '19:00:00', 'Édition spéciale hiver du célèbre festival. 5 pièces contemporaines à découvrir.', 800, 28, 'Avignon, Palais des Papes', 'culture', 'https://images.unsplash.com/photo-1503095396549-807759245b35?w=800'),
('Musée de la Nuit', 'publié', 'orga@nova.com', '2026-02-01', '21:00:00', 'Visitez le musée de nuit avec animations spéciales, concerts acoustiques et dégustations.', 500, 12, 'Lyon, Musée des Beaux-Arts', 'culture', 'https://images.unsplash.com/photo-1554907984-15263bfd63bd?w=800'),
('Lecture Poétique', 'publié', 'orga@nova.com', '2025-12-10', '18:30:00', 'Soirée lecture avec des poètes contemporains. Discussions et dédicaces après l''événement.', 100, 8, 'Paris, Shakespeare & Co', 'culture', 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=800'),
('Vernissage Art Contemporain', 'en attente', 'orga@nova.com', '2026-01-20', '19:00:00', 'Découvrez les œuvres de 15 artistes émergents. Cocktail offert.', 150, 0, 'Nantes, HAB Galerie', 'culture', 'https://images.unsplash.com/photo-1531243269054-5ebf6f34081e?w=800'),

-- SOIREE (6 événements)
('Nouvel An Électro', 'publié', 'orga@nova.com', '2025-12-31', '22:00:00', 'La plus grande soirée du Nouvel An! DJs internationaux, show laser et feu d''artifice à minuit.', 3000, 55, 'Paris, Palais de Tokyo', 'soiree', 'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?w=800'),
('Soirée Années 80', 'publié', 'orga@nova.com', '2025-12-14', '21:00:00', 'Retour dans les années 80! Dress code obligatoire. Hits de l''époque toute la nuit.', 500, 20, 'Lille, Le Warehouse', 'soiree', 'https://images.unsplash.com/photo-1429962714451-bb934ecdc4ec?w=800'),
('Gala Étudiant', 'publié', 'orga@nova.com', '2026-01-18', '20:00:00', 'Le gala annuel des étudiants. Dîner de gala, remise de prix et soirée dansante.', 600, 45, 'Strasbourg, Palais de la Musique', 'soiree', 'https://images.unsplash.com/photo-1519671482749-fd09be7ccebf?w=800'),
('After Work Rooftop', 'publié', 'orga@nova.com', '2025-12-06', '18:00:00', 'Networking et cocktails sur le plus beau rooftop de la ville. Vue panoramique.', 200, 15, 'Paris, Mama Shelter', 'soiree', 'https://images.unsplash.com/photo-1572116469696-31de0f17cc34?w=800'),
('Soirée Latino', 'publié', 'orga@nova.com', '2025-12-21', '21:30:00', 'Salsa, bachata, reggaeton! Cours de danse à 21h30, soirée à partir de 23h.', 400, 12, 'Montpellier, El Barrio', 'soiree', 'https://images.unsplash.com/photo-1504609813442-a8924e83f76e?w=800'),
('Silent Disco', 'en attente', 'orga@nova.com', '2026-02-08', '22:00:00', 'Trois DJs, trois ambiances, un casque. Choisissez votre musique et dansez!', 300, 18, 'Rennes, Le Liberté', 'soiree', 'https://images.unsplash.com/photo-1504196606672-aef5c9cefc92?w=800'),

-- CONFERENCE (6 événements)
('TEDx Innovation', 'publié', 'orga@nova.com', '2025-12-05', '09:00:00', 'Une journée d''inspiration avec 12 speakers sur le thème de l''innovation responsable.', 800, 75, 'Paris, Station F', 'conference', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800'),
('Forum Emploi Tech', 'publié', 'orga@nova.com', '2025-12-12', '10:00:00', 'Rencontrez les recruteurs des plus grandes entreprises tech. CV et motivation requis!', 2000, 0, 'Lyon, Cité Internationale', 'conference', 'https://images.unsplash.com/photo-1515187029135-18ee286d815b?w=800'),
('Conférence Intelligence Artificielle', 'publié', 'orga@nova.com', '2026-01-22', '14:00:00', 'Les dernières avancées en IA présentées par des chercheurs du CNRS et de Google.', 500, 35, 'Grenoble, Minatec', 'conference', 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800'),
('Workshop Développement Web', 'publié', 'orga@nova.com', '2025-12-18', '09:30:00', 'Formation pratique React & Node.js. Ordinateur portable requis. Déjeuner inclus.', 50, 120, 'Nantes, Cantine Numérique', 'conference', 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=800'),
('Séminaire Entrepreneuriat', 'publié', 'orga@nova.com', '2026-01-30', '08:30:00', 'De l''idée au business plan. Témoignages de startuppers et ateliers pratiques.', 150, 50, 'Bordeaux, Darwin', 'conference', 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?w=800'),
('Masterclass Photographie', 'en attente', 'orga@nova.com', '2026-02-20', '10:00:00', 'Techniques avancées de photographie avec un photographe National Geographic.', 30, 180, 'Paris, Studio Harcourt', 'conference', 'https://images.unsplash.com/photo-1542038784456-1ea8e935640e?w=800'),

-- FESTIVAL (6 événements)
('Festival Electro Beach', 'publié', 'orga@nova.com', '2026-02-28', '16:00:00', 'Trois jours de musique électro sur la plage. 50 artistes, 4 scènes.', 15000, 89, 'Cannes, Plage du Midi', 'festival', 'https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?w=800'),
('Festival du Film Court', 'publié', 'orga@nova.com', '2025-12-28', '14:00:00', 'Compétition internationale de courts métrages. 60 films en 3 jours.', 1000, 25, 'Clermont-Ferrand, Maison de la Culture', 'festival', 'https://images.unsplash.com/photo-1478720568477-152d9b164e26?w=800'),
('Festival Gastronomique', 'publié', 'orga@nova.com', '2026-01-08', '11:00:00', '30 chefs étoilés réunis pour un week-end gourmand. Dégustations et démonstrations.', 5000, 40, 'Lyon, Halles Paul Bocuse', 'festival', 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800'),
('Carnaval de Nice', 'publié', 'orga@nova.com', '2026-02-14', '14:30:00', 'Le plus grand carnaval de France! Chars, batailles de fleurs et concerts.', 100000, 0, 'Nice, Promenade des Anglais', 'festival', 'https://images.unsplash.com/photo-1518998053901-5348d3961a04?w=800'),
('Festival BD', 'publié', 'orga@nova.com', '2026-01-25', '10:00:00', 'Rencontrez vos auteurs préférés, dédicaces, expositions et concours de dessin.', 8000, 15, 'Angoulême, Centre-Ville', 'festival', 'https://images.unsplash.com/photo-1612036782180-6f0b6cd846fe?w=800'),
('Festival Jazz', 'en attente', 'orga@nova.com', '2026-03-01', '19:00:00', 'Une semaine de jazz avec des artistes internationaux. Concerts indoor et outdoor.', 3000, 55, 'Marciac, Chapiteau', 'festival', 'https://images.unsplash.com/photo-1511192336575-5a79af67a629?w=800');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
