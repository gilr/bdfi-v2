<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Venturecraft\Revisionable\Revision;
use App\Models\Announcement;
use App\Models\Publication;
use App\Models\Title;
use App\Models\Stat;

class StatController extends Controller
{
    public $context = [
        'area'     => 'stats',
        'subarea'  => '',
        'title'    => 'Statistiques',
        'icon'     => 'stats.png',
        'filament' => '',
        'page'     => ''
    ];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function welcome()
    {
        $this->context['page'] = '';

        $results['pubs'] = Publication::where('status', 'paru')->count();
        $results['roman'] = Publication::where('status', 'paru')->where('type','roman')->count();
        $results['fiction'] = Publication::where('status', 'paru')->where('type','fiction')->count();
        $results['compilation'] = Publication::where('status', 'paru')->where('type','compilation')->count();
        $results['omnibus'] = Publication::where('status', 'paru')->where('type','omnibus')->count();
        $results['periodique'] = Publication::where('status', 'paru')->where('type','periodique')->count();
        $results['non-fiction'] = Publication::where('status', 'paru')->where('type','non-fiction')->count();
        $results['titles'] = Title::where('variant_type', '!=', 'virtuel')->count();

        return view('front.stats.welcome', compact('results'), $this->context);
    }
    public function bdfi()
    {

        $this->context['page'] = 'Base';
        return view('front.stats.bdfi', $this->context);
    }
    public function production()
    {
        $this->context['page'] = 'Production';
        setlocale( LC_TIME, "fr-FR" );
        $results = Announcement::where([
              ['type', '<>', 'remerciement'],
                ['type', '<>', 'consecration']
            ])
            ->orderBy('date', 'desc')
            ->simplePaginate(25)
            ->withQueryString();

        return view('front.stats.production', compact('results'), $this->context);
    }
    public function analyse()
    {
        $this->context['page'] = 'Analyse';
        setlocale( LC_TIME, "fr-FR" );
        $results = Announcement::where([
              ['type', '<>', 'remerciement'],
                ['type', '<>', 'consecration']
            ])
            ->orderBy('date', 'desc')
            ->simplePaginate(25)
            ->withQueryString();

        return view('front.stats.analyse', compact('results'), $this->context);
    }

}
