<?php
// Configuration des identifiants de connexion
$host = 'localhost'; // Votre serveur local (XAMPP)
$dbname = 'gestion_events_etudiants'; // Le nom exact de votre base de données
$username = 'root'; // Nom d'utilisateur par défaut de MySQL sur XAMPP
$password = '';     // Mot de passe par défaut (vide) sur XAMPP

try {
    // Création d'une nouvelle instance de PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Configuration des attributs de connexion :
    
    // 1. Définir le mode d'erreur sur EXCEPTION pour une gestion des erreurs claire
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 2. Définir le mode de récupération par défaut sur FETCH_ASSOC
    // Cela permet de récupérer les résultats sous forme de tableau associatif (clés = noms des colonnes)
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Si la connexion réussit, l'objet $pdo est prêt à être utilisé.
    
} catch (PDOException $e) {
    // En cas d'échec de la connexion, on arrête le script et affiche l'erreur
    // IMPORTANT: Remplacez cette ligne par une erreur générique sur un site en production
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>