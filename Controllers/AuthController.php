<?php

namespace App\Controllers;

use App\Models\Privilege;
use App\Models\User;
use App\Providers\Auth;
use App\Providers\Session;
use App\Providers\Validator;
use App\Providers\View;

/**
 * Connexion et déconnexion des utilisateurs.
 */
class AuthController extends Controller
{
    /**
     * GET /login : formulaire de connexion.
     */
    public function index(): void
    {
        if (Auth::estConnecte()) {
            View::redirect('/');
        }

        View::render('auth/index', ['menu' => 'login']);
    }

    /**
     * POST /login : vérifie les identifiants, puis ouvre la session.
     */
    public function store(): void
    {
        $this->verifierJeton('/login');

        $donnees = $this->donneesFormulaire();

        $validateur = new Validator($donnees);
        $validateur->champ('username', 'Le nom d\'utilisateur')->requis()->max(50);
        $validateur->champ('password', 'Le mot de passe')->requis()->max(72);

        if (!$validateur->estValide()) {
            $this->afficherFormulaire($donnees, $validateur->erreurs());
            return;
        }

        $user = new User();

        if (!$user->checkUser(trim($donnees['username']), $donnees['password'])) {
            // Message général : on ne révèle pas si c'est le nom ou le mot de passe qui est faux
            $this->afficherFormulaire($donnees, [
                'message' => 'Nom d\'utilisateur ou mot de passe incorrect.',
            ]);
            return;
        }

        Session::ajouterMessage('succes', 'Bienvenue, ' . $_SESSION['user_name'] . '.');

        if ($_SESSION['privilege_id'] == Privilege::ADMIN) {
            View::redirect('/admin');
        }

        View::redirect('/livres');
    }

    /**
     * GET /logout : ferme la session.
     */
    public function delete(): void
    {
        // Vide la session, supprime son témoin dans le navigateur, puis la détruit
        $_SESSION = [];

        $parametres = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 3600,
            'path'     => $parametres['path'],
            'secure'   => $parametres['secure'],
            'httponly' => $parametres['httponly'],
            'samesite' => $parametres['samesite'],
        ]);

        session_destroy();

        // Nouvelle session vide, pour pouvoir afficher le message
        session_start();
        Session::ajouterMessage('succes', 'Vous êtes déconnecté.');
        View::redirect('/login');
    }

    /**
     * Affiche le formulaire de connexion. Le mot de passe n'est jamais réaffiché.
     */
    private function afficherFormulaire(array $donnees, array $erreurs): void
    {
        unset($donnees['password']);

        View::render('auth/index', [
            'menu'    => 'login',
            'user'    => $donnees,
            'erreurs' => $erreurs,
        ]);
    }
}
