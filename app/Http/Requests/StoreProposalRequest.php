<?php

namespace App\Http\Requests;

use App\Enums\AudienceTarget;
use App\Enums\GenreAppartenance;
use App\Enums\GenreStat;
use App\Enums\PublicationContent;
use App\Enums\PublicationFormat;
use App\Enums\PublicationSupport;
use App\Livewire\ScheduledPublications;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Proposition d'ajout d'un ouvrage paru (contributeur et plus) */
class StoreProposalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasProponentRole();
    }

    public function rules(): array
    {
        return [
            'name'                 => ['required', 'string', 'max:128'],
            'type'                 => ['required', Rule::enum(PublicationContent::class)],
            'support'              => ['required', Rule::enum(PublicationSupport::class)],
            'format'               => ['nullable', Rule::enum(PublicationFormat::class)],
            // AAAA-MM-JJ (MM/JJ à 00 si inconnus), AAAA-Tn-00 (trimestre), ou AAAA-circa
            'approximate_parution' => ['required', 'max:10', 'regex:/^(\d{4}-(\d{2}-\d{2}|T[1-4]-00)|-?\d{1,4}-circa)$/'],
            'is_genre'             => ['required', Rule::enum(GenreAppartenance::class)],
            'genre_stat'           => ['required', Rule::enum(GenreStat::class)],
            'target_audience'      => ['required', Rule::enum(AudienceTarget::class)],
            'isbn'                 => ['nullable', 'string', 'max:18'],
            'no_isbn'              => ['nullable', 'boolean'],
            'is_hardcover'         => ['nullable', 'boolean'],
            'has_dustjacket'       => ['nullable', 'boolean'],
            'has_coverflaps'       => ['nullable', 'boolean'],
            // Éditeur : existant (publisher_id), sinon nom saisi (à créer par l'équipe avant l'acceptation)
            'publisher_id'         => ['nullable', 'integer', Rule::exists('publishers', 'id')],
            'publisher_name'       => ['nullable', 'string', 'max:128'],
            'collection_id'        => ['nullable', 'integer', Rule::exists('collections', 'id')],
            'collection_number'    => ['nullable', 'string', 'max:16'],
            // Auteurs : existant (id), sinon nom et prénom saisis
            'author1_id'           => ['nullable', 'integer', Rule::exists('authors', 'id')],
            'author1_name'         => ['nullable', 'string', 'max:32'],
            'author1_first_name'   => ['nullable', 'string', 'max:32'],
            'author2_id'           => ['nullable', 'integer', Rule::exists('authors', 'id')],
            'author2_name'         => ['nullable', 'string', 'max:32'],
            'author2_first_name'   => ['nullable', 'string', 'max:32'],
            'private'              => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'approximate_parution.regex' => 'Date de parution : formats acceptés AAAA-MM-00, AAAA-00-00, AAAA-MM-JJ, AAAA-Tn-00 (trimestre) ou AAAA-circa.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'titre', 'approximate_parution' => 'date de parution', 'genre_stat' => 'genre',
            'target_audience' => 'public', 'publisher_name' => "nom de l'éditeur",
            'author1_name' => 'nom de l\'auteur 1', 'author1_first_name' => 'prénom de l\'auteur 1',
            'author2_name' => 'nom de l\'auteur 2', 'author2_first_name' => 'prénom de l\'auteur 2',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!filled($this->input('publisher_id')) && !filled($this->input('publisher_name'))) {
                $validator->errors()->add('publisher_name', "Éditeur : choisissez un éditeur existant, ou saisissez son nom s'il n'est pas dans la liste.");
            }

            $a1 = $this->input('author1_id');
            $a2 = $this->input('author2_id');
            if (filled($a1) && filled($a2) && (int) $a1 === (int) $a2) {
                $validator->errors()->add('author2_id', "Auteur 2 : ne peut pas être le même que l'auteur 1.");
            }

            // Un auteur saisi en clair : nom obligatoire
            foreach ([1, 2] as $n) {
                if (!filled($this->input("author{$n}_id")) && !filled($this->input("author{$n}_name")) && filled($this->input("author{$n}_first_name"))) {
                    $validator->errors()->add("author{$n}_name", "Auteur {$n} : le nom est obligatoire si le prénom est renseigné.");
                }
            }

            // Seuls les ouvrages déjà parus se proposent ici (les programmations sont gérées par l'équipe)
            $date = (string) $this->input('approximate_parution');
            if (!$validator->errors()->has('approximate_parution') && !ScheduledPublications::canBePublished($date)) {
                $validator->errors()->add('approximate_parution', "L'ouvrage n'est pas encore paru ({$date}) : seuls les ouvrages déjà parus peuvent être proposés.");
            }
        });
    }
}
