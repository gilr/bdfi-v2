<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCycleRequest;
use App\Http\Requests\StoreCycleRequest;
use Illuminate\Http\Request;
use App\Models\Cycle;

class CycleController extends Controller
{
    public $context = [
        'area'     => 'series',
        'title'    => 'Cycles, séries',
        'icon'     => 'series.png',
        'filament' => 'cycles',
        'page'     => '',
        'digit'    => 1
    ];

    /**
     * Accueil de la zone
     */
    public function welcome()
    {
        $results = Cycle::orderBy('updated_at', 'desc')->limit(25)->get();
        return view('front._generic.welcome', compact('results'), $this->context);
    }

    public function search(Request $request)
    {
        $text = $request->input('s');
        $large = $request->input('m');
        if ($large !== "on")
        {
            $results = Cycle::where(function($query) use($text) {
                $query->where('name', 'like', '%' . $text .'%');
            })->orderBy('name', 'asc')->paginate(60);
        }
        else
        {
            $results = Cycle::where(function($query) use($text) {
                $query->where('name', 'like', '%' . $text .'%')
                ->orWhere('alt_names', 'like', '%' . $text .'%')
                ->orWhere('vo_names', 'like', '%' . $text .'%');
            })->orderBy('name', 'asc')->paginate(60);

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
        if ((strlen($initial) == 1) && ctype_alpha($initial))
        {
            $this->context['page'] = 'Index ' . strtoupper($initial);
            $results = Cycle::where('name', 'like', $initial.'%')->orderBy('name', 'asc')->simplePaginate(800);
            return view('front._generic.index', compact('initial', 'results'), $this->context);
        }
        else if ((strlen($initial) == 1) && ctype_digit($initial))
        {
            $this->context['page'] = 'Index 0-9';
            $results = Cycle::whereBetween('name', ['0','9'])->orderBy('name', 'asc')->simplePaginate(800);
            return view('front._generic.index', compact('initial', 'results'), $this->context);
        }
        else
        {
            $request->session()->flash('warning', 'Vous semblez vous être perdu dans un lieu dédié aux initiales, mais avec une initiale non constituée d\'une lettre unique ("'.$initial.'" ?!). Vous avez pris des risques. Pour le coup, vous êtes renvoyé directement vers l\'accueil de la zone series...');
            return redirect('series');
        }
    }

    public function page(Request $request, $text)
    {
        if ($results=Cycle::find($text))
        {
            // /series/{id}
            // Un ID est passé - Pour l'instant c'est la façon propre d'afficher une page serie
            // TBD : Il faudra supprimer l'accès par Id au profit d'un slug => unicité
            $this->context['page'] = $results->name;
            return view ('front._generic.fiche', compact('results'), $this->context);
        }
        else if ((strlen($text) == 1) && ctype_alpha($text))
        {
            // /series/{i}
            // Une caractère seul est passé  => on renvoit sur l'initiale
            $request->session()->flash('warning', 'L\'URL utilisée ("/series/'.$text.'")ne correspond pas à l\'URL des index ("/series/index/'.$text.'"), mais comme on est cool, on a un peu bossé pour vous rediriger sur ce qui semble le bon index.');
            return redirect("series/index/$text");
        }
        else
        {
            // /series/{pattern}
            // Recherche de tous les series avec le pattern fourni
            $results = Cycle::where(function($query) use($text) {
                $query->where ('name', 'like', '%' . $text .'%')
                        ->orWhere('alt_names', 'like', '%' . $text .'%');
            })->orderBy('name', 'asc')->paginate(60);

            if ($results->isEmpty()) {
                // Aucun résultat, redirection vers l'accueil series
                $request->session()->flash('warning', 'Le nom ou l\'extrait de nom demandé ("' . $text . '") n\'est pas trouvé. Vous avez été redirigé sur l\'accueil de la zone series.');
                return redirect('series');
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
                return view ('front._generic.fiche', compact('results'), $this->context);
            }
            else
            {
                // Résultats multiples, on propose une page de choix
                $this->context['page'] = "/$text/";
                $request->session()->flash('warning', 'La série ou l\'extrait de titre de série demandé ("' . $text . '") n\'existe pas de façon unique. Nous vous redirigeons vers une page de choix en espérant que vous y trouviez votre bonheur. Utilisez de préférence notre moteur de recherche ou les index.');
                // Page de choix sur base du pattern fourni
                return view ('front._generic.choix', compact('text', 'results'), $this->context);
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
     * @param  \App\Http\Requests\StoreCycleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCycleRequest $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Cycle  $cycle
     * @return \Illuminate\Http\Response
     */
    public function edit(Cycle $cycle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCycleRequest  $request
     * @param  \App\Models\Cycle  $cycle
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCycleRequest $request, Cycle $cycle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cycle  $cycle
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cycle $cycle)
    {
        //
    }
}
