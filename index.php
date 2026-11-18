<?php

/**
 * Point d'entrée unique de l'application.
 *
 * Grâce au fichier .htaccess, toutes les requêtes passent par ce fichier,
 * qui charge la configuration, puis transmet l'adresse demandée au routeur.
 */

use App\Providers\View;
use App\Routes\Route;

/**
 * Session sécurisée : le témoin de session est inaccessible au JavaScript, n'est pas envoyé par d'autres sites,
 * passe seulement en HTTPS quand le site l'utilise, et PHP refuse un identifiant de session qu'il n'a pas créé lui-même (use_strict_mode).
 */
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Chemin de base du site. Sert à construire toutes les adresses.
 */
define('BASE', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/'));

/**
 * Toute exception non attrapée affiche la page d'erreur au lieu d'une page blanche.
 */
set_exception_handler(function (Throwable $exception): void {
    error_log($exception->getMessage());
    http_response_code(500);

    View::render('erreur', [
        'titre'   => 'La page n\'a pas pu être chargée',
        'message' => 'Vérifiez la connexion à la base de données dans config/config.php, puis réessayez.',
        'detail'  => MODE_DEBUG ? $exception->getMessage() : null,
    ]);
});

require_once __DIR__ . '/Routes/web.php';

Route::dispatch();
