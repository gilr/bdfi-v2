<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum AuthorPublicationRole: string implements HasLabel {
    case AUTHOR = 'author';
    case EDITOR = 'editor';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::AUTHOR => 'Auteur',
            self::EDITOR => 'Direction d\'ouvrage',
        };
    }

    public function getLabelPlural(): string
    {
        return match($this) {
            self::AUTHOR => 'Auteurs',
            self::EDITOR => 'Direction d\'ouvrage',
        };
    }
}