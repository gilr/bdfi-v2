@extends('front.layout')

@section('content')

    <div class="flex text-4xl pt-2 self-center">
        BDFI
    </div>
    <div class="flex text-xl pb-2 self-center">
        Version BDFI {{ env('VERSION') }} -
        Données <span class="font-bold mx-2">très</span> incomplètes (environ {{ $taux_remplissage }}%)
    </div>
    @if (env('APP_TEST') == "true")
        <div class="font-bold text-red-500 flex text-2xl pb-2 self-center">
            !! Site de test !!
        </div>
    @endif

    <div class='text-base p-4 m-4 bg-sky-100 self-center border border-blue-400 w-11/12 bg-sky-100'>
        <livewire:collapsible-block
            title="{!! $notice['titlev2'] !!}"
            intro="{!! $notice['introv2'] !!}"
            content="{!! $notice['contentv2'] !!}"
        />
    </div>

    <div class='text-base p-4 m-4 self-center border border-orange-400'>
        Le site BDFI (Base de Données Francophone de l'Imaginaire)  est consacré aux parutions et traductions francophones de l'imaginaire (science-fiction, fantastique, horreur, fantasy...). Il présente les bibliographies de plus de 16 000 auteurs, les éditeurs, les collections, les cycles, les principaux prix français et étrangers, ainsi que les évènements littéraires francophones associés à ces domaines de l'imaginaire.
        Visitez également nos forums et n'hésitez pas à participer !
    </div>

    <div class='grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-1 text-sm m-4 self-center'>
        <div class='border border-orange-400 px-2 sm:pl-5 sm:pr-2 md:pl-4 md:pr-4'>
            <div class='text-center font-bold mb-2'>Anniversaires de naissance</div>
            @foreach($births as $auteur)
                <div>{{ substr($auteur->birth_date, 0, 4) }} :
                    <x-front.lien-auteur link='/auteurs/{{ $auteur->slug }}'>{{ $auteur->first_name }} {{ $auteur->name }}</x-front.lien-auteur>
                    @if (substr($auteur->date_death, 0, 4) !== '0000')
                        (&dagger; {{ substr($auteur->date_death, 0, 4) }})
                    @endif
                </div>
            @endforeach
        </div>
        <div class='border border-orange-400 px-2 sm:pl-5 sm:pr-2 md:pl-4 md:pr-4'>
            <div class='text-center font-bold mb-2'>Dernières publications ajoutées</div>
            @foreach($created as $result)
                <div>
                    <x-front.lien-ouvrage link='/ouvrages/{{ $result->slug }}'>{{ $result->full_name ? Str::limit($result->full_name, 50) : Str::limit($result->name, 50) }}</x-front.lien-ouvrage>
                </div>
            @endforeach
        </div>
        <div class='border border-orange-400 px-2 sm:pl-5 sm:pr-2 md:pl-4 md:pr-4'>
            <div class='text-center font-bold mb-2'>Quelques parutions récentes</div>
            @php
                $images = array();
                foreach ($recents as $result) {
                    $images[] = array('url' => "https://www.bdfi.info/couvs/" . InitialeCouv($result->cover_front) . "/" . $result->cover_front . ".jpg", 'caption' => $result->name, 'book' => $result->slug);
                }
            @endphp
            <livewire:publication-carousel :images="$images" />
        </div>
        <div class='border border-orange-400 px-2 sm:pl-5 sm:pr-2 md:pl-4 md:pr-4'>
            <div class='text-center font-bold mb-2'>Annonces</div>
            @foreach($events as $result)
                <div>
                    <x-front.lien-texte link='/evenements/{{ $result->slug }}'>{{ $result->full_name ? Str::limit($result->full_name, 50) : Str::limit($result->name, 50) }}</x-front.lien-texte>
                </div>
            @endforeach
        </div>
    </div>

    <div class='grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-1 text-sm m-4 self-center'>
        <div class='border border-orange-400 px-2 sm:pl-5 sm:pr-2 md:pl-4 md:pr-4'>
            <div class='text-center font-bold mb-2'>Auteurs décédés ce jour</div>
            @foreach($deaths as $auteur)
                <div>{{ substr($auteur->birth_date, 0, 4) }}-{{ substr($auteur->date_death, 0, 4) }} :
                    <x-front.lien-auteur link='/auteurs/{{ $auteur->slug }}'>{{ $auteur->first_name }} {{ $auteur->name }}</x-front.lien-auteur>
                </div>
            @endforeach
        </div>
        <div class='border border-orange-400 px-2 sm:pl-5 sm:pr-2 md:pl-4 md:pr-4'>
            <div class='text-center font-bold mb-2'>Programmes de parution</div>
            @foreach($programme as $result)
                <div>
                    <x-front.lien-ouvrage link='/ouvrages/{{ $result->slug }}'>{{ $result->full_name ? Str::limit($result->full_name, 50) : Str::limit($result->name, 50) }}</x-front.lien-ouvrage>
                </div>
            @endforeach
        </div>
        <div class='border border-orange-400 px-2 sm:pl-5 sm:pr-2 md:pl-4 md:pr-4'>
            <div class='text-center font-bold mb-2'>Dernières publications mises à jour</div>
            @foreach($updated as $result)
                <div>
                    <x-front.lien-ouvrage link='/ouvrages/{{ $result->slug }}'>{{ $result->full_name ? Str::limit($result->full_name, 50) : Str::limit($result->name, 50) }}</x-front.lien-ouvrage>
                </div>
            @endforeach
        </div>
        <div class='border border-orange-400 px-2 sm:pl-5 sm:pr-2 md:pl-4 md:pr-4'>
            <div class='text-center font-bold mb-2'>Discussions actives forum</div>
                @foreach($forum_last_topics as $result)
                    <div>
                        <!-- TO DO TODO A FAIRE Exclure les privés !! -->
                        <x-front.lien-standard link='/tbd'>{{ Str::limit($result->subject, 50) }}</x-front.lien-standard>
                    </div>
            @endforeach
        </div>
    </div>

    @if (env('APP_TEST') == "true")
    <div class='text-base bg-sky-100 pt-1 p-5 mx-4 self-center border border-blue-400 text-blue-900 shadow-sm shadow-blue-600 rounded-sm px-1'>
        <div class='p-1'>
            <span class='text-base bg-sky-200 text-red-800 mt-1'>Comptes de test</span> :
            Un membre connecté avec assez de droits peut voir en supplément sur les pages/fiches du site quelques infos internes ainsi que des liens d'accès directs aux fiches.
            N'ont pas accès aux zones d'administration par défaut, les personnes non connectées, le compte <span class="font-bold text-blue-800">user@bdfi.net</span></li> ainsi que les comptes créés via le lien <span class="font-bold text-yellow-800">Inscription</span>. <br />
            Le compte <span class="font-bold text-blue-800">visitor@bdfi.net</span> permet de découvrir les pages d'admin sans autorisation de modification des tables, et <span class="font-bold text-blue-800">proposant@bdfi.net</span> est un compte permettant de demander l'ajout d'une publication.<br />
            Le mot de passe des comptes de test est <span class="font-bold text-blue-800">password</span>.<br />
            <!--
            Les comptes utilisables avec accès administration sont :<br />
            <ul class="list-disc pl-12">
                <li><span class="font-bold text-blue-800">editor@bdfi.net</span>, <span class="font-bold text-blue-800">editor2@bdfi.net</span> et <span class="font-bold text-blue-800">editor3@bdfi.net</span> : gestion des tables biblios</li>
                <li><span class="font-bold text-blue-800">admin@bdfi.net</span>, <span class="font-bold text-blue-800">admin2@bdfi.net</span> et <span class="font-bold text-blue-800">admin3@bdfi.net</span> : quelques droits supplémentaires</li>
                <li><span class="font-bold text-blue-800">sysadmin@bdfi.net</span> : pas de limitations (sauf si pas de sens)</li>
            </ul>
            -->
        </div>
        <div class='p-1'>
            <span class='text-base bg-sky-200 text-red-800 mt-1'>Nota</span> : Un mode debug peut parfois être activé (si besoin pour analyse complémentaire), mais vous pouvez cliquer sur la petite croix (extrémité droite) de la barre de debug en bas de page pour la réduire.
        </div>
        <div class='p-1 mt-2'>
                Ci-dessous en image, la visualisation des différents domaines et zones du site et la navigation :
        </div>
        <img src="/img/bdfi_navigation.jpg" />
    </div>
    @endif


@endsection
