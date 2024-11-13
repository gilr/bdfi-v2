<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\CycleController;
use App\Http\Controllers\PublisherController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\PublicationController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ReprintController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TitleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StatController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['public.maintenance'])->group(function () {
    // Les routes publiques, consultables sauf maintenance en cours

    Route::get('/', [HomeController::class, 'index'])->name('welcome');

    Route::get('/symlinkstorage', function () {
        Artisan::call('storage:link');
    });

    // Zone Auteurs
    Route::get('/auteurs', [AuthorController::class, 'welcome'])->name('auteurs');
    Route::get('/auteurs/search/', [AuthorController::class, 'search'])->name('auteurs.search');
    Route::get('/auteurs/index/{i}', [AuthorController::class, 'index']);
    Route::get('/auteurs/pays/', [AuthorController::class, 'index_pays']);
    Route::get('/auteurs/pays/{name}', [AuthorController::class, 'pays']);          // TBD --> SLUG pays
    Route::get('/auteurs/{slug}', [AuthorController::class, 'page']);

    // Zone convenstions, salons et autres évènements...
    // A revoir pour évènement générique, et édition particulière année X --> split table
    Route::get('/evenements', [EventController::class, 'welcome'])->name('evenements');
    Route::get('/evenements/search/', [EventController::class, 'search'])->name('evenements.search');
    Route::get('/evenements/index/{i}', [EventController::class, 'index']);       // --> Index évènement {initiale} (y compris 0 ou 9)
    Route::get('/evenements/{slug}', [EventController::class, 'page']);           // --> Page évènement avec slug
    // Route::get('/evenements/historique', ...);   --> Liste des évènements y compris passés --> après split table
    // TODO : En fait non -> devra être affiché directement sur la page évènement (sinon elle ne dit pas grand chose de plus)

    // Zone ouvrages
    // Voir les create et store supplémentaires à Filament en zone admin
    Route::get('/ouvrages', [PublicationController::class, 'welcome'])->name('ouvrages');
    Route::get('/ouvrages/search/', [PublicationController::class, 'search'])->name('ouvrages.search');
    Route::get('/ouvrages/index/{i}', [PublicationController::class, 'index']);   // --> Index ouvrages {initiale} (y compris 0 ou 9)
    Route::get('/ouvrages/{slug}', [PublicationController::class, 'page']);       // --> Page publi avec slug
    // Zone programmes
    Route::get('/programme', [PublicationController::class, 'programme'])->name('programme');

    // Zone retirages
    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        // Accès restreints de la zone admin :
        Route::middleware(['auth.bdfiadmin'])->group(function () {
            Route::get('/retirages', [ReprintController::class, 'welcome'])->name('retirages');
            Route::get('/retirages/search/', [ReprintController::class, 'search'])->name('retirages.search');
            Route::get('/retirages/index/{i}', [ReprintController::class, 'index']);   // --> Index retirages {initiale} (y compris 0 ou 9)
            Route::get('/retirages/{slug}', [ReprintController::class, 'page']);       // --> Page retirage avec slug
        });
    });

    // Zone textes/titres
    Route::get('/textes', [TitleController::class, 'welcome'])->name('textes');
    Route::get('/textes/search/', [TitleController::class, 'search'])->name('textes.search');
    Route::get('/textes/index/{i}', [TitleController::class, 'index']);   // --> Index titres {initiale} (y compris 0 ou 9)
    Route::get('/textes/{slug}', [TitleController::class, 'page']);       // --> Une page titre avec slug

    // Zone cycles et séries
    Route::get('/series', [CycleController::class, 'welcome'])->name('series');
    Route::get('/series/search/', [CycleController::class, 'search'])->name('series.search');
    Route::get('/series/index/{i}', [CycleController::class, 'index']); // --> Index série {initiale} (y compris 0 ou 9)
    Route::get('/series/{slug}', [CycleController::class, 'page']);     // --> Page série avec slug

    // Zone collections
    // Voir les create et store supplémentaires à Filament en zone admin
    Route::get('/collections', [CollectionController::class, 'welcome'])->name('collections');
    // Version bêta seule :
    Route::get('/collections/v2beta', [CollectionController::class, 'v2beta'])->name('v2b7');

    Route::get('/collections/search/', [CollectionController::class, 'search'])->name('collections.search');
    Route::get('/collections/index/{i}', [CollectionController::class, 'index']);       // --> Index collections {initiale} (y compris 0 ou 9)
    Route::get('/collections/{slug}', [CollectionController::class, 'page']);           // --> Page collection avec slug
    // TBD route par défaut pour n'importe quoi d'autre sous "/collection" ?
    // Route::get('/collections/{*}', [CollectionController::class, 'welcome'])->name('collections');

    // Zone éditeurs
    // Voir les create et store supplémentaires à Filament en zone admin
    Route::get('/editeurs', [PublisherController::class, 'welcome'])->name('editeurs');
    Route::get('/editeurs/search/', [PublisherController::class, 'search'])->name('editeurs.search');
    Route::get('/editeurs/index/{i}', [PublisherController::class, 'index']);   // --> Index éditeurs {initiale} (y compris 0 ou 9)
    Route::get('/editeurs/{slug}', [PublisherController::class, 'page']);       // --> Page éditeur avec slug
    Route::get('/editeurs/{name}/hc', [PublisherController::class, 'hc']);      // --> Page des ouvrages sans collection

    // Zone des récompenses
    Route::get('/prix', [AwardController::class, 'welcome'])->name('prix');
    Route::get('/prix/search/', [AwardController::class, 'search'])->name('prix.search');
    Route::get('/prix/{slug}', [AwardController::class, 'prix']);                   // Page prix avec slug
    Route::get('/prix/categorie/{id}', [AwardController::class, 'categorie']);      // TBD Page catégorie de prix avec slug
    Route::get('/prix/annee/{an}', [AwardController::class, 'annee']);              // Page des prix d'une année
    Route::get('/prix/genre/{name}', [AwardController::class, 'genre']);            // Page des prix d'un genre
    Route::get('/prix/type/{name}', [AwardController::class, 'type']);              // Page des prix d'un type (roman, nouvelle...)
    Route::get('/prix/pays/{name}', [AwardController::class, 'pays']);              // Page des prix d'un pays --> SLUG

    // Zone statistiques
    Route::get('/stats', [StatController::class, 'welcome'])->name('stats');
    Route::get('/stats/bdfi', [StatController::class, 'bdfi']);
    // + accès restreints, voir plus bas

    // Zone infos du site
    Route::get('/site', [AnnouncementController::class, 'welcome'])->name('site');
    Route::get('/site/news', [AnnouncementController::class, 'news']);
    Route::get('/site/base', [AnnouncementController::class, 'stats']);
    Route::get('/site/merci', [AnnouncementController::class, 'thanks']);
    Route::get('/site/aides', [AnnouncementController::class, 'help']);
    Route::get('/site/a-propos', [AnnouncementController::class, 'about']);
    Route::get('/site/contact', [AnnouncementController::class, 'contact']);
    Route::get('/site/historique-v2', [AnnouncementController::class, 'histov2']);

    // Forums => redirection sur sous-domaine en PHP 5.6
    // Temporaire
    Route::get('/forums', function () { return view('forums', ['area'  => 'forums', 'title'  => '', 'page'  => '']); });
});

