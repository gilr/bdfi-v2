<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CollectionType;
use App\Enums\CollectionSupport;
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
            'shortname'     => 'required|max:128',
            'year_start'    => 'required|numeric',
            'type'          => [Rule::enum(CollectionType::class)],
            'support'       => [Rule::enum(CollectionSupport::class)]
        ];
    }
}
