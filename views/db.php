<?php
/**
 * ============================================================
 * CONFIGURATION DE LA BASE DE DONNÉES - NOVA Événements
 * ============================================================
 * SÉCURITÉ: Ce fichier contient des informations sensibles.
 * En production, utilisez des variables d'environnement.
 * ============================================================
 */

// Configuration des identifiants de connexion
// ⚠️ EN PRODUCTION: Utilisez des variables d'environnement ou un fichier .env
$host = 'localhost';
$dbname = 'gestion_events_etudiants';
$username = 'root';
$password = '';

// Options de connexion PDO sécurisées
$options = [
    // Mode d'erreur: exceptions (jamais SILENT en développement!)
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    
    // Mode de récupération par défaut: tableau associatif
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    
    // Désactiver l'émulation des requêtes préparées (plus sécurisé)
    PDO::ATTR_EMULATE_PREPARES => false,
    
    // Utiliser des requêtes préparées natives
    PDO::MYSQL_ATTR_DIRECT_QUERY => false,
];

try {
    // Création de la connexion PDO avec les options sécurisées
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        $options
    );
    
} catch (PDOException $e) {
    // ⚠️ SÉCURITÉ: Ne jamais afficher les détails de l'erreur en production!
    // Logger l'erreur en interne
    error_log("Erreur de connexion DB: " . $e->getMessage());
    
    // Afficher un message générique à l'utilisateur
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
        die("Une erreur technique est survenue. Veuillez réessayer plus tard.");
    } else {
        // En développement seulement, afficher les détails
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}
?>