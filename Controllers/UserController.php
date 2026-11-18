<?php

namespace App\Controllers;

use App\Models\Privilege;
use App\Models\User;
use App\Providers\Auth;
use App\Providers\Session;
use App\Providers\View;

/**
 * Gestion des utilisateurs : réservée à l'administrateur.
 */
class UserController extends Controller
{
    /**
     * Toutes les méthodes de ce contrôleur exigent une session d'administrateur.
     */
    public function __construct()
    {
        Auth::session();
        Auth::privilege(Privilege::ADMIN);
    }

    /**
     * GET /users : liste des utilisateurs et de leur privilège.
     */
    public function index(): void
    {
        $user = new User();

        View::render('user/index', [
            'menu'  => 'admin',
            'users' => $user->selectAvecPrivilege(),
        ]);
    }

    /**
     * GET /user/create : formulaire d'ajout d'un utilisateur.
     */
    public function create(): void
    {
        $this->afficherFormulaire(['privilege_id' => Privilege::CLIENT]);
    }

    /**
     * POST /user/create : valide le mot de passe, puis ajoute l'utilisateur.
     */
    public function store(): void
    {
        $this->verifierJeton('/user/create');

        $donnees = $this->donneesFormulaire();
        $user = new User();
        $erreurs = $user->valider($donnees);

        if (!empty($erreurs)) {
            $this->afficherFormulaire($donnees, $erreurs);
            return;
        }

        $donnees['password'] = $user->hashPassword($donnees['password']);
        $user->insert($donnees);

        Session::ajouterMessage('succes', 'Le compte de ' . trim($donnees['name']) . ' a été créé.');
        View::redirect('/users');
    }

    /**
     * POST /user/delete : supprime un utilisateur. L'administrateur ne peut pas supprimer son propre compte.
     */
    public function delete(): void
    {
        $this->verifierJeton('/users');

        $user = new User();
        $selectId = $user->selectId($this->idRecu());

        if (!$selectId) {
            Session::ajouterMessage('erreur', 'Cet utilisateur n\'existe pas ou a été supprimé.');
            View::redirect('/users');
        }

        if ($selectId['id'] == $_SESSION['user_id']) {
            Session::ajouterMessage('erreur', 'Vous ne pouvez pas supprimer votre propre compte.');
            View::redirect('/users');
        }

        $user->delete($selectId['id']);

        Session::ajouterMessage('succes', 'Le compte de ' . $selectId['name'] . ' a été supprimé.');
        View::redirect('/users');
    }

    private function afficherFormulaire(array $donnees, array $erreurs = []): void
    {
        unset($donnees['password']);
        $privilege = new Privilege();

        View::render('user/create', [
            'menu'       => 'admin',
            'user'       => $donnees,
            'erreurs'    => $erreurs,
            'privileges' => $privilege->select('id'),
        ]);
    }
}
