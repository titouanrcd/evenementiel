<?php
/**
 * ============================================================
 * UPLOAD SÉCURISÉ - NOVA Événements
 * ============================================================
 * Gestion sécurisée des uploads de fichiers
 * ============================================================
 */

namespace App\Core;

class FileUpload
{
    private array $allowedMimeTypes;
    private int $maxSize;
    private string $uploadDir;
    private array $errors = [];
    
    /**
     * Types MIME autorisés par défaut (images seulement)
     */
    private const DEFAULT_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    
    /**
     * Constructeur
     */
    public function __construct(
        string $uploadDir = null,
        array $allowedMimeTypes = null,
        int $maxSize = null
    ) {
        $this->uploadDir = $uploadDir ?? ROOT_PATH . '/uploads/';
        $this->allowedMimeTypes = $allowedMimeTypes ?? self::DEFAULT_MIME_TYPES;
        $this->maxSize = $maxSize ?? UPLOAD_MAX_SIZE;
    }
    
    /**
     * Traiter un upload de fichier
     */
    public function upload(array $file, string $subDir = ''): ?string
    {
        $this->errors = [];
        
        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadError($file['error']);
            return null;
        }
        
        // Vérifier la taille
        if ($file['size'] > $this->maxSize) {
            $this->errors[] = sprintf(
                'Le fichier est trop volumineux (max: %d Mo).',
                $this->maxSize / 1024 / 1024
            );
            return null;
        }
        
        // Vérifier le type MIME réel (pas l'extension!)
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if (!array_key_exists($mimeType, $this->allowedMimeTypes)) {
            $this->errors[] = 'Type de fichier non autorisé.';
            Security::logSecurityEvent('upload_blocked', 'Unauthorized MIME type', [
                'mime' => $mimeType,
                'filename' => $file['name']
            ]);
            return null;
        }
        
        // Vérification supplémentaire pour les images
        if (strpos($mimeType, 'image/') === 0) {
            if (!$this->validateImage($file['tmp_name'])) {
                $this->errors[] = 'Le fichier image est invalide ou corrompu.';
                return null;
            }
        }
        
        // Obtenir l'extension correcte basée sur le MIME type
        $extension = $this->allowedMimeTypes[$mimeType];
        
        // Générer un nom de fichier sécurisé (aléatoire)
        $newFilename = bin2hex(random_bytes(16)) . '.' . $extension;
        
        // Préparer le répertoire de destination
        $uploadPath = rtrim($this->uploadDir, '/') . '/';
        if ($subDir) {
            $uploadPath .= trim($subDir, '/') . '/';
        }
        
        // Créer le dossier si nécessaire
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                $this->errors[] = 'Impossible de créer le répertoire d\'upload.';
                return null;
            }
        }
        
        // Vérifier que le répertoire est bien dans uploads
        $realUploadDir = realpath($this->uploadDir);
        $realTargetDir = realpath($uploadPath);
        if ($realTargetDir === false || strpos($realTargetDir, $realUploadDir) !== 0) {
            $this->errors[] = 'Chemin d\'upload invalide.';
            Security::logSecurityEvent('upload_traversal', 'Path traversal attempt', [
                'target' => $uploadPath
            ]);
            return null;
        }
        
        $fullPath = $uploadPath . $newFilename;
        
        // Déplacer le fichier
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            $this->errors[] = 'Impossible de déplacer le fichier uploadé.';
            return null;
        }
        
        // Définir des permissions sécurisées (lecture seule)
        chmod($fullPath, 0644);
        
        // Retourner le chemin relatif
        return str_replace(ROOT_PATH . '/', '', $fullPath);
    }
    
    /**
     * Valider une image
     */
    private function validateImage(string $filepath): bool
    {
        // Vérifier que c'est vraiment une image
        $imageInfo = @getimagesize($filepath);
        if ($imageInfo === false) {
            return false;
        }
        
        // Vérifier les dimensions (protection contre les bombes décompression)
        if ($imageInfo[0] > 5000 || $imageInfo[1] > 5000) {
            return false;
        }
        
        // Vérifier que le type d'image est supporté
        $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
        if (!in_array($imageInfo[2], $allowedTypes, true)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Obtenir le message d'erreur d'upload
     */
    private function getUploadError(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Le fichier est trop volumineux.',
            UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement téléchargé.',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été téléchargé.',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant.',
            UPLOAD_ERR_CANT_WRITE => 'Échec de l\'écriture du fichier.',
            UPLOAD_ERR_EXTENSION => 'Upload bloqué par une extension PHP.',
            default => 'Erreur lors de l\'upload du fichier.',
        };
    }
    
    /**
     * Obtenir les erreurs
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Vérifier si des erreurs sont survenues
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
    
    /**
     * Supprimer un fichier uploadé
     */
    public function delete(string $relativePath): bool
    {
        $fullPath = ROOT_PATH . '/' . ltrim($relativePath, '/');
        
        // Vérifier que le fichier est bien dans uploads
        $realUploadDir = realpath($this->uploadDir);
        $realFilePath = realpath(dirname($fullPath));
        
        if ($realFilePath === false || strpos($realFilePath, $realUploadDir) !== 0) {
            Security::logSecurityEvent('delete_traversal', 'Path traversal attempt', [
                'path' => $relativePath
            ]);
            return false;
        }
        
        if (file_exists($fullPath) && is_file($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
}
