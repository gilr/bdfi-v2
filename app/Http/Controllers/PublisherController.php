<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePublisherRequest;
use App\Http\Requests\StorePublisherRequest;
use Illuminate\Http\Request;
use App\Models\Publication;
use App\Models\Publisher;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;

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
            })->orderBy('name', 'asc')->simplePaginate($pagin)->withQueryString();
        }
        else
        {
            $results = Publisher::where(function($query) use($text) {
                $query->where('name', 'like', '%' . $text .'%')
                ->orWhere('alt_names', 'like', '%' . $text .'%');
            })->orderBy('name', 'asc')->simplePaginate($pagin)->withQueryString();

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
            $results = Publisher::where('name', 'like', $initial.'%')->orderBy('name', 'asc')->simplePaginate($pagin)->withQueryString();
            return view('front._generic.index', compact('initial', 'results'), $this->context);
        }
        else if ((strlen($initial) == 1) && ctype_digit($initial))
        {
            $this->context['page'] = 'Index 0-9';
            $results = Publisher::whereBetween('name', ['0','9'])->orderBy('name', 'asc')->simplePaginate($pagin)->withQueryString();
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
        if ($results=Publisher::firstWhere('slug', $text))
        {
            // /editeurs/{slug}
            $this->context['page'] = $results->name;
            $publications = Publication::where('publisher_id', $results->id)->get()->random(fn ($items) => min(10, count($items)))->shuffle();
            $info = buildRecordInfo($this->context['filament'], $this->context['area'], $results);
            return view ('front._generic.fiche', compact('results', 'publications', 'info'), $this->context);
        }
        else if ((strlen($text) == 1) && ctype_alpha($text))
        {
            // /éditeurs/{i}
            // Slug non trouvé, et un caractère seul est passé  => on renvoit sur l'initiale
            $request->session()->flash('warning', 'L\'URL utilisée ("/editeurs/'.$text.'")ne correspond pas à l\'URL des index ("/editeurs/index/'.$text.'"), mais comme on est sympa, on a travaillé pour vous rediriger sur l\'index adéquat. Hop.');
            return redirect("editeurs/index/$text");
        }
        else
        {
            // Sinon, on recherche
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
            })->orderBy('name', 'asc')->simplePaginate($pagin)->withQueryString();

            if ($results->count() == 0) {
                // Aucun résultat, redirection vers l'accueil éditeurs
                $request->session()->flash('warning', 'Le nom ou l\'extrait de nom demandé ("' . $text . '") n\'est pas trouvé. Vous avez été redirigé sur l\'accueil de la zone éditeurs.');
                return redirect('editeurs');
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
                $publications = Publication::where('publisher_id', $results->id)->get()->random(fn ($items) => min(10, count($items)))->shuffle();
                $info = buildRecordInfo($this->context['filament'], $this->context['area'], $results);
                return view ('front._generic.fiche', compact('results', 'publications', 'info'), $this->context);
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
        // /editeurs/{slug}/hc
        $publisher = Publisher::firstWhere('slug', $text);
        $results = Publisher::firstWhere('slug', $text)->publicationsWithoutCollection()->get();
        return view ('front.editeurs.hc', compact('publisher', 'results'), $this->context);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view ('admin.formulaires.creer_editeur');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePublisherRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePublisherRequest $request)
    {
        $validated = $request->validated();
        $liste_pays = [
            'France' => Country::select('id')->where('name', 'France')->get(),
            'Canada' => Country::select('id')->where('name', 'canada')->get(),
            'Suisse' => Country::select('id')->where('name', 'Suisse')->get(),
            'Belgique' => Country::select('id')->where('name', 'Belgique')->get(),
            'Luxembourg' => Country::select('id')->where('name', 'Luxembourg')->get()
        ];


        $pays = $liste_pays["$request->pays"];
        //$collection = new Collection;
        //$collection->name = $request->name;
        //$collection->save();
        $editeur = Publisher::create([
            'name' => $request->name,
            'alt_names' => $request->alt_names,
            'year_start' => $request->year_start,
            'type' => $request->type,
            'country_id' => $pays[0]['id'],
            'quality' => 'vide',
        ]);

        return view ('admin.formulaires.creer_editeur', $this->context)->with('status', true)->with('id', $editeur->id);
    }

}
