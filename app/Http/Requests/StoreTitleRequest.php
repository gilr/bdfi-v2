<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\TitleType;
use App\Enums\TitleVariantType;
use App\Enums\GenreAppartenance;
use App\Enums\GenreStat;
use App\Enums\AudienceTarget;
use Illuminate\Validation\Rule;

class StoreTitleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Attention, check de la vue de provenance et/ou de l'action demandée
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
            'name'               => 'required|max:256',
            'type'               => [Rule::enum(TitleType::class)],
            'variant_type'       => [Rule::enum(TitleVariantType::class)],
            'copyright'          => ['required', 'max:10', 'regex:/([\-012][\-0-9]{3}-(T[1-4]-00|[0-9]{2}-[0-9]{2}))/'],
            'is_genre'           => [Rule::enum(GenreAppartenance::class)],
            'genre_stat'         => [Rule::enum(GenreStat::class)],
            'target_audience'    => [Rule::enum(AudienceTarget::class)],
        ];
    }
}

