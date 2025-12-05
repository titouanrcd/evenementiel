<?php
/**
 * ============================================================
 * CONTRÔLEUR HOME - NOVA Événements
 * ============================================================
 */

namespace App\Controllers;

class HomeController extends Controller
{
    /**
     * Page d'accueil
     */
    public function index(): void
    {
        // Récupérer quelques événements à venir pour la page d'accueil
        $events = $this->db->fetchAll(
            "SELECT * FROM event 
             WHERE status = 'publié' AND event_date >= CURDATE() 
             ORDER BY event_date ASC 
             LIMIT 6"
        );
        
        $this->render('home/index', [
            'events' => $events,
            'flash' => $this->getFlash()
        ]);
    }
}
