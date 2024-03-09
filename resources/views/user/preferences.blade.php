<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Preferences') }}
    </h2>
</x-slot>

<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">

            <div class="p-6">
                <div class="border border-blue-800 bg-blue-100 rounded-lg p-2 hidden sm:block">
                    Cette page vous permet de sélectionner quelques configurations d'affichage, ainsi que, si souhaité, une fonction d'aide et une gestion de bibliothèque. Pour les informations de compte et la sécurité, il faut se rendre sur la page profil.
                </div>
                <form method="POST" action="/user/preferences" >
                    @csrf
                    <div class="px-2 pt-4 text-lg sm:text-2xl border-b border-blue-800">
                        Format d'affichage des dates en pages du site
                    </div>
                    <ul class="p-2">
                        <li>
                            <input type="radio" name="fad" value="abr" class="bg-blue-200" {{ Auth::user()->format_date === "abr" ? "checked" : "" }} /> Clair abrégé : <span class='font-semibold'>JJ mmmm AAAA</span>
                            (<span class='hidden sm:inline'>Exemples : </span>6 sept. 1987<span class='hidden lg:inline'> ; août 2012 ; 1er trim. 2022 ; c. 1570 ; c. 200 av. J.-C.</span>)
                        </li>
                        <li>
                            <input type="radio" name="fad" value="clair" class="bg-blue-200" {{ Auth::user()->format_date === "clair" ? "checked" : "" }} /> Clair complet : <span class='font-semibold'>JJ mmmmmm AAAA</span>
                            (<span class='hidden sm:inline'>Exemples : </span>6 septembre 1987<span class='hidden lg:inline'> ; août 2012 ; 1er trimestre 2022 ; circa 1570 ; circa 200 avant J.-C.</span>)
                        </li>
                        <li>
                            <input type="radio" name="fad" value="fr" class="bg-blue-200" {{ Auth::user()->format_date === "fr" ? "checked" : "" }} /> Abrégé : <span class='font-semibold'>J/M/AAAA</span>
                            (<span class='hidden sm:inline'>Exemples : </span>6/9/1987<span class='hidden lg:inline'> ; 8/2012 ; T1/2022 ; c. 1570. ; c. 200 av. J.-C.</span>)
                        </li>
                        <li>
                            <input type="radio" name="fad" value="fru" class="bg-blue-200" {{ Auth::user()->format_date === "fru" ? "checked" : "" }} /> Abrégé uniforme : <span class='font-semibold'>JJ/MM/AAAA</span>
                            (<span class='hidden sm:inline'>Exemples : </span>06/09/1987<span class='hidden lg:inline'> ; 08/2012 ; T1/2022 ; circa 1570 ; circa 200 av. J.-C.</span>)
                        </li>
                        <li>
                            <input type="radio" name="fad" value="db" class="bg-blue-200" {{ Auth::user()->format_date === "db" ? "checked" : "" }} /> Interne : <span class='font-semibold'>AAAA-MM-JJ</span>
                            (<span class='hidden sm:inline'>Exemples : </span>1987-19-06<span class='hidden lg:inline'> ; 2012-08-00 ; 2022-T1-00 ; 1570-circa ; -200-circa</span>)
                        </li>
                    </ul>

                    <div class="px-2 pt-4 text-lg sm:text-2xl border-b border-blue-800">
                        Nombre d'éléments maximum par page (index, recherche...)
                    </div>
                    @php
                        $tabval = [ 50, 100, 250, 500, 1000, 5000 ];
                    @endphp
                    <div class="p-2">
                        <select name="ipp" class="bg-blue-200">
                            @foreach ($tabval as $val)
                                <option name="ipp" value="{{ $val }}" {{ Auth::user()->items_par_page == $val ? "selected" : "" }}> {{ $val }}</option>
                            @endforeach
                        </select>
                        <div class='hidden sm:block'>
                            Plus le nombre est important, moins vous aurez à utiliser la touche "suivant", mais plus les pages seront chargées.
                        </div>
                    </div>

                    <div class="px-2 pt-4 text-lg sm:text-2xl border-b border-blue-800">
                        Gestion de bibliothèque
                    </div>
                    <div class="p-2">
                        <span>
                            <input type="checkbox" name="bib" class="bg-blue-200" {{ Auth::user()->gestion_biblio ? "checked" : "" }} /> Gestion activée  si cochée
                        </span>
                    </div>
                    <div class='hidden sm:block'>
                        @if (!Auth::user()->gestion_biblio)
                            Une fois la gestion activée, vous aurez accès aux menus de gestion sur cette page, ainsi que des affichages complémentaires en zone collections et publications.
                        @else
                            Vous pouvez gérer votre bibliothèque grâce à la page dédiée accessible sur la page d'accueil "Utilisateur" ou via le menu supérieur "Ma bibliothèque".
                        @endif
                    </div>

                    <div class="px-2 pt-4 text-lg sm:text-2xl border-b border-blue-800">
                        (A venir) Fonction permettant la possibilité de proposer des correctifs
                    </div>
                    <div class="p-2">
                        <span>
                            <input type="checkbox" name="cor" class="bg-blue-200" {{ Auth::user()->fonction_aide ? "checked" : "" }} disabled /> Possibilité de proposition d'aide et corrections activée si cochée
                        </span>
                    </div>
                    <div class='hidden sm:block'>
                        @if (!Auth::user()->is_participant)
                            L'activation ajoute sur la plupart des fiches un formulaire (discret, dépliable) permettant d'indiquer des erreurs et proposer des correctifs.
                        @else
                            Il vous est possible de proposer des corrections sur chaque fiche au travers d'un formulaire spécifique.
                        @endif
                    </div>
                    <button class="bg-blue-400 font-semibold border border-blue-800 rounded px-4 py-2 m-2" type="submit">Valider les modifications</button>
                </form>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
