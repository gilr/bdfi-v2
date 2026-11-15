<?php

namespace App\Bdfi\Import;

use App\Enums\AudienceTarget;
use App\Enums\AuthorPublicationRole;
use App\Enums\GenreAppartenance;
use App\Enums\GenreStat;
use App\Enums\IsNovelization;
use App\Enums\PublicationContent;
use App\Enums\PublicationFormat;
use App\Enums\PublicationStatus;
use App\Enums\PublicationSupport;
use App\Enums\TitleType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Valide le JSON canonique (voir AbstractBookConverter::$jsonSchema) contre le schéma de la base :
 * longueurs maximales des colonnes, valeurs d'enums, champs NOT NULL sans défaut.
 * Une valeur hors enum ou trop longue est donc détectée dès --analyse, avant tout INSERT.
 *
 * Tous types de contenu : le sommaire contient un ou plusieurs textes (CSV : un seul ; COL : sommaire complet
 * s'il est connu, sinon seulement l'élément "contenant"). Le premier texte est le contenu principal.
 */
final class BookValidator
{
    /** Le premier texte du sommaire (contenu principal / contenant) porte le même titre que le livre */
    public const REQUIRE_SAME_TITLE = true;

    private function rules(): array
    {
        return [
            // Éditeur requis, sauf si la collection est identifiée par son sigle (format COL) ou par un id
            'editeur'          => ['nullable', 'string', 'required_without_all:collection_sigle,publisher_id,collection_id'],
            'collection_sigle' => ['nullable', 'string', 'max:8'],
            'collection' => ['nullable', 'string'],   // optionnelle

            // Identifiants optionnels (.to-add.json annoté, proposition...) : raccourcis vers une donnée existante,
            // contrôlés à l'usage (BookLookup) ; un id qui ne correspond plus => recherche par nom, écart signalé
            'publisher_id'                       => ['nullable', 'integer', 'min:1'],
            'collection_id'                      => ['nullable', 'integer', 'min:1'],
            'auteurs.*.author_id'                => ['nullable', 'integer', 'min:1'],
            'sommaire.*.title_id'                => ['nullable', 'integer', 'min:1'],
            'sommaire.*.auteurs.*.author_id'     => ['nullable', 'integer', 'min:1'],
            'sommaire.*.series.*.cycle_id'       => ['nullable', 'integer', 'min:1'],

            // publications.name(128), cycle(128), cyclenum(10), isbn(18)
            'titre'      => ['required', 'string', 'max:128'],
            'serie'      => ['nullable', 'string', 'max:128'],
            'num_serie'  => ['nullable', 'string', 'max:10'],
            'isbn'       => ['nullable', 'string', 'max:18'],
            'no_collection' => ['nullable', 'string', 'max:16'],   // collection_publication.number(16)

            // Enums (valeurs vides acceptées : le défaut SQL s'applique)
            'status'   => ['nullable', Rule::enum(PublicationStatus::class)],
            'support'  => ['nullable', Rule::enum(PublicationSupport::class)],
            'format'   => ['nullable', Rule::enum(PublicationFormat::class)],
            'genre'    => ['nullable', Rule::enum(GenreStat::class)],
            'audience' => ['nullable', Rule::enum(AudienceTarget::class)],

            // NOT NULL sans défaut en base (enums PublicationContent / GenreAppartenance : règle Rule::enum à ajouter)
            'type_ouvrage' => ['required', Rule::enum(PublicationContent::class)],
            'est_genre'    => ['required', Rule::enum(GenreAppartenance::class)],

            'illustrateur_couv'    => ['nullable', 'string', 'max:256'],
            'illustrateurs'        => ['nullable', 'string', 'max:512'],
            'img_couv'             => ['nullable', 'string', 'max:64'],
            'img_back'             => ['nullable', 'string', 'max:64'],
            'img_dos'              => ['nullable', 'string', 'max:64'],
            'img_bandeau'          => ['nullable', 'string', 'max:64'],
            'img_jaquette'         => ['nullable', 'string', 'max:64'],
            'verifie_par'          => ['nullable', 'string', 'max:256'],
            'dl'                   => ['nullable', 'string', 'max:10'],
            'ai'                   => ['nullable', 'string', 'max:10'],
            'dimensions'           => ['nullable', 'string', 'max:10'],
            'imprimeur'            => ['nullable', 'string', 'max:128'],
            'approximate_parution' => ['nullable', 'string', 'max:10'],

            // authors.name(32) = nom, authors.first_name(32) = prénom
            // Peut être vide : revues, journaux, anthologies d'éditeur sans anthologiste crédité
            'auteurs'                 => ['present', 'array'],
            'auteurs.*.auteur_nom'    => ['required', 'string', 'max:32'],
            'auteurs.*.auteur_prenom' => ['nullable', 'string', 'max:32'],
            'auteurs.*.auteur_role'   => ['nullable', Rule::enum(AuthorPublicationRole::class)],

            // Retirages (table reprints : ai 10, approximate_parution 10, verified_by 256)
            'retirages.*.ai'                   => ['nullable', 'string', 'max:10'],
            'retirages.*.approximate_parution' => ['nullable', 'string', 'max:10'],
            'retirages.*.verifie_par'          => ['nullable', 'string', 'max:256'],

            'sommaire.*.page'                    => ['nullable', 'string', 'max:8'],   // table_of_content.start_page(8)
            'sommaire.*.niveau'                  => ['nullable', 'numeric', 'between:0,9'],   // table_of_content.level decimal(2,1) : 0.5, 1.5 = sections

            'sommaire'                           => ['required', 'array', 'min:1'],
            'sommaire.*.titre'                   => ['required', 'string', 'max:256'],   // titles.name(256)
            // Niveau texte (titles) : type, appartenance au genre, genre, novellisation
            'sommaire.*.type_texte'              => ['required', Rule::enum(TitleType::class)],   // titles.type NOT NULL
            'sommaire.*.est_genre'               => ['nullable', Rule::enum(GenreAppartenance::class)],
            'sommaire.*.genre'                   => ['nullable', Rule::enum(GenreStat::class)],
            'sommaire.*.novellisation'           => ['nullable', Rule::enum(IsNovelization::class)],
            'sommaire.*.titre_vo'                => ['nullable', 'string', 'max:512'],
            'sommaire.*.copyright'               => ['nullable', 'string', 'max:10'],
            'sommaire.*.serie'                   => ['nullable', 'string', 'max:128'],   // cycles.name(128)
            'sommaire.*.num_serie'               => ['nullable', 'string', 'max:16'],    // cycle_title.number(16)
            'sommaire.*.series'                  => ['nullable', 'array'],               // tous les cycles (format COL)
            'sommaire.*.series.*.serie'          => ['nullable', 'string', 'max:128'],
            'sommaire.*.series.*.num_serie'      => ['nullable', 'string', 'max:16'],
            'sommaire.*.series.*.parent'         => ['nullable', 'string', 'max:128'],
            'sommaire.*.auteurs'                 => ['present', 'array'],   // peut être vide (section, texte anonyme)
            'sommaire.*.auteurs.*.auteur_nom'    => ['required', 'string', 'max:32'],
            'sommaire.*.auteurs.*.auteur_prenom' => ['nullable', 'string', 'max:32'],
        ];
    }


