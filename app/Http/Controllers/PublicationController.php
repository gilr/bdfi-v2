<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePublicationRequest;
use App\Http\Requests\StorePublicationRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Publication;
use App\Models\Collection;
use App\Models\TableOfContent;
use App\Models\Title;
use App\Enums\TitleVariantType;
use App\Models\Publisher;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;
use App\Enums\PublicationStatus;
use App\Enums\PublicationFormat;
use App\Enums\PublisherType;
use App\Enums\AuthorPublicationRole;
use App\Enums\QualityStatus;

class PublicationController extends Controller
{
    public $context = [
        'area'     => 'ouvrages',
        'title'    => 'Publications',
        'extended' => 'aux ISBN et aux cycles',
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
        $results = Publication::orderBy('updated_at', 'desc')->limit(50)->with('publisher')->get();
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

        $normalized = str_replace('-', '', $text);

        $results = Publication::where(function($query) use($text, $normalized, $large)
        {
            // recherche sur titre
            $query->where('name', 'like', '%' . $text .'%');

            if ($large === "on")
            {
                // recherche étendue
                // ...aux ISBN (normalisé)
                $query->orWhereRaw("REPLACE(isbn, '-', '') LIKE ?", ["%{$normalized}%"]);
                // ...et aux cycles
                $query->orWhere('cycle', 'like', '%' . $text .'%');
            }
        })
        ->orderBy('name', 'asc')
        ->with('publisher')
        ->simplePaginate($pagin)
        ->withQueryString();

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
        if ($results=Publication::with(['titles.authors', 'titles.cycles', 'publisher', 'authors', 'reprints', 'collections'])->firstWhere('slug', $text))
        {
            // /ouvrages/{slug}
            $this->context['page'] = $results->name;

            $first = array();
            $prev = array();
            $next = array();
            $last = array();
            $progression = array();
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

                $first[$ipn] = ($collection->pivot->order !== 1 && $pivot_first !== null) ?
                    DB::table('publications')
                        ->where('id', $pivot_first->publication_id)
                        ->whereNull('deleted_at')
                        ->value('slug') : 0;
                $prev[$ipn] = $pivot_prev ?
                    DB::table('publications')
                        ->where('id', $pivot_prev->publication_id)
                        ->whereNull('deleted_at')
                        ->value('slug') : 0;
                $next[$ipn] = $pivot_next ?
                    DB::table('publications')
                        ->where('id', $pivot_next->publication_id)
                        ->whereNull('deleted_at')
                        ->value('slug') : 0;
                $last[$ipn] = ($collection->pivot->order !== $nb && $pivot_last !== null) ?
                    DB::table('publications')
                        ->where('id', $pivot_last->publication_id)
                        ->whereNull('deleted_at')
                        ->value('slug') : 0;
                $progression[$ipn] = $collection->pivot->order / $nb;
                $ipn++;
            }

            // Tableau de correspondance entre les noms des images et les propriétés du résultat
            $mapping = [
                "Couverture" => $results->cover_front,
                "4ième de couverture" => $results->cover_back,
                "Dos" => $results->cover_spine,
                "Couverture avec jaquette" => $results->dustjacket_front,
                "4ième de couverture avec jaquette" => $results->dustjacket_back,
                "Dos avec jaquette" => $results->dustjacket_spine,
                "Couverture avec bandeau" => $results->withband_front,
                "4ième de couverture avec bandeau" => $results->withband_back,
                "Dos avec bandeau" => $results->withband_spine,
            ];

            // Filtrer les éléments du tableau pour ne conserver que ceux dont la valeur n'est pas vide
            $images = array_filter($mapping, fn($value) => !empty($value));

            $info = buildRecordInfo($this->context['filament'], $this->context['area'], $results);
            return view ('front._generic.fiche', compact('results', 'first', 'prev', 'next', 'last', 'progression', 'images', 'info'), $this->context);
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
                $progression = array();
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
                    $progression[$ipn] = $collection->pivot->order / $nb;
                    $ipn++;
                }

