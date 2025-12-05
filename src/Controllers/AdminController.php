<?php
/**
 * ============================================================
 * CONTRÔLEUR ADMIN - NOVA Événements
 * ============================================================
 */

namespace App\Controllers;

use App\Core\Security;

class AdminController extends Controller
{
    private array $tags = [
        'sport' => 'Sport',
        'culture' => 'Culture',
        'soiree' => 'Soirée',
        'conference' => 'Conférence',
        'festival' => 'Festival',
        'autre' => 'Autre'
    ];
    
    /**
     * Dashboard admin
     */
    public function index(): void
    {
        $this->requireRole('admin');
        
        // Statistiques
        $stats = [
            'users' => $this->db->fetch("SELECT COUNT(*) as count FROM users")['count'],
            'events' => $this->db->fetch("SELECT COUNT(*) as count FROM event")['count'],
            'events_published' => $this->db->fetch("SELECT COUNT(*) as count FROM event WHERE status = 'publié'")['count'],
            'events_pending' => $this->db->fetch("SELECT COUNT(*) as count FROM event WHERE status = 'en attente'")['count'],
            'inscriptions' => $this->db->fetch("SELECT COUNT(*) as count FROM inscriptions WHERE statut = 'confirmé'")['count']
        ];
        
        // Événements en attente
        $pendingEvents = $this->db->fetchAll(
            "SELECT e.*, u.user as owner_name 
             FROM event e 
             LEFT JOIN users u ON e.owner_email = u.email
             WHERE e.status = 'en attente'
             ORDER BY e.event_date DESC
             LIMIT 5"
        );
        
        // Derniers utilisateurs
        $recentUsers = $this->db->fetchAll(
            "SELECT * FROM users ORDER BY created_at DESC LIMIT 5"
        );
        
        $this->render('admin/index', [
            'stats' => $stats,
            'pendingEvents' => $pendingEvents,
            'recentUsers' => $recentUsers,
            'tags' => $this->tags,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Liste des utilisateurs
     */
    public function users(): void
    {
        $this->requireRole('admin');
        
        $users = $this->db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");
        
        $this->render('admin/users', [
            'users' => $users,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Liste des événements
     */
    public function events(): void
    {
        $this->requireRole('admin');
        
        $status = $_GET['status'] ?? '';
        
        $sql = "SELECT e.*, u.user as owner_name,
                (SELECT COUNT(*) FROM inscriptions i WHERE i.id_event = e.id_event AND i.statut = 'confirmé') as nb_inscrits
                FROM event e 
                LEFT JOIN users u ON e.owner_email = u.email";
        $params = [];
        
        if ($status && in_array($status, ['publié', 'en attente'])) {
            $sql .= " WHERE e.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY e.event_date DESC";
        
        $events = $this->db->fetchAll($sql, $params);
        
        $this->render('admin/events', [
            'events' => $events,
            'tags' => $this->tags,
            'currentStatus' => $status,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Changer le rôle d'un utilisateur
     */
    public function changeRole(): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        
        $targetEmail = sanitizeEmail($_POST['user_email'] ?? '');
        $newRole = $_POST['new_role'] ?? '';
        
        // Ne pas modifier son propre rôle
        if ($targetEmail === $_SESSION['user_email']) {
            $this->flash('error', 'Vous ne pouvez pas modifier votre propre rôle.');
            $this->redirect('/admin/utilisateurs');
            return;
        }
        
        if ($targetEmail && in_array($newRole, ['user', 'organisateur', 'admin'])) {
            $this->db->execute(
                "UPDATE users SET role = ? WHERE email = ?",
                [$newRole, $targetEmail]
            );
            
            Security::logSecurityEvent('role_change', 'User role changed', [
                'target' => $targetEmail,
                'new_role' => $newRole,
                'by' => $_SESSION['user_email']
            ]);
            
            $this->flash('success', 'Rôle mis à jour avec succès.');
        } else {
            $this->flash('error', 'Données invalides.');
        }
        
        $this->redirect('/admin/utilisateurs');
    }
    
    /**
     * Supprimer un utilisateur
     */
    public function deleteUser(): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        
        $targetEmail = sanitizeEmail($_POST['user_email'] ?? '');
        
        // Ne pas se supprimer soi-même
        if ($targetEmail === $_SESSION['user_email']) {
            $this->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            $this->redirect('/admin/utilisateurs');
            return;
        }
        
        if ($targetEmail) {
            $this->db->execute("DELETE FROM users WHERE email = ?", [$targetEmail]);
            
            Security::logSecurityEvent('user_deleted', 'User deleted', [
                'target' => $targetEmail,
                'by' => $_SESSION['user_email']
            ]);
            
            $this->flash('success', 'Utilisateur supprimé.');
        }
        
        $this->redirect('/admin/utilisateurs');
    }
    
    /**
     * Changer le statut d'un événement
     */
    public function changeEventStatus(): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        
        $eventId = sanitizeInt($_POST['id_event'] ?? 0);
        $newStatus = $_POST['new_status'] ?? '';
        
        if ($eventId && in_array($newStatus, ['publié', 'en attente'])) {
            $this->db->execute(
                "UPDATE event SET status = ? WHERE id_event = ?",
                [$newStatus, $eventId]
            );
            
            $this->flash('success', 'Statut de l\'événement mis à jour.');
        } else {
            $this->flash('error', 'Données invalides.');
        }
        
        $this->redirect('/admin/evenements');
    }
    
    /**
     * Supprimer un événement
     */
    public function deleteEvent(): void
    {
        $this->requireRole('admin');
        $this->verifyCsrf();
        
        $eventId = sanitizeInt($_POST['id_event'] ?? 0);
        
        if ($eventId) {
            $this->db->execute("DELETE FROM event WHERE id_event = ?", [$eventId]);
            $this->flash('success', 'Événement supprimé.');
        }
        
        $this->redirect('/admin/evenements');
    }
}
