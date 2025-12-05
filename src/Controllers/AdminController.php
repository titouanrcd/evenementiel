<?php
/**
 * ============================================================
 * CONTRÔLEUR ADMIN - NOVA Événements
 * ============================================================
 */

namespace App\Controllers;

use App\Core\Security;
use App\Models\User;
use App\Models\Event;

class AdminController extends Controller
{
    private User $userModel;
    private Event $eventModel;
    private array $tags = [
        'sport' => 'Sport',
        'culture' => 'Culture',
        'soiree' => 'Soirée',
        'conference' => 'Conférence',
        'festival' => 'Festival',
        'autre' => 'Autre'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->eventModel = new Event();
    }
    
    /**
     * Dashboard admin
     */
    public function index(): void
    {
        $this->requireRole('admin');
        
        // Statistiques
        $stats = [
            'users' => $this->userModel->countAll(),
            'events' => $this->eventModel->countAll(),
            'events_published' => $this->eventModel->countByStatus('publié'),
            'events_pending' => $this->eventModel->countByStatus('en attente'),
            'inscriptions' => $this->eventModel->countTotalInscriptions()
        ];
        
        // Événements en attente
        $pendingEvents = $this->eventModel->findPendingWithDetails(5);
        
        // Derniers utilisateurs
        $recentUsers = $this->userModel->findRecent(5);
        
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
        
        $users = $this->userModel->findAll();
        
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
        $validStatus = in_array($status, ['publié', 'en attente']) ? $status : null;
        
        $events = $this->eventModel->findAllWithDetails($validStatus);
        
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
            $this->userModel->updateRole($targetEmail, $newRole);
            
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
            $this->userModel->delete($targetEmail);
            
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
            $this->eventModel->updateStatus($eventId, $newStatus);
            
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
            $this->eventModel->delete($eventId);
            $this->flash('success', 'Événement supprimé.');
        }
        
        $this->redirect('/admin/evenements');
    }
}
