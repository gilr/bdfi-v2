<?php

namespace App\Transformers;

use InvalidArgumentException;

class FormatTransformerManager
{
    protected array $transformers = [];

    public function register(string $key, FormatTransformer $transformer): void
    {
        $this->transformers[$key] = $transformer;
    }

    public function get(string $key): FormatTransformer
    {
        if (!isset($this->transformers[$key])) {
            throw new InvalidArgumentException("Transformer '$key' non enregistré.");
        }

        return $this->transformers[$key];
    }

    public function availableFormats(): array
    {
        return array_keys($this->transformers);
    }
}
