<?php

namespace App\Bdfi\Import;

/**
 * Interactions de l'import en mode web : les messages sont enregistrés (journal affiché après coup),
 * aucune question n'est posée (pas de mode pas-à-pas ni livre par livre en web : confirm() répond oui).
 */
final class WebInteractor implements ImportInteractor
{
    /** @var array<int, array{level: string, message: string}> */
    private array $log = [];

    public function info(string $message): void { $this->log[] = ['level' => 'info', 'message' => $message]; }
    public function warn(string $message): void { $this->log[] = ['level' => 'warn', 'message' => $message]; }
    public function debug(string $message): void { $this->log[] = ['level' => 'debug', 'message' => $message]; }
    public function confirm(string $question): bool { return true; }

    /** @return array<int, array{level: string, message: string}> */
    public function log(): array
    {
        return $this->log;
    }
}
