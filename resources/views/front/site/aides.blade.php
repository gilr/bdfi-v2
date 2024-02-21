@extends('front.layout')
@section('content')

<x-front.menu-site tab='aides' />

<div class='text-2xl my-2 md:mt-8 bold self-center'>
    <img sb-icon src="/img/{{ $icon }}" class="w-4 md:w-8 inline" title="{{ $title }}"/> <b>Et si je souhaite vous aider ? - How can I help?</b>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    <div class='text-lg pt-2 mt-2 self-center'>
        Wie kann ich helfen? - ¿Cómo puedo ayudar? - Чем могу помочь? - كيف يمكنني أن أقدم المساعدة؟ 
    </div>

    <div class='hidden md:flex text-base p-2 m-5 mx:4 md:mx-40 bg-yellow-200 border-l-4 border-red-600'>
         Toute aide est la bienvenue, selon votre temps et vos possibilités, que vous soyez auteur, passionné ou fanéditeur, vous pouvez contribuer ou compléter des données vous concernant.<br />
         Any help, contribution or correction is welcome!
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Auteur - Writer
    </div>
    <div class='text-base ml-2 sm:ml-8'>
        Nous recevrons avec plaisir les corrections ou compléments concernant votre bibliographie.<br />
        N'hésitez pas également si vous souhaitez ajouter d'autres informations; Voici la liste de ce que nous pouvons ajouter actuellement sur une page bibliographique : Nationalité, date et lieu de naissance - Court paragraphe biographique à la troisième personne (contenu à votre convenance, 1000 car. max) - Adresse de votre site web, ou une page personnelle, bibliographique ou non - Toutes les fictions (des domaines sf, fantastique, fantasy, merveilleux, horreur, étrange - Pas de hors-genres) parues sur support papier (y compris fanzines et auto-éditions) ou numérique (si non publié au format papier, et uniquement d'un éditeur ou d'un magazine de niveau national, ou d'une publication recensées par la BNF - Sont exclues les éditions à compte-d'auteur, plateforme de publication et POD, que ce soit en numérique ou papier.) - Parmi les fictions, celles qui appartiennent à un cycle ou à une série - Et également, et dans les mêmes domaines, les ouvrages de type essai ou guide, ainsi que les articles (même si ces derniers n'apparaissent pas tous pour l'instant dans nos pages).<br />
        Pour toute contribution, écrivez nous (voir la page <a class='font-semibold border-purple-300 border-b hover:bg-orange-100 border-b hover:border-purple-600' href='/site/contact'>Contact</a>).
    </div>
    <hr class="mx-24 my-2 border-dotted border-purple-800"/>
    <div class='text-base ml-2 sm:ml-8'>
        You are a writer?<br />
        We would be happy to receive corrections or complements about your bibliography.<br />
        Do not hesitate if you wish to add other information; Here is the list of what we can currently add on a bibliographical page: Nationality, date of birth and birthplace - Small biographical paragraph (feel free for the content) - Website or personal page URL - All texts of fiction translated in french (in the following genres only: sf, fantastic, fantasy, horror...) published on printed medium (including fanzines and vanity press), with indication of the first publication date and original and variant (if any) titles. We're also interested by your non-fiction books (essay, guide...) in the aforesaid genres.<br />
        If you want to contribute, please send an e-mail to C. Moulin and/or G. Richardot (refer to the <a class='font-semibold border-purple-300 border-b hover:bg-orange-100 border-b hover:border-purple-600' href='/site/contact'>Contact</a> page).<br />
        Many thanks!
    </div>

    <div class='text-lg font-bold text-purple-900'>
        Passionnés
    </div>
    <div class='text-base ml-2 sm:ml-8'>
        Vous êtes un passionné ? Nous recherchons des aides ponctuelles ou des collaborateurs réguliers, selon votre choix et en fonction de vos disponibilités :
        <ul class="list-disc pl-4 md:pl-12">
            <li>Correspondants en pays francophones (Belgique, Canada, Suisse...) pour les parutions et prévisions de parutions dans ces pays,</li>
            <li>Passionnés disposant de bases de données sur ces pays francophones, et désireux de collaborer à BDFI,</li>
            <li>Fans de l'imaginaire d'une autre nation européenne (Italie, Espagne, Allemagne, etc), pour nous aider à recenser les oeuvres traduites et mettre à jour les courtes infos/bios sur les auteurs.</li>
            <li>Collectionneurs d'un éditeur, une collection, un fanzine, une revue... pour la mise à jour et le contrôle de nos données actuelles (contenu des recueils, dates, etc),</li>
            <li>Collectionneurs de SF ou de fantastique d'avant-guerre ou de périodiques.</li>
        </ul>
        Pour toute contribution, écrivez-nous ! 
    </div>
    <div class='text-lg font-bold text-purple-900'>
        Fanéditeur
    </div>
    <div class='text-base ml-2 sm:ml-8'>
        Vous êtes ou avez été rédacteur en chef, responsable, ou bien vous avez partiticipé à l'aventure d'une revue, d'un fanzine ou d'une anthologie périodique, dans un des domaines science-fiction, fantastique, horreur, fantasy, étrange, merveilleux ?<br />
        Nous sommes interessés par le contenu fictionnel de ce périodique, pour compléter et corriger nos sources.<br />
        De plus, si des numéros de cette revue ou de ce fanzine sont encore disponibles, nous serions heureux de recevoir un ou quelques numéros marquants de ce périodique (les dons sont bien entendu possibles, mais nous sommes prêt à en payer le prix et bien entendu les frais de ports).
        Des scans de couvertures sont également les bienvenus.<br />
        N'hésiter pas à prendre contact ! 
    </div>
</div>
@endsection