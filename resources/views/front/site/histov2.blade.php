@extends('front.layout')
@section('content')

<x-front.menu-site tab='histov2' />

<div class='text-2xl my-2 md:mt-8 bold self-center'>
    <img sb-icon src="/img/{{ $icon }}" class="w-4 md:w-8 inline" title="{{ $title }}"/> <b>Releases V2 - Historique du développement</b>
</div>

<div class='text-base p-2 m-5 mx:4 md:mx-40 bg-yellow-200 border-l-4 border-red-600'>
    Les grandes lignes des évolutions de la version V2 du site. - Bientôt 5 ans de développement, mais avec énormément de discussions durant les deux premières années, puis souvent en pointillé, et avec des périodes d'arrêt parfois très longues.
</div>

<div class='grid grid-cols-1 pb-12 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

    <div class='text-lg font-bold text-purple-900'>
        Version V2 bêta 9 - 13/02/2026
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        <ul class="list-disc pl-4 ml-4">
            <li>Correctifs et améliorations suite aux tests de la bêta 8</li>
            <li>Séries : affichage en liste ou en galerie selon la taille, affiche/cacher les ouvrages (idem bibliographies), ajout d'ascenseur sur la zone des séries filles</li>
            <li>Séries, biblio, textes, collections, éditeurs : pouvoir zoomer sur une couverture</li>
            <li>Bibliographies : afficher les séries d'un auteur en page bibliographique, double drapeau pour les binationaux</li>
            <li>Collections : gestion des nouvelles éditions dans les galeries</li>
            <li>Ajout d'une page FAQ, sur laquelle renvoient quelques signes <sup title="Marque d'information et renvoi sur la FAQ"><a href="/site/faq"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 inline"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" /></svg></a></sup>.</li>
            <li>Administration : Ajout d'un rapport sur les nationalités mal renseignées + fix gros trou de sécurité</li>
        </ul>
    </div>
    <div class='text-lg font-bold text-purple-900'>
        Version V2 bêta 8 - 15/03/2025
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        <ul class="list-disc pl-4 ml-4">
            <li>Ouvrages/retirages : révision du design, zoom image, améliorations</li>
            <li>Accueil : ajout du bloc des dernières discussions forum, rendre collapsable les infos de page d'accueil et les blocs 'membres' de bas de fiche</li>
            <li>Infos site/stats : ajouts des courbes de stats BDFI et de parutions annuelles par genre et format.</li>
            <li>Administration : ajout rapide auteur, éditeur, collection, ou oeuvre, ajout rapide ouvrage paru, à paraître ou en proposition, visualiser les ouvrages à paraître, valider des ouvrages proposés, vérification rapide si auteur, collection, éditeur, publication ou oeuvre existe, révisions des menus, formulaires, regroupement outils et rapports</li>
            <li>Administration "filament" : tests et ajustements de toute la gestion filament des prix</li>
            <li>Ajout de modes maintenance (un pour le site, un pour l'admin)</li>
        </ul>
    </div>
    <div class='text-lg font-bold text-purple-900'>
        Version V2 bêta 7 - 22/10/2024
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        <ul class="list-disc pl-4 ml-4">
            <li>Gestion des collections multi-éditeurs</li>
            <li>Gestion et administration des articles (pour onglet de description d'une collections)</li>
            <li>"Slugification" des pages (url compréhensibles à la place des ID/numéro en base)</li>
            <li>Importante amélioration des temps d'affichage (particulièrement bibliographies et collections de centaines d'ouvrages)</li>
            <li>Ajout de l'informaton "vérifié" aux retirages</li>
            <li>Accueil : ajout d'un carrousel de quelques publication récentes, ajout des annonces de conventions et salons</li>
            <li>Initialisation d'une zone 'chiffres et statistiques'</li>
            <li>Salons : amélioration de la page d'accueil des salons et évènements</li>
            <li>Ajout d'une page listant les collections de la version béta...</li>
            <li>... et indiquant en pages éditeurs et recherche collection si une collection est présente en version bêta</li>
            <li>Mise en place de la version bêta sous bdfi.net - en supplément du site de test déjà existant</li>
            <li>Gestion des spécificités particulières (indications, users) aux sites de test et de production</li>
        </ul>
    </div>
    <div class='text-lg font-bold text-purple-900'>
        Version V2 bêta 6 - 16/03/2024
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        <ul class="list-disc pl-4 ml-4">
            <li>Accueil : ajout des auteurs nés et décédés ce jour, et des dernières publications crées et modifiées</li>
            <li>Ajout statut pour les publication (paru, annoncé, jamais paru, proposition), prise en compte des ouvrages annoncés non encore parus</li>
            <li>Ajout de la possibilité d'avoir un article approfondi pour certaines collections (pourra être étendu plus tard à d'autres types de fiches)</li>
            <li>Possibilité de stockage et présentations de documents PDF (type bibliographie) sur des auteurs (pourra être étendu plus tard à d'autres types de fiches)</li>
            <li>Ajout d'un "compte" utilisateur et de quelques préférences (format date, nombre d'items par page, gestion collection), et première mouture de la gestion de collection/bibliothèque personnelle</li>
            <li>Administation : ajout d'un rôle membre "proposant"</li>
            <li>Réinstallation sous Git avec stockage github</li>
        </ul>
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Version V2 bêta 5 - 12 au 18/02/2024
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        La v2b4 était ambitieuse, mais... peu testée. La nouvelle version suit donc de peu, avec comme but la correction de la plupart des bugs et approximations côté site web, et quelques petits plus au passage :
        <ul class="list-disc pl-4 ml-4">
            <li>Ouvrages : petit plus, les boutons de navigation au sein d'une collection "gagnent" un bouton de retour à zéro</li>
            <li>Séries : ordre propre des titres (10 après 9, "4 à 6" après 6), ajout d'une galerie - sans onglets pour l'instant</li>
            <li>Bibliographies : premières améliorations - Dans le cas d'un nom d'auteur avec pseudonyme, l'intégralité des textes sera affiché, quel que soit la signature. Dans le cas d'un pseudonyme, la page n'affiche que les textes parus sous cette signature.</li>
            <li>Gestion propre des "variantes" sur les fiches "texte" et sur les fiches "séries"</li>
            <li>Repérage complet des types de variantes (changement de titre, de trad, de signature, en épisode, extrait)</li>
            <li>Publication des feuilletons affichée proprement, aussi bien le texte entier que les épisodes, avec liens entre eux - La solution retenue est de gérer les liens feuilleton-épisode avec la même info de "variants" (avec le risque de pb si feuilleton d'un déjà variant, donc 2 niveaux possibles... à voir - Si pb il faudra revoir l'approche)</li>
            <li>Nettoyage du label de "type / contenu principal d'ouvrage"  principal" d'une fiche ouvrage. Il faut être synthétique mais propre, ce n'est pas si simple. Solution choisie pour l'instant assez proche de celle d'ISFDB</li>
            <li>Administration "maison" : ajout des backup de la dizaine de tables autour des publications et textes.</li>
            <li>Admin "filament" : révision des actions utiles ou non selon le contexte, ajustement de labels, nombreux fix et reprises suites aux modifs de table. Pas encore fini !</li>
        </ul>
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Version V2 bêta 4 - 02/02/2024
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        Encore pas mal de grosses évolutions sur cette version, popup de visualisation d'images, ajout de galeries sur la plupart des pages, passage en onglet sur les pages chargées, gestion des rééditions de texte et de variantes de titres...
        <ul class="list-disc pl-4 ml-4">
            <li>Ouvrages: affichage possible de plusieurs images (avec simulation d'un nombre aléatoire, mais même image) - Exemple : <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/ouvrages/Veruchia'>Veruchia</a>,</li>
            <li>Collections : affichage optionnel en onglet, selon nombre d'ouvrages - Exemples : <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/collections/Dimensions%20SF'>Dimensions SF</a>, ou <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/collections/Thraxas'>Thraxas</a>,</li>
            <li>Bibliographies : choix d'affichage ou non des publications (A voir s'il faudra garder, ou comme ISFDB, ne jamais afficher les publications pour un affichage plus synthétique (et rapide), ajout d'onglet pour la galerie si page plus longue, ainsi qu'un onglet pour la liste des prix si besoin</li>
            <li>Gestion des oeuvres communes à plusieurs publications - Exemple : <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/textes/reflux'>Le Reflux</a>,</li>
            <li>Début de gestion des variantes de titres - Exemple : <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/textes/lallia'>Lallia</a>,</li>
            <li>Gestion d'un support type journal - Exemple : <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/editeurs/v%20_%20Voir'>V magazine</a></li>
            <li>Ajustement des index : ordre alphabétique par colonne et non plus par ligne, et ellipse pour les titres trop long - Exemple : <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/editeurs/index/p'>Index P des éditeurs</a>,</li>
            <li>Création d'une zone Retirages, visible des membres uniquement,</li>
            <li>Révision des menus pour la taille "smartphone" - Pour tester, réduisez la fenêtre au min de largeur. Encore sans doute à peaufiner...</li>
            <li>Zone de recherche ajoutée partout à côté des initiales</li>
            <li>Abandon définitif de Nova</li>
        </ul>
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Version V2 bêta 3 - 22/12/2023
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        On quitte les versions "alpha", les bases de la plupart des zones du site et de l'admin sont posées. Difficile d'être exhaustif vu le nombre d'ajout, cependant le fonctionnel reste incomplet, avec surtout des parties encore approximatives et de très nombreux bugs.
        <ul class="list-disc pl-4 ml-4">
            <li>Reprise globale pour homogénéiser les pages, le look, les fontes utilisées...</li>
            <li>Bibliographies : amélioration du pavé de données biographiques</li>
            <li>Ouvrages : refonte et passage en revue de la présentation de la page (images, informations, organisation)</li>
            <li>Collections : finalisation d'une version plus propre</li>
            <li>Augmentation du nombre de "Galeries" de couvertures</li>
            <li>Page des filtres des auteurs par pays (plutôt qu'une liste sur les codes ISO)</li>
            <li>Informations supplémentaires pour un utilisateur membre et connecté</li>
            <li>Insertion en base de quelques collections "vérifiées"</li>
        </ul>
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Version V2 alpha 2 - 25/08/2023
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        Beaucoup de temps (presque 2 ans !) avant cette version, d'une part en raison de problèmes de santé handicapants - mais sans gravité et résolus, puis/et de gros problèmes techniques de table et de relations à résoudre, et en sus de ça, toute l'administration à revoir. Ajout de pages collections et ouvrages, navigation dans une collection (d'une page ouvrage à la précédente ou la suivante dans la même collection) et prise en compte des affichages responsifs : comment les pages peuvent s'adapter à la taille de l'écran, en retirant des infos progressivement, en réduisant les marges, et diminuant les polices...<br />
        Côté administration, une  bonne et une mauvaise nouvelle. La mauvaise, c'est que pour la gestion admin il faut abandonner Laravel Nova. C'est un package payant, acceptable, mais le problème est que pour rester compatible si on veut suivre les évolutions Laravel à partir de la v10 (après un début v7, puis v9), il faut racheter une nouvelle licence complète (il n'y pas de licence type "upgrade" !). De plus, bien que plutôt joli, et fonctionnant rapidement avec des choix simples, il n'est pas très "ouvert", et est limitatif dans la présentation des formulaires (listes verticales de champs uniquement). De plus, il n'offre que peu de choix de gestion de tous les types de liaisons : les liaisons N to M (par exemple une publication est lié à plusieurs titre - son contenu, et un titre est contenu dans plusieurs recueils) ne sont modifiables que depuis la vue "consultation".<br />
        La bonne nouvelle, c'est qu'il existe une alternative open source (Laravel Filament); après essai de la version courante sur quelques tables de base, il faut un petit temps d'adaptation (mais comme Nova) et après, c'est fonctionne bien - Sur des tables plutôt simples pour l'instant, il faudra voir en attaquant des tables avec relations complexes. Pour ne pas se bloquer complètement, Laravel reste en v9 pour pouvoir travailler pour l'instant en parralèle avec les deux packages admins.
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Version V2 alpha 1.5 - 23/05/2021
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        Ajout de la gestion des tables de prix, avec côté site web des pages par prix, par catégorie, par année, ainsi que des pages listant les catégories par genre (sf, fantastique...) ou par type (roman, nouvelle...).
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Version V2 alpha 1 - 25/04/2021
    </div>
    <div class='grid grid-cols-1 text-base px-2 mx-2 md:mx-12 self-center'>
        Le but de cette première version alpha était de montrer la faisabilité, et en particulier l'administration dans un contexte réaliste, adossé à une site web simplifié mais réel. Le site web lui-même ne comprend qu'un menu, un accès aux index auteurs et des pages auteurs. Les bases de la plupart des zones du site et de l'administration sont posées. L'administration (gestion des tables) est basée sur l'outil d'administration Laravel Nova (logiciel payant). Mais le fonctionnel reste incomplet, avec des parties manquantes et de très nombreux bugs.
    </div>

</div>

@endsection