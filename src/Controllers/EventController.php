<?php
/**
 * ============================================================
 * CONTRÔLEUR EVENT - NOVA Événements
 * ============================================================
 */

namespace App\Controllers;

use App\Core\Database;
use App\Core\Security;
use App\Models\Event;

class EventController extends Controller
{
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
        $this->eventModel = new Event();
    }
    
    /**
     * Liste des événements
     */
    public function index(): void
    {
        // Récupérer les filtres
        $filters = [
            'search' => sanitizeString($_GET['search'] ?? '', 100),
            'tag' => isset($_GET['tag']) && array_key_exists($_GET['tag'], $this->tags) ? $_GET['tag'] : '',
            'lieu' => sanitizeString($_GET['lieu'] ?? '', 255),
            'date_from' => sanitizeDate($_GET['date_from'] ?? ''),
            'date_to' => sanitizeDate($_GET['date_to'] ?? ''),
            'prix_max' => sanitizeInt($_GET['prix_max'] ?? 200, 0, 10000) ?? 200,
            'sort' => $_GET['sort'] ?? 'date'
        ];
        
        $events = $this->eventModel->findAllPublished($filters);
        
        // Récupérer les lieux pour le filtre
        $lieux = $this->eventModel->findDistinctLocations();
        
        // Vérifier les inscriptions de l'utilisateur
        $userRegistrations = [];
        if (Security::isLoggedIn()) {
            $userRegistrations = $this->eventModel->findUserRegistrations($_SESSION['user_email']);
        }
        
        $this->render('events/index', [
            'events' => $events,
            'tags' => $this->tags,
            'lieux' => array_column($lieux, 'lieu'),
            'userRegistrations' => $userRegistrations,
            'filters' => $filters,
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
        
        $event = $this->eventModel->findByIdWithDetails($eventId);
        
        if (!$event) {
            $this->redirect('/evenements');
        }
        
        // Vérifier si l'utilisateur est inscrit
        $isRegistered = false;
        if (Security::isLoggedIn()) {
            $isRegistered = $this->eventModel->isUserRegistered($_SESSION['user_email'], $eventId);
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
        if ($this->eventModel->isUserRegistered($userEmail, $eventId)) {
            $this->flash('error', 'Vous êtes déjà inscrit à cet événement.');
            $this->redirect('/evenements');
            return;
        }
        
        // Vérifier la capacité
        $event = $this->eventModel->checkCapacity($eventId);
        
        if ($event && $event['inscrits'] >= $event['capacite']) {
            $this->flash('error', 'Désolé, cet événement est complet.');
            $this->redirect('/evenements');
            return;
        }
        
        // Inscrire l'utilisateur
        $this->eventModel->registerUser($userEmail, $eventId);
        
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
        
        $this->eventModel->unregisterUser($inscriptionId, $_SESSION['user_email']);
        
        $this->flash('success', 'Inscription annulée.');
        $this->redirect('/profil');
    }
}
