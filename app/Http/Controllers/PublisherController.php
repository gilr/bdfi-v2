<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePublisherRequest;
use App\Http\Requests\StorePublisherRequest;
use Illuminate\Http\Request;
use App\Models\Publication;
use App\Models\Publisher;

class PublisherController extends Controller
{
    public $context = [
        'area'     => 'editeurs',
        'title'    => 'Editeurs',
        'icon'     => 'editeurs.png',
        'filament' => 'publishers',
        'page'     => '',
        'digit'    => 1
    ];

    /**
     * Accueil de la zone éditeurs : /editeurs
     */
    public function welcome()
    {
        // TODO : retrait temporaire des "in" qui devraient disparaître
        $results = Publisher::where('name', 'not like', '%<b>in</b>%')->orderBy('updated_at', 'desc')->limit(25)->get();
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
            $results = Publisher::where(function($query) use($text) {
                $query->where('name', 'like', '%' . $text .'%');
            })->orderBy('name', 'asc')->simplePaginate($pagin);
        }
        else
        {
            $results = Publisher::where(function($query) use($text) {
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
            $results = Publisher::where('name', 'like', $initial.'%')->orderBy('name', 'asc')->simplePaginate($pagin);
            return view('front._generic.index', compact('initial', 'results'), $this->context);
        }
        else if ((strlen($initial) == 1) && ctype_digit($initial))
        {
            $this->context['page'] = 'Index 0-9';
            $results = Publisher::whereBetween('name', ['0','9'])->orderBy('name', 'asc')->simplePaginate($pagin);
            return view('front._generic.index', compact('initial', 'results'), $this->context);
        }
        else
        {
            $request->session()->flash('warning', 'Vous semblez vous être perdu dans un lieu dédié aux initiales, mais avec une initiale non constituée d\'une lettre unique ("'.$initial.'" ?!). Vous avez pris des risques. Pour le coup, vous êtes renvoyé directement vers l\'accueil de la zone éditeurs...');
            return redirect('editeurs');
        }
    }
    public function page(Request $request, $text)
    {
        if ($results=Publisher::find($text))
        {
            // /editeurs/{id}
            // Un ID est passé - Pour l'instant c'est la façon propre d'afficher une page éditeur
            // TBD : Il faudra supprimer l'accès par Id au profit d'un slug => unicité
            $this->context['page'] = $results->name;
            $publications = Publication::where('publisher_id', $results->id)->get()->random(fn ($items) => min(10, count($items)))->shuffle();
            return view ('front._generic.fiche', compact('results', 'publications'), $this->context);
        }
        else if ((strlen($text) == 1) && ctype_alpha($text))
        {
            // /éditeurs/{i}
            // Une caractère seul est passé  => on renvoit sur l'initiale
            $request->session()->flash('warning', 'L\'URL utilisée ("/editeurs/'.$text.'")ne correspond pas à l\'URL des index ("/editeurs/index/'.$text.'"), mais comme on est sympa, on a travaillé pour vous rediriger sur l\'index adéquat. Hop.');
            return redirect("editeurs/index/$text");
        }
        else
        {
            $pagin = 1000;
            $user = Auth::user();
            if ($user)
            {
                $pagin = $user->items_par_page;
            }
            // /editeurs/{pattern}
            // Recherche de tous les éditeurs avec le pattern fourni
            $results = Publisher::where(function($query) use($text) {
                $query->where ('name', 'like', '%' . $text .'%')
                        ->orWhere('alt_names', 'like', '%' . $text .'%');
            })->orderBy('name', 'asc')->simplePaginate($pagin);

            if ($results->total() == 0) {
                // Aucun résultat, redirection vers l'accueil éditeurs
                $request->session()->flash('warning', 'Le nom ou l\'extrait de nom demandé ("' . $text . '") n\'est pas trouvé. Vous avez été redirigé sur l\'accueil de la zone éditeurs.');
                return redirect('editeurs');
            }
            else if($results->total() == 1)
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
                $publications = Publication::where('publisher_id', $results->id)->get()->random(fn ($items) => min(10, count($items)))->shuffle();
                return view ('front._generic.fiche', compact('results', 'publications'), $this->context);
            }
            else
            {
                // Résultats multiples, on propose une page de choix
                $this->context['page'] = "/$text/";
                $large = "";
                $request->session()->flash('warning', 'Le nom ou l\'extrait de nom demandé ("' . $text . '") n\'existe pas de façon unique. Nous vous redirigeons vers une page de choix en espérant que vous y trouviez votre bonheur. Utilisez de préférence notre moteur de recherche.');
                return view ('front._generic.choix', compact('text', 'results', 'large'), $this->context);
            }
        }
    }
    public function hc(Request $request, $text)
    {
        $publisher = Publisher::find($text);
        $results = Publisher::find($text)->publicationsWithoutCollection()->get();
        return view ('front.editeurs.hc', compact('publisher', 'results'), $this->context);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePublisherRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePublisherRequest $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Publisher  $publisher
     * @return \Illuminate\Http\Response
     */
    public function edit(Publisher $publisher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePublisherRequest  $request
     * @param  \App\Models\Publisher  $publisher
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePublisherRequest $request, Publisher $publisher)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Publisher  $publisher
     * @return \Illuminate\Http\Response
     */
    public function destroy(Publisher $publisher)
    {
        //
    }
}
