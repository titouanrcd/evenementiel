<?php
/**
 * ============================================================
 * MODÈLE EVENT - NOVA Événements
 * ============================================================
 */

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Event extends Model
{
    /**
     * Récupérer tous les événements publiés avec filtres
     */
    public function findAllPublished(array $filters = []): array
    {
        $sql = "SELECT e.*, 
                (SELECT COUNT(*) FROM inscriptions i WHERE i.id_event = e.id_event AND i.statut = 'confirmé') as nb_inscrits
                FROM event e 
                WHERE e.status = 'publié'";
        $params = [];
        
        if (!empty($filters['search'])) {
            $searchEscaped = Database::escapeLike($filters['search']);
            $sql .= " AND (e.name LIKE ? OR e.description LIKE ? OR e.lieu LIKE ?)";
            $searchParam = "%{$searchEscaped}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }
        
        if (!empty($filters['tag'])) {
            $sql .= " AND e.tag = ?";
            $params[] = $filters['tag'];
        }
        
        if (!empty($filters['lieu'])) {
            $sql .= " AND e.lieu LIKE ?";
            $params[] = "%" . Database::escapeLike($filters['lieu']) . "%";
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND e.event_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND e.event_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (isset($filters['prix_max']) && $filters['prix_max'] < 200) {
            $sql .= " AND e.prix <= ?";
            $params[] = $filters['prix_max'];
        }
        
        // Tri
        $sort = $filters['sort'] ?? 'date';
        switch ($sort) {
            case 'prix_asc':
                $sql .= " ORDER BY e.prix ASC, e.event_date ASC";
                break;
            case 'prix_desc':
                $sql .= " ORDER BY e.prix DESC, e.event_date ASC";
                break;
            default:
                $sql .= " ORDER BY e.event_date ASC";
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Récupérer la liste des lieux distincts
     */
    public function findDistinctLocations(): array
    {
        return $this->db->fetchAll(
            "SELECT DISTINCT lieu FROM event WHERE status = 'publié' ORDER BY lieu"
        );
    }
    
    /**
     * Récupérer les inscriptions d'un utilisateur
     */
    public function findUserRegistrations(string $userEmail): array
    {
        $registrations = $this->db->fetchAll(
            "SELECT id_event FROM inscriptions WHERE user_email = ? AND statut = 'confirmé'",
            [$userEmail]
        );
        return array_column($registrations, 'id_event');
    }
    
    /**
     * Trouver un événement par ID avec détails
     */
    public function findByIdWithDetails(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT e.*, u.user as owner_name,
             (SELECT COUNT(*) FROM inscriptions i WHERE i.id_event = e.id_event AND i.statut = 'confirmé') as nb_inscrits
             FROM event e 
             LEFT JOIN users u ON e.owner_email = u.email
             WHERE e.id_event = ? AND e.status = 'publié'",
            [$id]
        );
    }
    
    /**
     * Vérifier si un utilisateur est inscrit
     */
    public function isUserRegistered(string $userEmail, int $eventId): bool
    {
        $registration = $this->db->fetch(
            "SELECT id_inscription FROM inscriptions 
             WHERE user_email = ? AND id_event = ? AND statut = 'confirmé'",
            [$userEmail, $eventId]
        );
        return (bool)$registration;
    }
    
    /**
     * Vérifier la capacité d'un événement
     */
    public function checkCapacity(int $eventId): ?array
    {
        return $this->db->fetch(
            "SELECT e.capacite, COUNT(i.id_inscription) as inscrits 
             FROM event e 
             LEFT JOIN inscriptions i ON e.id_event = i.id_event AND i.statut = 'confirmé'
             WHERE e.id_event = ?
             GROUP BY e.id_event",
            [$eventId]
        );
    }
    
    /**
     * Inscrire un utilisateur
     */
    public function registerUser(string $userEmail, int $eventId): bool
    {
        // Tenter de réactiver une ancienne inscription
        $reactivated = $this->db->execute(
            "UPDATE inscriptions SET statut = 'confirmé', date_inscription = NOW() 
             WHERE user_email = ? AND id_event = ?",
            [$userEmail, $eventId]
        );
        
        if ($reactivated === 0) {
            // Créer une nouvelle inscription
            $this->db->execute(
                "INSERT INTO inscriptions (user_email, id_event, statut) VALUES (?, ?, 'confirmé')",
                [$userEmail, $eventId]
            );
        }
        
        return true;
    }
    
    /**
     * Désinscrire un utilisateur
     */
    public function unregisterUser(int $inscriptionId, string $userEmail): void
    {
        $this->db->execute(
            "UPDATE inscriptions SET statut = 'annulé' 
             WHERE id_inscription = ? AND user_email = ?",
            [$inscriptionId, $userEmail]
        );
    }

    /**
     * Récupérer les événements d'un organisateur
     */
    public function findByOwner(string $ownerEmail): array
    {
        return $this->db->fetchAll(
            "SELECT e.*, 
             (SELECT COUNT(*) FROM inscriptions i WHERE i.id_event = e.id_event AND i.statut = 'confirmé') as nb_inscrits
             FROM event e 
             WHERE e.owner_email = ?
             ORDER BY e.event_date DESC",
            [$ownerEmail]
        );
    }

    /**
     * Créer un événement
     */
    public function create(array $data): bool
    {
        return (bool)$this->db->execute(
            "INSERT INTO event (name, description, event_date, hour, lieu, capacite, prix, tag, image, owner_email, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['name'],
                $data['description'],
                $data['event_date'],
                $data['hour'],
                $data['lieu'],
                $data['capacite'],
                $data['prix'],
                $data['tag'],
                $data['image'],
                $data['owner_email'],
                $data['status']
            ]
        );
    }

    /**
     * Trouver un événement par ID (sans détails supplémentaires)
     */
    public function findById(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM event WHERE id_event = ?", [$id]);
    }

    /**
     * Mettre à jour un événement
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE event SET name = ?, description = ?, event_date = ?, hour = ?, lieu = ?, capacite = ?, prix = ?, tag = ?";
        $params = [
            $data['name'],
            $data['description'],
            $data['event_date'],
            $data['hour'],
            $data['lieu'],
            $data['capacite'],
            $data['prix'],
            $data['tag']
        ];

        if (isset($data['image'])) {
            $sql .= ", image = ?";
            $params[] = $data['image'];
        }

        $sql .= " WHERE id_event = ?";
        $params[] = $id;

        return (bool)$this->db->execute($sql, $params);
    }

    /**
     * Supprimer un événement
     */
    public function delete(int $id): bool
    {
        return (bool)$this->db->execute("DELETE FROM event WHERE id_event = ?", [$id]);
    }

    /**
     * Compter tous les événements
     */
    public function countAll(): int
    {
        return (int)$this->db->fetch("SELECT COUNT(*) as count FROM event")['count'];
    }

    /**
     * Compter les événements par statut
     */
    public function countByStatus(string $status): int
    {
        return (int)$this->db->fetch("SELECT COUNT(*) as count FROM event WHERE status = ?", [$status])['count'];
    }

    /**
     * Compter toutes les inscriptions confirmées
     */
    public function countTotalInscriptions(): int
    {
        return (int)$this->db->fetch("SELECT COUNT(*) as count FROM inscriptions WHERE statut = 'confirmé'")['count'];
    }

    /**
     * Récupérer les événements en attente avec le nom de l'organisateur
     */
    public function findPendingWithDetails(int $limit = 5): array
    {
        return $this->db->fetchAll(
            "SELECT e.*, u.user as owner_name 
             FROM event e 
             LEFT JOIN users u ON e.owner_email = u.email
             WHERE e.status = 'en attente'
             ORDER BY e.event_date DESC
             LIMIT ?",
            [$limit]
        );
    }

    /**
     * Récupérer tous les événements avec filtres admin
     */
    public function findAllWithDetails(?string $status = null): array
    {
        $sql = "SELECT e.*, u.user as owner_name,
                (SELECT COUNT(*) FROM inscriptions i WHERE i.id_event = e.id_event AND i.statut = 'confirmé') as nb_inscrits
                FROM event e 
                LEFT JOIN users u ON e.owner_email = u.email";
        $params = [];
        
        if ($status) {
            $sql .= " WHERE e.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY e.event_date DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Changer le statut d'un événement
     */
    public function updateStatus(int $id, string $status): bool
    {
        return (bool)$this->db->execute(
            "UPDATE event SET status = ? WHERE id_event = ?",
            [$status, $id]
        );
    }

    /**
     * Récupérer les événements à venir pour l'accueil
     */
    public function findUpcoming(int $limit = 6): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM event 
             WHERE status = 'publié' AND event_date >= CURDATE() 
             ORDER BY event_date ASC 
             LIMIT ?",
            [$limit]
        );
    }
}
