<?php

namespace App\Bdfi\Import;

use Illuminate\Console\Command;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleInteractor implements ImportInteractor
{
    public function __construct(private Command $command) {}

    public function info(string $message): void { $this->command->info($message); }
    public function warn(string $message): void { $this->command->warn($message); }
    public function confirm(string $question): bool { return $this->command->confirm($question, true); }

    public function debug(string $message): void
    {
        $this->command->line($message, null, OutputInterface::VERBOSITY_VERBOSE);
    }
}