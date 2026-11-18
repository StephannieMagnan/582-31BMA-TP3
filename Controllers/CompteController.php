<?php

namespace App\Controllers;

use App\Models\Journal;
use App\Models\User;
use App\Providers\Auth;
use App\Providers\View;

/**
 * Espace  Mon compte  : accessible à toute personne connectée.
 */
class CompteController extends Controller
{
    public function __construct()
    {
        Auth::session();
    }

    /**
     * GET /compte : informations du compte et dernières pages visitées.
     */
    public function index(): void
    {
        $user = new User();
        $compte = $user->selectId($_SESSION['user_id']);
        unset($compte['password']);

        $journal = new Journal();

        View::render('compte/index', [
            'menu'    => 'compte',
            'compte'  => $compte,
            'visites' => $journal->selectParUtilisateur($_SESSION['username']),
        ]);
    }
}
