<?php
/**
 * ============================================================
 * CONTRÔLEUR EVENT - NOVA Événements
 * ============================================================
 */

namespace App\Controllers;

use App\Core\Database;
use App\Core\Security;

class EventController extends Controller
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
     * Liste des événements
     */
    public function index(): void
    {
        // Récupérer les filtres
        $search = sanitizeString($_GET['search'] ?? '', 100);
        $tag = isset($_GET['tag']) && array_key_exists($_GET['tag'], $this->tags) ? $_GET['tag'] : '';
        $lieu = sanitizeString($_GET['lieu'] ?? '', 255);
        $dateFrom = sanitizeDate($_GET['date_from'] ?? '');
        $dateTo = sanitizeDate($_GET['date_to'] ?? '');
        $prixMax = sanitizeInt($_GET['prix_max'] ?? 200, 0, 10000) ?? 200;
        
        // Construire la requête
        $sql = "SELECT e.*, 
                (SELECT COUNT(*) FROM inscriptions i WHERE i.id_event = e.id_event AND i.statut = 'confirmé') as nb_inscrits
                FROM event e 
                WHERE e.status = 'publié'";
        $params = [];
        
        if (!empty($search)) {
            $searchEscaped = Database::escapeLike($search);
            $sql .= " AND (e.name LIKE ? OR e.description LIKE ? OR e.lieu LIKE ?)";
            $searchParam = "%{$searchEscaped}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }
        
        if (!empty($tag)) {
            $sql .= " AND e.tag = ?";
            $params[] = $tag;
        }
        
        if (!empty($lieu)) {
            $sql .= " AND e.lieu LIKE ?";
            $params[] = "%" . Database::escapeLike($lieu) . "%";
        }
        
        if ($dateFrom) {
            $sql .= " AND e.event_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND e.event_date <= ?";
            $params[] = $dateTo;
        }
        
        if ($prixMax < 200) {
            $sql .= " AND e.prix <= ?";
            $params[] = $prixMax;
        }
        
        // Tri par prix ou date
        $sort = $_GET['sort'] ?? 'date';
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
        
        $events = $this->db->fetchAll($sql, $params);
        
        // Récupérer les lieux pour le filtre
        $lieux = $this->db->fetchAll(
            "SELECT DISTINCT lieu FROM event WHERE status = 'publié' ORDER BY lieu"
        );
        
        // Vérifier les inscriptions de l'utilisateur
        $userRegistrations = [];
        if (Security::isLoggedIn()) {
            $registrations = $this->db->fetchAll(
                "SELECT id_event FROM inscriptions WHERE user_email = ? AND statut = 'confirmé'",
                [$_SESSION['user_email']]
            );
            $userRegistrations = array_column($registrations, 'id_event');
        }
        
        $this->render('events/index', [
            'events' => $events,
            'tags' => $this->tags,
            'lieux' => array_column($lieux, 'lieu'),
            'userRegistrations' => $userRegistrations,
            'filters' => [
                'search' => $search,
                'tag' => $tag,
                'lieu' => $lieu,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'prix_max' => $prixMax,
                'sort' => $_GET['sort'] ?? 'date'
            ],
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Détail d'un événement
     */
    public function show(string $id): void
    {
        $eventId = sanitizeInt($id);
        
        if (!$eventId) {
            $this->redirect('/evenements');
        }
        
        $event = $this->db->fetch(
            "SELECT e.*, u.user as owner_name,
             (SELECT COUNT(*) FROM inscriptions i WHERE i.id_event = e.id_event AND i.statut = 'confirmé') as nb_inscrits
             FROM event e 
             LEFT JOIN users u ON e.owner_email = u.email
             WHERE e.id_event = ? AND e.status = 'publié'",
            [$eventId]
        );
        
        if (!$event) {
            $this->redirect('/evenements');
        }
        
        // Vérifier si l'utilisateur est inscrit
        $isRegistered = false;
        if (Security::isLoggedIn()) {
            $registration = $this->db->fetch(
                "SELECT id_inscription FROM inscriptions 
                 WHERE user_email = ? AND id_event = ? AND statut = 'confirmé'",
                [$_SESSION['user_email'], $eventId]
            );
            $isRegistered = (bool)$registration;
        }
        
        $this->render('events/show', [
            'event' => $event,
            'isRegistered' => $isRegistered,
            'tags' => $this->tags,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Inscription à un événement
     */
    public function register(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        
        $eventId = sanitizeInt($_POST['id_event'] ?? 0);
        
        if (!$eventId) {
            $this->flash('error', 'Événement invalide.');
            $this->redirect('/evenements');
        }
        
        $userEmail = $_SESSION['user_email'];
        
        // Vérifier si déjà inscrit
        $existing = $this->db->fetch(
            "SELECT * FROM inscriptions WHERE user_email = ? AND id_event = ? AND statut = 'confirmé'",
            [$userEmail, $eventId]
        );
        
        if ($existing) {
            $this->flash('error', 'Vous êtes déjà inscrit à cet événement.');
            $this->redirect('/evenements');
            return;
        }
        
        // Vérifier la capacité
        $event = $this->db->fetch(
            "SELECT e.capacite, COUNT(i.id_inscription) as inscrits 
             FROM event e 
             LEFT JOIN inscriptions i ON e.id_event = i.id_event AND i.statut = 'confirmé'
             WHERE e.id_event = ?
             GROUP BY e.id_event",
            [$eventId]
        );
        
        if ($event && $event['inscrits'] >= $event['capacite']) {
            $this->flash('error', 'Désolé, cet événement est complet.');
            $this->redirect('/evenements');
            return;
        }
        
        // Réactiver ou créer l'inscription
        $reactivated = $this->db->execute(
            "UPDATE inscriptions SET statut = 'confirmé', date_inscription = NOW() 
             WHERE user_email = ? AND id_event = ?",
            [$userEmail, $eventId]
        );
        
        if ($reactivated === 0) {
            $this->db->execute(
                "INSERT INTO inscriptions (user_email, id_event, statut) VALUES (?, ?, 'confirmé')",
                [$userEmail, $eventId]
            );
        }
        
        $this->flash('success', 'Inscription réussie !');
        $this->redirect('/profil');
    }
    
    /**
     * Désinscription d'un événement
     */
    public function unregister(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        
        $inscriptionId = sanitizeInt($_POST['id_inscription'] ?? 0);
        
        if (!$inscriptionId) {
            $this->flash('error', 'Inscription invalide.');
            $this->redirect('/profil');
        }
        
        $this->db->execute(
            "UPDATE inscriptions SET statut = 'annulé' 
             WHERE id_inscription = ? AND user_email = ?",
            [$inscriptionId, $_SESSION['user_email']]
        );
        
        $this->flash('success', 'Inscription annulée.');
        $this->redirect('/profil');
    }
}
