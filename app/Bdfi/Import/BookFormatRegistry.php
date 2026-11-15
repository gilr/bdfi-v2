<?php

namespace App\Bdfi\BookConverter;

/**
 * Seul endroit à modifier pour ajouter un format.
 * Les converters (AbstractBookConverter) implémentent BookFormatHandler,
 * avec une méthode statique extensions() dans chaque classe dérivée.
 */
final class BookFormatRegistry
{
    private const HANDLERS = [
        JsonFormatHandler::class,   // JSON déjà au format canonique
        CsvConverter::class,
        ColConverter::class,
    ];

    /** @var array<string, class-string<BookFormatHandler>> */
    private array $map = [];

    public function __construct()
    {
        foreach (self::HANDLERS as $class) {
            foreach ($class::extensions() as $ext) {
                $this->map[$ext] = $class;
            }
        }
    }

    public function for(string $extension): BookFormatHandler
    {
        $class = $this->map[strtolower($extension)]
            ?? throw new \InvalidArgumentException(
                "Extension \".{$extension}\" non gérée (extensions acceptées : " . implode(', ', $this->extensions()) . ')'
            );

        return app($class);
    }

    /** @return string[] */
    public function extensions(): array
    {
        return array_keys($this->map);
    }
}
