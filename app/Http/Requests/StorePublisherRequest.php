<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\PublisherType;
use Illuminate\Validation\Rule;

class StorePublisherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Comme Filament (PublisherPolicy::create)
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
            // 0 = année inconnue (admis temporairement, cf. aide du formulaire) ; publishers.year_start NOT NULL
            'year_start'    => ['required', 'integer', 'min:0', 'max:' . (date('Y') + 1)],
            'country_id'    => ['required', 'integer', Rule::exists('countries', 'id')],
            'type'          => ['required', Rule::enum(PublisherType::class)],
            'alt_names'     => 'nullable|string|max:512',
            // Création malgré un éditeur de même nom déjà en base (cf. PublisherController::store)
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
