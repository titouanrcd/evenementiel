<?php
/**
 * ============================================================
 * CONTRÔLEUR HOME - NOVA Événements
 * ============================================================
 */

namespace App\Controllers;

use App\Models\Event;

class HomeController extends Controller
{
    private Event $eventModel;

    public function __construct()
    {
        parent::__construct();
        $this->eventModel = new Event();
    }

    /**
     * Page d'accueil
     */
    public function index(): void
    {
        // Récupérer quelques événements à venir pour la page d'accueil
        $events = $this->eventModel->findUpcoming(6);
        
        $this->render('home/index', [
            'events' => $events,
            'flash' => $this->getFlash()
        ]);
    }
}
