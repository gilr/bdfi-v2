<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reprint;
use App\Http\Requests\StoreReprintRequest;
use App\Http\Requests\UpdateReprintRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use DB;

class ReprintController extends Controller
{
    public $context = [
        'area'     => 'retirages',
        'title'    => 'Retirages',
        'icon'     => 'livre2.png',
        'filament' => 'reprints',
        'page'     => '',
        'digit'    => 0
    ];

    public function welcome()
    {
        $results = Reprint::orderBy('updated_at', 'desc')->limit(25)->get();
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
            $results = Reprint::select('reprints.*')
                ->leftJoin('publications', 'reprints.publication_id', '=', 'publications.id')
                ->where('reprints.ai', 'like', '%' . $text .'%')
                ->orwhere('publications.name', 'like', '%' . $text .'%')
                ->orderBy('publications.name', 'asc')
                ->simplePaginate($pagin)
                ->withQueryString();
        }
        else
        {
            $results = Reprint::select('reprints.*')
                ->leftJoin('publications', 'reprints.publication_id', '=', 'publications.id')
                ->where('reprints.ai', 'like', '%' . $text .'%')
                ->orwhere('publications.name', 'like', '%' . $text .'%')
                ->orwhere('publications.cycle', 'like', '%' . $text .'%')
                ->orderBy('publications.name', 'asc')
                ->simplePaginate($pagin)
                ->withQueryString();
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

        $this->context['page'] = 'Index ' . strtoupper($initial);
        if ((strlen($initial) == 1) && ctype_alpha($initial))
        {
            // La solution suivante fonctionne aussi... mais donne l'id de la publication donc à affiner
            /*
            $results = Reprint::join('publications', 'publication_id', '=', 'publications.id')
                ->where('name', 'like', $initial.'%')
                ->orderBy('name', 'asc')
                ->simplePaginate($pagin)->withQueryString();
            */
            $results = Reprint::whereHas('publication', function($q) use ($initial) {
                    $q->where('name', 'like', $initial.'%')
                    ->orderBy('name', 'asc');
                })->simplePaginate($pagin)->withQueryString();

            return view('front._generic.index', compact('initial', 'results'), $this->context);
        }
        else
        {
            $request->session()->flash('warning', 'Vous semblez vous être perdu dans un lieu dédié aux initiales, mais avec une initiale non constituée d\'une lettre unique ("'.$initial.'" ?!). Vous avez pris des risques. Pour le coup, vous êtes renvoyé directement vers l\'accueil de la zone ouvrages...');
            return redirect('ouvrages');
        }
    }

    public function page(Request $request, $text)
    {
        if ($results=Reprint::find($text))
        {
            // /ouvrages/{id}
            // Un ID est passé - Pour l'instant c'est la façon propre d'afficher une page ouvrage
            // TBD : Il faudra supprimer l'accès par Id au profit d'un slug => unicité
            $this->context['page'] = $results->name;
            return view ('front._generic.fiche', compact('results'), $this->context);
        }
        else if ((strlen($text) == 1) && ctype_alpha($text))
        {
            // /ouvrages/{i}
            // Une caractère seul est passé  => on renvoit sur l'initiale
            $request->session()->flash('warning', 'L\'URL utilisée ("/ouvrages/'.$text.'")ne correspond pas à l\'URL des index ("/ouvrages/index/'.$text.'"), mais comme on est sympa, on a travaillé pour vous rediriger sur l\'index adéquat. Hop.');
            return redirect("ouvrages/index/$text");
        }
        else
        {
            $pagin = 1000;
            $user = Auth::user();
            if ($user)
            {
                $pagin = $user->items_par_page;
            }

            // /ouvrages/{pattern}
            // Recherche de tous les ouvrages avec le pattern fourni
            $results = Reprint::select('reprints.*')
                ->leftJoin('publications', 'reprints.publication_id', '=', 'publications.id')
                ->where('reprints.ai', 'like', '%' . $text .'%')
                ->orwhere('publications.name', 'like', '%' . $text .'%')
                ->orderBy('publications.name', 'asc')
                ->simplePaginate($pagin)
                ->withQueryString();

            if ($results->count() == 0) {
                // Aucun résultat, redirection vers l'accueil ouvrages
                $request->session()->flash('warning', 'Le nom ou l\'extrait de nom demandé ("' . $text . '") n\'est pas trouvé. Vous avez été redirigé sur l\'accueil de la zone ouvrages.');
                return redirect('ouvrages');
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
                $request->session()->flash('warning', 'Le nom ou l\'extrait de nom demandé ("' . $text . '") n\'existe pas de façon unique. Nous vous redirigeons vers une page de choix en espérant que vous y trouviez votre bonheur. Utilisez de préférence notre moteur de recherche.');
                // Page de choix sur base du pattern fourni
                $large = 'off';
                return view ('front._generic.choix', compact('text', 'results','large'), $this->context);
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreReprintRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreReprintRequest $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Reprint  $reprint
     * @return \Illuminate\Http\Response
     */
    public function edit(Reprint $reprint)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateReprintRequest  $request
     * @param  \App\Models\Reprint  $reprint
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateReprintRequest $request, Reprint $reprint)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Reprint  $reprint
     * @return \Illuminate\Http\Response
     */
    public function destroy(Reprint $reprint)
    {
        //
    }
}
