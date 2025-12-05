<?php
/**
 * ============================================================
 * CONTRÔLEUR DE BASE - NOVA Événements
 * ============================================================
 */

namespace App\Controllers;

use App\Core\Database;
use App\Core\Security;
use App\Core\Validator;

abstract class Controller
{
    protected Database $db;
    protected Validator $validator;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->validator = new Validator();
    }
    
    /**
     * Rendre une vue
     */
    protected function render(string $view, array $data = []): void
    {
        // Ajouter les données communes
        $data['isLoggedIn'] = Security::isLoggedIn();
        $data['userName'] = $_SESSION['user_name'] ?? '';
        $data['userRole'] = $_SESSION['user_role'] ?? 'user';
        $data['userEmail'] = $_SESSION['user_email'] ?? '';
        $data['nonce'] = Security::getNonce();
        
        view($view, $data);
    }
    
    /**
     * Rediriger
     */
    protected function redirect(string $url): void
    {
        redirect($url);
    }
    
    /**
     * Réponse JSON
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Vérifier le token CSRF
     */
    protected function verifyCsrf(): void
    {
        Security::requireCsrfToken();
    }
    
    /**
     * Exiger une connexion
     */
    protected function requireAuth(): void
    {
        Security::requireLogin('/connexion');
    }
    
    /**
     * Exiger un rôle
     */
    protected function requireRole(string $role): void
    {
        Security::requireRole($role, '/profil');
    }
    
    /**
     * Exiger l'un des rôles
     */
    protected function requireAnyRole(array $roles): void
    {
        Security::requireLogin('/connexion');
        if (!Security::hasAnyRole($roles)) {
            $this->redirect('/profil');
        }
    }
    
    /**
     * Ajouter un message flash
     */
    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    /**
     * Obtenir et supprimer le message flash
     */
    protected function getFlash(): ?array
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}
