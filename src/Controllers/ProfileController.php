<?php
/**
 * ============================================================
 * CONTRÔLEUR PROFILE - NOVA Événements
 * ============================================================
 */

namespace App\Controllers;

use App\Core\Security;

class ProfileController extends Controller
{
    /**
     * Page de profil
     */
    public function index(): void
    {
        $this->requireAuth();
        
        $userEmail = $_SESSION['user_email'];
        
        // Récupérer les infos utilisateur
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE email = ?",
            [$userEmail]
        );
        
        if (!$user) {
            Security::logout();
            $this->redirect('/connexion');
        }
        
        // Récupérer les inscriptions
        $inscriptions = $this->db->fetchAll(
            "SELECT i.*, e.name, e.event_date, e.hour, e.lieu, e.description, e.prix, e.capacite, e.status, e.image
             FROM inscriptions i
             JOIN event e ON i.id_event = e.id_event
             WHERE i.user_email = ?
             ORDER BY e.event_date DESC",
            [$userEmail]
        );
        
        $this->render('profile/index', [
            'user' => $user,
            'inscriptions' => $inscriptions,
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Mettre à jour le profil
     */
    public function update(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        
        $userEmail = $_SESSION['user_email'];
        
        $newUser = sanitizeString($_POST['user'] ?? '', 100);
        $newNumber = sanitizePhone($_POST['number'] ?? '');
        
        if (empty($newUser) || strlen($newUser) < 3) {
            $this->flash('error', "Le nom d'utilisateur doit faire au moins 3 caractères.");
            $this->redirect('/profil');
            return;
        }
        
        $this->db->execute(
            "UPDATE users SET user = ?, number = ? WHERE email = ?",
            [$newUser, $newNumber, $userEmail]
        );
        
        $_SESSION['user_name'] = $newUser;
        
        $this->flash('success', 'Profil mis à jour avec succès !');
        $this->redirect('/profil');
    }
}
