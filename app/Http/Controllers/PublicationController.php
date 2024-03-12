<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePublicationRequest;
use App\Http\Requests\StorePublicationRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Publication;
use App\Models\Collection;
use Illuminate\Support\Facades\Auth;
use App\Enums\PublicationStatus;
use App\Enums\PublicationFormat;

class PublicationController extends Controller
{
    public $context = [
        'area'     => 'ouvrages',
        'title'    => 'Publications',
        'icon'     => 'livre.png',
        'filament' => 'publications',
        'page'     => '',
        'digit'    => 1
    ];

    /**
     * Accueil de la zone ouvrages : /welcome
     */
    public function welcome()
    {
        $results = Publication::orderBy('updated_at', 'desc')->limit(25)->get();
        return view('front._generic.welcome', compact('results'), $this->context);
    }

    /**
     * Accueil de la zone des programmes : /programme
     */
    public function programme()
    {
        $context = [
            'area'     => 'programme',
            'title'    => 'Programme',
            'icon'     => 'programme.png',
            'page'     => '',
        ];
        $results = Publication::where('status', PublicationStatus::ANNONCE->value)->orderBy('approximate_parution', 'asc')->get();
        return view('front.programme.welcome', compact('results'), $context);
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
            $results = Publication::where(function($query) use($text) {
                    $query->where('name', 'like', '%' . $text .'%');
                })
                ->orderBy('name', 'asc')
                ->simplePaginate($pagin)
                ->withQueryString();
        }
        else
        {
            $results = Publication::where(function($query) use($text) {
                    $query->where('name', 'like', '%' . $text .'%')
                        ->orWhere('cycle', 'like', '%' . $text .'%');
                })
                ->orderBy('name', 'asc')
                ->simplePaginate($pagin)
                ->withQueryString();

        }

/*
            $results = Publication::where(function($query) use($text) {
                $query->where ('name', 'like', '%' . $text .'%')
                ->orWhere('cycle', 'like', '%' . $text .'%')
                ->orWhere('information', 'like', '%' . $text .'%')
                ->orWhereRaw("SOUNDEX('$text') = SOUNDEX(name)");
            })->orderBy('name', 'asc')->paginate(60);
*/
        // Pour l'instant, dans tous les cas on aiguille sur la page de choix
        $this->context['page'] = "recherche \"$text\"";
        return view ('front._generic.choix', compact('text', 'results', 'large'), $this->context);
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
            $results = Publication::where('name', 'like', $initial.'%')
                ->orderBy('name', 'asc')
                ->simplePaginate($pagin)
                ->withQueryString();

