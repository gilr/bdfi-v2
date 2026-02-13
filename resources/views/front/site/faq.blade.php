@extends('front.layout')
@section('content')

<x-front.menu-site tab='faq' />

<div class='text-2xl my-2 md:mt-8 bold self-center'>
    @if ((!auth()->user()) || (auth()->user() && auth()->user()->with_icons))
        <img sb-icon src="/img/{{ $icon }}" class="w-4 md:w-8 inline" title="{{ $title }}"/>
    @endif
    <b>Questions fréquentes</b>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    <div class='text-lg font-bold text-purple-900'>
        Quel sens est donné au terme "retirage" sur BDFI ?
    </div>
    <div class='text-base p-2 mx-2 md:mx-12 self-center'>
        Le terme <span class="font-bold">retirage</span> est utilisé pour un ouvrage qui ne se différencie d'un autre ouvrage que par l'Achevé d'Imprimé (AI), et éventuellement les pages de fin listant les ouvrages de la même collection (la "bibliographie d'intérêt commercial ou publicitaire"). Exceptionnellement, dans le cas manifeste d'une erreur, la date de Dépot Légal (DL) peut avoir été modifiée.<br />
        Ce choix permet de ne pas surcharger les listes et galeries d'ouvrages visuellement identiques. Les retirages sont néanmoins recensés, et listés en page ouvrage.<br />
        <div class='text-sm mt-2'>Notes: <br />
        Les dictionnaires (Robert, Académie Française), qui définissent le retirage comme une nouvelle impression à l'identique, ne peuvent pas être suivis à la lettre puisque certaines informations imprimées sont de la responsabilité de l'imprimeur et non de l'éditeur (tels l'Achevé d'Imprimé ou l'indication du lieu d'impression).<br />
        Le modèle FRBR, une norme élaborée par l'IFLA (Fédération internationale des associations de bibliothécaires et des bibliothèques) ne définit pas les termes retirage et réimpression, mais use des terme "expression" (le contenu intellectuel) et "manifestation" (sa matérialisation), et définit ainsi ce que sont une nouvelle expression ou une nouvelle manifestation (<a href="https://multimedia-ext.bnf.fr/pdf/frbr_rapport_final.pdf">source</a>). Les règles de catalogage Sudoc sont particulières, bien qu'en partie basées sur ce modèle FRBR. Elles fixent les critères qui déterminent le besoin ou non de création d'une nouvelle notice : les critères de modifications acceptées sans nécessité de nouvelle notices sont (liste non exhaustive) la couverture, une variation minime de format, l'ISBN, le prix, la bibliographie d'intérêt commercial ou publicitaire, lees feuillets blancs de fin d'ouvrage... Le contenu intellectuel lui ne doit évidemment pas être modifié, ni le nombre de feuillets en début d'ouvrage (impact sur la pagination), sinon il est nécessaire de créer une nouvelle notice (<a href="https://documentation.abes.fr/sudoc/regles/Catalogage/Regles_EditionsImpressionsTirages.htm">source</a>).</div>
    </div>