                $images = array();
                // Tableau de correspondance entre les noms des images et les propriétés du résultat
                $mapping = [
                    "Couverture" => $results->cover_front,
                    "4ième de couverture" => $results->cover_back,
                    "Dos" => $results->cover_spine,
                    "Couverture avec jaquette" => $results->dustjacket_front,
                    "4ième de couverture avec jaquette" => $results->dustjacket_back,
                    "Dos avec jaquette" => $results->dustjacket_spine,
                    "Couverture avec bandeau" => $results->withband_front,
                    "4ième de couverture avec bandeau" => $results->withband_back,
                    "Dos avec bandeau" => $results->withband_spine,
                ];

                // Filtrer les éléments du tableau pour ne conserver que ceux dont la valeur n'est pas vide
                $images = array_filter($mapping, function ($value) {
                    return $value != "";
                });

                $info = buildRecordInfo($this->context['filament'], $this->context['area'], $results);
                return view ('front._generic.fiche', compact('results', 'first', 'prev', 'next', 'last', 'progression', 'images'), $this->context);
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
    /**
     * Création rapide d'une publication parue ou à paraître (formulaire unique).
     * ?statut=annonce présélectionne "à paraître". Après une création (redirection depuis store),
     * la session porte la publication créée.
     */
    public function create(Request $request)
    {
        $status = session('pubstatus')
            ?? ($request->query('statut') === PublicationStatus::ANNONCE->value
                ? PublicationStatus::ANNONCE->value : PublicationStatus::PUBLIE->value);

        return view('admin.formulaires.creer_publication', $this->context + [
            'pubStatus' => old('publication_status', $status),
            'status'    => session('status'),
            'id'        => session('id'),
            'label'     => session('label'),
            'slug'      => session('slug'),
            'content'   => session('content'),
        ]);
    }

