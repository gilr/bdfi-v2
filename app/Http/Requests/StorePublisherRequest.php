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
            'year_start'    => 'required|numeric',
            'pays'          => Rule::in(['France', 'Canada', 'Suisse', 'Belgique', 'Luxembourg']),
            'type'          => [Rule::enum(PublisherType::class)],
            'alt_names'     => 'max:512',
        ];
    }
}
