<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Collection;

/*
 * Bibliothèque utilisateur, onglet "Gestion" : ajout du suivi d'une collection
 * par recherche (extrait de nom) puis sélection directe dans les résultats.
 */
class BiblioCollectionAdd extends Component
{
    public $search = ''; // Extrait saisi par l'utilisateur

    // Nombre maximum de résultats affichés (au-delà : affiner la recherche)
    const MAX_RESULTS = 20;

    public function add($collectionId)
    {
        $user = Auth::user();
        $collection = Collection::find($collectionId);

        if (!$user || !$collection) {
            return;
        }

        $alreadyFollowed = DB::table('user_collection')
            ->where('user_id', $user->id)
            ->where('collection_id', $collection->id)
            ->exists();

        if ($alreadyFollowed) {
            session()->flash('flash.banner', 'La collection "' . $collection->name . '" est déjà suivie.');
            session()->flash('flash.bannerStyle', 'danger');
        }
        else {
            DB::table('user_collection')->insert([
                'status' => "en_cours", // BiblioCollectionStatus
                'user_id' => $user->id,
                'collection_id' => $collection->id,
            ]);
            session()->flash('flash.banner', 'Suivi de la collection "' . $collection->name . '" ajouté.');
            session()->flash('flash.bannerStyle', 'success');
        }

        // Rechargement de la page pour mettre à jour la liste des collections suivies
        return redirect('/user/gestion-biblio');
    }

    public function render()
    {
        $collections = collect();
        $more = false;
        $followed = [];

        if (mb_strlen(trim($this->search)) >= 2)
        {
            $text = trim($this->search);
            $collections = Collection::where(function ($query) use ($text) {
                    $query->where('name', 'like', "%{$text}%")
                        ->orWhere('alt_names', 'like', "%{$text}%");
                })
                ->orderBy('name', 'asc')
                ->with('publisher')
                ->limit(self::MAX_RESULTS + 1)
                ->get();

            $more = $collections->count() > self::MAX_RESULTS;
            $collections = $collections->take(self::MAX_RESULTS);

            $followed = DB::table('user_collection')
                ->where('user_id', Auth::id())
                ->pluck('collection_id')
                ->all();
        }

        return view('livewire.biblio-collection-add', [
            'collections' => $collections,
            'more' => $more,
            'followed' => $followed,
        ]);
    }
}
