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


Route::get('/', function () { return view('welcome', ['area'  => '', 'title'  => '', 'page'  => '']); })->name('welcome');

// Zone Auteurs
Route::get('/auteurs', [AuthorController::class, 'welcome'])->name('auteurs');
Route::get('/auteurs/search/', [AuthorController::class, 'search'])->name('auteurs.search');
Route::get('/auteurs/index/{i}', [AuthorController::class, 'index']);
Route::get('/auteurs/pays/', [AuthorController::class, 'index_pays']);
Route::get('/auteurs/pays/{name}', [AuthorController::class, 'pays']);
Route::get('/auteurs/{name}', [AuthorController::class, 'page']);

// Zone salons et autres évènements
Route::get('/evenements', [EventController::class, 'welcome'])->name('evenements');
Route::get('/evenements/search/', [EventController::class, 'search'])->name('evenements.search');
Route::get('/evenements/index/{i}', [EventController::class, 'index']);       // --> Index évènement {initiale} (y compris 0 ou 9)
Route::get('/evenements/{name}', [EventController::class, 'page']);      // --> Une page évènement avec ID (futur => => slug !)
// Route::get('/evenements/historique', ...);   --> Liste des évènements y compris passés

// Zone ouvrages
Route::get('/ouvrages', [PublicationController::class, 'welcome'])->name('ouvrages');
Route::get('/ouvrages/search/', [PublicationController::class, 'search'])->name('ouvrages.search');
Route::get('/ouvrages/index/{i}', [PublicationController::class, 'index']);   // --> Index ouvrages {initiale} (y compris 0 ou 9)
Route::get('/ouvrages/{name}', [PublicationController::class, 'page']);     // --> Une page publi avec ID (futur => => slug !)

// Zone retirages
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Accès restreint aux roles "user" (si existe)
    // ...

    // Accès restreints de la zone admin :
    Route::middleware(['auth.bdfiadmin'])->group(function () {
        Route::get('/retirages', [ReprintController::class, 'welcome'])->name('retirages');
        Route::get('/retirages/search/', [ReprintController::class, 'search'])->name('retirages.search');
        Route::get('/retirages/index/{i}', [ReprintController::class, 'index']);   // --> Index retirages {initiale} (y compris 0 ou 9)
        Route::get('/retirages/{name}', [ReprintController::class, 'page']);     // --> Une page publi avec ID (futur => => slug !)
    });
});

// Zone textes/titres
Route::get('/textes', [TitleController::class, 'welcome'])->name('textes');
Route::get('/textes/search/', [TitleController::class, 'search'])->name('textes.search');
Route::get('/textes/index/{i}', [TitleController::class, 'index']);   // --> Index titles {initiale} (y compris 0 ou 9)
Route::get('/textes/{name}', [TitleController::class, 'page']);     // --> Une page title avec ID (futur => => slug !)

// Zone cycles et séries
Route::get('/series', [CycleController::class, 'welcome'])->name('series');
Route::get('/series/search/', [CycleController::class, 'search'])->name('series.search');
Route::get('/series/index/{i}', [CycleController::class, 'index']); // --> Index série {initiale} (y compris 0 ou 9)
Route::get('/series/{name}', [CycleController::class, 'page']);    // --> Une page série avec ID (futur => => slug !)

// Zone collections
Route::get('/collections', [CollectionController::class, 'welcome'])->name('collections');
Route::get('/collections/search/', [CollectionController::class, 'search'])->name('collections.search');
Route::get('/collections/index/{i}', [CollectionController::class, 'index']);       // --> Index collections {initiale} (y compris 0 ou 9)
Route::get('/collections/{name}', [CollectionController::class, 'page']);      // --> Une page collection avec ID (futur => => slug !)
// TBD route par défaut pour n'importe quoi d'autre sous "/collection" ?
// Route::get('/collections/{*}', [CollectionController::class, 'welcome'])->name('collections');

// Zone éditeurs
Route::get('/editeurs', [PublisherController::class, 'welcome'])->name('editeurs');
Route::get('/editeurs/search/', [PublisherController::class, 'search'])->name('editeurs.search');
Route::get('/editeurs/index/{i}', [PublisherController::class, 'index']);   // --> Index éditeurs {initiale} (y compris 0 ou 9)
Route::get('/editeurs/{name}', [PublisherController::class, 'page']);      // --> Une page éditeur avec ID (futur => => slug !)
Route::get('/editeurs/{name}/hc', [PublisherController::class, 'hc']);     // --> Page des ouvrages sans collection

// Zone des récompenses
Route::get('/prix', [AwardController::class, 'welcome'])->name('prix');
Route::get('/prix/search/', [AwardController::class, 'search'])->name('prix.search');
Route::get('/prix/{name}', [AwardController::class, 'prix']);
Route::get('/prix/categorie/{name}', [AwardController::class, 'categorie']);
Route::get('/prix/annee/{an}', [AwardController::class, 'annee']);
Route::get('/prix/genre/{name}', [AwardController::class, 'genre']);
Route::get('/prix/type/{name}', [AwardController::class, 'type']);
Route::get('/prix/pays/{name}', [AwardController::class, 'pays']);

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


// Authentification
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Accès restreint aux roles "user" (si existe)
    // ...

    // Accès restreints de la zone admin :
    Route::middleware(['auth.bdfiadmin'])->group(function () {

        Route::get('/admin', function () { return view('admin/welcome'); })->name('admin');

        Route::get('/admin/formulaires', [FormController::class, 'index'])->name('admin/formulaires');

        Route::get('/admin/rapports', [ReportController::class, 'index'])->name('admin/rapports');
        Route::get('/admin/rapports/dates-bizarres', [ReportController::class, 'getStrangeDates']);
        Route::get('/admin/rapports/manque-date-naissance', [ReportController::class, 'getMissingBirthdates']);
        Route::get('/admin/rapports/manque-date-deces', [ReportController::class, 'getMissingDeathdates']);
        Route::get('/admin/rapports/etat-biographies-{i}', [ReportController::class, 'getBioStatus']);
        Route::get('/admin/rapports/manque-nationalite', [ReportController::class, 'getMissingCountries']);
        Route::get('/admin/rapports/manque-fiche', [ReportController::class, 'getMissingRecords']);
        Route::get('/admin/rapports/prix-{an}', [ReportController::class, 'getMissingAwards']);

        Route::get('/admin/outils', [ToolController::class, 'index'])->name('admin/outils');
        Route::get('/admin/outils/anniversaires-fb-jour', [ToolController::class, 'getFbToday']);
        Route::get('/admin/outils/anniversaires-fb-semaine', [ToolController::class, 'getFbWeek']);
        Route::get('/admin/outils/anniversaires-fb-mois', [ToolController::class, 'getFbMonth']);
        Route::get('/admin/outils/conversion-sommaire', [ToolController::class, 'getConvertContent']);

        // Gestion des Téléchargement tables - générique multi-modèle
        Route::get('/admin/telechargements', [DownloadController::class, 'index']);
        Route::get('/admin/telechargements/{i}', [DownloadController::class, 'exportCSV']);

    });
});

/*

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
*/