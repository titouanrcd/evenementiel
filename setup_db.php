<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'gestion_events_etudiants';
$sqlFile = __DIR__ . '/database.sql';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating database '$dbname' if it doesn't exist...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    
    echo "Selecting database...\n";
    $pdo->exec("USE `$dbname`");
    
    if (file_exists($sqlFile)) {
        echo "Importing SQL from $sqlFile...\n";
        $sql = file_get_contents($sqlFile);
        
        // Execute the SQL commands
        // Note: This is a simple split by semicolon, might fail on complex stored procedures but should work for simple dumps
        $pdo->exec($sql);
        echo "Database imported successfully!\n";
    } else {
        echo "Error: database.sql file not found at $sqlFile\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
