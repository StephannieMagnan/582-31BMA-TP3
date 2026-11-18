<?php

namespace App\Models;

use App\Models\CRUD;

/**
 * Modèle de la table journal : le journal de bord des visites. Une ligne est ajoutée à chaque page demandée au site.
 */
class Journal extends CRUD
{
    protected $table = 'journal';
    protected $primaryKey = 'id';
    protected $fillable = ['adresse_ip', 'utilisateur', 'methode', 'page'];

    /** Texte enregistré quand la personne n'est pas connectée. */
    public const VISITEUR = 'Visiteur';

    /**
     * Enregistre la visite de la page demandée. La date et l'heure sont ajoutées par la base.
     */
    public function enregistrerVisite($page)
    {
        $this->insert([
            'adresse_ip'  => $_SERVER['REMOTE_ADDR'] ?? 'inconnue',
            'utilisateur' => $_SESSION['username'] ?? self::VISITEUR,
            'methode'     => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'page'        => mb_substr($page, 0, 255),
        ]);
    }

    /**
     * Visites les plus récentes, filtrées au besoin par type de visiteur.
     */
    public function selectRecentes($type = 'tous', $limite = 200)
    {
        $sql = 'SELECT * FROM journal';

        if ($type === 'visiteurs') {
            $sql .= ' WHERE utilisateur = :visiteur';
        } elseif ($type === 'membres') {
            $sql .= ' WHERE utilisateur <> :visiteur';
        }

        $sql .= ' ORDER BY date_visite DESC, id DESC LIMIT ' . (int) $limite;

        $stmt = $this->prepare($sql);
        if ($type === 'visiteurs' || $type === 'membres') {
            $stmt->bindValue(':visiteur', self::VISITEUR);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Dernières visites d'un utilisateur connecté.
     */
    public function selectParUtilisateur($username, $limite = 10)
    {
        $sql = 'SELECT * FROM journal
                WHERE utilisateur = :utilisateur
                ORDER BY date_visite DESC, id DESC
                LIMIT ' . (int) $limite;

        $stmt = $this->prepare($sql);
        $stmt->bindValue(':utilisateur', $username);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Portrait du journal : nombre de visites, adresses IP différentes, visites d'aujourd'hui et visites de membres connectés.
     */
    public function statistiques()
    {
        $sql = 'SELECT COUNT(*) AS total,
                       COUNT(DISTINCT adresse_ip) AS adresses,
                       SUM(DATE(date_visite) = CURDATE()) AS aujourdhui,
                       SUM(utilisateur <> :visiteur) AS membres
                FROM journal';

        $stmt = $this->prepare($sql);
        $stmt->bindValue(':visiteur', self::VISITEUR);
        $stmt->execute();

        return $stmt->fetch();
    }
}
