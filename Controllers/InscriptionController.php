<?php

namespace App\Controllers;

use App\Models\Privilege;
use App\Models\User;
use App\Providers\Auth;
use App\Providers\Session;
use App\Providers\View;

/**
 * Inscription publique : un visiteur crée son compte client.
 * Un visiteur ne peut pas se donner lui-même les droits d'administrateur.
 */
class InscriptionController extends Controller
{
    /**
     * GET /inscription : formulaire d'inscription.
     */
    public function create(): void
    {
        if (Auth::estConnecte()) {
            View::redirect('/compte');
        }

        $this->afficherFormulaire([]);
    }

    /**
     * POST /inscription : valide, crée le compte client, puis renvoie à la connexion.
     */
    public function store(): void
    {
        $this->verifierJeton('/inscription');

        $donnees = $this->donneesFormulaire();
        $donnees['privilege_id'] = Privilege::CLIENT;

        $user = new User();
        $erreurs = $user->valider($donnees);

        if (!empty($erreurs)) {
            $this->afficherFormulaire($donnees, $erreurs);
            return;
        }

        $donnees['password'] = $user->hashPassword($donnees['password']);
        $user->insert($donnees);

        Session::ajouterMessage('succes', 'Votre compte est créé. Vous pouvez maintenant vous connecter.');
        View::redirect('/login');
    }

    private function afficherFormulaire(array $donnees, array $erreurs = []): void
    {
        unset($donnees['password']);

        View::render('auth/inscription', [
            'menu'    => 'inscription',
            'user'    => $donnees,
            'erreurs' => $erreurs,
        ]);
    }
}
