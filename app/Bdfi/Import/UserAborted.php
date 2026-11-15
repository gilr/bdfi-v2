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

/** L'opérateur a refusé une validation : rollback du livre en cours et arrêt de l'import. */
class UserAborted extends \RuntimeException {}
