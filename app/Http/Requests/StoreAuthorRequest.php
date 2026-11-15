<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\AuthorGender;
use Illuminate\Validation\Rule;

class StoreAuthorRequest extends FormRequest
{
    /** Date : AAAA-MM-JJ (MM/JJ à 00 si inconnus), ou AAAA-circa / -AAA-circa ; vide => inconnue (0000-00-00) */
    public const DATE_REGEX = '/^(\d{4}-\d{2}-\d{2}|-?\d{1,4}-circa)$/';

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Comme les autres formulaires de création et la policy Filament (AuthorPolicy::create)
        return $this->user()->hasMemberRole();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'          => 'required|max:32',
            // Optionnel : pseudonymes ou collectifs sans prénom (comme en base et dans l'import)
            'first_name'    => 'nullable|max:32',
            'country_id'    => ['required', 'integer', Rule::exists('countries', 'id')],
            'gender'        => [Rule::enum(AuthorGender::class)],
            'birth_date'    => ['nullable', 'max:10', 'regex:' . self::DATE_REGEX],
            'date_death'    => ['nullable', 'max:10', 'regex:' . self::DATE_REGEX],
            'is_pseudonym'  => ['nullable', 'boolean'],
            // Création malgré un auteur de même nom et prénom déjà en base (cf. AuthorController::store)
            'homonym'       => ['nullable', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'birth_date.regex' => 'Date de naissance : formats acceptés AAAA-MM-JJ (00 si inconnu), AAAA-circa ou -AAA-circa.',
            'date_death.regex' => 'Date de décès : formats acceptés AAAA-MM-JJ (00 si inconnu), AAAA-circa ou -AAA-circa.',
            'country_id.*'     => 'Nationalité : choisissez un pays de la liste.',
        ];
    }
}
