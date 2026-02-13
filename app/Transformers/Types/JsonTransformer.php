<?php

namespace App\Transformers\Types;

use App\Transformers\FormatTransformer;
use Illuminate\Http\UploadedFile;

class JsonTransformer {

    /**
     *   Transformation vers le format pivot
    **/
    public function toArray(array|string|UploadedFile $input): array
    {
        $data = json_decode($json, true);
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("JSON invalide : " . json_last_error_msg());
        }
        return $data;
    }

    /**
     *   Transformation depuis le format pivot
    **/
    public function fromArray(array $data): string {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
