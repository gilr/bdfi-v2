<?php

namespace App\Bdfi\Export;

use App\Bdfi\Import\SigleAliases;
use App\Models\Collection;
use App\Models\Publication;
use App\Models\Publisher;
use App\Models\Title;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;

/**
 * Export base => JSON canonique (le format produit par les converters et lu par BookImporter).
 * Sert à comparer le travail de l'import avec ce qui est déjà en base. Lecture seule ;
 * utilisable à l'identique en console et en web.
 *
 * Les valeurs suivent la représentation des converters : booléens en "oui"/"non", chaînes vides
 * plutôt que null (sauf images et est_visible), valeurs d'enum brutes.
 */
final class BookExporter
{
    private const RELATIONS = [
        'publisher', 'collections', 'authors', 'reprints', 'first_edition',
        'titles' => ['authors', 'cycles'],
    ];

    /**
     * Résout une cible : sigle de collection (alias compris), sigle d'éditeur (publications sans collection),
     * puis nom ou nom court de collection.
     *
     * @return array{type: 'collection'|'publisher', model: Collection|Publisher, sigle: string, label: string}
     * @throws \InvalidArgumentException cible introuvable ou ambiguë
     */
    public function resolve(string $target): array
    {
        $sigle = SigleAliases::sigle(trim($target));

        if ($collection = Collection::where('sigle_bdfi', $sigle)->first()) {
            return ['type' => 'collection', 'model' => $collection, 'sigle' => $sigle, 'label' => "collection {$collection->name} [{$sigle}]"];
        }

        if ($publisher = Publisher::where('sigle_bdfi', $sigle)->first()) {
            return ['type' => 'publisher', 'model' => $publisher, 'sigle' => $sigle, 'label' => "éditeur {$publisher->name} [{$sigle}], publications sans collection"];
        }

        $collections = Collection::where('name', $target)->orWhere('shortname', $target)->get();

        if ($collections->count() > 1) {
            throw new \InvalidArgumentException("Nom de collection ambigu « {$target} » : précisez le sigle ("
                . $collections->map(fn ($c) => "{$c->name} [" . ($c->sigle_bdfi ?? 'sans sigle') . ']')->implode(', ') . ').');
        }

        if ($collection = $collections->first()) {
            $sigle = (string) $collection->sigle_bdfi;

            return ['type' => 'collection', 'model' => $collection, 'sigle' => $sigle, 'label' => "collection {$collection->name} [{$sigle}]"];
        }

        throw new \InvalidArgumentException("Cible introuvable : « {$target} » (ni sigle de collection, ni sigle d'éditeur, ni nom de collection).");
    }

    /**
     * Livres d'une ou plusieurs cibles résolues, dans l'ordre des cibles. Une publication présente
     * dans plusieurs cibles n'est exportée qu'une fois.
     *
     * @param array<int, array{type: string, model: Collection|Publisher, sigle: string}> $targets
     * @return array<int, array<string, mixed>>
     */
    public function export(array $targets): array
    {
        $publications = [];   // id => [Publication, sigle de rangement, Collection ?]

        foreach ($targets as $target) {
            foreach ($this->publications($target) as $publication) {
                $publications[$publication->id] ??= [$publication, $target['sigle'], $target['type'] === 'collection' ? $target['model'] : null];
            }
        }

        $books = [];
        $group = 0;

        foreach ($this->groupByEdition($publications) as $members) {
            $group++;
            foreach ($members as $rank => [$publication, $sigle, $collection]) {
                $books[] = $this->book($publication, $sigle, $collection, $group, $rank);
            }
        }

        return $books;
    }

    /** @return \Illuminate\Support\Collection<int, Publication> */
    private function publications(array $target)
    {
        $query = Publication::with(self::relations());

        if ($target['type'] === 'collection') {
            $collectionId = $target['model']->id;

            // Ordre de la collection (pivot), sans le filtre "paru" de Collection::publications()
            return $query
                ->join('collection_publication as cp', 'cp.publication_id', '=', 'publications.id')
                ->where('cp.collection_id', $collectionId)
                ->whereNull('cp.deleted_at')
                ->orderBy('cp.order')->orderBy('cp.number')->orderBy('publications.id')
                ->select('publications.*')
                ->get();
        }

        return $query
            ->where('publisher_id', $target['model']->id)
            ->whereDoesntHave('collections')
            ->orderBy('approximate_parution')->orderBy('id')
            ->get();
    }

    private static function relations(): array
    {
        $out = [];
        foreach (self::RELATIONS as $key => $value) {
            if (is_int($key)) {
                $out[] = $value;
            } else {
                foreach ($value as $sub) {
                    $out[] = "{$key}.{$sub}";
                }
            }
        }

        return $out;
    }

    /**
     * Regroupe chaque ouvrage d'origine avec ses rééditions (first_edition_id), à la place de l'origine,
     * comme le fait le format COL. Une réédition dont l'origine n'est pas exportée forme son propre groupe.
     *
     * @return array<int, array<int, array>> groupes, rang 0 = ouvrage d'origine
     */
    private function groupByEdition(array $publications): array
    {
        $groups = [];

        foreach ($publications as $id => $entry) {
            // En base, une réédition peut pointer vers la réédition précédente : on remonte jusqu'à l'origine
            $key = $id;
            $seen = [$id => true];
            while (($origin = $publications[$key][0]->first_edition_id) !== null
                && isset($publications[$origin]) && !isset($seen[$origin])) {
                $key = $seen[$origin] = $origin;
            }
            $groups[$key][] = $entry;
        }

        // L'origine en tête de son groupe, les rééditions ensuite (ordre de la collection conservé)
        foreach ($groups as $key => &$members) {
            usort($members, fn ($a, $b) => ($a[0]->id === $key ? 0 : 1) <=> ($b[0]->id === $key ? 0 : 1));
        }

        return array_values($groups);
    }

