<?php
/**
 * ============================================================
 * CONTRÔLEUR PROFILE - NOVA Événements
 * ============================================================
 */

namespace App\Controllers;

use App\Core\Security;
use App\Models\User;

class ProfileController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Page de profil
     */
    public function index(): void
    {
        $this->requireAuth();
        
        $userEmail = $_SESSION['user_email'];
        
        // Récupérer les infos utilisateur
        $user = $this->userModel->findByEmail($userEmail);
        
        if (!$user) {
            Security::logout();
            $this->redirect('/connexion');
        }
        
        // Récupérer les inscriptions
        $inscriptions = $this->userModel->getInscriptions($userEmail);
        
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
        
        $this->userModel->update($userEmail, [
            'user' => $newUser,
            'number' => $newNumber
        ]);
        
        $_SESSION['user_name'] = $newUser;
        
        $this->flash('success', 'Profil mis à jour avec succès !');
        $this->redirect('/profil');
    }
}
