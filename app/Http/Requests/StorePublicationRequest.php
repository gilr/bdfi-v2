<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\PublicationStatus;
use App\Enums\PublicationContent;
use App\Enums\PublicationSupport;
use App\Enums\PublicationFormat;
use App\Enums\GenreAppartenance;
use App\Enums\GenreStat;
use App\Enums\AudienceTarget;
use App\Bdfi\MainContentFinder;
use App\Enums\IsNovelization;
use App\Enums\TitleType;
use App\Models\Title;
use Illuminate\Validation\Rule;

class StorePublicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Attention, check de la vue de provenance et/ou de l'action demandée
        if ($this->publication_status === PublicationStatus::PUBLIE->value)
        {
            return $this->user()->hasMemberRole();
        }
        else if ($this->publication_status === PublicationStatus::ANNONCE->value)
        {
            return $this->user()->hasMemberRole();
        }
        else
        {
            // Strange ?!
            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'publication_status'    => ['required', Rule::enum(PublicationStatus::class)],
            'name'                  => 'required|max:128',
            'type'                  => ['required', Rule::enum(PublicationContent::class)],
            'support'               => ['required', Rule::enum(PublicationSupport::class)],
            'format'                => ['nullable', Rule::enum(PublicationFormat::class)],
            // AAAA-MM-JJ (MM/JJ à 00 si inconnus), AAAA-Tn-00 (trimestre), ou AAAA-circa
            'approximate_parution'  => ['required', 'max:10', 'regex:/^(\d{4}-(\d{2}-\d{2}|T[1-4]-00)|-?\d{1,4}-circa)$/'],
            'is_genre'              => ['required', Rule::enum(GenreAppartenance::class)],
            'genre_stat'            => ['required', Rule::enum(GenreStat::class)],
            'target_audience'       => ['required', Rule::enum(AudienceTarget::class)],
            'isbn'                  => 'nullable|max:18',
            'no_isbn'               => 'nullable|boolean',   // l'ouvrage n'a pas d'ISBN => "-"
            'is_hardcover'          => 'nullable|boolean',
            'has_dustjacket'        => 'nullable|boolean',
            'has_coverflaps'        => 'nullable|boolean',
            // Éditeur (EntityPicker) : existant (publisher_id), sinon à créer à la volée (publisher_name)
            'publisher_id'          => ['nullable', 'integer', Rule::exists('publishers', 'id')],
            'publisher_name'        => ['nullable', 'string', 'max:128'],
            // Collection (EntityPicker, existante uniquement) et son n° éventuel dans cette collection
            'collection_id'         => ['nullable', 'integer', Rule::exists('collections', 'id')],
            'collection_number'     => ['nullable', 'string', 'max:16'],
            // Auteurs (EntityPicker, existants uniquement) : jusqu'à deux pour l'instant
            'author1_id'            => ['nullable', 'integer', Rule::exists('authors', 'id')],
            'author2_id'            => ['nullable', 'integer', Rule::exists('authors', 'id')],
            // Contenu principal : texte existant identique (id), ou "new" : nouveau texte créé avec la publication
            'title_choice'          => ['required', 'string', 'max:12'],
            'new_title_type'        => ['nullable', Rule::enum(TitleType::class)],
            'new_title_copyright'   => ['nullable', 'max:10', 'regex:/^([\-012][\-0-9]{3}-(T[1-4]-00|[0-9]{2}-[0-9]{2}))$/'],
            'new_title_extract'     => ['nullable', 'boolean'],
            'new_title_novelization' => ['nullable', Rule::enum(IsNovelization::class)],
            'new_title_vo'          => ['nullable', 'string', 'max:512'],
            'new_title_translators' => ['nullable', 'string', 'max:512'],
        ];
    }

    public function messages()
    {
        return [
            'approximate_parution.regex' => 'Date de parution : formats acceptés AAAA-MM-00, AAAA-00-00, AAAA-MM-JJ, AAAA-Tn-00 (trimestre) ou AAAA-circa.',
            'title_choice.required' => 'Contenu principal : cherchez puis choisissez un texte existant identique, ou créez un nouveau texte.',
            'new_title_copyright.regex' => 'Date de copyright : formats acceptés AAAA-MM-JJ (00 si inconnu) ou AAAA-Tn-00 (trimestre).',
        ];
    }

    /**
     * Ouvrage à paraître : la parution prévue ne peut pas être passée, avec une tolérance d'un mois
     * (ouvrage annoncé dont la parution effective n'a pas encore été vérifiée). Pas de date approximative.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $author1 = $this->input('author1_id');
            $author2 = $this->input('author2_id');
            if (filled($author1) && filled($author2) && (int) $author1 === (int) $author2) {
                $validator->errors()->add('author2_id', 'Auteur 2 : ne peut pas être le même que l\'auteur 1.');
            }

            $this->validateMainContent($validator);

            if ($this->input('publication_status') !== PublicationStatus::ANNONCE->value
                || $validator->errors()->has('approximate_parution')) {
                return;
            }

            $date = (string) $this->input('approximate_parution');
            $end = self::periodEnd($date);

            if ($end === null) {
                $validator->errors()->add('approximate_parution',
                    'Ouvrage à paraître : la date prévue doit être précise (AAAA-MM-00, AAAA-Tn-00, AAAA-00-00...), pas approximative.');
            } elseif ($end->lt(now()->subMonthNoOverflow()->startOfMonth())) {
                $validator->errors()->add('approximate_parution',
                    "Ouvrage à paraître : la date prévue ({$date}) est passée. Au plus un mois en arrière est toléré "
                    . '(à partir de ' . now()->subMonthNoOverflow()->format('m/Y') . ') ; sinon, enregistrer l\'ouvrage comme paru.');
            }
        });
    }

    /** Contenu principal : nouveau texte (type obligatoire), ou texte existant réellement identique (même titre, mêmes auteurs) */
    private function validateMainContent($validator): void
    {
        $choice = (string) $this->input('title_choice');
        if ($choice === '' || $validator->errors()->has('title_choice')) {
            return;
        }

        if ($choice === 'new') {
            $type = (string) $this->input('new_title_type');
            $allowed = MainContentFinder::allowedTypes((string) $this->input('type'));
            $extract = $this->boolean('new_title_extract');
            if ($extract && !(PublicationContent::tryFrom((string) $this->input('type'))?->allowsExtract())) {
                $validator->errors()->add('new_title_extract', "Un extrait ne peut pas être le contenu principal de ce type de publication.");
            } elseif ($type === '') {
                $validator->errors()->add('new_title_type', 'Nouveau texte : choisissez son type.');
            } elseif ($extract) {
                // Extrait : tout type de texte
            } elseif ($allowed !== null && !in_array($type, array_map(fn ($t) => $t->value, $allowed), true)) {
                $validator->errors()->add('new_title_type', "Nouveau texte : ce type de texte n'est pas compatible avec le type de la publication.");
            }

            return;
        }

        $title = ctype_digit($choice) ? Title::find((int) $choice) : null;
        if (!$title || !MainContentFinder::isIdentical($title, (string) $this->input('name'),
            [$this->input('author1_id'), $this->input('author2_id')], (string) $this->input('type'))) {
            $validator->errors()->add('title_choice', "Contenu principal : le texte choisi n'a pas le même titre, les mêmes auteurs et un type compatible avec la publication. Relancez la recherche.");
        }
    }

    /** Dernier jour de la période désignée par la date (année, trimestre, mois ou jour) ; null si approximative */
    private static function periodEnd(string $date): ?\Illuminate\Support\Carbon
    {
        if (!preg_match('/^(\d{4})-(\d{2}|T([1-4]))-(\d{2})$/', $date, $m)) {
            return null;   // AAAA-circa
        }

        $year = (int) $m[1];

        if (($m[3] ?? '') !== '') {                       // trimestre : dernier jour du 3e mois
            return \Illuminate\Support\Carbon::create($year, (int) $m[3] * 3, 1)->endOfMonth();
        }

        $month = (int) $m[2];
        $day = (int) $m[4];

        if ($month === 0) {                               // année seule
            return \Illuminate\Support\Carbon::create($year, 12, 31)->endOfDay();
        }
        if ($day === 0 || !checkdate($month, $day, $year)) {   // mois seul (ou jour invalide : fin du mois)
            return \Illuminate\Support\Carbon::create($year, $month, 1)->endOfMonth();
        }

        return \Illuminate\Support\Carbon::create($year, $month, $day)->endOfDay();
    }
}
