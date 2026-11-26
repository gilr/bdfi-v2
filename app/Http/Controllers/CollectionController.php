<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateCollectionRequest;
use App\Http\Requests\StoreCollectionRequest;
use Illuminate\Http\Request;
use App\Models\Collection;
use Illuminate\Support\Facades\Auth;
use App\Enums\QualityStatus;
use App\Models\Publication;
use App\Models\Title;
use App\Models\Publisher;
use App\Models\Country;
use App\Enums\PublisherType;

class CollectionController extends Controller
{
    public $context = [
        'area'     => 'collections',
        'title'    => 'Collections',
        'extended' => 'aux variantes de noms et éditeurs',
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
        $results = Collection::orderBy('updated_at', 'desc')
            ->limit(25)
            ->with('publisher')
            ->get();

        return view('front._generic.welcome', compact('results'), $this->context);
    }

    /**
     * Liste temporaire des collections gérées en V2
     */
    public function v2beta()
    {
        $results = Collection::where('is_in_v2beta', 1)
                ->with('publisher')
                ->orderBy('name', 'asc')
                ->get();

        // Taux de remplissage approximatif
        $taux_remplissage = round((Publication::where('status', '<>', 'annonce')->count() + Title::count()) * 100 / 240000, 1);

        $this->context['page'] = 'Collections V2 bêta';

        return view('front.collections.v2beta', compact('results', 'taux_remplissage'), $this->context);
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
            $results = Collection::where(function($query) use($text) {
                    $query->where('name', 'like', '%' . $text .'%');
                })
                ->orderBy('name', 'asc')
                ->with(['publisher', 'publisher2', 'publisher3'])
                ->simplePaginate($pagin)
                ->withQueryString();
        }
        else
        {
            // Recherche étendue
            $relations = ['publisher', 'publisher2', 'publisher3'];

            $results = Collection::query()
                ->leftJoin('publishers as p1', 'collections.publisher_id', '=', 'p1.id')
                ->leftJoin('publishers as p2', 'collections.publisher2_id', '=', 'p2.id')
                ->leftJoin('publishers as p3', 'collections.publisher3_id', '=', 'p3.id')
                ->where(function($query) use ($text) {
                    $query->where('collections.name', 'like', '%' . $text . '%')
                          ->orWhere('collections.alt_names', 'like', '%' . $text . '%')
                          ->orWhere('p1.name', 'like', '%' . $text . '%')
                          ->orWhere('p2.name', 'like', '%' . $text . '%')
                          ->orWhere('p3.name', 'like', '%' . $text . '%');
                })
                ->orderBy('collections.name', 'asc')
                ->select('collections.*') // indispensable pour hydrater correctement le modèle
                ->with($relations)        // eager loading des relations
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

        if ((strlen($initial) == 1) && ctype_alpha($initial))
        {
            $this->context['page'] = 'Index ' . strtoupper($initial);
            $results = Collection::where('name', 'like', $initial.'%')
                ->orderBy('name', 'asc')
                ->with(['publisher', 'publisher2', 'publisher3'])
                ->simplePaginate($pagin)
                ->withQueryString();

            return view('front._generic.index', compact('initial', 'results'), $this->context);
        }
        else if ((strlen($initial) == 1) && ctype_digit($initial))
        {
            $this->context['page'] = 'Index ' . strtoupper($initial);
            $results = Collection::whereBetween('name', ['0','9'])
                ->orderBy('name', 'asc')
                ->with(['publisher', 'publisher2', 'publisher3'])
                ->simplePaginate($pagin)
                ->withQueryString();

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
        if ($results=Collection::with(['publications.authors', 'publisher', 'parent.publisher', 'subcollections.publisher'])->firstWhere('slug', $text))
        {
            // /collections/{slug}
            $this->context['page'] = $results->name;
            $info = buildRecordInfo($this->context['filament'], $this->context['area'], $results);

            // Si au moins un ouvrage de la collection a un champs 'first_edition_id' non nul :
            $has_new_editions = $results->publications()->whereNotNull('first_edition_id')->exists();

            return view ('front._generic.fiche', compact('results', 'info', 'has_new_editions'), $this->context);
        }
        else if ((strlen($text) == 1) && ctype_alpha($text))
        {
            // /collections/{i}
            // Slug non trouvé, et un caractère seul est passé  => on renvoit sur l'initiale
            $request->session()->flash('warning', 'L\'URL utilisée ("/collections/'.$text.'")ne correspond pas à l\'URL des index ("/collections/index/'.$text.'"), mais comme on est sympa, on a travaillé pour vous rediriger sur l\'index adéquat. Hop.');
            return redirect("collections/index/$text");
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
            // /collections/{pattern}
            // Recherche de toutes les collections avec le pattern fourni
            $results = Collection::where(function($query) use($text) {
                    $query->where ('name', 'like', '%' . $text .'%')
                        ->orWhere('alt_names', 'like', '%' . $text .'%');
                })
                ->orderBy('name', 'asc')
                ->simplePaginate($pagin)
                ->withQueryString();

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
                $info = buildRecordInfo($this->context['filament'], $this->context['area'], $results);

                // Si au moins un ouvrage de la collection a un champs 'first_edition_id' non nul :
                return view ('front._generic.fiche', compact('results', 'info', 'has_new_editions'), $this->context);
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
    /**
     * Show the form for creating a new resource.
     * Après une création (redirection depuis store), la session porte la collection créée.
     */
    public function create()
    {
        return view('admin.formulaires.creer_collection', $this->context + [
            'status' => session('status'),
            'id'     => session('id'),
            'label'  => session('label'),
            'slug'   => session('slug'),
        ]);
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
        $name = trim($validated['name']);
        // Nom court = nom (cas sans sous-collection) si laissé vide
        $shortname = filled($validated['shortname'] ?? null) ? trim($validated['shortname']) : $name;

        // Doublon : même nom déjà en base. Création seulement si l'homonymie est confirmée.
        if (!$request->boolean('homonym')) {
            $duplicates = Collection::where('name', $name)
                ->get(['id', 'name', 'slug', 'year_start']);

            if ($duplicates->isNotEmpty()) {
                return back()->withInput()
                    ->with('duplicates', $duplicates->map(fn ($c) => [
                        'id'    => $c->id,
                        'label' => $c->name,
                        'slug'  => $c->slug,
                        'dates' => (string) $c->year_start,
                    ])->all())
                    ->withErrors(['name' => 'Une collection de même nom existe déjà : vérifiez ci-dessous, '
                        . 'puis cochez « Créer quand même » s\'il s\'agit d\'une homonymie.']);
            }
        }

        // Éditeur (EntityPicker) : existant, à créer à la volée, ou aucun pour l'instant
        $publisherId = $this->resolvePublisher($validated['publisher_id'] ?? null, $validated['publisher_name'] ?? null);
        if ($publisherId instanceof \Illuminate\Http\RedirectResponse) {
            return $publisherId;
        }

        $collection = Collection::create([
            'name' => $name,
            'shortname' => $shortname,
            'year_start' => $validated['year_start'],
            'type' => $validated['type'],
            'support' => $validated['support'],
            // NOT NULL sans défaut, absents de l'ancien formulaire (le crée échouait en base)
            'periodicity' => $validated['periodicity'],
            'is_in_v2beta' => false,
            'quality' => QualityStatus::VIDE->value,
            'publisher_id' => $publisherId,
        ]);

        // Redirection (POST/redirect/GET) : un rafraîchissement de la page ne recrée pas la collection
        return redirect('/admin/formulaires/ajout-collection')->with([
            'status' => true,
            'id'     => $collection->id,
            'label'  => $collection->name,
            'slug'   => $collection->slug,
        ]);
    }

    /**
     * Résout l'éditeur choisi via l'EntityPicker : existant (id), à créer à la volée (nom saisi sans
     * sélection), ou aucun (champ laissé vide). Un nom qui correspond exactement à un éditeur existant
     * n'est jamais créé en double : la sélection via la recherche est demandée à la place.
     *
     * @return int|null|\Illuminate\Http\RedirectResponse id de l'éditeur à lier, null si aucun,
     *         ou une redirection (erreur) si le nom saisi correspond à un éditeur déjà en base
     */
    private function resolvePublisher(?int $publisherId, ?string $publisherName): int|null|\Illuminate\Http\RedirectResponse
    {
        if (filled($publisherId)) {
            return $publisherId;
        }

        $name = trim((string) $publisherName);
        if ($name === '') {
            return null;
        }

        $existing = Publisher::where('name', $name)->first(['id', 'slug']);
        if ($existing) {
            return back()->withInput()->withErrors(['publisher_name' => "Un éditeur nommé « {$name} » existe déjà "
                . "(#{$existing->id}) : utilisez la recherche pour le sélectionner, au lieu de taper son nom."]);
        }

        // Création à la volée : mêmes valeurs par défaut que le formulaire dédié (ajout-editeur)
        return Publisher::create([
            'name' => $name,
            'year_start' => 0,   // inconnue ; à corriger ensuite dans Filament si besoin
            'type' => PublisherType::EDITEUR->value,
            'country_id' => Country::where('name', '?')->value('id'),
            'quality' => QualityStatus::VIDE->value,
        ])->id;
    }

}
