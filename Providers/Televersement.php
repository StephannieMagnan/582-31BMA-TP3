<?php

namespace App\Providers;

/**
 * Téléversement des images de couverture des livres.
 *
 * Les images sont enregistrées dans le dossier uploads/couvertures, sous un
 * nom généré au hasard. Seul ce nom est enregistré dans la base de données.
 */
class Televersement
{
    /** Dossier des couvertures, à partir de la racine du projet. */
    public const DOSSIER = 'uploads/couvertures';

    /** Taille maximale d'une image : 2 Mo. */
    public const TAILLE_MAX = 2 * 1024 * 1024;

    /** Types d'images acceptés et extension utilisée pour chacun. */
    private const TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    /**
     * Indique si un fichier a été choisi dans le champ du formulaire.
     */
    public static function fichierRecu(?array $fichier): bool
    {
        return $fichier !== null && ($fichier['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Vérifie l'image reçue. Retourne un message d'erreur, ou null si elle est valide.
     */
    public static function valider(array $fichier): ?string
    {
        if ($fichier['error'] === UPLOAD_ERR_INI_SIZE || $fichier['error'] === UPLOAD_ERR_FORM_SIZE) {
            return 'L\'image doit peser 2 Mo ou moins.';
        }

        if ($fichier['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($fichier['tmp_name'])) {
            return 'L\'image n\'a pas pu être reçue. Réessayez.';
        }

        if ($fichier['size'] > self::TAILLE_MAX) {
            return 'L\'image doit peser 2 Mo ou moins.';
        }

        // Le type est lu dans le contenu du fichier, pas dans son nom
        $infos = @getimagesize($fichier['tmp_name']);

        if ($infos === false || !isset(self::TYPES[$infos['mime']])) {
            return 'L\'image doit être au format JPG, PNG ou WebP.';
        }

        return null;
    }

    /**
     * Déplace l'image validée dans le dossier des couvertures. Retourne le nom du fichier enregistré.
     */
    public static function enregistrer(array $fichier): string
    {
        $infos = getimagesize($fichier['tmp_name']);
        $nom = 'livre-' . bin2hex(random_bytes(8)) . '.' . self::TYPES[$infos['mime']];

        $dossier = self::cheminDossier();
        if (!is_dir($dossier)) {
            mkdir($dossier, 0755, true);
        }

        if (!move_uploaded_file($fichier['tmp_name'], $dossier . '/' . $nom)) {
            throw new \RuntimeException('Impossible d\'enregistrer l\'image dans ' . self::DOSSIER . '.');
        }

        return $nom;
    }

    /**
     * Supprime une image du dossier des couvertures, si elle existe.
     */
    public static function supprimer(?string $nom): void
    {
        if ($nom === null || $nom === '') {
            return;
        }

        // basename() empêche de sortir du dossier (ex. : ../../index.php)
        $chemin = self::cheminDossier() . '/' . basename($nom);

        if (is_file($chemin)) {
            unlink($chemin);
        }
    }

    /**
     * Chemin complet du dossier des couvertures sur le serveur.
     */
    private static function cheminDossier(): string
    {
        return dirname(__DIR__) . '/' . self::DOSSIER;
    }
}
