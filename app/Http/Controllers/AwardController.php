<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAwardRequest;
use App\Http\Requests\StoreAwardRequest;
use Illuminate\Http\Request;
use App\Models\AwardCategory;
use App\Models\AwardWinner;
use App\Models\Award;
use App\Enums\AwardCategoryType;
use App\Enums\AwardCategoryGenre;

class AwardController extends Controller
{
    public $context = [
        'area'     => 'prix',
        'title'    => 'Prix',
        'icon'     => 'prix.png',
        'filament' => 'awards',
        'page'     => ''
    ];


    protected $types = [AwardCategoryType::AUTEUR, AwardCategoryType::ROMAN, AwardCategoryType::NOVELLA, AwardCategoryType::NOUVELLE, AwardCategoryType::ANTHOLOGIE, AwardCategoryType::RECUEIL, AwardCategoryType::TEXTE, AwardCategoryType::SPECIAL];

    protected $genres = [AwardCategoryGenre::SF, AwardCategoryGenre::FANTASTIQUE, AwardCategoryGenre::FANTASY, AwardCategoryGenre::HORREUR, AwardCategoryGenre::IMAGINAIRE, AwardCategoryGenre::MAINSTREAM];

    protected function annees ()
    {
        return array_merge (range (1927, 1933), range (1951, date("Y")));
    }
    protected function listepays ()
    {
        $listeprix = Award::join('countries', 'countries.id', '=', 'awards.country_id')->select('country_id', 'countries.name')->orderBy('country_id', 'asc')->get()->unique('country_id')->all();
        return $listeprix;
    }

    public function welcome()
    {
        $genres = $this->genres;
        $types = $this->types;
        $annees = $this->annees();
        $pays = $this->listepays();
        $prix = Award::orderBy('name', 'asc')->orderBy('country_id', 'asc')->get();

        return view('front.prix.welcome', compact('annees', 'types', 'genres', 'pays', 'prix'), $this->context);
    }

    public function annee(Request $request, $annee)
    {
        $this->context['page'] = "Année $annee";
        $annees = $this->annees();

        // Récupération de l'ordre des types (suit la définition de l'enum)
        $liste = AwardCategoryType::getOrder();
        // Ou en dur : orderByRaw("FIELD(award_categories.type, 'roman', 'novella', 'nouvelle', 'anthologie', 'recueil', 'texte', 'auteur', 'special')") ...
        $laureats = AwardWinner::where('year', $annee)->where('position', 1)->join('award_categories', 'award_categories.id', '=', 'award_category_id')->orderByRaw("FIELD(award_categories.type, $liste)")->select('award_winners.*', 'award_categories.type')->get()->groupBy('type');

        // Limitation du range aux années existantes, et sauter les non existantes
        // En dur, ahum... Mais bon, paraît que le passé ne devrait plus trop bouger, donc acceptable.
        $prev = $annee - 1;
        if ($prev == 1950) { $prev = 1933;}
        if ($prev == 1926) { $prev = 0; }

        $next = $annee + 1;
        if ($next == 1934) { $next = 1951;}
        if ($next == now()->year + 1) { $next = 0; }

        return view('front.prix.annee', compact('annees', 'annee', 'laureats', 'prev', 'next'), $this->context);
    }

    public function genre(Request $request, $genre)
    {
        $this->context['page'] = "Genre $genre";
        $genres = $this->genres;
        $categories = AwardCategory::where('genre', $genre)->join('awards', 'awards.id', '=', 'award_id')->orderBy('awards.name', 'ASC')->orderBy('type')->select('award_categories.*')->get();

        return view('front.prix.genre', compact('genres', 'genre', 'categories'), $this->context);
    }

    public function type(Request $request, $type)
    {
        $this->context['page'] = "Type $type";
        $types = $this->types;
        $categories = AwardCategory::where('type', $type)->join('awards', 'awards.id', '=', 'award_id')->orderBy('awards.name', 'ASC')->orderBy('internal_order')->select('award_categories.*')->get();

        return view('front.prix.type', compact('types', 'type', 'categories'), $this->context);
    }

    public function pays(Request $request, $pays)
    {
        $this->context['page'] = "$pays";
        $listepays = $this->listepays();
        //$categories = AwardCategory::join('awards', 'awards.id', '=', 'award_id')->join('countries', 'countries.id', '=', 'awards.country_id')->where('countries.name', $pays)->orderBy('awards.name', 'ASC')->orderBy('internal_order')->select('award_categories.*')->get();
        $prix = Award::join('countries', 'countries.id', '=', 'awards.country_id')->where('countries.name', $pays)->orderBy('awards.name', 'ASC')->select('awards.*')->get();

        return view('front.prix.pays', compact('listepays', 'pays', 'prix'), $this->context);
    }

    public function categorie(Request $request, $category_id)
    {
        $result = AwardCategory::find($category_id);
        $prix = $result->award;
        $categorie = $result;

        $this->context['page'] = $categorie->name;
        $this->context['subarea'] = $prix->id;
        $this->context['subtitle'] = $prix->name;

        //$laureats = $result->award_winners()->orderBy('year', 'asc')->get();
        $laureats = AwardWinner::where('award_category_id', $category_id)->orderBy('year', 'asc')->get();

        return view('front.prix.categorie', compact('result', 'prix', 'categorie', 'laureats'), $this->context);
    }

    public function prix(Request $request, $award)
    {
        if (!$prix=Award::find($award))
        {
            $prix = Award::where('name', $award)->first();
        }
        $this->context['page'] = $prix->name;

        $categories = AwardCategory::where('award_id', $prix->id)->orderBy('internal_order', 'asc')->get();
        $laureats = NULL;
        if ($categories->count() == 1) {
            $laureats = AwardWinner::where('award_category_id', $categories->first()->id)->orderBy('year', 'asc')->get();
        }
        return view('front.prix.prix', compact('prix', 'categories', 'laureats'), $this->context);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Models\Award  $award
     * @return \Illuminate\Http\Response
     */
    public function show(Award $award)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Award  $award
     * @return \Illuminate\Http\Response
     */
    public function edit(Award $award)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Award  $award
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Award $award)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Award  $award
     * @return \Illuminate\Http\Response
     */
    public function destroy(Award $award)
    {
        //
    }
}
