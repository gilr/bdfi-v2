<?php

namespace App\Bdfi\Proposal;

use App\Bdfi\Import\BookLookup;
use App\Bdfi\Import\BookValidator;
use App\Enums\AuthorPublicationRole;
use App\Enums\ProposalStatus;
use App\Enums\ProposalType;
use App\Enums\PublicationContent;
use App\Enums\PublicationStatus;
use App\Enums\TitleType;
use App\Models\Author;
use App\Models\Collection;
use App\Models\Proposal;
use App\Models\Publisher;
use App\Models\User;

/**
 * Proposition d'ajout d'un ouvrage : la proposition est un livre au format JSON canonique de l'import
 * (voir AbstractBookConverter::$jsonSchema), validé par BookValidator et, à l'acceptation, importé par BookImporter.
 * Pas de sommaire dans cette première version : le sommaire du livre se réduit au contenu principal (même titre
 * que le livre), comme pour un import dont le sommaire est inconnu. Les corrections et sommaires feront l'objet
 * de propositions d'autres types.
 */
final class PublicationProposalService
{
    public function __construct(private BookLookup $lookup = new BookLookup()) {}

    /**
     * Livre JSON canonique à partir des données validées du formulaire.
     * Éditeur, collection et auteurs : existants (id) ou, pour l'éditeur et les auteurs, saisis en clair.
     *
     * @param array<string, mixed> $d données validées (voir StoreProposalRequest)
     */
    public function buildBook(array $d): array
    {
        $book = [
            'titre'                => BookLookup::norm($d['name']),
            'status'               => PublicationStatus::PUBLIE->value,   // la proposition concerne un ouvrage paru
            'type_ouvrage'         => $d['type'],
            'support'              => $d['support'],
            'format'               => $d['format'] ?? null,
            'approximate_parution' => $d['approximate_parution'],
            'est_genre'            => $d['is_genre'],
            'genre'                => $d['genre_stat'],
            'audience'             => $d['target_audience'],
            // "-" : pas d'ISBN ; "?" : ISBN inconnu (champ vide), comme dans la base
            'isbn'                 => !empty($d['no_isbn']) ? '-' : BookLookup::storedIsbn($d['isbn'] ?? ''),
            'reliure'              => !empty($d['is_hardcover']),
            'jaquette'             => !empty($d['has_dustjacket']),
            'rabat'                => !empty($d['has_coverflaps']),
            // Notes du proposant (sources, liens...) : champ privé de la publication, lu par l'équipe
            'private'              => filled($d['private'] ?? null) ? trim($d['private']) : null,
            'est_verifie'          => false,
        ];

        // Éditeur : existant (id), ou nom saisi (l'éditeur sera à créer avant l'acceptation)
        if (filled($d['publisher_id'] ?? null) && ($publisher = Publisher::find((int) $d['publisher_id']))) {
            $book['editeur'] = $publisher->name;
            $book['publisher_id'] = $publisher->id;
        } else {
            $book['editeur'] = BookLookup::norm($d['publisher_name'] ?? '');
        }

        // Collection : existante seulement
        if (filled($d['collection_id'] ?? null) && ($collection = Collection::find((int) $d['collection_id']))) {
            $book['collection'] = $collection->name;
            $book['collection_id'] = $collection->id;
            $book['no_collection'] = filled($d['collection_number'] ?? null) ? trim($d['collection_number']) : null;
        }

        $book['auteurs'] = $this->authors($d);

        // Sommaire réduit au contenu principal : même titre, mêmes auteurs que le livre
        $book['sommaire'] = [[
            'titre'      => $book['titre'],
            'type_texte' => (PublicationContent::tryFrom($d['type'])?->mainContentTypes()[0] ?? TitleType::INCONNU)->value,
            'auteurs'    => $book['auteurs'],
        ]];

        return array_filter($book, fn ($v) => $v !== null && $v !== '');
    }

