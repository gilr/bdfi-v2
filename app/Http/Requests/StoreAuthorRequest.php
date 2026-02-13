<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\AuthorGender;
use Illuminate\Validation\Rule;

class StoreAuthorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
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
            'first_name'    => 'required|max:32',
            'pays'          => Rule::in(['France', 'Canada', 'Etats-Unis', 'Royaume Uni', '?']),
            'gender'        => [Rule::enum(AuthorGender::class)],
            'birth_date'    => ['required', 'size:10', 'regex:/[\-012][\-0-9]{3}-([0-9]{2}-[0-9]{2}|circa)/']
        ];
    }
}
