<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAwardWinnerRequest;
use Illuminate\Http\Request;
use App\Models\Award;
use App\Models\AwardCategory;
use App\Models\AwardWinner;
use Illuminate\Support\Facades\DB;

class AwardWinnerController extends Controller
{
    public $context = [
        'area'     => 'prix',
        'title'    => 'Prix',
        'icon'     => 'prix.png',
        'filament' => 'award-winners',
        'page'     => '',
    ];

    /**
     * Show the form for creating a new resource.
     * ?award_id=&year= présélectionnent le prix et l'année (ex. lien depuis le rapport "prix manquants").
     * Après un enregistrement (redirection depuis store), la session porte le résultat.
     */
    public function create(Request $request)
    {
        return view('admin.formulaires.ajout_annee_prix', $this->context + [
            'status'       => session('status'),
            'awardName'    => session('awardName'),
            'year'         => session('year'),
            'savedCount'   => session('savedCount'),
            'oldAwardId'   => old('award_id', $request->query('award_id')),
            'oldYear'      => old('year', $request->query('year')),
        ]);
    }

    /**
     * Enregistre les gagnants de l'année : une ligne par catégorie du prix, rattachée par
     * (award_category_id, year). Toutes les catégories du prix sont retraitées à chaque envoi, y compris
     * celles laissées vides (position 99 : catégorie non décernée cette année-là).
     */
    public function store(StoreAwardWinnerRequest $request)
    {
        $validated = $request->validated();
        $awardId = $validated['award_id'];
        $year = $validated['year'];

        $categories = AwardCategory::where('award_id', $awardId)->get(['id']);

        DB::transaction(function () use ($categories, $year, $request) {
            foreach ($categories as $category) {
                $row = (array) $request->input("winners.{$category->id}", []);

                $name = trim((string) ($row['name'] ?? ''));
                $title = trim((string) ($row['title'] ?? ''));
                $voTitle = trim((string) ($row['vo_title'] ?? ''));
                $author1 = filled($row['author1_id'] ?? null) ? (int) $row['author1_id'] : null;
                $author2 = filled($row['author2_id'] ?? null) ? (int) $row['author2_id'] : null;
                $author3 = filled($row['author3_id'] ?? null) ? (int) $row['author3_id'] : null;

                // Validé par StoreAwardWinnerRequest : name rempli (position 1), ou ligne entièrement vide (position 99)
                $position = $name !== '' ? 1 : 99;

                AwardWinner::updateOrCreate(
                    ['award_category_id' => $category->id, 'year' => $year],
                    [
                        'position'   => $position,
                        'name'       => $name !== '' ? $name : null,
                        'author_id'  => $author1,
                        'author2_id' => $author2,
                        'author3_id' => $author3,
                        'title'      => $title !== '' ? $title : null,
                        'vo_title'   => $voTitle !== '' ? $voTitle : null,
                    ]
                );
            }
        });

        // Redirection (POST/redirect/GET) : un rafraîchissement de la page ne ré-enregistre pas
        return redirect('/admin/formulaires/ajout-annee-prix?award_id=' . $awardId . '&year=' . $year)->with([
            'status'     => true,
            'awardName'  => Award::find($awardId)?->name,
            'year'       => $year,
            'savedCount' => $categories->count(),
        ]);
    }
}
