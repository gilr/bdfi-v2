<?php

namespace App\Bdfi\BookConverter;

use Illuminate\Support\Facades\File;


abstract class AbstractBookConverter implements BookFormatHandler
{

    use ReadsEncodedSource;

    public function import(string $path, array $options = []): array
    {
        $this->encoding = $options['encoding'] ?? 'auto';
        return $this->convert($path);
    }

    public function export(array $books): string
    {
        throw new \LogicException('Export non implémenté pour ' . static::class);
    }

    /* Structure cible JSON canonique */
    protected array $jsonSchema = [
        // éditeur & collection
        'editeur' => null,
        'collection' => null,
        'no_collection' => null,

        'auteurs' => [
        ],

        'titre' => null,        // $table->string('name', 128);
        'status' => null,       // $table->string('status')->default(PublicationStatus::PUBLIE->value); // PUBLIE, ANNONCE, PROPOSE, ABANDONNE
        'serie' => null,        // $table->string('cycle', 128)->nullable();
        'num_serie' => null,    // $table->string('cyclenum', 10)->nullable();

        'est_visible' => null,  // $table->boolean('is_visible')->default(true);

        'isbn' => null, // $table->string('isbn', 18)->nullable();

        'illustrateur_couv' => null,    // $table->string('cover', 256)->nullable();
        'illustrateurs' => null,        // $table->string('illustrators', 512)->nullable();
        'information' => null,          // $table->text('information')->nullable();
        'private' => null,              // $table->text('private')->nullable();

        // images (img_fichier) : TODO dans les builders : déduire img_couv, et compléter avec les images de bandeau & back si besoin
        'img_couv' => null,        // $table->string('cover_front', 64)->nullable();
        'img_back' => null,        // $table->string('cover_back', 64)->nullable();
            // $table->string('cover_spine', 64)->nullable();
        'img_dos' => null,         // $table->string('withband_spine', 64)->nullable();
        'img_bandeau' => null,     // $table->string('withband_front', 64)->nullable();
            // $table->string('withband_back', 64)->nullable();
            // $table->string('withband_spine', 64)->nullable();
        'img_jaquette' => null,    // $table->string('dustjacket_front', 64)->nullable();
            // $table->string('dustjacket_back', 64)->nullable();
            // $table->string('dustjacket_spine', 64)->nullable();

        'reliure' => null,  // $table->boolean('is_hardcover')->default(false);
        'jaquette' => null, // $table->boolean('has_dustjacket')->default(false);
        'rabat' => null,    // $table->boolean('has_coverflaps')->default(false);

        'est_verifie' => null,  // $table->boolean('is_verified');
        'verifie_par' => null,  // $table->string('verified_by', 256)->nullable();
        // Données issues du livre
        'dl' => null,           // $table->string('dl', 10)->nullable();
        'ai' => null,           // $table->string('ai', 10)->nullable();
                                // $table->string('edition', 64)->nullable();
        'dimensions' => null,   // $table->string('dimensions', 10)->nullable();
                                // $table->string('thickness', 4)->nullable();
        'imprimeur' => null,    // $table->string('printer', 128)->nullable();
                                // $table->string('printed_price', 32)->nullable();
                                // $table->string('pagination', 32)->nullable();
                                // $table->string('pages_dpi')->nullable();
                                // $table->string('pages_dpu')->nullable();

        // Données indicatives :
        'approximate_pages' => null,    // $table->string('approximate_pages')->nullable();
            // Format CSV => à remplacer par format base 'approximate parution'
            // 'mois_parution' => null,
            // 'annee_parution' => null,
        'approximate_parution' => null, // $table->string('approximate_parution', 10)->nullable();
                                        // $table->string('approximate_price', 32)->nullable();

        'support' => null,      // $table->string('support')->default(PublicationSupport::PAPIER->value); // papier, numerique, audio, autre
        'format' => null,       // $table->string('format')->default(PublicationFormat::INCONNU->value); // Poche, GF, MF, autre, n-a, inconnu
        'type_ouvrage' => null, // $table->string('type'); // Enum PublicationContent : fiction, assemblage, omnibus, revue, non-fiction

        // type_tirage (inedit, premiere, retirage, reedition) - PAS EN BASE (CSV CM)

        // Genre, audience
        'est_genre' => null,    // $table->string('is_genre'); // Enum GenreAppartenance : yes, partial, no
        'genre' => null,        // $table->string('genre_stat')->default(GenreStat::INCONNU->value); // sf, fantasy, fantastique, hybride, autre, mainstream
        'audience' => null,     // $table->string('target_audience')->default(AudienceTarget::INCONNU->value);;
        'age' => null,          // $table->string('target_age')->nullable();

        'sommaire' => [
            "0" => [
                'titre' => null,
                'auteurs' => [
                ],
                'titre_vo' => null,
                'copyright' => null,
                'serie' => null,
                'num_serie' => null,
                'type_texte' => null,
                'traducteurs' => [
                ],
            ]
        ],
    ];

    /**
     * Extraction format source, fonction du format de départ
     */
     abstract protected function extract(string $source): array;

    /**
     * Construction d'un livre au format JSON en fonction du format de départ
     */
     abstract protected function buildBook(array $sourceBook): array;

    /**
     * Moteur de conversion commun quelque soit le format d'entrée
     */
     public function convert(string $source): array
     {
        // extraction dans un array au format d'import
        $extractedBooks = $this->extract($source);

        /** Pour chaque book, initialiser/prérenseigner la structure (jsonSchema)
         * puis fusionner en remplaçant les valeurs extraites présentes
         */
        $result = [];
        foreach ($extractedBooks as $book) {
            $result[] = array_replace_recursive(
                $this->jsonSchema,
                $this->buildBook($book)
            );
        }

        return $result;
     }


}