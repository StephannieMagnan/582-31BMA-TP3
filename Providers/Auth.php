<?php

namespace App\Providers;

use App\Models\Privilege;

/**
 * Vérification de la session et des privilèges d'accès.
 *
 *     Auth::session();       // la personne doit être connectée
 *     Auth::privilege(1);    // la personne doit être administrateur
 */
class Auth
{
    /**
     * Indique si la personne est connectée.
     * L'empreinte (navigateur + adresse IP) doit correspondre à celle enregistrée à la connexion : une session volée sur un autre poste est refusée.
     */
    public static function estConnecte(): bool
    {
        return isset($_SESSION['fingerPrint'])
            && $_SESSION['fingerPrint'] == md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);
    }

    /**
     * Indique si la personne connectée est administrateur.
     */
    public static function estAdmin(): bool
    {
        return self::estConnecte() && $_SESSION['privilege_id'] == Privilege::ADMIN;
    }

    /**
     * Laisse passer seulement une personne connectée. Sinon, renvoie à la page de connexion.
     */
    public static function session(): bool
    {
        if (self::estConnecte()) {
            return true;
        }

        Session::ajouterMessage('erreur', 'Connectez-vous pour accéder à cette page.');
        View::redirect('/login');
    }

    /**
     * Laisse passer seulement une personne qui a le privilège demandé. Sinon, renvoie à l'accueil avec un message.
     */
    public static function privilege(int $id): bool
    {
        if (self::estConnecte() && $_SESSION['privilege_id'] == $id) {
            return true;
        }

        Session::ajouterMessage('erreur', 'Vous n\'avez pas accès à cette page.');
        View::redirect('/');
    }
}
