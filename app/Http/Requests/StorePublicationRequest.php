<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\PublicationStatus;
use App\Enums\publicationContent;
use App\Enums\publicationSupport;
use App\Enums\GenreAppartenance;
use App\Enums\GenreStat;
use App\Enums\AudienceTarget;
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
        // TO DO : Attention, si même action, il faut checker la vue de provenance
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
            'publication_status'    => [Rule::enum(PublicationStatus::class)],
            'name'                  => 'required|max:128',
            'type'                  => [Rule::enum(publicationContent::class)],
            'support'               => [Rule::enum(publicationSupport::class)],
            'approximate_parution'  => ['required', 'max:10', 'regex:/([\-012][\-0-9]{3}-(T[1-4]-00|[0-9]{2}-[0-9]{2}))/'],
            'is_genre'              => [Rule::enum(GenreAppartenance::class)],
            'genre_stat'            => [Rule::enum(GenreStat::class)],
            'target_audience'       => [Rule::enum(AudienceTarget::class)],
            'isbn'                  => 'max:18',
        ];
    }
}
