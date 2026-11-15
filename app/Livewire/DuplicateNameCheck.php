<?php

namespace App\Livewire;

use Livewire\Component;

/**
 * Avertissement en direct (avant l'envoi) : le nom tapé (éventuellement avec un second champ, ex. le
 * prénom pour un auteur, auquel cas le doublon porte sur la PAIRE) correspond-il déjà à un enregistrement
 * existant ? Rend lui-même la ou les lignes "label + champ" correspondantes dans le <form> englobant,
 * classique (non-Livewire), sur le même principe qu'EntityPicker.
 *
 * Contrairement à EntityPicker (qui sert à CHOISIR une entité existante pour un AUTRE formulaire, donc
 * bloque un nom en double), ce composant sert seulement à prévenir avant la création de l'entité
 * elle-même : il n'empêche rien. Au contrôleur de garder la main sur l'homonymie volontaire (liste des
 * doublons + case "créer quand même" à l'envoi).
 *
 * Liste blanche des modèles utilisables, par sécurité (même principe qu'EntityPicker) : ajouter une
 * entrée ici pour un nouveau modèle.
 */
class DuplicateNameCheck extends Component
{
    private const ALLOWED_MODELS = [
        \App\Models\Publisher::class,
        \App\Models\Collection::class,
        \App\Models\Author::class,
    ];

    // Champ principal
    public string $model;
    public string $fieldName;
    public string $label;
    public string $column;
    public string $noun;                  // ex. "un éditeur nommé" (genre et "nommé(e)" inclus)
    public string $help = '';
    public string $placeholder = '';
    public string $cssClass = 'w-full border rounded bg-yellow-100 max-w-xl';
    public int $maxlength = 128;
    public bool $required = true;
    public bool $uppercaseWarning = false;   // avertissement si saisi tout en majuscules (ex. noms d'auteur)

    public string $value = '';

    // Champ secondaire optionnel (ex. prénom pour un auteur) : le doublon porte alors sur la PAIRE
    // (second champ vide équivalent à NULL ou '', comme le contrôle serveur correspondant)
    public ?string $secondFieldName = null;
    public ?string $secondLabel = null;
    public ?string $secondColumn = null;
    public string $secondHelp = '';
    public string $secondPlaceholder = '';
    public string $secondCssClass = 'w-full border rounded bg-yellow-100 max-w-xl';
    public int $secondMaxlength = 128;

    public string $secondValue = '';

    public ?string $frontLink = null;     // ex. "/editeurs/" (préfixe d'URL + slug)

    /** @var array{id: int, label: string, slug: ?string}|null */
    public ?array $match = null;

    public function mount(
        string $model, string $fieldName, string $label, string $column, string $noun,
        ?string $help = null, ?string $placeholder = null, ?string $cssClass = null,
        int $maxlength = 128, bool $required = true, bool $uppercaseWarning = false,
        ?string $secondFieldName = null, ?string $secondLabel = null, ?string $secondColumn = null,
        ?string $secondHelp = null, ?string $secondPlaceholder = null, ?string $secondCssClass = null,
        int $secondMaxlength = 128,
        ?string $frontLink = null,
        mixed $oldValue = null, mixed $oldSecondValue = null,
    ): void {
        if (!in_array($model, self::ALLOWED_MODELS, true)) {
            throw new \InvalidArgumentException("DuplicateNameCheck : modèle non autorisé « {$model} ».");
        }

        $this->model = $model;
        $this->fieldName = $fieldName;
        $this->label = $label;
        $this->column = $column;
        $this->noun = $noun;
        $this->help = $help ?? '';
        $this->placeholder = $placeholder ?? '';
        $this->cssClass = $cssClass ?? $this->cssClass;
        $this->maxlength = $maxlength;
        $this->required = $required;
        $this->uppercaseWarning = $uppercaseWarning;

        $this->secondFieldName = $secondFieldName;
        $this->secondLabel = $secondLabel;
        $this->secondColumn = $secondColumn;
        $this->secondHelp = $secondHelp ?? '';
        $this->secondPlaceholder = $secondPlaceholder ?? '';
        $this->secondCssClass = $secondCssClass ?? $this->secondCssClass;
        $this->secondMaxlength = $secondMaxlength;

        $this->frontLink = $frontLink;

        // Après une erreur de validation (old()) : la saisie précédente est restaurée, doublon revérifié
        if (filled($oldValue)) {
            $this->value = (string) $oldValue;
        }
        if (filled($oldSecondValue)) {
            $this->secondValue = (string) $oldSecondValue;
        }
        if ($this->value !== '') {
            $this->check();
        }
    }

    public function updatedValue(): void
    {
        $this->check();
    }

    public function updatedSecondValue(): void
    {
        $this->check();
    }

    /** Majuscules intégrales (ex. "MASTERTON" au lieu de "Masterton") : avertissement, pas de blocage */
    public function getIsUppercaseProperty(): bool
    {
        $v = trim($this->value);

        return $this->uppercaseWarning && mb_strlen($v) > 1 && $v === mb_strtoupper($v) && $v !== mb_strtolower($v);
    }

    private function check(): void
    {
        $name = trim($this->value);

        if ($name === '') {
            $this->match = null;

            return;
        }

        $model = $this->model;
        $query = $model::where($this->column, $name);

        if ($this->secondColumn) {
            $second = trim($this->secondValue);
            $query->where(fn ($q) => $second === ''
                ? $q->whereNull($this->secondColumn)->orWhere($this->secondColumn, '')
                : $q->where($this->secondColumn, $second));
        }

        $columns = array_values(array_unique(array_filter(['id', 'slug', $this->column, $this->secondColumn])));
        $entity = $query->first($columns);

        if (!$entity) {
            $this->match = null;

            return;
        }

        $label = $this->secondColumn
            ? trim(($entity->{$this->secondColumn} ?? '') . ' ' . $entity->{$this->column})
            : $entity->{$this->column};

        $this->match = ['id' => $entity->id, 'label' => $label, 'slug' => $entity->slug ?? null];
    }

    public function render()
    {
        return view('livewire.duplicate-name-check');
    }
}
