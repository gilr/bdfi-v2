<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public $context = [
        'area'     => 'evenements',
        'title'    => 'Salons et évènements',
        'icon'     => 'festival.png',
        'filament' => 'events',
        'page'     => '',
        'digit'    => 1
    ];

    public function welcome()
    {
        $results = Event::orderBy('updated_at', 'desc')->limit(25)->get();
        // A FAIRE - TO DO - TBD - liste des actifs en plus (-> sumenu subview)
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
            $results = Event::where(function($query) use($text) {
                $query->where('name', 'like', '%' . $text .'%');
            })->orderBy('name', 'asc')->simplePaginate($pagin);
        }
        else
        {
            $results = Event::where(function($query) use($text) {
                $query->where('name', 'like', '%' . $text .'%')
                ->orWhere('place', 'like', '%' . $text .'%')
                ->orWhere('information', 'like', '%' . $text .'%');
            })->orderBy('name', 'asc')->simplePaginate($pagin);

        }

        // Pour l'instant, dans tous les cas on aiguille sur la page de choix
        $this->context['page'] = "recherche \"$text\"";
        return view ('front._generic.choix', compact('text', 'results','large'), $this->context);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
            $results = Event::where('name', 'like', $initial.'%')->orderBy('name', 'asc')->simplePaginate($pagin);
            return view('front._generic.index', compact('initial', 'results'), $this->context);
        }
        else if ((strlen($initial) == 1) && ctype_digit($initial))
        {
            $this->context['page'] = 'Index 0-9';
            $results = Event::whereBetween('name', ['0','9'])->orderBy('name', 'asc')->simplePaginate($pagin);
            return view('front._generic.index', compact('initial', 'results'), $this->context);
        }
        else
        {
            $request->session()->flash('warning', 'Vous semblez vous être perdu dans un lieu dédié aux initiales, mais avec une initiale non constituée d\'une lettre unique ("'.$initial.'" ?!). Vous avez pris des risques. Pour le coup, vous êtes renvoyé directement vers l\'accueil de la zone evenements...');
            return redirect('evenements');
        }
    }

    public function page(Request $request, $text)
    {
        if ($results=Event::find($text))
        {
            // /evenements/{id}
            // Un ID est passé - Pour l'instant c'est la façon propre d'afficher une page evenement
            // TBD : Il faudra supprimer l'accès par Id au profit d'un slug => unicité
            $this->context['page'] = $results->name;
            return view ('front._generic.fiche', compact('results'), $this->context);
        }
        else if ((strlen($text) == 1) && ctype_alpha($text))
        {
            // /evenements/{i}
            // Une caractère seul est passé  => on renvoit sur l'initiale
            $request->session()->flash('warning', 'L\'URL utilisée ("/evenements/'.$text.'")ne correspond pas à l\'URL des index ("/evenements/index/'.$text.'"), mais comme on est sympa, on a travaillé pour vous rediriger sur l\'index adéquat. Hop.');
            return redirect("evenements/index/$text");
        }
        else
        {
            $pagin = 1000;
            $user = Auth::user();
            if ($user)
            {
                $pagin = $user->items_par_page;
            }

            // /evenements/{pattern}
            // Recherche de tous les evenements avec le pattern fourni
            $results = Event::where(function($query) use($text) {
                $query->where ('name', 'like', '%' . $text .'%');
            })->orderBy('name', 'asc')->simplePaginate($pagin);

            if ($results->count() == 0) {
                // Aucun résultat, redirection vers l'accueil evenements
                $request->session()->flash('warning', 'Le nom ou l\'extrait de nom demandé ("' . $text . '") n\'est pas trouvé. Vous avez été redirigé sur l\'accueil de la zone evenements.');
                return redirect('evenements');
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
                return view ('front._generic.choix', compact('text', 'results'), $this->context);
            }
        }
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Event  $event
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        //
    }
}
