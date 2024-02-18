@extends('front.layout')
@section('content')

<x-bdfi-menu-site tab='about' />

<div class='text-2xl my-2 md:mt-8 bold self-center'>
     <img sb-icon src="/img/{{ $icon }}" class="w-4 md:w-8 inline" title="{{ $title }}"/> <b>A propos de BDFI</b>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

    <div class='text-base p-2 m-5 mx:4 md:mx-20 bg-yellow-200 border-l-4 border-red-600'>
        Le site <span class="font-bold text-red-800">BDFI</span> a été créé début 2001 par Christian Moulin et Gilles Richardot. Il a fait suite et remplacé le site <span class="font-bold italic text-red-800">Imagine...</span> qui était apparu en ligne deux ans auparavant, en juin 1999.
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Membres actifs
    </div>
    <div class='text-base ml-2 sm:ml-8 pb-2'>
        <ul class="list-disc pl-4 md:pl-12">
            <li><span class="text-blue-800">Christian Moulin</span> (France, Seine Maritime) - Administrateur, Services de Presse, gestion base de données, programmes & parutions...</li>
            <li><span class="text-blue-800">Gilles Richardot</span> (France, Ile de France) - Administrateur, développement, gestion base de données, contrôleur et webmaster...</li>
            <li><span class="text-blue-800">"MobyDick"</span> (France) - Administration biographies d'auteurs, contributions, forum...</li>
            <li><span class="text-blue-800">Philippe Ethuin</span> (France, Picardie) - Contributions, forum</li>
            <li><span class="text-blue-800">Hervé Lagoguey</span> (France, Champagne-Ardennes) - Contributions, forum</li>
            <li><span class="text-blue-800">Alain Nodet</span> (France, Ile de France) - Contributions, forum, Wiki</li>
            <li><span class="text-blue-800">"Elmer"</span> (France, Aquitaine) - Contributions, forum, Wiki</li>
            <li><span class="text-blue-800">Roland Pawlak</span> (Océan Indien) - Contributions, forum</li>
        </ul>
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Remerciements particuliers
    </div>
    <div class='text-base ml-2 sm:ml-8 pb-2'>
        ... aux anciens membres et à ceux temporairement en sommeil, aux contributeurs actifs (du forum ou par messagerie), et aux personnes ayant apportés une aide particulièrement remarquable et/ou régulière à une période donnée :
        <ul class="list-disc pl-4 md:pl-12">
            <li><span class="text-blue-800">Christine Luce</span> (France, Nord-Pas de Calais)</li>
            <li><span class="text-blue-800">Cendrine Van Klaveren</span></li>
            <li><span class="text-blue-800">Hervé Hauck</span> (France, Loire-Atlantique)</li>
            <li><span class="text-blue-800">Michel Hertzog</span> (France)</li>
            <li><span class="text-blue-800">Pierre-Luc Lafrance</span> (Canada)</li>
            <li><span class="text-blue-800">Marc Madouraud</span> (France, Ile de France)</li>
            <li><span class="text-blue-800">Franck Thomas</span> (France, Isère)</li>
            <li><span class="text-blue-800">Michel Vincke</span> (Belgique)</li>
        </ul>
        Et on en oublie malheureusement certainement quelques uns... D'autres remerciements se trouvent sur la <a class="border-b border-dotted border-red-700 text-red-800 hover:text-purple-700" href="/sites/merci">page dédiée</a>.
        <br /><br />
        ... ainsi qu'aux personnes et sites qui nous ont accordés partenariats ou droits d'usage :
        <ul class="list-disc pl-4 md:pl-12">
            <li><a class="border-b border-dotted border-red-700 text-red-800 hover:text-purple-700" href="https://litteraturepopulaire.1fr1.net/">A propos de Littérature Populaire</a>, forum dédié à la littérature populaire, littérature marginale, autre littérature et autre paralittérature.</li>
            <li><a class="border-b border-dotted border-red-700 text-red-800 hover:text-purple-700" href="http://www.forumpimpf.net/">Pimpf</a>, le gand forum de la BD populaire (petits formats, Nous les Vaillants, ...) et également de la Littérature Populaire.</li>
        </ul>
        <div class='m-2 md:mx-40'>
            <img class='inline mx-4' src="https://i.servimg.com/u/f15/11/11/75/03/a_prop10.jpg" style="height: 150px" /> 
            <img class='inline mx-4' src="https://www.forumpimpf.net/styles/forumpimpf/theme/images/site_logo.png" style="height: 100px" />
        </div>
        <ul class="list-disc pl-4 md:pl-12">
            <li><span class="text-blue-800">Charles Vassallo</span> qui nous avait permis d'utiliser un extrait de son travail pour notre bannière. Voir sur son site ses <a class="border-b border-dotted border-red-700 text-red-800 hover:text-purple-700" href="http://charles.vassallo.pagesperso-orange.fr/">travaux sur les fractales de Markus-Lyapounov</a>.</li>
            <li><span class="text-blue-800">Patrick Bouteau</span> qui nous avait permis d'utiliser les marques-pages orientés SF, fantasy et fantastique de son superbe site, <a class="border-b border-dotted border-red-700 text-red-800 hover:text-purple-700" href="http://www.lemuseedumarquepage.fr/Site/Accueil.htm/">le Musée du Marque-Page</a>.</li>
        </ul>
        <div class='m-2 md:mx-40'>
            <img class='inline mx-4' src="http://charles.vassallo.pagesperso-orange.fr/images/lyap_art/comp03.jpg" style="height: 250px" />
            <img class='inline mx-4' src="http://www.lemuseedumarquepage.fr/Editions/Images-B/Belial.Fr/97.jpg" style="height: 250px" />
            <img class='inline mx-4' src="http://www.lemuseedumarquepage.fr/Editions/Images-M/Mnemos.Fr/55.jpg" style="height: 250px" />
        </div>
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Techniques
    </div>
    <div class='text-base p-2 m-5 mx:4 md:mx-20 bg-yellow-200 border-l-4 border-red-600'>
        Un important chantier est toujours en cours actuellement, cette partie est encore amenée à évoluer.
    </div>
    <div class='text-base ml-2 sm:ml-8 pb-2'>
        <ul>
            <li><span class="font-semibold">Laravel</span> - framework de développement PHP</li>
            <li><span class="font-semibold">Tailwind CSS</span> - framework de développement CSS (feuilles de style) </li>
            <li><span class="font-semibold">MySQL</span> - base de données</li>
            <li><span class="font-semibold">Laravel Filament</span> - plugin panneau d'administration pour Laravel</li>
            <li><span class="font-semibold">barryvdh/laravel-debugbar</span> - plugin de debug laravel</li>
            <li><span class="font-semibold">venturecraft/revisionable</span> - plugin de gestion d'historique</li>
        </ul>
    </div>
</div>

@endsection