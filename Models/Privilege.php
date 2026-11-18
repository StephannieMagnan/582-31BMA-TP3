<?php

namespace App\Models;

use App\Models\CRUD;

/**
 * Modèle de la table privilege.
 * 1 = Admin, 2 = Client
 */
class Privilege extends CRUD
{
    protected $table = 'privilege';
    protected $primaryKey = 'id';

    /** Identifiant du privilège administrateur. */
    public const ADMIN = 1;

    /** Identifiant du privilège client. */
    public const CLIENT = 2;
}
