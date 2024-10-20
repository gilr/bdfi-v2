@extends('front.layout')
@section('content')

<x-front.menu-site tab='histov2' />

<div class='text-2xl my-2 md:mt-8 bold self-center'>
    <img sb-icon src="/img/{{ $icon }}" class="w-4 md:w-8 inline" title="{{ $title }}"/> <b>Releases V2 - Historique du développement</b>
</div>

<div class='text-base p-2 m-5 mx:4 md:mx-40 bg-yellow-200 border-l-4 border-red-600'>
    Les grandes lignes des évolutions de la version V2 du site. - Déjà 3 ans de développement, mais en pointillé et avec beaucoup de discussions pendant les deux premières années.
</div>

<div class='grid grid-cols-1 pb-12 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

    <div class='text-lg font-bold text-purple-900'>
        Version V2 bêta 7.1 - 18/10/2024
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        <ul class="list-disc pl-4 ml-4">
            <li>Corrections</li>
        </ul>
    </div>
    <div class='text-lg font-bold text-purple-900'>
        Version V2 bêta 7 - 16/10/2024
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        <ul class="list-disc pl-4 ml-4">
            <li>Gestion des collections multi-éditeurs</li>
            <li>Gestion et administration des articles (pour onglet de description d'une collections)</li>
            <li>"Slugification" des pages (url compréhensibles à la place des ID/numéro en base)</li>
            <li>Importante amélioration des temps d'affichage (particulièrement bibliographies et collections de centaines d'ouvrages)</li>
            <li>Ajout de l'info "vérifié" aux retirages</li>
            <li>Ajout des annonces de conventions et salons en page d'accueil</li>
            <li>Initialisation d'une zone 'chiffres et statistiques'</li>
            <li>Amélioration de la page d'accueil des salons et évènements</li>
            <li>Ajout d'une page listant les collections de la version béta...</li>
            <li>... et indiquant en pages éditeurs et recherche collection si une collection est présente en version bêta</li>
            <li>Ajout d'une première version d'un carrousel de quelques publication récentes en page d'accueil</li>
            <li>Mise en place de la version bêta sous bdfi.net (ne remplace pas le site de test existant)</li>
            <li>Gestion des spécificités particulières (indication, users) aux sites de test et de production</li>
        </ul>
    </div>
    <div class='text-lg font-bold text-purple-900'>
        Version V2 bêta 6 - 16/03/2024
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        <ul class="list-disc pl-4 ml-4">
            <li>Réinstallation sous Git avec stockage github</li>
            <li>Ajout d'un "compte" utilisateur et de quelques préférences (format date, nombre d'items par page, gestion collection)</li>
            <li>Ajout de la possibilité d'avoir un article approfondi pour certaines collections (pourra être étendu plus tard à d'autres types de fiches)</li>
            <li>Possibilité de stockage et présentations de documents PDF (type bibliographie) sur des auteurs (pourra être étendu plus tard à d'autres types de fiches)</li>
            <li>Ajout statut pour les publication (paru, annoncé, jamais paru, proposition)</li>
            <li>... et donc prise en compte des ouvrages annoncés non encore parus</li>
            <li>... et ajout d'un rôle membre "proposant"</li>
            <li>Une première mouture de la gestion de collection/bibliothèque personnelle</li>
            <li>Ajout en page d'accueil des auteurs nés et décédés ce jour, et des dernières publications crées et modifiées</li>
        </ul>
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Version V2 bêta 5 - 12 au 18/02/2024
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        La v2b4 était ambitieuse, mais... peu testée. La nouvelle version suit donc de peu, avec comme but le fix de la plupart des bugs et approximations côté site web, et quelques petits plus au passage :
        <ul class="list-disc pl-4 ml-4">
            <li>Gestion propre des "variantes" sur les fiches "texte" et sur les fiches "séries"</li>
            <li>Repérage complet des types de variantes (changement de titre, de trad, de signature, en épisode, extrait)</li>
            <li>Ordre propre des titres sur les fiches séries (10 après 9, "4 à 6" après 6).</li>
            <li>Publication des feuilletons affichée proprement, aussi bien le texte entier que les épisodes, avec liens entre eux - La solution retenue est de gérer les liens feuilleton-épisode avec la même info de "variants" (avec le risque de pb si feuilleton d'un déjà variant, donc 2 niveaux possibles... à voir - Si pb il faudra revoir l'approche)</li>
            <li>Petit plus, sur une fiche ouvrage, les boutons de navigation au sein d'une collection "gagnent" un bouton de retour à zéro</li>
            <li>Nettoyage du label de "type / contenu principal d'ouvrage"  principal" d'une fiche ouvrage. Il faut être synthétique mais propre, ce n'est pas si simple. Solution choisie pour l'instant assez proche de celle d'ISFDB</li>
            <li>Ajout d'une galerie sur les fiches "série" également - Sans onglets pour l'instant</li>
            <li>Premières améliorations des pages (biblios) auteur - Dans le cas d'un nom d'auteur avec pseudonyme, l'intégralité des textes sera affiché, quel que soit la signature. Dans le cas d'un pseudonyme, la page n'affiche que les textes parus sous cette signature.</li>
            <li>Admin "maison" : ajout des backup de la dizaine de tables autour des publications et textes. Voir les pages Asimov & French, Andrevon & Brutsche, ou encore Tubb ou Brackett.</li>
            <li>Admin "filament" : révision des actions utiles ou non selon le contexte, ajustement de labels, nombreux fix et reprises suites aux modifs de table. Pas encore fini !</li>
        </ul>
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Version V2 bêta 4 - 02/02/2024
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        Encore pas mal de grosses évolutions sur cette version, popup de visualisation d'images, ajout de galeries sur la plupart des pages, passage en onglet sur les pages chargées, gestion des rééditions de texte et de variantes de titres...
        <ul class="list-disc pl-4 ml-4">
            <li>Affichage possible de plusieurs images pour les ouvrages (avec simulation d'un nombre aléatoire, mais même image) - Exemple : <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/ouvrages/Veruchia'>Veruchia</a>,</li>
            <li>Affichage collection en onglet ou pas, selon nombre d'ouvrages - Exemples : <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/collections/Dimensions%20SF'>Dimensions SF</a>, ou <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/collections/Thraxas'>Thraxas</a>,</li>
            <li>Choix d'affichage ou non des publications d'une biblio auteur - Exemple : <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/auteurs/conners'>Conners</a> - A voir s'il faudra garder, ou comme ISFDB, ne jamais afficher les publications, pour un affichage plus synthétique (et rapide),</li>
            <li>Ajout d'onglet pour la galerie si page plus longue - Exemple : <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/auteurs/tubb'>E. C. Tubb</a>,</li>
            <li>... et ajout d'onglet pour la liste des prix - Exemple : <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/auteurs/evangelisti'>Evangelisti</a>,</li>
            <li>Gestion des oeuvres communes à plusieurs publications - Exemple : <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/textes/reflux'>Le Reflux</a>,</li>
            <li>Début de gestion des variantes de titres - Exemple : <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/textes/lallia'>Lallia</a>,</li>
            <li>Gestion d'un support type journal - Exemple : <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/editeurs/v%20_%20Voir'>V magazine</a></li>
            <li>Ajustement des index : ordre alphabétique par colonne et non plus par ligne, et ellipse pour les titres trop long - Exemple : <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/editeurs/index/p'>Index P des éditeurs</a>,</li>
            <li>Zone "<a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/retirages'>Retirages</a>" visible des membres seulement,</li>
            <li>Révision des menus surtout pour le "smartphone" - Pour tester, réduisez la fenêtre au min de largeur. Sera encore à peaufiner.</li>
            <li>Zone de recherche ajoutée partout à côté des initiales
        </ul>
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Version V2 bêta 3 - 22/12/2023
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        On quitte vraiment les versions "alpha", ça commence à ressembler à quelque chose, et les bases de la plupart des zones du site et de l'admin sont posées. Difficile d'être complet vu le nombre d'ajout, mais le fonctionnel reste incomplet, et surtout avec des parties très approximatives et de très nombreux bugs.
        <ul class="list-disc pl-4 ml-4">
            <li>Passe globale pour homogénéiser les pages, look, fontes utilisées</li>
            <li>Amélioration du pavé de données biographiques auteur</li>
            <li>Refonte et passage en revue de la présentation d'une page ouvrage (images, informations, organisation)</li>
            <li>Informations supplémentaires pour un utilisateur membre et connecté</li>
            <li>Finalisation d'une version plus propre des collections </li>
            <li>Augmentation du nombre de "Galeries" de couvertures</li>
            <li>Page des de filtre des auteurs par pays (plutôt qu'une liste sur les codes ISO)</li>
            <li>Insertion en base de quelques collections "vérifiées"</li>
        </ul>
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Version V2 alpha / bêta 2 - 25/08/2023
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        Beaucoup de temps (presque 2 ans !) avant cette alpha 2, d'une part des problèmes de santé handicapants - mais résolus, puis/et de gros problèmes techniques de table et de relations à résoudre, et en sus de ça, toute l'administration à revoir. Ajout de pages collections et ouvrages, navigation dans une collection (d'une page ouvrage à la précédente ou la suivante dans la même collection) et prise en compte des affichages responsifs : comment les pages peuvent s'adapter à la taille de l'écran, en retirant des infos progressivement, en réduisant les marges, et diminuant les polices...
        Côté admin, une  bonne et une mauvaise nouvelle. La mauvaise, c'est que pour la gestion admin j'utilisais Laravel Nova, qui est un package payant. Le problème est que pour rester compatible si on veut suivre les évolutions Laravel à partir de la v10 (j'étais en v7, je suis déjà passé en v9), il faut racheter une licence complète (même pas de licence type "upgrade" !). De plus, bien que plutôt joli, et fonctionne  rapidement avec des choix simples, il n'est pas très "ouvert", est limitatif dans la présentation des formulaires (que des listes verticales de champs), n'offre pas beaucoup de choix pour gérer tous les types de liaisons : les liaisons N to M (par exemple une publication est lié à plusieurs titre - son contenu, et un titre est contenu dans plusieurs recueils) ne sont modifiables que depuis la vue "consultation". Et si tout les 3-4 versions de Laravel il faut racheter ou être bloqué, ce n'est pas top. Donc j'abandonne Nova.

        La bonne nouvelle, c'est qu'il existe une alternative open source (Laravel Filament); j'ai essayé la version courante sur quelques tables de base, il faut un petit temps d'adaptation (mais comme Nova) et après, c'est nickel - Mais sur des tables plutôt simple pour l'instant, il faudra voir en attaquant des tables avec relations complexes. Pour ne pas se bloquer complètement, je reste sur la version v9 de laravel pour pouvoir travailler avec les deux admins, en continuant l'ancien et en développant aussi le nouveau en parallèle.
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Version V2 alpha 1+ - 23/05/2021
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        Ajout de la gestion des tables de prix, avec côté site web des pages par prix, par catégorie, par année, ainsi que des pages listant les catégories par genre (sf, fantastique...) ou par type (roman, nouvelle...).
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Version V2 alpha 1 - 25/04/2021
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        Le but de cette première version alpha est de montrer la faisabilité, et en particulier l'administration dans un contexte réaliste, adossé à une site web simplifié mais réel. Le site web lui-même ne comprend que le menu, un accès aux index auteurs et des pages auteurs chose, et les bases de la plupart des zones du site et de l'admin sont posées. L'administration (gestion des tables) est basée sur l'outil d'administration Laravel Nova (logiciel payant). Mais le fonctionnel reste incomplet, avec des parties manquantes et de très nombreux bugs.
    </div>

</div>

@endsection