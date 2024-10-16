<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Publication;
use App\Models\Event;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
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

        // Définir des variables supplémentaires
        $area = '';
        $title = '';
        $page = '';

        // Retourner la vue avec les données
        return view('welcome', compact('births', 'deaths', 'updated', 'created', 'recents', 'programme', 'events', 'area', 'title', 'page'));
    }
}