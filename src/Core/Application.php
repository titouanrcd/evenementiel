<?php
/**
 * ============================================================
 * APPLICATION - NOVA Événements
 * ============================================================
 * Classe principale de l'application
 * ============================================================
 */

namespace App\Core;

class Application
{
    private Security $security;
    private Router $router;
    private ?Database $db = null;
    
    public function __construct()
    {
        // Initialiser la sécurité
        $this->security = new Security();
        $this->security->init();
        
        // Initialiser le routeur
        $this->router = new Router();
        $this->registerRoutes();
    }
    
    /**
     * Obtenir l'instance de la base de données (lazy loading)
     */
    public function getDatabase(): Database
    {
        if ($this->db === null) {
            $this->db = Database::getInstance();
        }
        return $this->db;
    }
    
    /**
     * Obtenir l'instance de sécurité
     */
    public function getSecurity(): Security
    {
        return $this->security;
    }
    
    /**
     * Enregistrer les routes de l'application
     */
    private function registerRoutes(): void
    {
        // Pages publiques
        $this->router->get('/', 'HomeController@index');
        $this->router->get('/accueil', 'HomeController@index');
        $this->router->get('/evenements', 'EventController@index');
        $this->router->get('/evenement/{id}', 'EventController@show');
        $this->router->get('/connexion', 'AuthController@showLogin');
        $this->router->post('/connexion', 'AuthController@login');
        $this->router->post('/inscription', 'AuthController@register');
        $this->router->get('/deconnexion', 'AuthController@logout');
        
        // Pages protégées (utilisateur connecté)
        $this->router->get('/profil', 'ProfileController@index');
        $this->router->post('/profil/update', 'ProfileController@update');
        $this->router->post('/evenement/inscription', 'EventController@register');
        $this->router->post('/evenement/desinscription', 'EventController@unregister');
        
        // Pages organisateur
        $this->router->get('/organisateur', 'OrganizerController@index');
        $this->router->get('/organisateur/creer', 'OrganizerController@create');
        $this->router->post('/organisateur/creer', 'OrganizerController@store');
        $this->router->get('/organisateur/editer/{id}', 'OrganizerController@edit');
        $this->router->post('/organisateur/editer/{id}', 'OrganizerController@update');
        $this->router->post('/organisateur/supprimer/{id}', 'OrganizerController@delete');
        
        // Pages admin
        $this->router->get('/admin', 'AdminController@index');
        $this->router->get('/admin/utilisateurs', 'AdminController@users');
        $this->router->get('/admin/evenements', 'AdminController@events');
        $this->router->post('/admin/utilisateur/role', 'AdminController@changeRole');
        $this->router->post('/admin/utilisateur/supprimer', 'AdminController@deleteUser');
        $this->router->post('/admin/evenement/statut', 'AdminController@changeEventStatus');
        $this->router->post('/admin/evenement/supprimer', 'AdminController@deleteEvent');
        
        // API
        $this->router->get('/api/weather', 'ApiController@weather');
    }
    
    /**
     * Exécuter l'application
     */
    public function run(): void
    {
        try {
            $this->router->dispatch();
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * Gérer les erreurs
     */
    private function handleError(\Exception $e): void
    {
        // Logger l'erreur
        error_log(sprintf(
            "[%s] %s in %s:%d\n%s",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        ));
        
        // Afficher une page d'erreur générique
        if (ENVIRONMENT === 'production') {
            http_response_code(500);
            include ROOT_PATH . '/src/Views/errors/500.php';
        } else {
            throw $e;
        }
    }
}
