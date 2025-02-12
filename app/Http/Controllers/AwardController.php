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
use Illuminate\Support\Facades\Auth;

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
        if ($result = AwardCategory::find($category_id))
        {
            // TBD --> vers /prix/categorie/{slug}, mais requête des lauréats à revoir
            $prix = $result->award;
            $categorie = $result;

            $this->context['page'] = $categorie->name;
            $this->context['subarea'] = $prix->slug;
            $this->context['subtitle'] = $prix->name;

            //$laureats = $result->award_winners()->orderBy('year', 'asc')->get();
            $laureats = AwardWinner::where('award_category_id', $category_id)->orderBy('year', 'desc')->get();

            $info = buildRecordInfo($this->context['filament'], $this->context['area'], $result);
            return view('front.prix.categorie', compact('result', 'prix', 'categorie', 'laureats', 'info'), $this->context);
        }
        else
        {
            $request->session()->flash('warning', 'Pas de catégorie de ce nom. Pour la peine, nous vous renvoyons directement à la case départ...');
            return redirect('prix');
        }
    }

    public function prix(Request $request, $text)
    {

        if ($results=Award::firstWhere('slug', $text))
        {
            // /prix/{slug}
            $this->context['page'] = $results->name;

            $categories = AwardCategory::where('award_id', $results->id)->orderBy('internal_order', 'asc')->get();
            $laureats = NULL;
            if ($categories->count() == 1) {
                $laureats = AwardWinner::where('award_category_id', $categories->first()->id)->orderBy('year', 'desc')->get();
            }
            $info = buildRecordInfo($this->context['filament'], $this->context['area'], $results);
            return view('front.prix.prix', compact('results', 'categories', 'laureats', 'info'), $this->context);
        }
        else
        {
            // /prix/{pattern}
            // Recherche de tous les prix avec le pattern fourni
            $pagin = 1000;
            $user = Auth::user();
            if ($user)
            {
                $pagin = $user->items_par_page;
            }

            $results = Award::where(function($query) use($text) {
                $query->where ('name', 'like', '%' . $text .'%')
                        ->orWhere('alt_names', 'like', '%' . $text .'%');
            })->orderBy('name', 'asc')->simplePaginate($pagin)->withQueryString();

            if ($results->count() == 0) {
                // Aucun résultat, redirection vers l'accueil éditeurs
                $request->session()->flash('warning', 'Le nom ou l\'extrait de nom demandé ("' . $text . '") n\'est pas trouvé. Vous avez été redirigé sur l\'accueil de la zone prix.');
                return redirect('prix');
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
                $categories = AwardCategory::where('award_id', $results->id)->orderBy('internal_order', 'asc')->get();
                $laureats = NULL;
                if ($categories->count() == 1) {
                    $laureats = AwardWinner::where('award_category_id', $categories->first()->id)->orderBy('year', 'desc')->get();
                }
                $info = buildRecordInfo($this->context['filament'], $this->context['area'], $results);
                return view('front.prix.prix', compact('results', 'categories', 'laureats', 'info'), $this->context);
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

}
