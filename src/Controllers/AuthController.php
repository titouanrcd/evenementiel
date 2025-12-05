<?php
/**
 * ============================================================
 * CONTRÔLEUR AUTH - NOVA Événements
 * ============================================================
 */

namespace App\Controllers;

use App\Core\Security;

class AuthController extends Controller
{
    /**
     * Afficher la page de connexion
     */
    public function showLogin(): void
    {
        // Rediriger si déjà connecté
        if (Security::isLoggedIn()) {
            $this->redirect('/profil');
        }
        
        $this->render('auth/login', [
            'errors' => [],
            'activeTab' => $_GET['tab'] ?? 'login',
            'flash' => $this->getFlash()
        ]);
    }
    
    /**
     * Traiter la connexion
     */
    public function login(): void
    {
        $this->verifyCsrf();
        
        $errors = [];
        $pdo = $this->db->getConnection();
        $clientIp = Security::getClientIp();
        
        // Vérifier si l'IP est bloquée
        if (Security::isIpBlocked($pdo, $clientIp)) {
            $errors[] = "Trop de tentatives de connexion. Veuillez réessayer dans 15 minutes.";
            $this->render('auth/login', [
                'errors' => $errors,
                'activeTab' => 'login'
            ]);
            return;
        }
        
        $identifier = sanitizeString($_POST['identifier'] ?? '', 255);
        $password = $_POST['password'] ?? '';
        
        if (empty($identifier) || empty($password)) {
            $errors[] = "Veuillez remplir tous les champs.";
        } else {
            $user = $this->db->fetch(
                "SELECT * FROM users WHERE email = ? OR user = ?",
                [$identifier, $identifier]
            );
            
            if ($user && verifyPassword($password, $user['password'])) {
                // Connexion réussie
                Security::regenerateSession();
                
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['user'];
                $_SESSION['user_role'] = $user['role'] ?? 'user';
                $_SESSION['user_id'] = $user['id'];
                
                // Nettoyer les tentatives
                Security::cleanOldAttempts($pdo);
                
                $this->flash('success', 'Connexion réussie !');
                $this->redirect('/');
            } else {
                // Échec de connexion
                Security::recordFailedAttempt($pdo, $clientIp);
                Security::logSecurityEvent('login_failed', 'Failed login attempt', [
                    'identifier' => $identifier
                ]);
                $errors[] = "Identifiants incorrects.";
            }
        }
        
        $this->render('auth/login', [
            'errors' => $errors,
            'activeTab' => 'login'
        ]);
    }
    
    /**
     * Traiter l'inscription
     */
    public function register(): void
    {
        $this->verifyCsrf();
        
        $errors = [];
        
        // Validation
        $user = sanitizeString($_POST['user'] ?? '', 100);
        $dateOfBirth = sanitizeDate($_POST['date_of_birth'] ?? '');
        $sexe = in_array($_POST['sexe'] ?? '', ['H', 'F', 'A']) ? $_POST['sexe'] : '';
        $number = sanitizePhone($_POST['number'] ?? '');
        $email = sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validations
        if (empty($user) || strlen($user) < 3) {
            $errors[] = "Le nom d'utilisateur doit faire au moins 3 caractères.";
        }
        
        if (!$email) {
            $errors[] = "L'email n'est pas valide.";
        }
        
        if (!$dateOfBirth) {
            $errors[] = "La date de naissance n'est pas valide.";
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
        
        $passwordCheck = validatePassword($password);
        if (!$passwordCheck['valid']) {
            $errors = array_merge($errors, $passwordCheck['errors']);
        }
        
        if (empty($errors)) {
            // Vérifier si l'email existe déjà
            $existing = $this->db->fetch(
                "SELECT email FROM users WHERE email = ?",
                [$email]
            );
            
            if ($existing) {
                $errors[] = "Cet email est déjà utilisé.";
            } else {
                // Créer l'utilisateur
                $hash = hashPassword($password);
                
                $this->db->execute(
                    "INSERT INTO users (user, email, date_of_birth, sexe, number, password, role) 
                     VALUES (?, ?, ?, ?, ?, ?, 'user')",
                    [$user, $email, $dateOfBirth, $sexe, $number ?: null, $hash]
                );
                
                // Connexion automatique
                Security::regenerateSession();
                $_SESSION['user_email'] = $email;
                $_SESSION['user_name'] = $user;
                $_SESSION['user_role'] = 'user';
                $_SESSION['user_id'] = $this->db->lastInsertId();
                
                $this->flash('success', 'Compte créé avec succès !');
                $this->redirect('/');
            }
        }
        
        $this->render('auth/login', [
            'errors' => $errors,
            'activeTab' => 'register',
            'old' => [
                'user' => $user,
                'date_of_birth' => $dateOfBirth,
                'sexe' => $sexe,
                'number' => $number,
                'email' => $email
            ]
        ]);
    }
    
    /**
     * Déconnexion
     */
    public function logout(): void
    {
        Security::logout();
        $this->flash('success', 'Vous avez été déconnecté.');
        $this->redirect('/');
    }
}
