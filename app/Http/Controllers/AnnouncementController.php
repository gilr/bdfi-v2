<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAnnouncementRequest;
use App\Http\Requests\StoreAnnouncementRequest;
use \Venturecraft\Revisionable\Revision;
use Illuminate\Http\Request;
use App\Models\Announcement;
use App\Models\Publication;
use App\Models\Stat;

class AnnouncementController extends Controller
{

    public $context = [
        'area'     => 'site',
        'subarea'  => '',
        'title'    => 'Le site',
        'icon'     => 'annonces.png',
        'filament' => 'announcements',
        'page'     => ''
    ];
    public function welcome()
    {
        $this->context['page'] = '';
        $results = Publication::orderBy('created_at', 'desc')
            ->limit(25)
            ->with('publisher')
            ->get();
        $results2 = Revision::orderBy('updated_at', 'desc')
            ->limit(25)
            ->get();

        return view('front.site.welcome', compact('results', 'results2'), $this->context);
    }

    public function news()
    {
        $this->context['page'] = 'Evolutions';
        setlocale( LC_TIME, "fr-FR" );
        $results = Announcement::where([
              ['type', '<>', 'remerciement'],
                ['type', '<>', 'consecration']
            ])
            ->orderBy('date', 'desc')
            ->simplePaginate(25)
            ->withQueryString();

        return view('front.site.news', compact('results'), $this->context);
    }

    public function stats()
    {
        $this->context['page'] = 'Base';
        $results = Stat::orderBy('date')
            ->get();

        return view('front.site.base', compact('results'), $this->context);
    }

    public function thanks()
    {
        $this->context['page'] = 'Remerciements';
        $results = Announcement::where('type', '=', 'remerciement')
            ->orderBy('date', 'desc')
            ->simplePaginate(100)
            ->withQueryString();

        return view('front.site.merci', compact('results'), $this->context);
    }

    public function help()
    {
        $this->context['page'] = 'Nous aider';
        return view('front.site.aides', $this->context);
    }

    public function about()
    {
        $this->context['page'] = 'A propos';
        return view('front.site.about', $this->context);
    }

    public function histov2()
    {
        $this->context['page'] = 'histo-v2';
        return view('front.site.histov2', $this->context);
    }

    public function contact()
    {
        $this->context['page'] = 'Contact';
        return view('front.site.contact', $this->context);
    }

}
