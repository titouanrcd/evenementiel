<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'gestion_events_etudiants';

echo "Testing connection to MySQL...\n";

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    echo "Connected to MySQL server successfully.\n";
    
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
    $db = $stmt->fetch();
    
    if ($db) {
        echo "Database '$dbname' exists.\n";
        
        // Try connecting to the specific database
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
        echo "Connected to database '$dbname' successfully.\n";
    } else {
        echo "ERROR: Database '$dbname' does NOT exist.\n";
        echo "You need to import database.sql.\n";
    }
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
