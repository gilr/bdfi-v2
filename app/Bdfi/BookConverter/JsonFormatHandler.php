<?php

namespace App\Bdfi\BookConverter;

final class JsonFormatHandler implements BookFormatHandler
{
    public static function extensions(): array
    {
        return ['json'];
    }

    public function import(string $path, array $options = []): array
    {
        $data = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        if (!is_array($data) || !array_is_list($data)) {
            throw new \InvalidArgumentException('Le JSON doit contenir une liste de livres.');
        }

        return $data;
    }

    public function export(array $books): string
    {
        return json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
