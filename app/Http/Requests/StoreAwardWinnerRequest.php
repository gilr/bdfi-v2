<?php

namespace App\Http\Requests;

use App\Models\AwardCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Saisie des gagnants d'une année de prix (une ligne par catégorie du prix choisi, cf. AwardWinnersYear).
 * Règle par ligne : le champ "lauréat" (winners.*.name) doit être rempli dès qu'un autre champ de la même
 * ligne l'est (auteur, titre, titre original) ; sinon la ligne doit être entièrement vide.
 */
class StoreAwardWinnerRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasMemberRole();
    }

    public function rules()
    {
        return [
            'award_id' => ['required', 'integer', Rule::exists('awards', 'id')],
            'year'     => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],

            'winners'                   => ['nullable', 'array'],
            'winners.*.name'            => ['nullable', 'string', 'max:256'],
            'winners.*.title'           => ['nullable', 'string', 'max:256'],
            'winners.*.vo_title'        => ['nullable', 'string', 'max:256'],
            'winners.*.author1_id'      => ['nullable', 'integer', Rule::exists('authors', 'id')],
            'winners.*.author2_id'      => ['nullable', 'integer', Rule::exists('authors', 'id')],
            'winners.*.author3_id'      => ['nullable', 'integer', Rule::exists('authors', 'id')],
        ];
    }

    /**
     * Contrôle par catégorie (pas déclaratif : dépend des catégories réelles du prix choisi, retrouvées
     * côté serveur, jamais depuis le formulaire). Un identifiant de catégorie absent du formulaire (ligne
     * jamais affichée) équivaut à une ligne vide : rien à valider ni à enregistrer pour elle.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->has('award_id')) {
                return;   // prix invalide : pas de catégories fiables à contrôler
            }

            $categories = AwardCategory::where('award_id', $this->input('award_id'))->get(['id', 'name']);

            foreach ($categories as $category) {
                $row = (array) $this->input("winners.{$category->id}", []);
                $name = trim((string) ($row['name'] ?? ''));
                $otherFilled = filled($row['author1_id'] ?? null) || filled($row['author2_id'] ?? null)
                    || filled($row['author3_id'] ?? null) || trim((string) ($row['title'] ?? '')) !== ''
                    || trim((string) ($row['vo_title'] ?? '')) !== '';

                if ($name === '' && $otherFilled) {
                    $validator->errors()->add("winners.{$category->id}.name",
                        "« {$category->name} » : le champ « Attribué à » doit être rempli dès qu'un autre champ de la ligne "
                        . 'l\'est (auteur, titre...) — ou laissez toute la ligne vide si la catégorie n\'a pas été décernée.');
                }
            }
        });
    }
}
