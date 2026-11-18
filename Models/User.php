<?php

namespace App\Models;

use App\Models\CRUD;
use App\Providers\Validator;

/**
 * Modèle de la table user.
 * Chaque utilisateur a un privilège (clé étrangère privilege_id).
 */
class User extends CRUD
{
    protected $table = 'user';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'username', 'password', 'email', 'privilege_id'];

    /**
     * Hache le mot de passe avant de l'enregistrer.
     */
    public function hashPassword($password, $cost = 10)
    {
        $options = [
            'cost' => $cost
        ];

        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    /**
     * Vérifie le nom d'utilisateur et le mot de passe. Si tout est correct, ouvre la session de l'utilisateur et retourne true.
     */
    public function checkUser($username, $password)
    {
        $user = $this->unique('username', $username);

        if ($user && password_verify($password, $user['password'])) {
            // Nouvel identifiant de session : empêche la fixation de session
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['privilege_id'] = $user['privilege_id'];
            $_SESSION['fingerPrint'] = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR']);

            return true;
        }

        return false;
    }

    /**
     * Utilisateurs avec le nom de leur privilège, triés par nom.
     */
    public function selectAvecPrivilege()
    {
        $sql = 'SELECT u.id, u.name, u.username, u.email, u.privilege_id, u.created_at,
                       p.privilege
                FROM user u
                JOIN privilege p ON p.id = u.privilege_id
                ORDER BY u.name';

        return $this->query($sql)->fetchAll();
    }

    /**
     * Règles de validation du formulaire d'un utilisateur. Utilisées par l'inscription d'un client et par l'ajout d'un utilisateur (admin).
     */
    public function valider(array $donnees)
    {
        $privilege = new Privilege();
        $validateur = new Validator($donnees);

        $validateur->champ('name', 'Le nom')->requis()->min(2)->max(50);

        $validateur->champ('username', 'Le nom d\'utilisateur')
            ->requis()
            ->min(3)
            ->max(50)
            ->regle(
                fn ($username) => preg_match('/^[a-zA-Z0-9._-]+$/', $username) === 1,
                'Le nom d\'utilisateur peut contenir seulement des lettres sans accents, des chiffres, un point, un tiret ou un trait de soulignement.'
            )
            ->unique('User');

        $validateur->champ('email', 'Le courriel')->requis()->email()->max(100)->unique('User');
        $validateur->champ('password', 'Le mot de passe')->requis()->min(8)->max(72);

        $validateur->champ('privilege_id', 'Le privilège')
            ->requis('Choisissez un privilège dans la liste.')
            ->regle(fn ($id) => $privilege->selectId($id) !== false, 'Choisissez un privilège dans la liste.');

        return $validateur->erreurs();
    }
}
