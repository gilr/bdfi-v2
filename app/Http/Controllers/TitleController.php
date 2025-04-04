<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Title;
use App\Http\Requests\StoreTitleRequest;
use App\Http\Requests\UpdateTitleRequest;
use Illuminate\Support\Facades\Auth;
use App\Enums\TitleVariantType;

class TitleController extends Controller
{
    public $context = [
        'area'     => 'textes',
        'title'    => 'Textes, oeuvres',
        'icon'     => 'texte.png',
        'filament' => 'titles',
        'page'     => '',
        'digit'    => 1
    ];

    /**
     * Accueil de la zone
     */
    public function welcome()
    {
        $results = Title::orderBy('updated_at', 'desc')->limit(25)->get();
        return view('front._generic.welcome', compact('results'), $this->context);
    }

    public function fullPubs($result)
    {
        // TODO : Ici il faudrait query toutes les publications uniques liées au titre parent (si parent_id <> 0) et à ses variantes, ou (si parent_id = 0) au titre courant et à ses variantes
        // récupérer le parent_id
        // !!! Nota : pour l'instant fait en local dans la vue
        $parent_id = $result->parent_id;
        if ($parent_id == 0) {
            // TODO
        }
        else
        {
            // TODO
        }
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
            $results = Title::where(function($query) use($text) {
                $query->where('name', 'like', '%' . $text .'%');
            })->orderBy('name', 'asc')->simplePaginate($pagin)->withQueryString();
        }
        else
        {
            $results = Title::where(function($query) use($text) {
                $query->where('name', 'like', '%' . $text .'%')
                ->orWhere('title_vo', 'like', '%' . $text .'%')
                ->orWhere('information', 'like', '%' . $text .'%')
                ->orWhere('synopsis', 'like', '%' . $text .'%');
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
            $results = Title::where('name', 'like', $initial.'%')->orderBy('name', 'asc')->simplePaginate($pagin)->withQueryString();
            return view('front._generic.index', compact('initial', 'results'), $this->context);
        }
        else if ((strlen($initial) == 1) && ctype_digit($initial))
        {
            $this->context['page'] = 'Index 0-9';
            $results = Title::whereBetween('name', ['0','9'])->orderBy('name', 'asc')->simplePaginate($pagin)->withQueryString();
            return view('front._generic.index', compact('initial', 'results'), $this->context);
        }
        else
        {
            $request->session()->flash('warning', 'Vous semblez vous être perdu dans un lieu dédié aux initiales, mais avec une initiale non constituée d\'une lettre unique ("'.$initial.'" ?!). Vous avez pris des risques. Pour le coup, vous êtes renvoyé directement vers l\'accueil de la zone textes...');
            return redirect('textes');
        }
    }
    public function page(Request $request, $text)
    {
        if ($results=Title::with(['publications.authors', 'publications.collections', 'publications.publisher'])->firstWhere('slug', $text))
        {
            // /textes/{slug}
            $this->context['page'] = $results->name;
            $info = buildRecordInfo($this->context['filament'], $this->context['area'], $results);
            return view ('front._generic.fiche', compact('results', 'info'), $this->context);
        }
        else if ((strlen($text) == 1) && ctype_alpha($text))
        {
            // /textes/{i}
            // Une caractère seul est passé  => on renvoit sur l'initiale
            $request->session()->flash('warning', 'L\'URL utilisée ("/textes/'.$text.'")ne correspond pas à l\'URL des index ("/textes/index/'.$text.'"), mais comme on est sympa, on a travaillé pour vous rediriger sur l\'index adéquat. Hop.');
            return redirect("textes/index/$text");
        }
        else
        {
            // recherche
            $pagin = 1000;
            $user = Auth::user();
            if ($user)
            {
                $pagin = $user->items_par_page;
            }

            // /textes/{pattern}
            // Recherche de tous les textes  avec le pattern fourni
            $results = Title::where(function($query) use($text) {
                $query->where ('name', 'like', '%' . $text .'%');
            })->orderBy('name', 'asc')->simplePaginate($pagin)->withQueryString();

            if ($results->count() == 0) {
                // Aucun résultat, redirection vers l'accueil textes
                $request->session()->flash('warning', 'Le nom ou l\'extrait de nom demandé ("' . $text . '") n\'est pas trouvé. Vous avez été redirigé sur l\'accueil de la zone titres de textes.');
                return redirect('textes');
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
                return view ('front._generic.fiche', compact('results', 'info'), $this->context);
            }
            else
            {
                // Résultats multiples, on propose une page de choix
                $this->context['page'] = "/$text/";
                $request->session()->flash('warning', 'Le nom ou l\'extrait de nom demandé ("' . $text . '") n\'existe pas de façon unique. Nous vous redirigeons vers une page de choix en espérant que vous y trouviez votre bonheur. Utilisez de préférence notre moteur de recherche.');

                // Page de choix sur base du pattern fourni
                $large = 'off';
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
        return view ('admin.formulaires.creer_texte');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTitleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTitleRequest $request)
    {

        $validated = $request->validated();
        $texte = Title::create([
            'name' => $request->name,
            'type' => $request->type,
            'variant_type' => TitleVariantType::PREMIER->value,
            'copyright' => $request->copyright,
            'is_genre' => $request->is_genre,
            'genre_stat' => $request->genre_stat,
            'target_audience' => $request->target_audience,
        ]);

        return view ('admin.formulaires.creer_texte', $this->context)->with('status', true)->with('id', $texte->id);
    }
}