</div>
<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    <div class='text-lg font-bold text-purple-900'>
        Et le terme réimpression ?
    </div>
    <div class='text-base p-2 mx-2 md:mx-12 self-center'>
        Le terme <span class='font-bold'>réimpression</span> est souvent assimilé à retirage, mais est parfois également utilisé pour une nouvelle édition contenant des modifications mineures. Les limites de ces modifications n'étant pas clairement définies, ce terme n'a pas été utilisé.<br />
        <div class='text-sm mt-2'>Note: Pour Sudoc, est une réimpression un ouvrage qui ne nécessite pas de nouvelle notice. Et donc, un ouvrage relié qui devient broché, avec nouvel ISBN, nouvelle couverture, modifications des feuillets de fin est une réimpression. Par contre un ouvrage pour lequel la seule modification est l'ajout de deux pages de bibliographie en début d'ouvrage ne sera pas une réimpression mais une nouvelle édition. Choix qui peut sembler illogique, mais qui est pragmatique, lié au décalage de numérotation du contenu.</div>
    </div>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    <div class='text-lg font-bold text-purple-900'>
        Dans quel cas sont utilisés les termes "nouvelle édition" et réédition" ?
    </div>
    <div class='text-base p-2 mx-2 md:mx-12 self-center'>
        Le terme <span class="font-bold">nouvelle édition</span> pour un ouvrage est utilisé de façon spécifique, <b>uniquement dans le cadre d'une même collection</b>, s'il ne s'agit pas d'un retirage, mais que le contenu principal intellectuel est identique ou très proche (préfacé ou révisé par exemple...).<br />
        Il permet de pouvoir filtrer l'affichage des nouvelles éditions, ce qui peut s'avérer pratique dans le cas d'une collection avec une multitudes de variantes de couverture.<br />
        Le terme <span class='font-bold'>réédition</span> est évité, il s'appliquait plutôt à l'origine à une oeuvre (réédition du contenu intellectuel, et non de l'objet physique livre), même si le sens est devenu plus large... et moins précis.
    </div>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    <div class='text-lg font-bold text-purple-900'>
        Pourquoi cet ouvrage est-il noté SF (ou fantastique, ou fantasy) alors qu'il s'agit de fantastique (ou de fantasy ou de sf) ?
    </div>
    <div class='text-base p-2 mx-2 md:mx-12 self-center'>
        Ce "genre" attaché à un ouvrage, un texte ou une série ne se veut pas une classification officielle et définitive, mais plutôt un label de référencement à usage statistique. Ce label peut parfois être discutable, en particulier dans le cas d'une oeuvre en limite de genres. Il n'est là ni pour délimiter ou "mettre dans des cases", ni pour imposer la vision de BDFI, mais uniquement pour permettre de réaliser des statistiques globales, par exemple sur l'évolution et la répartition de la production francophone d'imaginaire.
    </div>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    <div class='text-lg font-bold text-purple-900'>
        Pourquoi utiliser les termes "Fix-Up" et "chroniques" en plus ou au lieu de "recueil" ?
    </div>
    <div class='text-base p-2 mx-2 md:mx-12 self-center'>
        Le terme <span class='font-bold'>fix-up</span> (ou roman-collage) est utilisé usuellement pour un roman que l'auteur a constitué à partir de textes pré-publiés, possiblement révisés et/ou complétés d'un prologue, de chapitres intermédiaires ou complémentaires, et d'épilogue. Si les nouvelles reprises sont clairement identifiables et possèdent un titre, elles sont alors indiquées de la même façon qu'un sommaire. Dans le cas contraire, le détail n'est pas listé, mais ces textes originaux peuvent être indiqués dans la partie commentaire de l'oeuvre ou de l'ouvrage.<br />
        Le terme <span class='font-bold'>chroniques</span> est utilisé sur BDFI pour un recueil de textes qui possèdent tous une relation forte entre eux. Il ne s'agit pas d'une relation thématique, mais d'une relation liée aux histoires elles-mêmes, que ce soit des histoires d'un même personnage ou d'un même groupe, les histoires d'une même cité, ou des histoires différentes mais racontées par un même groupe de personnage.

    </div>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    <div class='text-lg font-bold text-purple-900'>
        A quoi correspond la "recherche élargie" dans une zone donnée ?
    </div>
    <div class='text-base p-2 mx-2 md:mx-12 self-center'>
        La recherche simple (non "élargie") permet de chercher tous les éléments de cette zone dont le nom principal contient la chaîne de texte (l'extrait de nom) saisie. La recherche élargie permet d'étendre cette recherche à d'autres informations attachées aux élements, qui peuvent être les pseudonymes, le nom complet ou une autre forme d'écriture (pour un auteur), ou des variantes de nommage (pour un éditeur, une collection, un ouvrage, un texte ou une série), voire même des informations liées (les noms d'éditeurs pour une recherche de collections ou inversement).
    </div>
</div>

@endsection