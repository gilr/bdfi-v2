<?php

namespace App\Transformers;
use Illuminate\Http\UploadedFile;

interface FormatTransformer
{
    public function toArray(array|string|UploadedFile $input): array;

    public function fromArray(array $data): string|array;
}