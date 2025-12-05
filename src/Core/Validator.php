<?php
/**
 * ============================================================
 * VALIDATEUR - NOVA Événements
 * ============================================================
 * Classe de validation des entrées utilisateur
 * ============================================================
 */

namespace App\Core;

class Validator
{
    private array $errors = [];
    private array $data = [];
    
    /**
     * Valider des données
     */
    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];
        $this->data = $data;
        
        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $rulesList = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            
            foreach ($rulesList as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }
        
        return empty($this->errors);
    }
    
    /**
     * Appliquer une règle de validation
     */
    private function applyRule(string $field, $value, string $rule): void
    {
        $parts = explode(':', $rule);
        $ruleName = $parts[0];
        $param = $parts[1] ?? null;
        
        switch ($ruleName) {
            case 'required':
                if ($value === null || $value === '') {
                    $this->addError($field, 'Ce champ est requis.');
                }
                break;
                
            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'L\'adresse email n\'est pas valide.');
                }
                break;
                
            case 'min':
                if ($value && strlen($value) < (int)$param) {
                    $this->addError($field, "Ce champ doit contenir au moins {$param} caractères.");
                }
                break;
                
            case 'max':
                if ($value && strlen($value) > (int)$param) {
                    $this->addError($field, "Ce champ ne doit pas dépasser {$param} caractères.");
                }
                break;
                
            case 'numeric':
                if ($value && !is_numeric($value)) {
                    $this->addError($field, 'Ce champ doit être un nombre.');
                }
                break;
                
            case 'int':
                if ($value && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, 'Ce champ doit être un entier.');
                }
                break;
                
            case 'date':
                if ($value) {
                    $d = \DateTime::createFromFormat('Y-m-d', $value);
                    if (!$d || $d->format('Y-m-d') !== $value) {
                        $this->addError($field, 'La date n\'est pas valide.');
                    }
                }
                break;
                
            case 'in':
                $allowed = explode(',', $param);
                if ($value && !in_array($value, $allowed, true)) {
                    $this->addError($field, 'La valeur sélectionnée n\'est pas valide.');
                }
                break;
                
            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($this->data[$confirmField] ?? null)) {
                    $this->addError($field, 'Les valeurs ne correspondent pas.');
                }
                break;
                
            case 'password':
                if ($value) {
                    $result = validatePassword($value);
                    if (!$result['valid']) {
                        foreach ($result['errors'] as $error) {
                            $this->addError($field, $error);
                        }
                    }
                }
                break;
                
            case 'phone':
                if ($value) {
                    $phone = preg_replace('/[^0-9+]/', '', $value);
                    if (!preg_match('/^(\+33|0)[1-9][0-9]{8}$/', $phone)) {
                        $this->addError($field, 'Le numéro de téléphone n\'est pas valide.');
                    }
                }
                break;
                
            case 'url':
                if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, 'L\'URL n\'est pas valide.');
                }
                break;
        }
    }
    
    /**
     * Ajouter une erreur
     */
    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
    
    /**
     * Obtenir toutes les erreurs
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Obtenir les erreurs aplaties
     */
    public function getErrorsFlat(): array
    {
        $flat = [];
        foreach ($this->errors as $fieldErrors) {
            $flat = array_merge($flat, $fieldErrors);
        }
        return $flat;
    }
    
    /**
     * Obtenir les erreurs d'un champ
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }
    
    /**
     * Vérifier si un champ a des erreurs
     */
    public function hasError(string $field): bool
    {
        return !empty($this->errors[$field]);
    }
}
