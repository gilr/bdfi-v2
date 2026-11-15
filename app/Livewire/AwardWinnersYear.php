<?php

namespace App\Livewire;

use App\Models\Award;
use App\Models\AwardCategory;
use App\Models\AwardWinner;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Formulaire "Ajout rapide : gagnants d'une année de prix" : choix d'un prix et d'une année, puis
 * affichage d'une ligne par catégorie de ce prix. Les champs texte (lauréat, titre, titre original)
 * et les trois sélecteurs d'auteur (EntityPicker, mode "sélection seule") sont rendus dans le <form>
 * englobant, classique (non-Livewire) ; ce composant ne pilote que le choix du prix/de l'année et la
 * liste des catégories qui en résulte.
 *
 * Noms des champs soumis, par catégorie (id = AwardCategory::id) :
 *   winners[{id}][name|title|vo_title]            (texte)
 *   winners[{id}][author1_id|author2_id|author3_id] (EntityPicker, "_id" uniquement : mode sélection seule)
 */
class AwardWinnersYear extends Component
{
    public ?int $awardId = null;
    public ?int $year = null;

    /** @var \Illuminate\Support\Collection<int, AwardCategory> catégories du prix choisi, dans leur ordre */
    public Collection $categories;

    /** @var array<int, array{name: string, title: string, vo_title: string, author_id: ?int, author2_id: ?int, author3_id: ?int}> par id de catégorie : valeurs déjà en base pour cette année (édition) */
    public array $existing = [];

    public function mount(mixed $oldAwardId = null, mixed $oldYear = null): void
    {
        $this->categories = collect();

        if (filled($oldAwardId) && ctype_digit((string) $oldAwardId)) {
            $this->awardId = (int) $oldAwardId;
        }
        if (filled($oldYear) && ctype_digit((string) $oldYear)) {
            $this->year = (int) $oldYear;
        }

        $this->refresh();
    }

    public function updatedAwardId(): void
    {
        $this->refresh();
    }

    public function updatedYear(): void
    {
        $this->refresh();
    }

    private function refresh(): void
    {
        if (!$this->awardId || !$this->year) {
            $this->categories = collect();
            $this->existing = [];

            return;
        }

        $this->categories = AwardCategory::where('award_id', $this->awardId)
            ->orderBy('internal_order')->orderBy('name')->get();

        $this->existing = AwardWinner::where('year', $this->year)
            ->whereIn('award_category_id', $this->categories->pluck('id'))
            ->get()
            ->keyBy('award_category_id')
            ->map(fn (AwardWinner $w) => [
                'name' => (string) $w->name,
                'title' => (string) $w->title,
                'vo_title' => (string) $w->vo_title,
                'author_id' => $w->author_id,
                'author2_id' => $w->author2_id,
                'author3_id' => $w->author3_id,
            ])
            ->all();
    }

    /** Champ texte d'une catégorie (name/title/vo_title) : saisie précédente (erreur) > existant (édition) > vide */
    public function fieldValue(int $categoryId, string $field): string
    {
        return (string) old("winners.{$categoryId}.{$field}", $this->existing[$categoryId][$field] ?? '');
    }

    /** Id d'auteur d'une catégorie (author_id/author2_id/author3_id) : saisie précédente (erreur) > existant > aucun */
    public function authorValue(int $categoryId, string $field): ?int
    {
        $value = old("winners.{$categoryId}.{$field}", $this->existing[$categoryId][$field] ?? null);

        return filled($value) && ctype_digit((string) $value) ? (int) $value : null;
    }

    public function render()
    {
        return view('livewire.award-winners-year', [
            'awards' => Award::orderBy('name')->get(['id', 'name']),
            'years' => $this->yearsRange(),
        ]);
    }

    /** Mêmes bornes que AwardController::annees() (les quelques années sans attribution n'existent pas) */
    private function yearsRange(): array
    {
        return array_reverse(array_merge(range(1927, 1933), range(1951, (int) date('Y'))));
    }
}
