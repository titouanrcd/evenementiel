<?php
/**
 * ============================================================
 * CONTRÔLEUR ORGANISATEUR - NOVA Événements
 * ============================================================
 */

namespace App\Controllers;

use App\Core\Security;
use App\Core\FileUpload;

class OrganizerController extends Controller
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
     * Dashboard organisateur
     */
    public function index(): void
    {
        $this->requireAnyRole(['organisateur', 'admin']);
        
        $userEmail = $_SESSION['user_email'];
        
        // Récupérer les événements de l'organisateur
        $events = $this->db->fetchAll(
            "SELECT e.*, 
             (SELECT COUNT(*) FROM inscriptions i WHERE i.id_event = e.id_event AND i.statut = 'confirmé') as nb_inscrits
             FROM event e 
             WHERE e.owner_email = ?
             ORDER BY e.event_date DESC",
            [$userEmail]
        );
        
        $this->render('organizer/index', [
            'events' => $events,
            'tags' => $this->tags,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Formulaire de création
     */
    public function create(): void
    {
        $this->requireAnyRole(['organisateur', 'admin']);
        
        $this->render('organizer/create', [
            'tags' => $this->tags,
            'errors' => [],
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Enregistrer un nouvel événement
     */
    public function store(): void
    {
        $this->requireAnyRole(['organisateur', 'admin']);
        $this->verifyCsrf();
        
        $errors = [];
        
        $name = sanitizeString($_POST['name'] ?? '', 255);
        $description = sanitizeString($_POST['description'] ?? '', 5000);
        $eventDate = sanitizeDate($_POST['event_date'] ?? '');
        $hour = sanitizeTime($_POST['hour'] ?? '') ?? '';
        $lieu = sanitizeString($_POST['lieu'] ?? '', 255);
        $capacite = sanitizeInt($_POST['capacite'] ?? 0, 1, 100000);
        $prix = sanitizeInt($_POST['prix'] ?? 0, 0, 10000) ?? 0;
        $tag = array_key_exists($_POST['tag'] ?? '', $this->tags) ? $_POST['tag'] : 'autre';
        $imageUrl = '';
        
        // Gestion de l'image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploader = new FileUpload(ROOT_PATH . '/uploads/');
            $uploadedPath = $uploader->upload($_FILES['image'], 'events');
            
            if ($uploadedPath) {
                $imageUrl = $uploadedPath;
            } else {
                $errors = array_merge($errors, $uploader->getErrors());
            }
        } elseif (!empty($_POST['image_url'])) {
            $imageUrl = filter_var($_POST['image_url'], FILTER_VALIDATE_URL) ? $_POST['image_url'] : '';
        }
        
        // Validation
        if (empty($name)) {
            $errors[] = "Le nom de l'événement est requis.";
        }
        if (!$eventDate) {
            $errors[] = "La date de l'événement est requise.";
        }
        if (empty($lieu)) {
            $errors[] = "Le lieu est requis.";
        }
        if (!$capacite) {
            $errors[] = "La capacité est requise.";
        }
        
        if (empty($errors)) {
            $this->db->execute(
                "INSERT INTO event (name, description, event_date, hour, lieu, capacite, prix, tag, image, owner_email, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en attente')",
                [$name, $description, $eventDate, $hour, $lieu, $capacite, $prix, $tag, $imageUrl, $_SESSION['user_email']]
            );
            
            $this->flash('success', 'Événement créé avec succès ! Il sera visible après validation.');
            $this->redirect('/organisateur');
        } else {
            $this->render('organizer/create', [
                'tags' => $this->tags,
                'errors' => $errors,
                'old' => $_POST
            ]);
        }
    }
    
    /**
     * Formulaire d'édition
     */
    public function edit(string $id): void
    {
        $this->requireAnyRole(['organisateur', 'admin']);
        
        $eventId = sanitizeInt($id);
        
        $event = $this->db->fetch(
            "SELECT * FROM event WHERE id_event = ? AND owner_email = ?",
            [$eventId, $_SESSION['user_email']]
        );
        
        if (!$event) {
            $this->flash('error', 'Événement non trouvé.');
            $this->redirect('/organisateur');
        }
        
        $this->render('organizer/edit', [
            'event' => $event,
            'tags' => $this->tags,
            'errors' => [],
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Mettre à jour un événement
     */
    public function update(string $id): void
    {
        $this->requireAnyRole(['organisateur', 'admin']);
        $this->verifyCsrf();
        
        $eventId = sanitizeInt($id);
        $errors = [];
        
        // Vérifier que l'événement appartient à l'utilisateur
        $currentEvent = $this->db->fetch(
            "SELECT * FROM event WHERE id_event = ? AND owner_email = ?",
            [$eventId, $_SESSION['user_email']]
        );
        
        if (!$currentEvent) {
            $this->flash('error', 'Événement non trouvé.');
            $this->redirect('/organisateur');
        }
        
        $name = sanitizeString($_POST['name'] ?? '', 255);
        $description = sanitizeString($_POST['description'] ?? '', 5000);
        $eventDate = sanitizeDate($_POST['event_date'] ?? '');
        $hour = sanitizeTime($_POST['hour'] ?? '') ?? '';
        $lieu = sanitizeString($_POST['lieu'] ?? '', 255);
        $capacite = sanitizeInt($_POST['capacite'] ?? 0, 1, 100000);
        $prix = sanitizeInt($_POST['prix'] ?? 0, 0, 10000) ?? 0;
        $tag = array_key_exists($_POST['tag'] ?? '', $this->tags) ? $_POST['tag'] : 'autre';
        $imageUrl = $currentEvent['image'];
        
        // Gestion de la nouvelle image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploader = new FileUpload(ROOT_PATH . '/uploads/');
            $uploadedPath = $uploader->upload($_FILES['image'], 'events');
            
            if ($uploadedPath) {
                $imageUrl = $uploadedPath;
            } else {
                $errors = array_merge($errors, $uploader->getErrors());
            }
        } elseif (!empty($_POST['image_url'])) {
            $imageUrl = filter_var($_POST['image_url'], FILTER_VALIDATE_URL) ? $_POST['image_url'] : $imageUrl;
        }
        
        if (empty($errors)) {
            $this->db->execute(
                "UPDATE event SET name = ?, description = ?, event_date = ?, hour = ?, lieu = ?, 
                 capacite = ?, prix = ?, tag = ?, image = ? 
                 WHERE id_event = ? AND owner_email = ?",
                [$name, $description, $eventDate, $hour, $lieu, $capacite, $prix, $tag, $imageUrl, 
                 $eventId, $_SESSION['user_email']]
            );
            
            $this->flash('success', 'Événement modifié avec succès !');
            $this->redirect('/organisateur');
        } else {
            $this->render('organizer/edit', [
                'event' => array_merge($currentEvent, $_POST),
                'tags' => $this->tags,
                'errors' => $errors
            ]);
        }
    }
    
    /**
     * Supprimer un événement
     */
    public function delete(string $id): void
    {
        $this->requireAnyRole(['organisateur', 'admin']);
        $this->verifyCsrf();
        
        $eventId = sanitizeInt($id);
        
        $this->db->execute(
            "DELETE FROM event WHERE id_event = ? AND owner_email = ?",
            [$eventId, $_SESSION['user_email']]
        );
        
        $this->flash('success', 'Événement supprimé.');
        $this->redirect('/organisateur');
    }
}
