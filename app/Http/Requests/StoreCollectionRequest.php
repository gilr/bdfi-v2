<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CollectionType;
use App\Enums\CollectionSupport;
use App\Enums\CollectionPeriodicity;
use Illuminate\Validation\Rule;

class StoreCollectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'name'          => 'required|max:128',
            // Vide => même valeur que "name" (cf. CollectionController::store)
            'shortname'     => 'nullable|max:128',
            // 0 = année inconnue (admis temporairement, cf. aide du formulaire) ; collections.year_start NOT NULL
            'year_start'    => ['required', 'integer', 'min:0', 'max:' . (date('Y') + 1)],
            'type'          => ['required', Rule::enum(CollectionType::class)],
            'support'       => ['required', Rule::enum(CollectionSupport::class)],
            // collections.periodicity NOT NULL sans défaut (bug corrigé : absent de l'ancien formulaire)
            'periodicity'   => ['required', Rule::enum(CollectionPeriodicity::class)],
            // Éditeur (EntityPicker) : existant (publisher_id), sinon à créer à la volée (publisher_name)
            'publisher_id'   => ['nullable', 'integer', Rule::exists('publishers', 'id')],
            'publisher_name' => ['nullable', 'string', 'max:128'],
            // Création malgré une collection de même nom déjà en base (cf. CollectionController::store)
            'homonym'       => ['nullable', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'year_start.max' => 'Année de création : ne peut pas dépasser ' . (date('Y') + 1) . '.',
        ];
    }
}
