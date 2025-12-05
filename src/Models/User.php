<?php
/**
 * ============================================================
 * MODÈLE USER - NOVA Événements
 * ============================================================
 */

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    /**
     * Trouver un utilisateur par email ou nom d'utilisateur
     */
    public function findByIdentifier(string $identifier): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM users WHERE email = ? OR user = ?",
            [$identifier, $identifier]
        );
    }
    
    /**
     * Trouver un utilisateur par email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
    }
    
    /**
     * Créer un nouvel utilisateur
     */
    public function create(array $data): bool
    {
        return (bool)$this->db->execute(
            "INSERT INTO users (user, email, date_of_birth, sexe, number, password, role) 
             VALUES (?, ?, ?, ?, ?, ?, 'user')",
            [
                $data['user'],
                $data['email'],
                $data['date_of_birth'],
                $data['sexe'],
                $data['number'] ?: null,
                $data['password']
            ]
        );
    }
    
    /**
     * Mettre à jour un utilisateur
     */
    public function update(string $email, array $data): bool
    {
        return (bool)$this->db->execute(
            "UPDATE users SET user = ?, number = ? WHERE email = ?",
            [$data['user'], $data['number'], $email]
        );
    }
    
    /**
     * Récupérer les inscriptions d'un utilisateur avec les détails de l'événement
     */
    public function getInscriptions(string $email): array
    {
        return $this->db->fetchAll(
            "SELECT i.*, e.name, e.event_date, e.hour, e.lieu, e.description, e.prix, e.capacite, e.status, e.image
             FROM inscriptions i
             JOIN event e ON i.id_event = e.id_event
             WHERE i.user_email = ?
             ORDER BY e.event_date DESC",
            [$email]
        );
    }
    
    /**
     * Récupérer tous les utilisateurs (pour admin)
     */
    public function findAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");
    }
    
    /**
     * Changer le rôle d'un utilisateur
     */
    public function updateRole(string $email, string $role): bool
    {
        return (bool)$this->db->execute(
            "UPDATE users SET role = ? WHERE email = ?",
            [$role, $email]
        );
    }
    
    /**
     * Supprimer un utilisateur
     */
    public function delete(string $email): bool
    {
        return (bool)$this->db->execute(
            "DELETE FROM users WHERE email = ?",
            [$email]
        );
    }
    
    /**
     * Obtenir le dernier ID inséré
     */
    public function getLastInsertId(): string
    {
        return $this->db->lastInsertId();
    }

    /**
     * Compter tous les utilisateurs
     */
    public function countAll(): int
    {
        return (int)$this->db->fetch("SELECT COUNT(*) as count FROM users")['count'];
    }

    /**
     * Récupérer les derniers utilisateurs inscrits
     */
    public function findRecent(int $limit = 5): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM users ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }
}
