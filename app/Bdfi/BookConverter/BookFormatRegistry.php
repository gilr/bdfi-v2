<?php

namespace App\Bdfi\BookConverter;

/**
 * Seul endroit à modifier pour ajouter un format : ajouter la classe du handler dans HANDLERS.
 * Les handlers existants (BBCode, .col, CSV, Excel) peuvent simplement déléguer
 * à leurs méthodes extract()/convert() actuelles.
 */
final class BookFormatRegistry
{
    private const HANDLERS = [
        CsvConverter::class,
        ColConverter::class,
        JsonFormatHandler::class,   // JSON pivot direct (.to-add.json, export, proposition...)
        // ExcelFormatHandler::class,
        // BbcodeFormatHandler::class,
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
