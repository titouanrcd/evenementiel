<?php
/**
 * ============================================================
 * BASE DE DONNÉES - NOVA Événements
 * ============================================================
 * Connexion PDO sécurisée (Singleton)
 * ============================================================
 */

namespace App\Core;

class Database
{
    private static ?Database $instance = null;
    private \PDO $pdo;
    
    /**
     * Constructeur privé (Singleton)
     */
    private function __construct()
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );
            
            $options = [
                // Mode d'erreur: exceptions
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                
                // Mode de récupération: tableau associatif
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                
                // Désactiver l'émulation des requêtes préparées (SÉCURITÉ)
                \PDO::ATTR_EMULATE_PREPARES => false,
                
                // Utiliser des requêtes préparées natives
                \PDO::MYSQL_ATTR_DIRECT_QUERY => false,
                
                // Connexion persistante (performance)
                \PDO::ATTR_PERSISTENT => false,
                
                // Timeout de connexion
                \PDO::ATTR_TIMEOUT => 5,
            ];
            
            $this->pdo = new \PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Configuration SQL sécurisée
            $this->pdo->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
            
        } catch (\PDOException $e) {
            error_log("Erreur connexion DB: " . $e->getMessage());
            throw new \Exception("Erreur de connexion à la base de données");
        }
    }
    
    /**
     * Obtenir l'instance unique
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtenir la connexion PDO
     */
    public function getConnection(): \PDO
    {
        return $this->pdo;
    }
    
    /**
     * Exécuter une requête préparée (SELECT)
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Exécuter une requête préparée et récupérer tous les résultats
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }
    
    /**
     * Exécuter une requête préparée et récupérer un seul résultat
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }
    
    /**
     * Exécuter une requête préparée (INSERT, UPDATE, DELETE)
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
    
    /**
     * Obtenir le dernier ID inséré
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Démarrer une transaction
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Valider une transaction
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }
    
    /**
     * Annuler une transaction
     */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }
    
    /**
     * Échapper les caractères spéciaux pour LIKE
     */
    public static function escapeLike(string $value): string
    {
        return addcslashes($value, '%_\\');
    }
    
    /**
     * Empêcher le clonage (Singleton)
     */
    private function __clone() {}
    
    /**
     * Empêcher la désérialisation (Singleton)
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