    /** Ancienne adresse du formulaire "à paraître" : regroupé avec le formulaire de création */
    public function createFuture()
    {
        return redirect('/admin/formulaires/ajout-publication?statut=' . PublicationStatus::ANNONCE->value);
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
    // Actions de validation/confirmation (à écrire) : réservées aux membres et plus
    public function updateExpiredFuture()
    {
        abort_unless(auth()->user()?->hasMemberRole(), 403);
        //Route::put('/admin/formulaires/programmes-echus', [PublicationController::class, 'updateExpiredFuture']);
    }
    public function updateFuture()
    {
        abort_unless(auth()->user()?->hasMemberRole(), 403);
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

        // Éditeur (EntityPicker) : existant, à créer à la volée, ou aucun pour l'instant
        $publisherId = $this->resolvePublisher($validated['publisher_id'] ?? null, $validated['publisher_name'] ?? null);
        if ($publisherId instanceof \Illuminate\Http\RedirectResponse) {
            return $publisherId;
        }

        // Publication, rattachements et contenu principal : tout ou rien
        [$publication, $content] = DB::transaction(function () use ($request, $validated, $publisherId) {
        $publication = Publication::create([
            'status' => $validated['publication_status'],
            'name' => trim($validated['name']),
            'type' => $validated['type'],
            'support' => $validated['support'],
            'format' => $validated['format'] ?? PublicationFormat::INCONNU->value,
            'approximate_parution' => $validated['approximate_parution'],
            'is_genre' => $validated['is_genre'],
            'genre_stat' => $validated['genre_stat'],
            'target_audience' => $validated['target_audience'],
            // "-" : pas d'ISBN (ouvrage ancien, fanzine...) ; "?" : ISBN inconnu (champ laissé vide)
            'isbn' => $request->boolean('no_isbn') ? '-' : (filled($validated['isbn'] ?? null) ? trim($validated['isbn']) : '?'),
            'is_hardcover' => $request->boolean('is_hardcover'),
            'has_dustjacket' => $request->boolean('has_dustjacket'),
            'has_coverflaps' => $request->boolean('has_coverflaps'),
            'is_verified' => false,
            'private' => $request->private,
            'publisher_id' => $publisherId,
        ]);

        // Collection (EntityPicker, existante uniquement) et son n° éventuel : rien si non choisie
        if (filled($validated['collection_id'] ?? null)) {
            $number = trim((string) ($validated['collection_number'] ?? ''));
            $publication->collections()->attach($validated['collection_id'], [
                // order (NOT NULL) : même convention que l'import (partie numérique du n°, 0 si absente/non numérique)
                'order'  => (int) (preg_match('/\d+/', $number, $m) ? $m[0] : 0),
                'number' => $number !== '' ? $number : null,
            ]);
        }

        // Auteurs (EntityPicker, existants uniquement) : jusqu'à deux pour l'instant, rôle "auteur"
        $authorIds = collect([$validated['author1_id'] ?? null, $validated['author2_id'] ?? null])
            ->filter(fn ($id) => filled($id))
            ->unique();
        if ($authorIds->isNotEmpty()) {
            $publication->authors()->attach(
                $authorIds->mapWithKeys(fn ($id) => [(int) $id => ['role' => AuthorPublicationRole::AUTHOR->value]])->all()
            );
        }

        // Contenu principal (premier élément du sommaire) : texte existant identique, ou nouveau texte
        $title = $validated['title_choice'] === 'new'
            ? $this->createMainContent($publication, $validated, $authorIds->all())
            : Title::findOrFail((int) $validated['title_choice']);
        TableOfContent::create(['publication_id' => $publication->id, 'title_id' => $title->id, 'level' => 0, 'order' => 1]);

        return [$publication, ['id' => $title->id, 'slug' => $title->slug, 'created' => $validated['title_choice'] === 'new']];
        });

        // Redirection (POST/redirect/GET) : un rafraîchissement de la page ne recrée pas la publication
        $flash = ['status' => true, 'id' => $publication->id, 'label' => $publication->name, 'slug' => $publication->slug,
                  'pubstatus' => $publication->status->value, 'content' => $content];

        return redirect('/admin/formulaires/ajout-publication?statut=' . $publication->status->value)->with($flash);
    }

    /**
     * Nouveau texte, contenu principal de la publication : même titre et mêmes auteurs que la publication,
     * première publication connue (variante "premier"), genre et public de la publication. Sa date de première
     * parution française est celle de la publication, si elle est précise (ni "circa" ni approximative).
     */
    private function createMainContent(Publication $publication, array $validated, array $authorIds): Title
    {
        $parution = (string) $publication->approximate_parution;

        $title = Title::create(array_filter([
            'name'            => $publication->name,
            'type'            => $validated['new_title_type'],
            // Extrait : à rattacher ensuite à son texte complet (formulaire des variantes)
            'variant_type'    => ($validated['new_title_extract'] ?? false) ? TitleVariantType::EXTRAIT->value : TitleVariantType::PREMIER->value,
            'is_novelization' => ($validated['new_title_novelization'] ?? null) ?: null,
            'copyright'       => filled($validated['new_title_copyright'] ?? null) ? trim($validated['new_title_copyright']) : null,
            'copyright_fr'    => preg_match('/^\d{4}-(\d{2}-\d{2}|T[1-4]-00)$/', $parution) ? $parution : null,
            'title_vo'        => filled($validated['new_title_vo'] ?? null) ? trim($validated['new_title_vo']) : null,
            'translators'     => filled($validated['new_title_translators'] ?? null) ? trim($validated['new_title_translators']) : null,
            'is_genre'        => $validated['is_genre'],
            'genre_stat'      => $validated['genre_stat'],
            'target_audience' => $validated['target_audience'],
        ], fn ($v) => $v !== null));

        if ($authorIds) {
            $title->authors()->attach($authorIds);
        }

        return $title;
    }

    /**
     * Résout l'éditeur choisi via l'EntityPicker : existant (id), à créer à la volée (nom saisi sans
     * sélection), ou aucun (champ laissé vide). Même logique que CollectionController::resolvePublisher().
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
