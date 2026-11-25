<?php

namespace App\Livewire\Concerns;

use App\Models\Publication;

/**
 * Recherche d'une publication par extrait de titre ou d'ISBN, pour les formulaires de modification
 * et de vérification. Le composant peut restreindre la recherche en surchargeant searchableQuery().
 */
trait SearchesPublications
{
    public $search = '';
    public $publications = [];

    public function updatedSearch()
    {
        $text = trim($this->search);
        if (mb_strlen($text) < 2) {
            $this->publications = [];

            return;
        }

        $isbn = str_replace(['-', ' '], '', $text);

        $this->publications = $this->searchableQuery()
            ->where(function ($q) use ($text, $isbn) {
                $q->where('name', 'like', "%{$text}%");
                if (ctype_digit($isbn) && strlen($isbn) >= 4) {
                    $q->orWhereRaw("REPLACE(isbn, '-', '') LIKE ?", ["%{$isbn}%"]);
                }
            })
            ->orderBy('name')->orderBy('approximate_parution')
            ->limit(24)
            ->get();
    }

    protected function searchableQuery()
    {
        return Publication::with('publisher');
    }
}
