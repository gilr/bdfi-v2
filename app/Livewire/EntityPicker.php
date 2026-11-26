<?php

namespace App\Livewire;

use App\Models\Author;
use App\Models\Collection;
use App\Models\Publisher;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

/**
 * Sélecteur réutilisable pour un formulaire classique (non-Livewire) : recherche parmi un modèle,
 * sélection d'un résultat, ou (sauf en mode $selectOnly) saisie libre d'un nom pour une création à la volée.
 *
 * Rend deux champs cachés dans le <form> englobant :
 *  - "{fieldName}_id"   : id de l'entité existante sélectionnée, vide sinon
 *  - "{fieldName}_name" : nom saisi quand rien n'est sélectionné (candidat à une création), vide sinon
 * Au contrôleur de décider : id renseigné => rattacher l'existant ; sinon nom renseigné => créer
 * (avec les valeurs par défaut adaptées à ce modèle) ; les deux vides => rien à lier pour l'instant.
 * En mode $selectOnly (ex. auteurs d'un prix) : seule une entité EXISTANTE peut être choisie, "_name"
 * n'est jamais utilisé par le contrôleur, et le message d'absence de résultat ne parle pas de création.
 *
 * Liste blanche des modèles utilisables, par sécurité (cf. l'ancien DynamicSelect, qui ne l'avait pas) :
 * ajouter une entrée ici pour un nouveau modèle (titre, cycle...).
 */
class EntityPicker extends Component
{
    /** @var array<class-string, string[]> colonnes utilisées pour la recherche ("like") et pour composer le libellé */
    private const ALLOWED_MODELS = [
        Publisher::class  => ['name'],
        Author::class     => ['first_name', 'name'],   // libellé composé : "Prénom Nom"
        Collection::class => ['name'],
    ];

    public string $model;
    public string $fieldName;
    public string $idFieldName;
    public string $nameFieldName;
    public string $label;
    public string $placeholder = 'Rechercher par nom…';
    public string $noun = 'un élément';           // ex. "un éditeur" (genre inclus), pour le message de création
    public bool $selectOnly = false;              // true : pas de création à la volée, choix parmi l'existant seulement
    public string $search = '';
    public ?int $selectedId = null;
    public ?string $selectedLabel = null;

    /** @var array<int, array{id: int, label: string}> */
    public array $results = [];

    public function mount(string $model, string $fieldName, string $label, ?string $placeholder = null, ?string $noun = null,
        bool $selectOnly = false, mixed $oldId = null, mixed $oldName = null,
        ?string $idFieldName = null, ?string $nameFieldName = null): void
    {
        if (!array_key_exists($model, self::ALLOWED_MODELS)) {
            throw new \InvalidArgumentException("EntityPicker : modèle non autorisé « {$model} ».");
        }

        $this->model = $model;
        $this->fieldName = $fieldName;
        // Cas général (fieldName plat, ex. "publisher") : "{fieldName}_id"/"_name" suffisent.
        // Cas d'un champ de tableau (ex. winners[12][author1_id]) : le crochet fermant doit être DANS
        // le nom, donc le fieldName seul (potentiellement entrouvert) ne suffit pas ; l'appelant précise alors
        // idFieldName/nameFieldName explicitement plutôt que de les faire déduire par simple concaténation.
        $this->idFieldName = $idFieldName ?? "{$fieldName}_id";
        $this->nameFieldName = $nameFieldName ?? "{$fieldName}_name";
        $this->label = $label;
        $this->placeholder = $placeholder ?? $this->placeholder;
        $this->noun = $noun ?? $this->noun;
        $this->selectOnly = $selectOnly;

        // Après une erreur de validation (old()) : la sélection ou la saisie précédente est restaurée
        if (filled($oldId) && ctype_digit((string) $oldId)) {
            $entity = $model::find((int) $oldId);
            if ($entity) {
                $this->selectedId = $entity->id;
                $this->selectedLabel = $this->label($entity);
            }
        } elseif (filled($oldName)) {
            $this->search = (string) $oldName;
            // Relance tout de suite la recherche (ex. après un refus de doublon : le résultat existant
            // redevient cliquable sans que l'utilisateur ait besoin de retaper un caractère)
            $this->results = $this->searchResults($this->search);
        }
    }

    /** @return string[] */
    private function searchColumns(): array
    {
        return self::ALLOWED_MODELS[$this->model];
    }

    /** Libellé affiché : une seule colonne ("Nom"), ou plusieurs mises bout à bout ("Prénom Nom") */
    private function label(Model $entity): string
    {
        return trim(implode(' ', array_map(fn ($c) => (string) $entity->{$c}, $this->searchColumns())));
    }

    /** @return array<int, array{id: int, label: string}> */
    private function searchResults(string $term): array
    {
        $term = trim($term);

        if (mb_strlen($term) < 2) {
            return [];
        }

        $columns = $this->searchColumns();
        $model = $this->model;
        // Chaque mot doit se trouver dans l'une des colonnes : "Tad Williams" trouve prénom "Tad" + nom "Williams"
        $words = preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY);

        return $model::where(function ($q) use ($columns, $words) {
                foreach ($words as $word) {
                    $q->where(function ($q) use ($columns, $word) {
                        foreach ($columns as $c) {
                            $q->orWhere($c, 'like', '%' . $word . '%');
                        }
                    });
                }
            })
            ->orderBy($columns[count($columns) - 1])   // nom de famille en dernier pour Author : tri par nom
            ->limit(10)->get(['id', ...$columns])
            ->map(fn ($r) => ['id' => $r->id, 'label' => $this->label($r)])
            ->all();
    }

    public function updatedSearch(): void
    {
        $this->selectedId = null;
        $this->selectedLabel = null;
        $this->results = $this->searchResults($this->search);
    }

    public function select(int $id): void
    {
        $entity = ($this->model)::find($id);

        if ($entity) {
            $this->selectedId = $entity->id;
            $this->selectedLabel = $this->label($entity);
        }

        $this->results = [];
    }

    public function clear(): void
    {
        $this->selectedId = null;
        $this->selectedLabel = null;
        $this->search = '';
        $this->results = [];
    }

    public function render()
    {
        return view('livewire.entity-picker');
    }
}
