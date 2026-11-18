<?php

namespace App\Controllers;

use App\Models\Auteur;
use App\Models\Journal;
use App\Models\Livre;
use App\Models\Privilege;
use App\Models\User;
use App\Providers\Auth;
use App\Providers\View;

/**
 * Tableau de bord de l'interface administrateur.
 */
class AdminController extends Controller
{
    public function __construct()
    {
        Auth::session();
        Auth::privilege(Privilege::ADMIN);
    }

    /**
     * GET /admin : accès rapide à toutes les fonctions de gestion.
     */
    public function index(): void
    {
        $livre = new Livre();
        $auteur = new Auteur();
        $user = new User();
        $journal = new Journal();

        View::render('admin/index', [
            'menu'          => 'admin',
            'nombreLivres'  => $livre->compter(),
            'nombreAuteurs' => $auteur->compter(),
            'nombreUsers'   => $user->compter(),
            'statistiques'  => $journal->statistiques(),
            'stockFaible'   => count($livre->selectStockFaible()),
        ]);
    }
}
