<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    public function storePreferences(Request $request)
    {
        $user = Auth::user();

        // A force d'utiliser des admins externes, va falloir repotasser la gestion propre des validations avec rules & co
        // C'est le bordel ! ...alors qu'il y a 4-5 ans, j'ai fait toute une admin à la main :-(
        //dd($request);
        if ($request->filled('fad')) {
            $fad = $request->input('fad');
            // protection
            if (($fad == 'clair') || ($fad == 'abr') || ($fad == 'fr') || ($fad == 'fru') || ($fad == 'db'))
            {
                $user->format_date = $fad;
            }
            else
            {
                $user->format_date = 'abr';
            }
        }

        if ($request->filled('ipp')) {
            $ipp = $request->input('ipp');
            if (($ipp == 50) || ($ipp == 100) || ($ipp == 250) || ($ipp == 500) || ($ipp == 1000) || ($ipp == 5000))
            {
                $user->items_par_page = $ipp;
            }
            else
            {
                $user->items_par_page = 1000;
            }
        }

        if ($request->filled('icon')) {
            $user->with_icons = 1;
        }
        else
        {
            $user->with_icons = 0;
        }

        if ($request->filled('cor')) {
            $user->fonction_aide = 1;
        }
        else
        {
            $user->fonction_aide = 0;
        }

        if ($request->filled('bib')) {
            $user->gestion_biblio = 1;
        }
        else
        {
            $user->gestion_biblio = 0;
        }

        $user->save();

        return view('user.preferences');
    }

    public function addBiblioCollection(Request $request)
    {
        $user = Auth::user();

        if ($request->filled('col')) {
            $col = $request->input('col');

            // TO DO : contrôler pas d'ajout en double !!  (tuple (user, collection) unique)
            DB::table('user_collection')->insert([
                'status' => "en_cours", // BiblioCollectionStatus
                'user_id' => $user->id,
                'collection_id' => $col
            ]);
        }

        return redirect('/user/gestion-biblio');
    }

    public function updateBiblioCollection(Request $request)
    {
        $user = Auth::user();

        if ($request->filled('col')) {
            $col = $request->input('col');
            $col_status = $request->input('col_status');

            DB::table('user_collection')
                    ->where('user_id', $user->id)
                    ->where('collection_id', $col)
                    ->update(['status' => $col_status]);
        }

        return redirect('/user/gestion-biblio');
    }

    public function removeBiblioCollection(Request $request)
    {
        $user = Auth::user();

        if ($request->filled('col')) {
            $col = $request->input('col');

            DB::table('user_collection')
                    ->where('user_id', $user->id)
                    ->where('collection_id', $col)
                    ->delete();
        }

        return redirect('/user/gestion-biblio');
    }

    public function addBiblioPublication(Request $request)
    {
        $user = Auth::user();

        if ($request->filled('pub')) {
            $pub = $request->input('pub');

            // TO DO : contrôler pas d'ajout en double !!  (tuple (user, collection) unique)
            DB::table('user_publication')->insert([
                'user_id' => $user->id,
                'publication_id' => $pub
            ]);
        }

        return redirect('/user/affiche-collection');
    }

    public function removeBiblioPublication(Request $request)
    {
        $user = Auth::user();

        if ($request->filled('pub')) {
            $pub = $request->input('pub');

            DB::table('user_publication')
                    ->where('user_id', $user->id)
                    ->where('publication_id', $pub)
                    ->delete();
        }

        return redirect('/user/affiche-collection');
    }

}

