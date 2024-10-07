<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAuthorRequest;
use App\Http\Requests\StoreAuthorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Models\AwardWinner;
use App\Models\Country;
use App\Models\Author;
use App\Models\Title;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class AuthorController extends Controller
{
    public $context = [
        'area'     => 'auteurs',
        'title'    => 'Auteurs',
        'icon'     => 'auteur.png',
        'filament' => 'authors',
        'page'     => '',
        'digit'    => 0
    ];

    /**
     * Accueil de la zone
     */
    public function welcome()
    {
        $countries = Country::select('name', 'code')
            ->orderBy('name', 'asc')
            ->get();
        $results = Author::orderBy('updated_at', 'desc')
            ->limit(25)
            ->get();

        return view('front._generic.welcome', compact('countries', 'results'), $this->context);
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
            $results = Author::where(function($query) use($text) {
                    $query->where('name', 'like', '%' . $text .'%')
                        ->orWhere('first_name', 'like', '%' . $text .'%');
                    })
                ->orderBy('name', 'asc')
                ->simplePaginate($pagin)
                ->withQueryString();
        }
        else
        {
            $results = Author::where(function($query) use($text) {
                    $query->where('name', 'like', '%' . $text .'%')
                        ->orWhere('first_name', 'like', '%' . $text .'%')
                        ->orWhere('legal_name', 'like', '%' . $text .'%')
                        ->orWhere('alt_names', 'like', '%' . $text .'%');
                    })
                ->orderBy('name', 'asc')
                ->simplePaginate($pagin)
                ->withQueryString();

        }

/*
            $results = Author::where(function($query) use($text) {
                $query->where ('name', 'like', '%' . $text .'%')
                ->orWhere('cycle', 'like', '%' . $text .'%')
                ->orWhere('information', 'like', '%' . $text .'%')
                ->orWhereRaw("SOUNDEX('$text') = SOUNDEX(name)");
            })->orderBy('name', 'asc')->paginate(60);
*/
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
            $results = Author::where('is_visible', 1)
                ->where('name', 'like', $initial.'%')
                ->orderBy('name', 'asc')
                ->simplePaginate($pagin)
                ->withQueryString();

            return view('front._generic.index', compact('initial', 'results'), $this->context);
        }
        else
        {
            $request->session()->flash('warning', 'Vous semblez vous être perdu dans un lieu dédié aux initiales, mais avec une initiale non constituée d\'une lettre unique ("'.$initial.'" ?!). Vous avez pris des risques. Pour le coup, vous êtes renvoyé directement vers l\'accueil de la zone auteurs...');
            return redirect('auteurs');
        }
    }

    public function index_pays(Request $request)
    {
        $this->context['page'] = 'Index pays';
        $countries = Country::select('name', 'code')
            ->orderBy('name', 'asc')
            ->get();

        return view('front.auteurs.indexpays', compact('countries'), $this->context);
    }

    /**
     * Index des auteurs par pays : /auteurs/pays/{pays}
     */
    public function pays(Request $request, $text)
    {

        $pagin = 1000;
        $user = Auth::user();
        if ($user)
        {
            $pagin = $user->items_par_page;
        }

        $this->context['page'] = 'Index ' . ucfirst($text);
        $this->context['subarea'] = 'pays';
        $this->context['subtitle'] = 'Pays';
        if ($searched_country = Country::where('name', $text)->get())
        {
            $pays = $searched_country[0]->name;
            $countries = Country::select('name', 'code')
                ->orderBy('name', 'asc')
                ->get();
            $results = Author::where('is_visible', 1)
                ->where('country_id', $searched_country[0]->id)
                ->orderBy('name', 'asc')
                ->simplePaginate(1000)
                ->withQueryString();

            return view('front.auteurs.pays', compact('pays', 'countries', 'results'), $this->context);
        }
    }

    /**
     * Page auteur... ou renvoi vers une initiale... ou page de choix : /auteurs/{pattern}
     */
    public function page(Request $request, $text)
    {
        if (($results=Author::with(['publications.publisher', 'websites.website_type'])->firstWhere('slug', $text)) && ($results->is_visible == 1))
        {
            // /auteurs/{slug}
            $this->context['page'] = $results->fullName;
            $id = $results->id;

            // Envoi du type pour simplifier le code de la vue d'affichage
            $refs = $results->references()->get();
            if ($results->ReferencesCount == 0)
            {
                $type = 'normal';
            }
            else if ($results->ReferencesCount == 1)
            {
                $type = 'redirect';
            }
            else if ($results->ReferencesCount == 2)
            {
                $type = 'collaboration';
            }
            else if ($results->ReferencesCount > 2)
            {
                $type = 'multi';
            }

            if ($type === 'normal')
            {
                // Dans le cas "normal" (auteur sans référence),
                // on prends tous les titres sous ce nom ou sous signature
                $plucked = $results->signatures()->get()->pluck('id');
                $plucked->prepend($id);
            }
            else
            {
                // Dans le cas "pseudonyme" (auteur sans aucune référence),
                // on ne prends que les titres sous ce nom
                $plucked = collect([]);
                $plucked->prepend($id);
            }
            $bibliofull = Title::join('author_title', 'titles.id', '=', "author_title.title_id")
                            ->join('authors', 'authors.id', '=', "author_title.author_id")
                            ->whereIn('authors.id', $plucked)
                            ->orderBy('copyright', 'asc')
                            ->select('titles.*')
                            ->with(['authors', 'publications.collections', 'publications.publisher', 'parent', 'variants'])
                            ->get();

            if ($type !== 'normal')
            {
                $full2 = new \Illuminate\Database\Eloquent\Collection;
                foreach ($bibliofull as $t)
                {
                    if ($t->parent_id === NULL)
                    {
                        $full2[] = $t;
                    }
                    else
                    {
                        $full2[] = $t->parent;
                    }
                }
                $bibliofull = $full2;
            }

            // Détermination des autres pseudonymes de la ou des auteurs "références"
            $autres_pseudos = NULL;
            if ($results->ReferencesCount > 0)
            {
                $premier = 0;
                $query = "";
                foreach ($refs as $ref)
                {
                    if ($premier == 0)
                    {
                        $query = sprintf("SELECT * FROM authors a INNER JOIN signatures ON a.id = signature_id WHERE (author_id='%s'", $ref->id);
                    }
                    else
                    {
                        $query = $query . sprintf(" OR author_id='%s'", $ref->id);
                    }
                    $premier += 1;
                }
                // Bien entendu, exclure la signature courante !
                $query = $query . sprintf(") AND signature_id<>'%s'", $id);
                // ... et les "Trashed" !!!
                $query = $query . sprintf(" AND signatures.deleted_at=NULL");
                $rrr = DB::select($query);
                // Moyen détourné pour caster le résultat stdClass en modèle Laravel :
                $autres_pseudos = Author::hydrate($rrr);
            }
            $award_years = AwardWinner::where('author_id', $id)->orWhere('author2_id', $id)->orWhere('author3_id', $id)->get('year')->sort()->unique('year')->toArray();

            return view ('front._generic.fiche', compact('results', 'award_years', 'type', 'autres_pseudos', 'bibliofull'), $this->context);
        }
        else if ((strlen($text) == 1) && ctype_alpha($text))
        {
            // /auteurs/{i}
            // Une caractère seul est passé  => on renvoit sur l'initiale
            $request->session()->flash('warning', 'L\'URL utilisée ("/auteurs/'.$text.'")ne correspond pas à l\'URL des index ("/series/auteurs/'.$text.'"), mais comme on est sympa, on a travaillé pour vous rediriger sur l\'index adéquat. Hop.');
            return redirect("auteurs/index/$text");
        }
        else
        {
            // /auteurs/{pattern}
            // Recherche de tous les auteurs avec le pattern fourni
            $results = Author::where('is_visible', 1)->where(function($query) use($text) {
                                $query->where ('name', 'like', '%' . $text .'%')
                                ->orWhere('first_name', 'like', '%' . $text .'%');
                            })
                            ->orderBy('name', 'asc')
                            ->paginate(60)->withQueryString();

            if ($results->count() == 0) {
                // Aucun résultat, redirection vers l'accueil auteurs
                $request->session()->flash('warning', 'Le nom ou l\'extrait de nom demandé ("' . $text . '") n\'est pas trouvé. Vous avez été redirigé sur l\'accueil de la zone auteurs.');
                return redirect('auteurs');
            }
            else if($results->count() == 1)
            {
                // Un résultat unique, on redirige gentiment vers lui avec un éventuel avertissement
                $results = $results[0];
                if (strtolower($text) != strtolower($results->name)) {
                    $this->context['page'] = "/$text/ ⇒ $results->fullName";
                    $request->session()->flash('warning', 'Le nom demandé ("' . $text . '") n\'existe pas exactement sous cette forme. Mais comme chez BDFI on est cool, on a fouillé un peu et on vous a trouvé un résultat possible. Essayez quand même d\'indiquer un nom exact la prochaine fois, ou passez par le moteur de recherche.');
                }
                else {
                    $this->context['page'] = "$text";
                }
                $id = $results->id;

                // Envoi du type pour simplifier le code de la vue d'affichage
                $refs = $results->references()->get();
                if ($results->ReferencesCount == 0)
                {
                    $type = 'normal';
                }
                else if ($results->ReferencesCount == 1)
                {
                    $type = 'redirect';
                }
                else if ($results->ReferencesCount == 2)
                {
                    $type = 'collaboration';
                }
                else if ($results->ReferencesCount > 2)
                {
                    $type = 'multi';
                }

                // Détermination des autres pseudonyme de la ou des auteurs "références"
                $autres_pseudos = NULL;
                if ($results->ReferencesCount > 0)
                {
                    $premier = 0;
                    $query = "";
                    foreach ($refs as $ref)
                    {
                        if ($premier == 0)
                        {
                            $query = sprintf("SELECT * FROM authors a INNER JOIN signatures ON a.id = signature_id WHERE (author_id='%s'", $ref->id);
                        }
                        else
                        {
                            $query = $query . sprintf(" OR author_id='%s'", $ref->id);
                        }
                        $premier += 1;
                    }
                    // Bien entendu, exclure la signature courante !
                    $query = $query . sprintf(") AND signature_id<>'%s'", $id);
                    // ... et les "Trashed" !!!
                    $query = $query . sprintf(" AND signatures.deleted_at=NULL");
                    $rrr = DB::select($query);
                    // Moyen détourné pour caster le résultat stdClass en modèle Laravel :
                    $autres_pseudos = Author::hydrate($rrr);
                }
                $award_years = AwardWinner::where('author_id', $id)
                    ->orWhere('author2_id', $id)
                    ->orWhere('author3_id', $id)
                    ->get('year')
                    ->sort()
                    ->unique('year')
                    ->toArray();

                return view ('front._generic.fiche', compact('results', 'award_years', 'type', 'autres_pseudos'), $this->context);
            }
            else
            {
                $this->context['page'] = "/$text/";
                $large = "";
                // Résultats multiples, on propose une page de choix
                $request->session()->flash('warning', 'Le nom ou l\'extrait de nom demandé ("' . $text . '") n\'existe pas de façon unique. Nous vous redirigeons vers une page de choix en espérant que vous y trouviez votre bonheur. Utilisez de préférence notre moteur de recherche.');
                // Page de choix sur base du pattern fourni
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
     * @param  \App\Models\Author  $author
     * @return \Illuminate\Http\Response
     */
    public function show(Author $author)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Author  $author
     * @return \Illuminate\Http\Response
     */
    public function edit(Author $author)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Author  $author
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Author $author)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Author  $author
     * @return \Illuminate\Http\Response
     */
    public function destroy(Author $author)
    {
        //
    }
}
