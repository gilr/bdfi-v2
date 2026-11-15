<?php

namespace App\Bdfi\Import;

/**
 * Abstraction des interactions avec l'opérateur.
 * - Console : ConsoleInteractor
 * - Web admin : à implémenter plus tard
 */
interface ImportInteractor
{
    public function info(string $message): void;
    public function warn(string $message): void;

    /** Message de détail, affiché seulement en mode verbeux (-v en console) */
    public function debug(string $message): void;

    /** @return bool true = valider, false = refuser (=> rollback) */
    public function confirm(string $question): bool;
}