    private function book(Publication $p, string $sigle, ?Collection $collection, int $group, int $rank): array
    {
        $pivot = $collection ? $p->collections->firstWhere('id', $collection->id)?->pivot : null;
        $publisherName = $p->publisher_name ?: ($p->publisher?->name ?? '');

        return [
            // Identifiants (optionnels dans le format pivot) : un export réimporté désigne exactement ces données
            'publisher_id'   => $p->publisher_id,
            'collection_id'  => $collection?->id,
            'editeur'        => $publisherName,
            'collection'     => $collection?->name ?? '',
            'no_collection'  => self::str($pivot?->number),
            'auteurs'        => $p->authors->sortBy('pivot.id')->map(fn ($a) => [
                'author_id'     => $a->id,
                'auteur_prenom' => self::str($a->first_name),
                'auteur_nom'    => self::str($a->name),
                'auteur_role'   => self::value($a->pivot->role) ?? 'author',
            ])->values()->all(),
            'titre'          => self::str($p->name),
            'status'         => self::value($p->status),
            'serie'          => self::str($p->cycle),
            'num_serie'      => self::str($p->cyclenum),
            'est_visible'    => self::yesNo($p->is_visible),
            'isbn'           => self::str($p->isbn),
            'illustrateur_couv' => self::str($p->cover),
            'illustrateurs'  => self::str($p->illustrators),
            'information'    => self::str($p->information),
            'private'        => self::str($p->private),
            'img_couv'       => $p->cover_front,
            'img_back'       => $p->cover_back,
            'img_dos'        => $p->cover_spine,
            'img_bandeau'    => $p->withband_front,
            'img_jaquette'   => $p->dustjacket_front,
            'reliure'        => self::yesNo($p->is_hardcover),
            'jaquette'       => self::yesNo($p->has_dustjacket),
            'rabat'          => self::yesNo($p->has_coverflaps),
            'est_verifie'    => self::yesNo($p->is_verified),
            'verifie_par'    => self::str($p->verified_by),
            'dl'             => self::str($p->dl),
            'ai'             => self::str($p->ai),
            'dimensions'     => self::str($p->dimensions),
            'imprimeur'      => self::str($p->printer),
            'approximate_pages'    => self::str($p->approximate_pages),
            'approximate_parution' => self::str($p->approximate_parution),
            'support'        => self::value($p->support),
            'format'         => self::value($p->format),
            'type_ouvrage'   => self::value($p->type),
            'est_genre'      => self::value($p->is_genre),
            'genre'          => self::value($p->genre_stat),
            'audience'       => self::value($p->target_audience),
            'age'            => self::str($p->target_age),
            'sommaire'       => $p->titles->sortBy('pivot.order')->map(fn (Title $t) => $this->text($t))->values()->all(),
            'collection_sigle' => SigleAliases::original($sigle, $p->publisher_name),
            'pages_dpu'      => self::str($p->pages_dpu),
            'pages_dpi'      => self::str($p->pages_dpi),
            'pagination'     => self::str($p->pagination),
            'prix'           => self::str($p->printed_price),
            'code_prix'      => '',   // pas de colonne en base
            'est_reedition'  => $p->first_edition_id !== null,
            'groupe_edition' => $group,
            'rang_edition'   => $rank,
            'edition_originale_isbn' => self::str($p->first_edition?->isbn),
            'retirages'      => $p->reprints->sortBy('id')->map(fn ($r) => [
                'approximate_parution' => self::str($r->approximate_parution),
                'ai'                   => self::str($r->ai),
                'est_verifie'          => self::yesNo($r->is_verified),
                'verifie_par'          => self::str($r->verified_by),
            ])->values()->all(),
            '_id'            => $p->id,   // pour retrouver la publication ; ignoré par l'import
        ];
    }

    /** Texte du sommaire (table_of_content + titles + cycle_title) */
    private function text(Title $t): array
    {
        $cycle = $t->cycles->first();
        $level = (float) $t->pivot->level;

        return [
            'title_id'      => $t->id,
            'titre'         => self::str($t->name),
            'auteurs'       => $t->authors->map(fn ($a) => [
                'author_id'     => $a->id,
                'auteur_prenom' => self::str($a->first_name),
                'auteur_nom'    => self::str($a->name),
                'auteur_role'   => 'author',
            ])->values()->all(),
            'titre_vo'      => self::str($t->title_vo),
            'copyright'     => self::str($t->copyright),
            'serie'         => self::str($cycle?->name),
            'num_serie'     => self::str($cycle?->pivot->number),
            'series'        => $t->cycles->map(fn ($c) => [
                'cycle_id'  => $c->id,
                'serie'     => self::str($c->name),
                'num_serie' => self::str($c->pivot->number),
            ])->values()->all(),
            'type_texte'    => self::value($t->type),
            'traducteurs'   => self::str($t->translators),
            'est_genre'     => self::value($t->is_genre),
            'novellisation' => self::value($t->is_novelization),
            'genre'         => self::value($t->genre_stat),
            'niveau'        => floor($level) === $level ? (int) $level : $level,
            'page'          => self::str($t->pivot->start_page),
        ];
    }

    private static function str(mixed $value): string
    {
        return $value === null ? '' : (string) $value;
    }

    private static function value(mixed $value): ?string
    {
        return $value instanceof BackedEnum ? (string) $value->value : ($value === null ? null : (string) $value);
    }

    private static function yesNo(mixed $value): ?string
    {
        return $value === null ? null : ((bool) $value ? 'oui' : 'non');
    }
}
