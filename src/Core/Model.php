<?php
/**
 * ============================================================
 * MODÈLE DE BASE - NOVA Événements
 * ============================================================
 */

namespace App\Core;

abstract class Model
{
    protected Database $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
}
