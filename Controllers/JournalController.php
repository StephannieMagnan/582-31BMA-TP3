<?php

namespace App\Controllers;

use App\Models\Journal;
use App\Models\Privilege;
use App\Providers\Auth;
use App\Providers\View;

/**
 * Consultation du journal de bord : réservée à l'administrateur.
 */
class JournalController extends Controller
{
    public function __construct()
    {
        Auth::session();
        Auth::privilege(Privilege::ADMIN);
    }

    /**
     * GET /journal : dernières visites, filtrées par type de visiteur.
     */
    public function index(): void
    {
        $type = $_GET['type'] ?? 'tous';
        if (!in_array($type, ['tous', 'visiteurs', 'membres'], true)) {
            $type = 'tous';
        }

        $journal = new Journal();

        View::render('journal/index', [
            'menu'         => 'journal',
            'visites'      => $journal->selectRecentes($type),
            'statistiques' => $journal->statistiques(),
            'type'         => $type,
        ]);
    }
}
