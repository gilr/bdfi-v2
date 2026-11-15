<?php

/*

 -- Fichiers utiles à compléter et faire évoluer en fonction des besoins
 ImportBooks.php        : commande console d'import
 BookFileConverter.php  : Conversion d'un fichier d'entrée en JSON "livres"
 BookImporter.php       : logique d'import en base

 -- Fichiers outils définitifs
 Exceptions.hph         : Exceptions spécifiques
 ImportInteractor.php   : Abstraction des interactions avec l'opérateur
 ConsoleInteractor.php  : interaction dans le cas commande console
 ImportOptions.php      : Définitions des options d'import
*/

namespace App\Bdfi\Import;

/** Ce livre est ignoré (ex : éditeur introuvable). L'import continue avec le suivant. */
class SkipBook extends \RuntimeException {}
