<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function storepref(Request $request)
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

}
