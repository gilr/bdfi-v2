<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePublisherRequest;
use App\Http\Requests\StorePublisherRequest;
use Illuminate\Http\Request;
use App\Models\Publication;
use App\Models\Publisher;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PublisherController extends Controller
{
    public $context = [
        'area'     => 'editeurs',
        'title'    => 'Editeurs',
        'extended' => 'aux variantes de noms et collections',
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
            // Recherche simple
            $results = Publisher::where(function($query) use($text) {
                $query->where('name', 'like', '%' . $text .'%');
            })->orderBy('name', 'asc')->simplePaginate($pagin)->withQueryString();
        }
        else
        {
            // Recherche étendue
            $relations = ['collections', 'collections2', 'collections3'];

            $results = Publisher::query()
                ->leftJoin('collections as c1', 'publishers.id', '=', 'c1.publisher_id')
                ->leftJoin('collections as c2', 'publishers.id', '=', 'c2.publisher2_id')
                ->leftJoin('collections as c3', 'publishers.id', '=', 'c3.publisher3_id')
                ->where(function($query) use ($text) {
                    $query->where('publishers.name', 'like', '%' . $text . '%')
                          ->orWhere('publishers.alt_names', 'like', '%' . $text . '%')
                          ->orWhere('c1.name', 'like', '%' . $text . '%')
                          ->orWhere('c1.alt_names', 'like', '%' . $text . '%')
                          ->orWhere('c2.name', 'like', '%' . $text . '%')
                          ->orWhere('c2.alt_names', 'like', '%' . $text . '%')
                          ->orWhere('c3.name', 'like', '%' . $text . '%')
                          ->orWhere('c3.alt_names', 'like', '%' . $text . '%');
                })
                ->orderBy('publishers.name', 'asc')
                ->select('publishers.*')   // indispensable pour éviter les collisions de colonnes
                ->with($relations)         // eager loading des relations
                ->simplePaginate($pagin)
                ->withQueryString();

/*            $results = Publisher::where(function($query) use($text) {
                $query->where('name', 'like', '%' . $text .'%')
                ->orWhere('alt_names', 'like', '%' . $text .'%');
            })->orderBy('name', 'asc')->simplePaginate($pagin)->withQueryString();
            */
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

        // Si au moins un ouvrage de l'ensemble a un champ 'first_edition_id' non nul :
        $has_new_editions = $results->whereNotNull('first_edition_id')->isNotEmpty();

        return view ('front.editeurs.hc', compact('publisher', 'results', 'has_new_editions'), $this->context);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * Show the form for creating a new resource.
     * Après une création (redirection depuis store), la session porte l'éditeur créé.
     */
    public function create()
    {
        return view('admin.formulaires.creer_editeur', $this->context + [
            'status'    => session('status'),
            'id'        => session('id'),
            'label'     => session('label'),
            'slug'      => session('slug'),
            'countries' => $this->countryOptions(),
            'unknownCountryId' => Country::where('name', '?')->value('id'),
        ]);
    }

    /**
     * Pays pour la liste du formulaire : "Inconnu", puis les plus fréquents chez les éditeurs,
     * puis tous les autres par ordre alphabétique.
     * @return array{frequent: \Illuminate\Support\Collection, others: \Illuminate\Support\Collection}
     */
    private function countryOptions(): array
    {
        $frequentIds = DB::table('publishers')->whereNull('deleted_at')->whereNotNull('country_id')
            ->select('country_id', DB::raw('count(*) as n'))->groupBy('country_id')
            ->orderByDesc('n')->limit(8)->pluck('country_id')->all();

        $all = Country::orderBy('name')->get(['id', 'name'])->reject(fn ($c) => $c->name === '?');

        return [
            'frequent' => collect($frequentIds)->map(fn ($id) => $all->firstWhere('id', $id))->filter()->values(),
            'others'   => $all->reject(fn ($c) => in_array($c->id, $frequentIds))->values(),
        ];
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
        $name = trim($validated['name']);

        // Doublon : même nom déjà en base. Création seulement si l'homonymie est confirmée.
        if (!$request->boolean('homonym')) {
            $duplicates = Publisher::where('name', $name)
                ->get(['id', 'name', 'slug', 'year_start']);

            if ($duplicates->isNotEmpty()) {
                return back()->withInput()
                    ->with('duplicates', $duplicates->map(fn ($p) => [
                        'id'    => $p->id,
                        'label' => $p->name,
                        'slug'  => $p->slug,
                        'dates' => (string) $p->year_start,
                    ])->all())
                    ->withErrors(['name' => 'Un éditeur de même nom existe déjà : vérifiez ci-dessous, '
                        . 'puis cochez « Créer quand même » s\'il s\'agit d\'un homonyme.']);
            }
        }

        $editeur = Publisher::create([
            'name' => $name,
            'alt_names' => filled($validated['alt_names'] ?? null) ? trim($validated['alt_names']) : null,
            'year_start' => $validated['year_start'],
            'type' => $validated['type'],
            'country_id' => $validated['country_id'],
            'quality' => 'vide',
        ]);

        // Redirection (POST/redirect/GET) : un rafraîchissement de la page ne recrée pas l'éditeur
        return redirect('/admin/formulaires/ajout-editeur')->with([
            'status' => true,
            'id'     => $editeur->id,
            'label'  => $editeur->name,
            'slug'   => $editeur->slug,
        ]);
    }

}