            return view('front._generic.index', compact('initial', 'results'), $this->context);
        }
        else if ((strlen($initial) == 1) && ctype_digit($initial))
        {
            $this->context['page'] = 'Index 0-9';
            $results = Publication::whereBetween('name', ['0','9'])
                ->orderBy('name', 'asc')
                ->simplePaginate($pagin)
                ->withQueryString();

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
        if ($results=Publication::find($text))
        {
            // /ouvrages/{id}
            // Un ID est passé - Pour l'instant c'est la façon propre d'afficher une page ouvrage
            // TBD : Il faudra supprimer l'accès par Id au profit d'un slug => unicité
            $this->context['page'] = $results->name;

            $first = array();
            $prev = array();
            $next = array();
            $last = array();
            $ipn = 0;
            foreach ($results->collections as $collection)
            {
                $nb = $collection->publications->count();
                $numprev = $collection->pivot->order - 1;
                $numnext = $collection->pivot->order + 1;
                $pivot_first = DB::table('collection_publication')->where('collection_id', $collection->id)->where('order', 1)->first();
                $pivot_prev = DB::table('collection_publication')->where('collection_id', $collection->id)->where('order', $numprev)->first();
                $pivot_next = DB::table('collection_publication')->where('collection_id', $collection->id)->where('order', $numnext)->first();
                $pivot_last = DB::table('collection_publication')->where('collection_id', $collection->id)->where('order', $nb)->first();

                $first[$ipn] = $collection->pivot->order !== 1 ? ($pivot_first !== NULL ? $pivot_first->publication_id : 0) : 0;
                $prev[$ipn] = $pivot_prev ? $pivot_prev->publication_id : 0;
                $next[$ipn] = $pivot_next ? $pivot_next->publication_id : 0;
                $last[$ipn] = $collection->pivot->order !== $nb ? ($pivot_last !== NULL ? $pivot_last->publication_id : 0) : 0;
                $ipn++;
            }

            $images = array();
            if ($results->dustjacket_front != "") {
                $images["jaquette"] = $results->cover_front;
            }
            if ($results->cover_front != "") {
                $images["couverture"] = $results->cover_front;
            }
            if ($results->withband_front != "") {
                $images["bandeau"] = $results->cover_front;
            }
            if ($results->dustjacket_back != "") {
                $images["4ième avec jaquette"] = $results->cover_front;
            }
            if ($results->cover_back != "") {
                $images["4ième"] = $results->cover_front;
            }
            if ($results->withband_back != "") {
                $images["4ième avec bandeau"] = $results->cover_front;
            }

            return view ('front._generic.fiche', compact('results', 'first', 'prev', 'next', 'last', 'images'), $this->context);
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
            $results = Publication::where(function($query) use($text) {
                    $query->where ('name', 'like', '%' . $text .'%');
                })
                ->orderBy('name', 'asc')
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

                $first = array();
                $prev = array();
                $next = array();
                $last = array();
                $ipn = 0;
                foreach ($results->collections as $collection)
                {
                    $nb = $collection->publications->count();
                    $numprev = $collection->pivot->order - 1;
                    $numnext = $collection->pivot->order + 1;
                    $pivot_first = DB::table('collection_publication')->where('collection_id', $collection->id)->where('order', 1)->first();
                    $pivot_prev = DB::table('collection_publication')->where('collection_id', $collection->id)->where('order', $numprev)->first();
                    $pivot_next = DB::table('collection_publication')->where('collection_id', $collection->id)->where('order', $numnext)->first();
                    $pivot_last = DB::table('collection_publication')->where('collection_id', $collection->id)->where('order', $nb)->first();

                    $first[$ipn] = $collection->pivot->order !== 1 ? $pivot_first->publication_id : 0;
                    $prev[$ipn] = $pivot_prev ? $pivot_prev->publication_id : 0;
                    $next[$ipn] = $pivot_next ? $pivot_next->publication_id : 0;
                    $last[$ipn] = $collection->pivot->order !== $nb ? ($pivot_last !== NULL ? $pivot_last->publication_id : 0) : 0;
                    $ipn++;
                }

                $images = array();
                if ($results->dustjacket_front != "") {
                    $images["jaquette"] = $results->cover_front;
                }
                if ($results->cover_front != "") {
                    $images["couverture"] = $results->cover_front;
                }
                if ($results->withband_front != "") {
                    $images["bandeau"] = $results->cover_front;
                }
                if ($results->dustjacket_back != "") {
                    $images["4ième avec jaquette"] = $results->cover_front;
                }
                if ($results->cover_back != "") {
                    $images["4ième"] = $results->cover_front;
                }
                if ($results->withband_back != "") {
                    $images["4ième avec bandeau"] = $results->cover_front;
                }

                return view ('front._generic.fiche', compact('results', 'first', 'prev', 'next', 'last', 'images'), $this->context);
            }
            else
            {
                // Résultats multiples, on propose une page de choix
                $this->context['page'] = "/$text/";
                $request->session()->flash('warning', 'Le nom ou l\'extrait de nom demandé ("' . $text . '") n\'existe pas de façon unique. Nous vous redirigeons vers une page de choix en espérant que vous y trouviez votre bonheur. Utilisez de préférence notre moteur de recherche.');

                $large = 'off';

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
        return view ('admin.formulaires.creer_publication');
    }
    public function createFuture()
    {
        return view ('admin.formulaires.programme_parution');
    }
    public function propose()
    {
        return view ('admin.formulaires.proposer_publication');
    }
    public function indexProposal()
    {
        $results = Publication::where('status', PublicationStatus::PROPOSE->value)
            ->orderBy('created_at', 'desc')
            ->get();

        return view ('admin.formulaires.publications_proposees', compact('results'));
    }
    public function indexExpiredFuture()
    {
        $today = date("Y-m-d");
        // dd($today);
        $results = Publication::where('status', PublicationStatus::ANNONCE->value)
            ->where('approximate_parution', '<=', $today)
            ->orderBy('approximate_parution', 'desc')
            ->get();

        return view ('admin.formulaires.programmes_echus', compact('results'));
    }
    public function indexFuture()
    {
        $today = date("Y-m-d");
        $results = Publication::where('status', PublicationStatus::ANNONCE->value)
            ->where('approximate_parution', '>=', $today)
            ->orderBy('approximate_parution', 'asc')
            ->get();

        return view ('admin.formulaires.programmes_non_echus', compact('results'));
    }
    public function validateProposal()
    {
        //Route::put('/admin/formulaires/publications-proposees', [PublicationController::class, 'validateProposal']);
    }
    public function updateExpiredFuture()
    {
        //Route::put('/admin/formulaires/programmes-echus', [PublicationController::class, 'updateExpiredFuture']);
    }
    public function updateFuture()
    {
        //Route::put('/admin/formulaires/programmes-non-echus', [PublicationController::class, 'updateFuture']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePublicationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePublicationRequest $request)
    {

        $validated = $request->validated();
        $publication = Publication::create([
            'status' => $request->publication_status,
            'name' => $request->name,
            'type' => $request->type,
            'support' => $request->support,
            'approximate_parution' => $request->approximate_parution,
            'is_genre' => $request->is_genre,
            'genre_stat' => $request->genre_stat,
            'target_audience' => $request->target_audience,
            'isbn' => $request->isbn,
            'is_verified' => false,
            'private' => $request->private,
            'format' => PublicationFormat::INCONNU->value,
        ]);

        // Trier en fonction de la provenance pour ré-aiguiller sur la bonne page
        if ($request->publication_status === PublicationStatus::PUBLIE->value)
        {
            return view ('admin.formulaires.creer_publication', $this->context)->with('status', true)->with('id', $publication->id);
        }
        else if ($request->publication_status === PublicationStatus::ANNONCE->value)
        {
            return view ('admin.formulaires.programme_parution', $this->context)->with('status', true)->with('id', $publication->id);
        }
        else if ($request->publication_status === PublicationStatus::PROPOSE->value)
        {
            return view ('admin.formulaires.proposer_publication', $this->context)->with('status', true)->with('id', $publication->id);
        }
        else
        {
            // Strange ?!
            return view ('admin.formulaires.index');
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Http\Response
     */
    public function edit(Publication $publication)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePublicationRequest  $request
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePublicationRequest $request, Publication $publication)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Publication  $publication
     * @return \Illuminate\Http\Response
     */
    public function destroy(Publication $publication)
    {
        //
    }
}