// Authentification
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Accès restreint aux utilisateurs connectés - Roles "user" et tout type d'admin (y compris "visitor")
    Route::middleware(['public.maintenance'])->group(function () {
        // ... Mais seulement si la partie publique n'est pas en maintenance // TO DO voir si 'admin' ou 'public'
        Route::get('/user', function () { return view('user/welcome'); })->name('user');
        Route::get('/user/preferences', function () { return view('user/preferences'); })->name('preferences.show');
        Route::post('/user/preferences', [UserController::class, 'storePreferences']);
        Route::get('/user/bibliotheque', function () { return view('user/bibliotheque'); })->name('user/bibliotheque');
        Route::get('/user/gestion-biblio', function () { return view('user/gestionbiblio'); });
        Route::get('/user/affiche-collection', function () { return view('user/affichecollection'); });
        Route::get('/user/mancoliste', function () { return view('user/mancoliste'); });
        Route::post('/user/ajouter-collection', [UserController::class, 'addBiblioCollection']);
        Route::post('/user/modifier-collection', [UserController::class, 'updateBiblioCollection']);
        Route::post('/user/retirer-collection', [UserController::class, 'removeBiblioCollection']);
        Route::post('/user/ajouter-publication', [UserController::class, 'addBiblioPublication']);
        Route::post('/user/retirer-publication', [UserController::class, 'removeBiblioPublication']);

        // Zone statistiques accès restreint
        Route::get('/stats/production', [StatController::class, 'production']);
        Route::get('/stats/analyse', [StatController::class, 'analyse']);
    });

    Route::middleware(['auth.bdfiadmin', 'admin.maintenance'])->group(function () {
        // Accès restreints de la zone admin (y compris visitor - les restrictions sont au niveau filament & admin) :
        // ... et si la partie admin n'est pas en maintenance
        Route::get('/admin', function () { return view('admin/welcome'); })->name('admin');

        // Create et store supplémentaires à Filament pour actions "rapides"
        Route::get('/admin/formulaires', function () { return view('admin/formulaires/index'); })->name('admin/formulaires');

        Route::get('/admin/formulaires/ajout-auteur', [AuthorController::class, 'create']);
        Route::post('/admin/formulaires/ajout-auteur', [AuthorController::class, 'store']);
        Route::get('/admin/formulaires/modifier-auteur', [AuthorController::class, 'edit']);
        Route::put('/admin/formulaires/modifier-auteur', [AuthorController::class, 'store']);
        Route::get('/admin/formulaires/ajout-editeur', [PublisherController::class, 'create']);
        Route::post('/admin/formulaires/ajout-editeur', [PublisherController::class, 'store']);
        Route::get('/admin/formulaires/ajout-collection', [CollectionController::class, 'create']);
        Route::post('/admin/formulaires/ajout-collection', [CollectionController::class, 'store']);
        Route::get('/admin/formulaires/ajout-publication', [PublicationController::class, 'create']);
        Route::get('/admin/formulaires/programme-parution', [PublicationController::class, 'createFuture']);
        Route::get('/admin/formulaires/proposer-publication', [PublicationController::class, 'propose']);
        // les 3 publis sont des store classiques :
        Route::post('/admin/formulaires/ajout-publication', [PublicationController::class, 'store']);
        Route::post('/admin/formulaires/programme-parution', [PublicationController::class, 'store']);
        Route::post('/admin/formulaires/proposer-publication', [PublicationController::class, 'store']);
        // Mais aussi les autres actions comme validation et confirmation
        Route::get('/admin/formulaires/publications-proposees', [PublicationController::class, 'indexProposal']);
        Route::get('/admin/formulaires/programmes-echus', [PublicationController::class, 'indexExpiredFuture']);
        Route::get('/admin/formulaires/programmes-non-echus', [PublicationController::class, 'indexFuture']);
        // plus les put pour valider/confirmer unitairement...
        Route::put('/admin/formulaires/publications-proposees', [PublicationController::class, 'validateProposal']);
        Route::put('/admin/formulaires/programmes-echus', [PublicationController::class, 'updateExpiredFuture']);
        Route::put('/admin/formulaires/programmes-non-echus', [PublicationController::class, 'updateFuture']);

        Route::get('/admin/outils', [ToolController::class, 'index'])->name('admin/outils');
        Route::get('/admin/outils/dates-bizarres', [ToolController::class, 'getStrangeDates']);
        Route::get('/admin/outils/manque-date-naissance', [ToolController::class, 'getMissingBirthdates']);
        Route::get('/admin/outils/manque-date-deces', [ToolController::class, 'getMissingDeathdates']);
        Route::get('/admin/outils/etat-biographies-{i}', [ToolController::class, 'getBioStatus']);
        Route::get('/admin/outils/manque-nationalite', [ToolController::class, 'getMissingCountries']);
//        Route::get('/admin/outils/manque-fiche', [ToolController::class, 'getMissingRecords']);
        Route::get('/admin/outils/prix-{an}', [ToolController::class, 'getMissingAwards']);
        Route::get('/admin/outils/anniversaires-fb-jour', [ToolController::class, 'getFbToday']);
        Route::get('/admin/outils/anniversaires-fb-semaine', [ToolController::class, 'getFbWeek']);
        Route::get('/admin/outils/anniversaires-fb-mois', [ToolController::class, 'getFbMonth']);
        Route::get('/admin/outils/conversion-sommaire', [ToolController::class, 'getConvertContent']);

        // Gestion des Téléchargement tables - générique multi-modèle
        Route::get('/admin/telechargements', [DownloadController::class, 'index']);
        Route::get('/admin/telechargements/{i}', [DownloadController::class, 'exportCSV']);

    });
});