    /** Auteurs du livre : existants (id), ou saisis en clair (nom et prénom) à défaut d'un choix */
    private function authors(array $d): array
    {
        $authors = [];

        foreach ([1, 2] as $n) {
            $id = $d["author{$n}_id"] ?? null;

            if (filled($id) && ($author = Author::find((int) $id))) {
                $authors[] = [
                    'author_id'     => $author->id,
                    'auteur_nom'    => (string) $author->name,
                    'auteur_prenom' => (string) $author->first_name,
                    'auteur_role'   => AuthorPublicationRole::AUTHOR->value,
                ];
            } elseif (filled($d["author{$n}_name"] ?? null)) {
                $authors[] = [
                    'auteur_nom'    => BookLookup::norm($d["author{$n}_name"]),
                    'auteur_prenom' => BookLookup::norm($d["author{$n}_first_name"] ?? ''),
                    'auteur_role'   => AuthorPublicationRole::AUTHOR->value,
                ];
            }
        }

        return $authors;
    }

    /**
     * Erreurs de structure du livre selon le schéma de la base (BookValidator).
     * @return string[]
     */
    public function validationMessages(array $book): array
    {
        $errors = (new BookValidator())->validate([$book])[1] ?? [];

        return array_map(fn ($e) => $e['message'], $errors);
    }

    /**
     * Doublon : l'ouvrage est déjà en base (même ISBN et même année ; sans ISBN : mêmes éditeur, collection, titre,
     * auteurs, année et format), ou déjà proposé et en attente. Retourne le motif du refus, ou null.
     */
    public function duplicateReason(array $book): ?string
    {
        $isbn = BookLookup::realIsbn($book['isbn'] ?? '');

        if ($isbn !== '' && $this->lookup->publicationExists($isbn, $book['approximate_parution'] ?? null)) {
            return "Un ouvrage d'ISBN {$isbn} existe déjà dans la base pour cette année de parution.";
        }

        if ($isbn === '') {
            $resolved = $this->lookup->resolve($book);
            if ($resolved['missing'] === null
                && $this->lookup->publicationExistsWithoutIsbn($book, $resolved['publisher'], $resolved['collection'])) {
                return 'Cet ouvrage (mêmes éditeur, collection, titre, auteurs, année et format) existe déjà dans la base.';
            }
        }

        $year = substr((string) ($book['approximate_parution'] ?? ''), 0, 4);
        $pending = Proposal::where('type', ProposalType::PUBLICATION_ADD->value)
            ->where('status', ProposalStatus::SUBMITTED->value)
            ->get()
            ->first(function (Proposal $p) use ($book, $isbn, $year) {
                $other = $p->payload;
                if ($isbn !== '') {
                    return BookLookup::realIsbn($other['isbn'] ?? '') === $isbn
                        && substr((string) ($other['approximate_parution'] ?? ''), 0, 4) === $year;
                }

                return BookLookup::realIsbn($other['isbn'] ?? '') === ''
                    && BookLookup::key($other['titre'] ?? '') === BookLookup::key($book['titre'])
                    && BookLookup::key($other['editeur'] ?? '') === BookLookup::key($book['editeur'] ?? '')
                    && substr((string) ($other['approximate_parution'] ?? ''), 0, 4) === $year;
            });

        return $pending ? "Cet ouvrage a déjà été proposé (proposition n° {$pending->id}, en attente d'étude)." : null;
    }

    public function store(User $user, array $book): Proposal
    {
        return Proposal::create([
            'user_id'              => $user->id,
            'type'                 => ProposalType::PUBLICATION_ADD->value,
            'status'               => ProposalStatus::SUBMITTED->value,
            'title'                => mb_substr($book['titre'], 0, 128),
            'publisher_name'       => isset($book['editeur']) ? mb_substr($book['editeur'], 0, 128) : null,
            'approximate_parution' => $book['approximate_parution'] ?? null,
            'isbn'                 => BookLookup::realIsbn($book['isbn'] ?? '') ?: null,
            'payload'              => $book,
        ]);
    }

    /** Libellé du type d'ouvrage (affichage) */
    public static function contentLabel(?string $type): string
    {
        return PublicationContent::tryFrom((string) $type)?->getLabel() ?? (string) $type;
    }
}
