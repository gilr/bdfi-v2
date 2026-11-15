<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Enums\BiblioPublicationStatus;

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

            // (L'ajout courant passe par le composant Livewire BiblioCollectionAdd ; route conservée)
            // Pas d'ajout en double (tuple (user, collection) unique), ni de collection inexistante
            $exists = DB::table('collections')->where('id', $col)->exists();
            $alreadyFollowed = DB::table('user_collection')
                    ->where('user_id', $user->id)
                    ->where('collection_id', $col)
                    ->exists();
            if ($exists && !$alreadyFollowed) {
                DB::table('user_collection')->insert([
                    'status' => "en_cours", // BiblioCollectionStatus
                    'user_id' => $user->id,
                    'collection_id' => $col
                ]);
            }
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

            $deleted = DB::table('user_collection')
                    ->where('user_id', $user->id)
                    ->where('collection_id', $col)
                    ->delete();

            if ($deleted) {
                $name = DB::table('collections')->where('id', $col)->value('name');
                session()->flash('flash.banner', 'Suivi de la collection "' . $name . '" retiré.');
                session()->flash('flash.bannerStyle', 'success');
            }
        }

        return redirect('/user/gestion-biblio');
    }

    public function addBiblioPublication(Request $request)
    {
        $user = Auth::user();

        if ($request->filled('pub')) {
            $pub = $request->input('pub');

            // Pas d'ajout en double (tuple (user, publication) unique), ni d'ouvrage inexistant
            $exists = DB::table('publications')->where('id', $pub)->exists();
            $alreadyOwned = DB::table('user_publication')
                    ->where('user_id', $user->id)
                    ->where('publication_id', $pub)
                    ->exists();
            if ($exists && !$alreadyOwned) {
                DB::table('user_publication')->insert([
                    'user_id' => $user->id,
                    'publication_id' => $pub,
                    'status' => BiblioPublicationStatus::POSSEDE->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Appel sans rechargement depuis le Listing
        if ($request->expectsJson()) {
            return response()->json(['owned' => true]);
        }
        return redirect('/user/affiche-collection');
    }

    // Bascule de l'état d'un ouvrage possédé (possédé <-> abîmé), appelée sans rechargement depuis le Listing
    public function toggleBiblioPublicationStatus(Request $request)
    {
        $user = Auth::user();
        $pub = $request->input('pub');

        $current = DB::table('user_publication')
                ->where('user_id', $user->id)
                ->where('publication_id', $pub)
                ->value('status');

        if ($current === null) {
            return response()->json(['error' => 'Ouvrage non possédé'], 404);
        }

        // État demandé explicitement (Listing), sinon bascule de l'état actuel
        $requested = BiblioPublicationStatus::tryFrom((string) $request->input('status'));
        $new = $requested?->value ?? (($current === BiblioPublicationStatus::ABIME->value)
                ? BiblioPublicationStatus::POSSEDE->value
                : BiblioPublicationStatus::ABIME->value);

        DB::table('user_publication')
                ->where('user_id', $user->id)
                ->where('publication_id', $pub)
                ->update(['status' => $new, 'updated_at' => now()]);

        return response()->json(['status' => $new]);
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

        // Appel sans rechargement depuis le Listing
        if ($request->expectsJson()) {
            return response()->json(['owned' => false]);
        }
        return redirect('/user/affiche-collection');
    }

}

