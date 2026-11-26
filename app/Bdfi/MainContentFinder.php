<?php

namespace App\Bdfi;

use App\Bdfi\Import\BookLookup;
use App\Enums\PublicationContent;
use App\Enums\TitleType;
use App\Enums\TitleVariantType;
use App\Models\Title;
use Illuminate\Support\Collection;

/**
 * Contenu principal d'une publication (l'équivalent du titre de l'ouvrage, premier élément de son sommaire) :
 * un texte (table titles) existant et identique, ou à créer avec la publication.
 * Seuls sont proposés les textes de MÊME TITRE et de MÊMES AUTEURS (ensemble exact), à charge pour
 * l'utilisateur de choisir celui qui est vraiment identique (autre traduction, autre version : autre texte).
 */
final class MainContentFinder
{
    /** @return int[] ids triés, uniques */
    private static function ids(array $authorIds): array
    {
        return collect($authorIds)->filter(fn ($id) => filled($id))->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();
    }

    /**
     * Textes existants candidats, du plus ancien au plus récent (date de première parution française).
     * Sans auteur, aucun rapprochement n'est possible (comme pour l'import).
     *
     * @return Collection<int, Title>
     */
    public static function candidates(string $name, array $authorIds, ?string $publicationType = null): Collection
    {
        $name = BookLookup::norm($name);
        $expected = self::ids($authorIds);

        if ($name === '' || $expected === []) {
            return collect();
        }

        // Type de texte compatible avec le type de la publication (ex. pas de roman pour une fiction courte)
        // (sauf les extraits, quel que soit leur type, pour les publications qui les admettent : fiction courte)
        $allowed = self::allowedTypes($publicationType);
        $extracts = PublicationContent::tryFrom((string) $publicationType)?->allowsExtract() ?? false;

        return Title::where('name', $name)
            ->when($allowed !== null, fn ($q) => $q->where(function ($q) use ($allowed, $extracts) {
                $q->whereIn('type', array_map(fn ($t) => $t->value, $allowed));
                if ($extracts) {
                    $q->orWhere('variant_type', TitleVariantType::EXTRAIT->value);
                }
            }))
            ->whereHas('authors', fn ($q) => $q->whereIn('authors.id', $expected))
            ->with(['authors:id,name,first_name', 'publications' => fn ($q) => $q->with('publisher:id,name')->limit(4)])
            ->get()
            ->filter(fn (Title $t) => $t->authors->pluck('id')->sort()->values()->all() === $expected)
            ->sortBy(fn (Title $t) => (string) $t->copyright_fr)
            ->values();
    }

    /** Le texte choisi est-il bien de même titre et de mêmes auteurs ? (contrôle serveur du choix du formulaire) */
    public static function isIdentical(Title $title, string $name, array $authorIds, ?string $publicationType = null): bool
    {
        return self::candidates($name, $authorIds, $publicationType)->contains(fn (Title $t) => $t->id === $title->id);
    }

    /**
     * Types de texte admis pour le contenu principal d'une publication de ce type (PublicationContent::mainContentTypes),
     * null si le type de publication est inconnu (pas de restriction).
     *
     * @return TitleType[]|null
     */
    public static function allowedTypes(?string $publicationType): ?array
    {
        return PublicationContent::tryFrom((string) $publicationType)?->mainContentTypes();
    }
}