    /**
     * @return array<int, array<int, array{attribute: string, message: string, value: mixed}>>
     *         n° de livre (1-based, = index + 1) => erreurs (champ, message, valeur fautive)
     */
    public function validate(array $books): array
    {
        $errors = [];
        $rules = $this->rules();

        foreach ($books as $index => $book) {
            $book = is_array($book) ? $book : [];

            $validator = Validator::make($book, $rules);
            $validator->after(fn ($v) => $this->checkScope($book, $v));

            if ($validator->fails()) {
                $errors[$index + 1] = $this->details($book, $validator->errors()->messages());
            }
        }

        return $errors;
    }

    /** Associe à chaque message la valeur exacte du champ fautif (null si absent, tableau si structure). */
    private function details(array $book, array $messages): array
    {
        $details = [];

        foreach ($messages as $attribute => $list) {
            $value = data_get($book, $attribute);

            foreach ($list as $message) {
                $details[] = ['attribute' => $attribute, 'message' => $message, 'value' => $value];
            }
        }

        return $details;
    }

    private function checkScope(array $book, $validator): void
    {
        $texts = is_array($book['sommaire'] ?? null) ? array_values($book['sommaire']) : [];

        if (self::REQUIRE_SAME_TITLE
            && isset($texts[0]['titre'], $book['titre'])
            && BookLookup::key((string) $texts[0]['titre']) !== BookLookup::key((string) $book['titre'])) {
            $validator->errors()->add('sommaire',
                "Le titre du premier texte (« {$texts[0]['titre']} ») diffère de celui du livre (« {$book['titre']} »).");
        }
    }
}