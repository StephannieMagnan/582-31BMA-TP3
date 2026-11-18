# Nemesis : librairie de l'étrange (TP3)

Projet du cours 582-31VB-MA, Programmation Web avancée, Collège de Maisonneuve.
TP3 (Continuation du TP1+TP2) : comptes utilisateurs, connexion sécurisée, privilèges
(administrateur et client), journal de bord et téléversement des couvertures.

## Structure

| Dossier                 | Rôle                                                                  |
|-------------------------|-----------------------------------------------------------------------|
| `Models/`               | Modèles : classe `CRUD` (hérite de PDO) et un modèle par table         |
| `Controllers/`          | Contrôleurs : méthodes d'action appelées par les routes                |
| `views/`                | Vues Twig (`.php.twig`)                                                |
| `Routes/`               | Routeur (`Route.php`, enregistre aussi le journal) et routes (`web.php`) |
| `Providers/`            | `View` (Twig), `Auth` (session et privilèges), `Session`, `Validator`, `Televersement` |
| `uploads/couvertures/`  | Images de couverture téléversées                                       |
| `config/`               | Configuration de la base de données                                    |

## Accès

| Page                                   | Visiteur | Client | Admin |
|----------------------------------------|:--------:|:------:|:-----:|
| Accueil, catalogue, fiche d'un livre   | ✓        | ✓      | ✓     |
| Connexion, inscription                 | ✓        |        |       |
| Auteurs, éditeurs, catégories (lecture), mon compte |  | ✓ | ✓ |
| Ajout, modification, suppression, inventaire |    |        | ✓     |
| Utilisateurs, journal de bord          |          |        | ✓     |

Comptes de démonstration : `admin` / `Nemesis2026!` (administrateur) et `lecteur` / `Lecteur2026!` (client).

## Installation

1. Importer `librairie.sql` dans MySQL.
2. Copier `config/config.exemple.php` sous le nom `config/config.php` et entrer les accès à la base.
3. Installer les dépendances si `vendor/` est absent : `composer install`.
4. Le dossier `uploads/couvertures/` doit être accessible en écriture par PHP.
