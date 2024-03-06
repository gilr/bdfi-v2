@extends('front.layout')

@section('content')

    <div class="flex text-4xl pt-2 self-center">
        BDFI
    </div>
    <div class="flex text-xl pb-2 self-center">
        Version de test - {{ env('VERSION') }}
    </div>

    <div class='text-base p-4 m-4 bg-sky-100 self-center border border-blue-400'>
        <span class="font-bold text-slate-600">/!\ Version de test BDFI</span>. Pour les informations de test, voir un peu plus bas.
        <br />L'historique du développement en cours a été déplacée sur la page des <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/site/historique-v2'>versions V2</a>. Vous pouvez aussi consulter <a class='underline text-blue-800 sm:p-0.5 md:px-0.5' href='https://docs.google.com/spreadsheets/d/1TpwE3NHkqAyoUvy53aJ76SZpF0ZY87gl6y3EUMfq1pM'>fichier de suivi</a> (doc Google). La base des ouvrages est volontairement limitée tant que toutes les fonctionnalités ne sont pas développées. On peut néanmoins y trouver :
        <ul class="list-disc pl-4 ml-4">
            <li>Des listes d'ouvrages vérifiées (au moins partiellement) : collections 'Dimensions SF' et 'Fantasy' de l'éditeur <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/editeurs/calmann-lévy'>Calmann-Levy</a>, 'Epées et Dragons', 'Super Fiction' et 'Super + Fiction' de <a class='text-red-700 sm:p-0.5 md:px-0.5' href='/editeurs/albin%20michel'>Albin Michel</a>, plusieurs mini-collections (Shadowrun, Tomb Raider, Titan AE, Thraxas, Vampires, Virtuel et Wacraft) ainsi que les Thriller Fantastique chez <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/editeurs/fleuve%20noir'>Fleuve Noir</a>, les collections <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/collections/payot%20sf'>Payot SF</a>, <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/collections/millénaires'>J'ai Lu Millénaires</a>, les ouvrages de <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/editeurs/argyll'>Argyll</a>, ainsi que les Folio Fantasy, Rivages fantasy, et Alire Grand Format.</li>
            <li>D'autres collections non vérifiées, ajoutées afin d'augmenter un peu la taille des biblios et séries, et de voir l'impact de collections de 100, 200 ou 300 ouvrages : Le Masque fantastique (rouge et noir), le Masque SF, Galaxie bis, les différentes éditions "Dumarest", et la collection Angoisse Fleuve noir.
            <li>Un exemple de support de type magazine : <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/editeurs/v%20_%20Voir'>V magazine</a></li>
            <li>Un exemple de support de type revue/fanzine, <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/editeurs/basis'>Basis</a></li>
            <li>Des exemples de <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/textes/chaise%20infernale'>feuilleton (parus en épisodes)</a>, de <a class='underline text-red-700 sm:p-0.5 md:px-0.5' href='/ouvrages/route%20étoilée'>retirage (réimpression)</a>, de texte repris dans plusieurs publications, et de gestion de "variantes" de texte (signature modifiée, titre modifié, traduction modifiée).
        </ul>

        <br />Sont envisagés - à priori - pour la prochaine release :
        <ul class="list-disc pl-4 ml-4">
            <li>Tests et adaptations de pages biblios de pseudos partagés (Erin Hunter, Benoît Becker, Limite...) et des auteurs qui les utilisent</li>
            <li>Ajout de la gestion des ouvrages "à paraître"</li>
            <li>Première mouture d'une gestion de bibliothèque pour les inscrits</li>
            <li>Création de quelques interfaces d'admin "conviviales", parmi : création publi, création éditeur, création collection, clone publi...</li>
            <li>Voir comment gérer les traducteurs, les illustrateurs</li>
            <li>La suite des tests et corrections de l'administration classique</li>
    </div>

    <div class='text-base p-4 m-4 self-center border border-orange-400'>
        Le site BDFI (Base de Données Francophone de l'Imaginaire)  est consacré aux parutions et traductions francophones de l'imaginaire (science-fiction, fantastique, horreur, fantasy...). Il présente les bibliographies de plus de 16 000 auteurs, les éditeurs, les collections, les cycles, les principaux prix français et étrangers, ainsi que les évènements littéraires francophones associés à ces domaines de l'imaginaire.
        Visitez également nos forums et n'hésitez pas à participer !
    </div>
    <div class='grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-1 text-sm m-4 self-center'>
        <div class='border border-orange-400 px-2 sm:pl-5 sm:pr-2 md:pl-4 md:pr-4'>
            <div class='text-center font-bold'>Programmes et parutions</div>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. In quis luctus nisi. Nullam lectus ligula, tincidunt et mi vitae, ornare molestie dui. Morbi porttitor dictum bibendum. Nullam pretium lectus id eros tincidunt pretium. Integer porta iaculis eros, in lacinia arcu imperdiet et. Nunc consectetur velit sit amet ligula porta...
        </div>
        <div class='border border-orange-400 px-2 sm:pl-5 sm:pr-2 md:pl-4 md:pr-4'>
            <div class='text-center font-bold'>Annonces</div>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. In quis luctus nisi. Nullam lectus ligula, tincidunt et mi vitae, ornare molestie dui. Morbi porttitor dictum bibendum. Nullam pretium lectus id eros tincidunt pretium. Integer porta iaculis eros, in lacinia arcu imperdiet et. Nunc consectetur velit sit amet ligula porta...
        </div>
        <div class='border border-orange-400 px-2 sm:pl-5 sm:pr-2 md:pl-4 md:pr-4'>
            <div class='text-center font-bold'>Dernières mises à jour</div>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. In quis luctus nisi. Nullam lectus ligula, tincidunt et mi vitae, ornare molestie dui. Morbi porttitor dictum bibendum. Nullam pretium lectus id eros tincidunt pretium. Integer porta iaculis eros, in lacinia arcu imperdiet et. Nunc consectetur velit sit amet ligula porta...
        </div>
    </div>
    <div class='grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-1 text-sm m-4 self-center'>
        <div class='border border-orange-400 px-2 sm:pl-5 sm:pr-2 md:pl-4 md:pr-4'>
            <div class='text-center font-bold'>Anniversaires de naissance</div>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. In quis luctus nisi. Nullam lectus ligula, tincidunt et mi vitae, ornare molestie dui. Morbi porttitor dictum bibendum. Nullam pretium lectus id eros tincidunt pretium. Integer porta iaculis eros, in lacinia arcu imperdiet et. Nunc consectetur velit sit amet ligula porta...
        </div>
        <div class='border border-orange-400 px-2 sm:pl-5 sm:pr-2 md:pl-4 md:pr-4'>
            <div class='text-center font-bold'>Dernières discussions forum</div>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. In quis luctus nisi. Nullam lectus ligula, tincidunt et mi vitae, ornare molestie dui. Morbi porttitor dictum bibendum. Nullam pretium lectus id eros tincidunt pretium. Integer porta iaculis eros, in lacinia arcu imperdiet et. Nunc consectetur velit sit amet ligula porta...
        </div>
        <div class='border border-orange-400 px-2 sm:pl-5 sm:pr-2 md:pl-4 md:pr-4'>
            <div class='text-center font-bold'>Auteurs décédés ce jour</div>
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. In quis luctus nisi. Nullam lectus ligula, tincidunt et mi vitae, ornare molestie dui. Morbi porttitor dictum bibendum. Nullam pretium lectus id eros tincidunt pretium. Integer porta iaculis eros, in lacinia arcu imperdiet et. Nunc consectetur velit sit amet ligula porta...
        </div>
    </div>

    <div class='text-base bg-sky-100 pt-1 p-5 mx-4 self-center border border-blue-400 text-blue-900 shadow-sm shadow-blue-600 rounded-sm px-1'>
        <div class='p-1'>
            <span class='text-base bg-sky-200 text-red-800 mt-1'>Informations de test</span> :
            Le menu latéral permet de naviguer de zone en zone. Il est possible de plier ou déplier ce menu, et votre configuration est conservée. Au survol en mode icone, le nom de la zone apparait.<br />
            La connexion n'est pas obligatoire pour naviguer sur le site.
        </div>
        <div class='p-1'>
            <span class='text-base bg-sky-200 text-red-800 mt-1'>Comptes de test</span> :
            Un membre connecté avec assez de droits peut voir en supplément sur les pages/fiches du site quelques infos internes ainsi que des liens d'accès directs aux fiches, permettant ainsi des modifications (données de test, sans risques donc).
            N'ont pas accès aux zones d'administration par défaut, les personnes non connectées, le compte <span class="font-bold text-blue-800">user@bdfi.net</span></li> ainsi que les comptes créés via le lien <span class="font-bold text-yellow-800">Inscription</span>.
            Le mot de passe de tous les comptes de test pré-créés est <span class="font-bold text-blue-800">password</span>.<br />
            Les comptes utilisables avec accès administration sont :<br />
            <ul class="list-disc pl-12">
                <li><span class="font-bold text-blue-800">visitor@bdfi.net</span> : ne permet aucune modification</li>
                <li><span class="font-bold text-blue-800">proposant@bdfi.net</span> : permet de demander un ajout</li>
                <li><span class="font-bold text-blue-800">editor@bdfi.net</span>, <span class="font-bold text-blue-800">editor2@bdfi.net</span> et <span class="font-bold text-blue-800">editor3@bdfi.net</span> : gestion des tables biblios</li>
                <li><span class="font-bold text-blue-800">admin@bdfi.net</span>, <span class="font-bold text-blue-800">admin2@bdfi.net</span> et <span class="font-bold text-blue-800">admin3@bdfi.net</span> : quelques droits supplémentaires</li>
                <li><span class="font-bold text-blue-800">sysadmin@bdfi.net</span> : pas de limitations (sauf si pas de sens)</li>
            </ul>
            L'accès administration s'effectue via  les liens en haut à droite, <span class="font-bold text-yellow-800">Se connecter</span> si non déjà authentifié, ou <span class="font-bold text-yellow-800">Administration</span> ou <span class="font-bold text-yellow-800">Gestion des tables</span> si vous l'êtes.<br />
        </div>
        <div class='p-1'>
            <span class='text-base bg-sky-200 text-red-800 mt-1'>Nota</span> : Un mode debug est temporairement activé, mais vous pouvez cliquer sur la petite croix (extrémité droite) de la barre de debug en bas de page pour la réduire.
        </div>
        <div class='p-1 mt-2'>
                Ci-dessous en image, la visualisation des différents domaines et zones du site et la navigation :
        </div>
        <img src="/img/bdfi_navigation.jpg" />
    </div>

@endsection
