<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCollectionRequest;
use App\Http\Requests\StoreCollectionRequest;
use Illuminate\Http\Request;
use App\Models\Collection;
use Illuminate\Support\Facades\Auth;
use App\Enums\QualityStatus;

class CollectionController extends Controller
{
    public $context = [
        'area'     => 'collections',
        'title'    => 'Collections',
        'icon'     => 'collection.png',
        'filament' => 'collections',
        'page'     => '',
        'digit'    => 1
    ];

    /**
     * Accueil de la zone
     */
    public function welcome()
    {
        $results = Collection::orderBy('updated_at', 'desc')->limit(25)->get();
        return view('front._generic.welcome', compact('results'), $this->context);
    }

    public function search(Request $request)
    {
        $pagin = 1000;
        $user = Auth::user();
        if ($user)
        {
            $pagin = $user->items_par_page;
        }

        $text = $request->input('s');
        $large = $request->input('m');
        if ($large !== "on")
        {
            $results = Collection::where(function($query) use($text) {
                $query->where('name', 'like', '%' . $text .'%');
            })->orderBy('name', 'asc')->simplePaginate($pagin);
        }
        else
        {
            $results = Collection::where(function($query) use($text) {
                $query->where('name', 'like', '%' . $text .'%')
                ->orWhere('alt_names', 'like', '%' . $text .'%');
            })->orderBy('name', 'asc')->simplePaginate($pagin);

        }

        // Pour l'instant, dans tous les cas on aiguille sur la page de choix
        $this->context['page'] = "recherche \"$text\"";
        return view ('front._generic.choix', compact('text', 'results','large'), $this->context);
    }

    /**
     * Index par initiale
     */
    public function index(Request $request, $initial)
    {
        $pagin = 1000;
        $user = Auth::user();
        if ($user)
        {
            $pagin = $user->items_par_page;
        }

        if ((strlen($initial) == 1) && ctype_alpha($initial))
        {
            $this->context['page'] = 'Index ' . strtoupper($initial);
            $results = Collection::where('name', 'like', $initial.'%')->orderBy('name', 'asc')->simplePaginate($pagin);
            return view('front._generic.index', compact('initial', 'results'), $this->context);
        }
        else if ((strlen($initial) == 1) && ctype_digit($initial))
        {
            $this->context['page'] = 'Index ' . strtoupper($initial);
            $results = Collection::whereBetween('name', ['0','9'])->orderBy('name', 'asc')->simplePaginate($pagin);
            return view('front._generic.index', compact('initial', 'results'), $this->context);
        }
        else
        {
            $request->session()->flash('warning', 'Vous semblez vous être perdu dans un lieu dédié aux initiales, mais avec une initiale non constituée d\'une lettre unique ("'.$initial.'" ?!). Vous avez pris des risques. Pour le coup, vous êtes renvoyé directement vers l\'accueil de la zone collections...');
            return redirect('collections');
        }
    }

    public function page(Request $request, $text)
    {
        if ($results=Collection::find($text))
        {
            // /collections/{id}
            // Un ID est passé - Pour l'instant c'est la façon propre d'afficher une page collection
            // TBD : Il faudra supprimer l'accès par Id au profit d'un slug => unicité
            $this->context['page'] = $results->name;
            return view ('front._generic.fiche', compact('results'), $this->context);
        }
        else if ((strlen($text) == 1) && ctype_alpha($text))
        {
            // /collections/{i}
            // Une caractère seul est passé  => on renvoit sur l'initiale
            $request->session()->flash('warning', 'L\'URL utilisée ("/collections/'.$text.'")ne correspond pas à l\'URL des index ("/collections/index/'.$text.'"), mais comme on est sympa, on a travaillé pour vous rediriger sur l\'index adéquat. Hop.');
            return redirect("collections/index/$text");
        }
        else
        {
            $pagin = 1000;
            $user = Auth::user();
            if ($user)
            {
                $pagin = $user->items_par_page;
            }
            // /collections/{pattern}
            // Recherche de toutes les collections avec le pattern fourni
            $results = Collection::where(function($query) use($text) {
                $query->where ('name', 'like', '%' . $text .'%')
                        ->orWhere('alt_names', 'like', '%' . $text .'%');
            })->orderBy('name', 'asc')->simplePaginate($pagin);

            if ($results->count() == 0) {
                // Aucun résultat, redirection vers l'accueil collections
                $request->session()->flash('warning', 'Le nom ou l\'extrait de nom demandé ("' . $text . '") n\'est pas trouvé. Vous avez été redirigé sur l\'accueil de la zone collections.');
                return redirect('collections');
            }
            else if($results->count() == 1)
            {
                // Un résultat unique, on redirige gentiment vers lui avec un éventuel avertissement
                $results = $results[0];
                if (strtolower($text) != strtolower($results->name)) {
                    $this->context['page'] = "/$text/ ⇒ $results->name";
                    $request->session()->flash('warning', 'Le nom demandé ("' . $text . '") n\'existe pas exactement sous cette forme. Mais comme chez BDFI on est cool, on a fouillé un peu et on vous a trouvé un résultat possible. Essayez quand même d\'indiquer un nom exact la prochaine fois, ou passez par le moteur de recherche.');
                }
                else {
                    $this->context['page'] = "$text";
                }
                return view ('front._generic.fiche', compact('results'), $this->context);
            }
            else
            {
                // Résultats multiples, on propose une page de choix
                $this->context['page'] = "/$text/";
                $large='';
                $request->session()->flash('warning', 'Le nom ou l\'extrait de nom demandé ("' . $text . '") n\'existe pas de façon unique. Nous vous redirigeons vers une page de choix en espérant que vous y trouviez votre bonheur. Utilisez de préférence notre moteur de recherche.');
                // Page de choix sur base du pattern fourni
                return view ('front._generic.choix', compact('text', 'results', 'large'), $this->context);
            }
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view ('admin.formulaires.creer_collection');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCollectionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCollectionRequest $request)
    {
        $validated = $request->validated();

        //$collection = new Collection;
        //$collection->name = $request->name;
        //$collection->save();
        $collection = Collection::create([
            'name' => $request->name,
            'shortname' => $request->shortname,
            'year_start' => $request->year_start,
            'type' => $request->type,
            'support' => $request->support,
            'quality' => QualityStatus::VIDE->value,
        ]);

        return view ('admin.formulaires.creer_collection', $this->context)->with('status', true)->with('id', $collection->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Collection  $collection
     * @return \Illuminate\Http\Response
     */
    public function edit(Collection $collection)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCollectionRequest  $request
     * @param  \App\Models\Collection  $collection
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCollectionRequest $request, Collection $collection)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Collection  $collection
     * @return \Illuminate\Http\Response
     */
    public function destroy(Collection $collection)
    {
        //
    }
}
