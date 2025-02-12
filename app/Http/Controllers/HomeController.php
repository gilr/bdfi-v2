<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Publication;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $notice = [
            'titlev2' => env('APP_TEST') === "true" ? "<span class='font-bold text-slate-600'><img src='/img/warning.png' class='inline w-5 mb-1' /> Version bêta de test du site BDFI V2</span>." : "<span class='font-bold text-slate-600'><img src='/img/warning.png' class='inline w-5 mb-1' /> Version bêta du site BDFI V2</span>.",
            'introv2' => "<span class='font-bold text-slate-600'>Attention</span>, la base des ouvrages est une <b>base très incomplète</b>, qui ne contient qu'une poignée de collections sur lequel une première passe de vérifications a eu lieu.",
            "contentv2" =>"Pour en connaître le contenu, consulter la <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/collections/v2beta'>page des collections incluses</a>. En fiche éditeur ou en zone de recherche de collection, les collections incluses sont repérables car précédées de l'icone <img src='/img/cible_bleue.png' class='inline w-5 mb-1' title='Collection présente en V2 bêta'>. On trouvera notamment :
        <ul class='list-disc pl-4 ml-4'>
            <li>Quelques collections de centaines d'ouvrages, comme
                <a class='border-b border-dotted border-purple-700 hover:text-purple-700' href='/collections/folio-sf'>Folio SF</a>,
                <a class='border-b border-dotted border-purple-700 hover:text-purple-700' href='/collections/terreur'>Pocket terreur</a> ou
                <a class='border-b border-dotted border-purple-700 hover:text-purple-700' href='/collections/angoisse'>Fleuve Noir angoisse</a>.
            </li>
            <li>Un exemple de support de type revue/fanzine, <a class='border-b border-dotted border-purple-700 hover:text-purple-700' href='/editeurs/basis'>Basis</a>, et un exemple de support de type magazine : <a class='border-b border-dotted border-purple-700 hover:text-purple-700' href='/editeurs/v-voir'>V magazine</a></li>
            <li>Des exemples de <a class='text-green-900 border-b border-dotted border-purple-700 hover:text-purple-700' href='/textes/la-chaise-infernale'>feuilleton (parus en épisodes)</a>, de <a class='text-blue-900 border-b border-dotted border-purple-700 hover:text-purple-700' href='/ouvrages/la-route-étoilée'>retirage (réimpression)</a>, de texte repris dans plusieurs publications, et de gestion de
                <a class='text-green-900 border-b border-dotted border-purple-700 hover:text-purple-700' href='https://bdfi-v2.test/textes/la-bataille-des-astres'>
                variantes de texte</a> (signature, titre et/ou traduction modifiés).
        </ul>
        <span class='font-semibold text-red-800'>Attention</span>, les ouvrages 'programmés' sont des données 'fake' générées uniquement pour test.<br />"
        ];
        if (env('APP_TEST') == "true")
        {
            $notice['contentv2'] = $notice['contentv2'] . "Pour des informations de test, voir en bas de page.";
        }
        else
        {
            $notice['contentv2'] = $notice['contentv2'] . "Pour des informations sur le développement, voir <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/site/historique-v2'>avancement version V2</a> ou les commits sur <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='https://github.com/gilr/bdfi-v2'>github</a>.";
        }

        // Récupérer les données nécessaires
        $births = Author::getBirthsOfDay();
        $deaths = Author::getDeathsOfDay();
        $updated = Publication::where('status', '<>', 'annonce')
                    ->orderBy('updated_at', 'desc')
                    ->limit(15)
                    ->with('publisher')
                    ->get();
        $created = Publication::where('status', '<>', 'propose')
                    ->where('status', '<>', 'annonce')
                    ->orderBy('created_at', 'desc')
                    ->limit(15)
                    ->with('publisher')
                    ->get();
        $recents = Publication::where('status', '<>', 'propose')
                    ->where('status', '<>', 'annonce')
                    ->where('cover_front', '<>', '')
                    ->orderBy('approximate_parution', 'desc')
                    ->limit(15)
                    ->with('publisher')
                    ->get();
        $programme = Publication::where('status', 'annonce')
                    ->orderBy('created_at', 'desc')
                    ->limit(15)
                    ->with('publisher')
                    ->get();
        $events = Event::where('is_full_scope', '1')
                    ->where('is_confirmed', '1')
                    ->where('end_date','>=', date("Y-m-d"))
                    ->orderBy('start_date', 'asc')
                    ->limit(15)
                    ->get();

        // Exclure les forums privés !
        $forum_last_topics = DB::connection('mysqlforum')->table('topics')
            ->orderBy('last_post', 'desc')
            ->whereNotIn('forum_id', [34, 27, 24, 20, 4, 13])
            ->limit(15)
            ->get();

        // Définir des variables supplémentaires
        $area = '';
        $title = '';
        $page = '';

        // Retourner la vue avec les données
        return view('welcome', compact('notice', 'births', 'deaths', 'updated', 'created', 'recents', 'programme', 'events', 'forum_last_topics', 'area', 'title', 'page'));
    }
}