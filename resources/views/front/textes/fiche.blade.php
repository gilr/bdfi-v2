<div class='grid grid-cols-1 lg:grid-cols-2 gap-0.5 bg-gradient-to-b from-yellow-400 via-pink-500 to-purple-500 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4'>

<div class='bg-gray-100 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

    <div class='text-base mt-2 font-semibold bg-yellow-50'>
         {{ $results->name }}
    </div>

    <div class='text-base'>
        Type d'oeuvre : <span class='font-semibold'>{{ $results->type->getLabel() }}</span>
        @if ($results->is_serial)
            (épisode / partie)
        @endif
        @if ($results->is_fullserial)
            (publication en feuilleton)
        @endif
    </div>


    <div class='text-base'>Auteur(s) :
        @forelse($results->authors as $author)
            <x-front.lien-auteur link='/auteurs/{{ $author->id }}'>{{ $author->fullname }}</x-front.lien-auteur>
        @empty
            <span class='font-semibold text-red-500'> Non crédité ou inconnu</span>
        @endforelse
    </div>

    @if ($results->is_novelization->value != 'non')
        <div class='text-base'>
            Novelisation {{ $results->is_novelization->getLabel() }}
        </div>
    @endif

    @if ($results->title_vo)
        <div class='text-base'>
            Titre VO : <span class='font-semibold'>{{ $results->title_vo }}</span>
        </div>
    @endif

    <div class='text-base'>
        Copyright : <span class='font-semibold'>{{ StrDateformat($results->copyright) }}</span>
        @if ($results->pub_vo)
            - <span class='font-semibold'>{{ $results->pub_vo }}</span>
        @endif
    </div>

    @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->translators))
        <div class='text-base'>
            Traduction : <span class='font-semibold'>{{ normalizeTraducteurs($results->translators) }}</span>
        </div>
    @endif

    <div class='text-base'>
        @if ($results->is_genre == App\Enums\GenreAppartenance::NON)
            <img src='/img/error.png' class="inline w-5 mb-1" /> Hors genres référencés par BDFI
        @elseif ($results->genre_stat != App\Enums\GenreStat::INCONNU)
            {{ $results->genre_stat->getLabel() }} <sup title="Choix de référencement BDFI"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 inline"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" /></svg></sup>
        @endif
    </div>

    <div class='text-base'>
        @if ($results->target_audience != App\Enums\AudienceTarget::INCONNU)
            Public cible : {{ $results->target_audience->getLabel() }}
        @endif
        @if ($results->target_age)
            ({{ $results->target_age }})
        @endif
    </div>

    @if ($results->parent_id)
        <div class='text-base'>
            @if ($results->is_serial)
                Episode du feuilleton :
            @else
                Première publication française sous :
            @endif
            <x-front.lien-texte link='/textes/{{ $results->parent_id }}'>{{ $results->parent->name }}</x-front.lien-texte>
            {{-- on affiche l'auteur que si la variante courante est une variante incluant la  signature --}}
            @if (($results->variant_type === App\Enums\TitleVariantType::SIGN->value) ||
                 ($results->variant_type === App\Enums\TitleVariantType::SIGNTRAD->value) ||
                 ($results->variant_type === App\Enums\TitleVariantType::SIGNTRADTITRE->value))
                -
                @forelse($results->parent->authors as $author)
                    <x-front.lien-auteur link='/auteurs/{{ $author->id }}'>{{ $author->fullname }}</x-front.lien-auteur>
                @empty
                    <span class='font-semibold text-red-500'> Non crédité ou inconnu</span>
                @endforelse
            @endif
        </div>

        @if($results->parent->variants->count() > 1)
            @if($results->parent->variants->count() > 2)
                <div class='text-base'>Autres variantes :</div>
            @else
                <div class='text-base'>Autre variante :</div>
            @endif
            @foreach ($results->parent->variants as $variant)
                <div class='text-base ml-2 md:ml-8'>
                    @if ($results->id !== $variant->id)
                        <x-front.lien-texte link='/textes/{{ $variant->id }}'>{{ $variant->name }}</x-front.lien-texte>
{{--
    case PREMIER       = 'premier';
    case VIRTUEL       = 'virtuel';
    case EPISODE       = 'feuilleton';
    case EXTRAIT       = 'extrait';
    case SIGN          = 'signature';
    case TRAD          = 'traduction';
    case TITRE         = 'titre';
    case SIGNTRADTITRE = 'sign+trad+titre';
    case SIGNTRAD      = 'sign+trad';
    case SIGNTITRE     = 'sign+titre';
    case TRADTITRE     = 'trad+titre';
    case INCONNU       = 'inconnu';
--}}
                        @if (($variant->variant_type === App\Enums\TitleVariantType::SIGN->value) ||
                             ($variant->variant_type === App\Enums\TitleVariantType::SIGNTRAD->value) ||
                             ($variant->variant_type === App\Enums\TitleVariantType::SIGNTRADTITRE->value))
                            <span class='italic'> - signé</span>
                            @forelse($variant->authors as $author)
                                <x-front.lien-auteur link='/auteurs/{{ $author->id }}'>{{ $author->fullname }}</x-front.lien-auteur>
                            @empty
                                <span class='font-semibold text-red-500'> Non crédité ou inconnu</span>
                            @endforelse
                        @elseif (($variant->variant_type === App\Enums\TitleVariantType::TRAD->value) ||
                             ($variant->variant_type === App\Enums\TitleVariantType::TRADTITRE->value))
                            <span class='italic'> - nouvelle traduction</span>
                        @endif
                    @endif
                </div>
            @endforeach
        @endif
    @endif

    @if(count($results->variants) !== 0)
        <div class='text-base'>
            Première publication française de l'oeuvre.
        </div>
        <div class='text-base'>
            <span class='font-semibold'>Variantes :</span>
            @foreach ($results->variants as $variant)
                <div class='ml-2 md:ml-8'>
                    <x-front.lien-texte link='/textes/{{ $variant->id }}'>{{ $variant->name }}</x-front.lien-texte>

                    @if (($variant->variant_type === App\Enums\TitleVariantType::SIGN->value) ||
                         ($variant->variant_type === App\Enums\TitleVariantType::SIGNTRAD->value) ||
                         ($variant->variant_type === App\Enums\TitleVariantType::SIGNTRADTITRE->value))
                        <span class='italic'>- signé</span>
                        @forelse($variant->authors as $author)
                            <x-front.lien-auteur link='/auteurs/{{ $author->id }}'>{{ $author->fullname }}</x-front.lien-auteur>
                        @empty
                            <span class='font-semibold text-red-500'> Non crédité ou inconnu</span>
                        @endforelse
                    @elseif (($variant->variant_type === App\Enums\TitleVariantType::TRAD->value) ||
                         ($variant->variant_type === App\Enums\TitleVariantType::TRADTITRE->value))
                        <span class='italic'>- nouvelle traduction</span>
                    @endif

                </div>
            @endforeach
        </div>
    @endif
    @if(count($results->episodes) !== 0)
        <div class='text-base'>
            <span class='font-semibold'>Episodes :</span>
            @foreach ($results->episodes as $variant)
                <div class='ml-2 md:ml-8'>
                    <x-front.lien-texte link='/textes/{{ $variant->id }}'>{{ $variant->name }}</x-front.lien-texte> -
                    @forelse($variant->authors as $author)
                        <x-front.lien-auteur link='/auteurs/{{ $author->id }}'>{{ $author->fullname }}</x-front.lien-auteur>
                    @empty
                        <span class='font-semibold text-red-500'> Non crédité ou inconnu</span>
                    @endforelse
                </div>
            @endforeach
        </div>
    @endif

</div>

<div class='bg-gray-100 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

    @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->information) || ($results->is_fullserial))
        <div class='text-base my-4 p-2 border border-yellow-500 bg-yellow-50'>
            @if (($results->information) || ($results->is_fullserial))
                @if ($results->information)
                    {!! $results->information !!}
                @endif
                @if ($results->is_fullserial)
                    Paru en feuilleton.
                @endif
            @else
                <span class='italic'>... pas de description</span>
            @endif
        </div>
    @endif

    @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->synopsis))
        <div class='text-base my-4 p-2 border border-yellow-500 bg-yellow-50'>
            @if ($results->synopsis)
                {!! $results->synopsis !!}
            @else
                <span class='italic'>... pas de synopsis</span>
            @endif
        </div>
    @endif

    @if($results->cycles->count() > 0)
        <hr class="mx-24 my-2 border-dotted border-purple-800"/>

        <div class='text-base'>
            @if ($results->cycles->count() > 1)
                <span class='font-semibold'>Cette oeuvre est attachée aux cycles : </span>
            @else
                <span class='font-semibold'>Cette oeuvre est attachée au cycle : </span>
            @endif
            @foreach ($results->cycles as $cycle)
                <div class='ml-2 md:ml-8'>
                    <x-front.lien-serie link='/series/{{ $cycle->id }}'>{{ $cycle->name }}</x-front.lien-serie>
                    @if($cycle->pivot->number)
                    (n° {{ $cycle->pivot->number }})
                    @endif
                </div>
            @endforeach
        </div>
    @endif

</div>
</div>




<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    <hr class="mx-24 my-4   border-dotted border-purple-800"/>
<?php $pubs=array(); ?>

    <div class='text-base'>
        @if (!$results->parent_id)
            <!-- C'est le parent  -->
            @if ((auth()->user() && auth()->user()->hasGuestRole()))
                <span class='bg-lime-400 font-normal text-xs italic'>Cas 1. - Parent</span>
            @endif
            @if ($results->is_fullserial)
                <div class='font-semibold'>
                    Publication :
                </div>
                @if ((auth()->user() && auth()->user()->hasGuestRole()))
                    <span class='bg-lime-400 font-normal text-xs italic'>Cas 1.1 - Parent et feuilleton complet</span>
                @endif
                <!-- sans parent, publié en feuilleton : liste des "morceaux" -->
                <div class='ml-2 md:ml-8'>
                    {{ $results->serial_info }} :
                        @foreach ($results->episodes as $variant)
                            @foreach ($variant->publications->sortBy('approximate_parution') as $publication)
                                <?php $pubs[] = array("id" => $publication->id, "cover_front" => $publication->cover_front, "name" => $publication->name); ?>

                                <div class='ml-2 md:ml-8'>

                                    <x-front.lien-ouvrage link='/ouvrages/{{ $publication->id }}'>{{ $publication->name }}</x-front.lien-ouvrage>
                                      @if(count($publication->authors) > 0)
                                        -
                                        @foreach($publication->authors as $author)
                                            @if (!$loop->first)
                                                ,
                                            @endif
                                            <x-front.lien-auteur link='/auteurs/{{ $author->id }}'>{{ $author->fullname }}</x-front.lien-auteur>
                                        @endforeach
                                    @endif

                                    @if($publication->publisher)
                                        - <x-front.lien-editeur link='/sediteurs/{{ $publication->publisher_id }}'>{{ $publication->publisher_name != "" ? $publication->publisher_name : $publication->publisher->name }}</x-front.lien-editeur>
                                    @endif

                                    @if(count($publication->collections) !== 0)
                                        @if($publication->collections)
                                            / <x-front.lien-collection link='/collections/{{ $publication->collections[0]->id }}'>{{ $publication->collections[0]->name }}</x-front.lien-collection>
                                            @if($publication->collections[0]->pivot->number)
                                                n° {{ $publication->collections[0]->pivot->number }}
                                            @endif
                                        @endif
                                    @endif
                                     ({{ StrDateformat($publication->approximate_parution) }})

                                    @if($publication->pivot->start_page)
                                        en page {{ $publication->pivot->start_page }}
                                    @endif
                                </div>
                            @endforeach
                        @endforeach
                </div>
            @endif

            <!-- Sans parent -> toutes ces propres publis -->
            @if ((auth()->user() && auth()->user()->hasGuestRole()))
                <div>
                    <span class='bg-lime-400 font-normal text-xs italic'>Cas 1.2 - Les propres publis de ce parent (forme fr première)</span>
                </div>
            @endif
            @if ($results->publications->count() > 0)
                <div class='font-semibold'>
                    Publication :
                </div>
            @endif
            @foreach ($results->publications->sortBy('approximate_parution') as $publication)
                <?php $pubs[] = array("id" => $publication->id, "cover_front" => $publication->cover_front, "name" => $publication->name); ?>

                <div class='ml-2 md:ml-8'>
                    <x-front.lien-ouvrage link='/ouvrages/{{ $publication->id }}'>{{ $publication->name }}</x-front.lien-ouvrage>
                    @if(count($publication->authors) > 0)
                        -
                        @foreach($publication->authors as $author)
                            @if (!$loop->first)
                                ,
                            @endif
                            <x-front.lien-auteur link='/auteurs/{{ $author->id }}'>{{ $author->fullname }}</x-front.lien-auteur>
                        @endforeach
                    @endif

                    @if($publication->publisher)
                        - <x-front.lien-editeur link='/sediteurs/{{ $publication->publisher_id }}'>{{ $publication->publisher_name != "" ? $publication->publisher_name : $publication->publisher->name }}</x-front.lien-editeur>
                    @endif

                    @if(count($publication->collections) !== 0)
                        @if($publication->collections)
                            / <x-front.lien-collection link='/collections/{{ $publication->collections[0]->id }}'>{{ $publication->collections[0]->name }}</x-front.lien-collection>
                            @if($publication->collections[0]->pivot->number)
                                n° {{ $publication->collections[0]->pivot->number }}
                            @endif
                        @endif
                    @endif
                     ({{ StrDateformat($publication->approximate_parution) }})

                    @if($publication->pivot->start_page)
                        en page {{ $publication->pivot->start_page }}
                    @endif
                </div>
            @endforeach

            @if ((auth()->user() && auth()->user()->hasGuestRole()))
                <div>
                    <span class='bg-lime-400 font-normal text-xs italic'>Cas 1.3 - Les publis de toutes ses variantes</span>
                </div>
            @endif
            @if (count($results->variants) > 0)
                <!-- Sans parent -> ... plus les possibles variantes -->
                <div class='font-semibold'>
                    Publications des variantes :
                </div>
                @foreach ($results->variants as $variant)
                    @foreach ($variant->publications->sortBy('approximate_parution') as $publication)
                        <?php $pubs[] = array("id" => $publication->id, "cover_front" => $publication->cover_front, "name" => $publication->name); ?>

                        <div class='ml-2 md:ml-8'>
                            <x-front.lien-ouvrage link='/ouvrages/{{ $publication->id }}'>{{ $publication->name }}</x-front.lien-ouvrage>
                            @if(count($publication->authors) > 0)
                            -
                                @foreach($publication->authors as $author)
                                    @if (!$loop->first)
                                        ,
                                    @endif
                                    <x-front.lien-auteur link='/auteurs/{{ $author->id }}'>{{ $author->fullname }}</x-front.lien-auteur>
                                @endforeach
                            @endif

                            @if($publication->publisher)
                                - <x-front.lien-editeur link='/sediteurs/{{ $publication->publisher_id }}'>{{ $publication->publisher_name != "" ? $publication->publisher_name : $publication->publisher->name }}</x-front.lien-editeur>
                            @endif

                            @if(count($publication->collections) !== 0)
                                @if($publication->collections)
                                    / <x-front.lien-collection link='/collections/{{ $publication->collections[0]->id }}'>{{ $publication->collections[0]->name }}</x-front.lien-collection>
                                    @if($publication->collections[0]->pivot->number)
                                        n° {{ $publication->collections[0]->pivot->number }}
                                    @endif
                                @endif
                            @endif
                             ({{ StrDateformat($publication->approximate_parution) }})

                            @if($publication->pivot->start_page)
                                en page {{ $publication->pivot->start_page }}
                            @endif
                        </div>
                    @endforeach
                    @endforeach
            @endif

        @else
            <!-- C'est une variante -->
            @if ((auth()->user() && auth()->user()->hasGuestRole()))
                <span class='bg-lime-400 font-normal text-xs italic'>Cas 2. - Le texte a un parent (donc est variante ou épisode)</span>
            @endif

            @if ((auth()->user() && auth()->user()->hasGuestRole()))
                <div>
                    <span class='bg-lime-400 font-normal text-xs italic'>Cas 2.1 - Si le texte est un feuilleton complet</span>
                </div>
            @endif
            @if ($results->is_fullserial)
                <!-- Publié en feuilleton : liste des morceaux -->
                <div class='font-semibold'>
                    Publication des épisodes :
                </div>
                <div class='ml-2 md:ml-8'>
                    {{ $results->serial_info }} :
                @foreach ($results->episodes as $variant)
                @foreach ($variant->publications->sortBy('approximate_parution') as $publication)
                    <?php $pubs[] = array("id" => $publication->id, "cover_front" => $publication->cover_front, "name" => $publication->name); ?>

                    <div class='ml-2 md:ml-8'>
                        <x-front.lien-ouvrage link='/ouvrages/{{ $publication->id }}'>{{ $publication->name }}</x-front.lien-ouvrage>
                        @if(count($publication->authors) > 0)
                            -
                            @foreach($publication->authors as $author)
                                @if (!$loop->first)
                                    ,
                                @endif
                                <x-front.lien-auteur link='/auteurs/{{ $author->id }}'>{{ $author->fullname }}</x-front.lien-auteur>
                            @endforeach
                        @endif

                        @if($publication->publisher)
                            - <x-front.lien-editeur link='/sediteurs/{{ $publication->publisher_id }}'>{{ $publication->publisher_name != "" ? $publication->publisher_name : $publication->publisher->name }}</x-front.lien-editeur>
                        @endif

                        @if(count($publication->collections) !== 0)
                            @if($publication->collections)
                                / <x-front.lien-collection link='/collections/{{ $publication->collections[0]->id }}'>{{ $publication->collections[0]->name }}</x-front.lien-collection>
                                @if($publication->collections[0]->pivot->number)
                                    n° {{ $publication->collections[0]->pivot->number }}
                                @endif
                            @endif
                        @endif
                         ({{ StrDateformat($publication->approximate_parution) }})

                        @if($publication->pivot->start_page)
                            en page {{ $publication->pivot->start_page }}
                        @endif
                    </div>
                @endforeach
                @endforeach

                </div>
            @endif


            <!-- Avec parent -> toutes ces propres publis -->

                <div class='font-semibold'>
                    Publication :
                </div>
                @if ((auth()->user() && auth()->user()->hasGuestRole()))
                    <span class='bg-lime-400 font-normal text-xs italic'>Cas 2.2 - Les propres publis de ce texte, variante ou épisode</span>
                @endif
                @foreach ($results->publications->sortBy('approximate_parution') as $publication)
                    <?php $pubs[] = array("id" => $publication->id, "cover_front" => $publication->cover_front, "name" => $publication->name); ?>

                    <div class='ml-2 md:ml-8'>
                        <x-front.lien-ouvrage link='/ouvrages/{{ $publication->id }}'>{{ $publication->name }}</x-front.lien-ouvrage>
                        @if(count($publication->authors) > 0)
                            -
                            @foreach($publication->authors as $author)
                                @if (!$loop->first)
                                    ,
                                @endif
                                <x-front.lien-auteur link='/auteurs/{{ $author->id }}'>{{ $author->fullname }}</x-front.lien-auteur>
                            @endforeach
                        @endif

                        @if($publication->publisher)
                            - <x-front.lien-editeur link='/sediteurs/{{ $publication->publisher_id }}'>{{ $publication->publisher_name != "" ? $publication->publisher_name : $publication->publisher->name }}</x-front.lien-editeur>
                        @endif

                        @if(count($publication->collections) !== 0)
                            @if($publication->collections)
                                / <x-front.lien-collection link='/collections/{{ $publication->collections[0]->id }}'>{{ $publication->collections[0]->name }}</x-front.lien-collection>
                                @if($publication->collections[0]->pivot->number)
                                    n° {{ $publication->collections[0]->pivot->number }}
                                @endif
                            @endif
                        @endif
                         ({{ StrDateformat($publication->approximate_parution) }})

                        @if($publication->pivot->start_page)
                            en page {{ $publication->pivot->start_page }}
                        @endif
                    </div>
                @endforeach

                <!-- Avec parent -> plus les publis du parent... -->
                @if (count($results->parent->publications) + count($results->parent->variants) > 1)
                    <div class='font-semibold'>
                        Publication des autres variantes :
                    </div>
                @endif

                @if ((auth()->user() && auth()->user()->hasGuestRole()))
                    <span class='bg-lime-400 font-normal text-xs italic'>Cas 2.3 - ... Plus les publis de son parent</span>
                @endif
                @foreach ($results->parent->publications->sortBy('approximate_parution') as $publication)
                    <?php $pubs[] = array("id" => $publication->id, "cover_front" => $publication->cover_front, "name" => $publication->name); ?>

                    <div class='ml-2 md:ml-8'>
                        <x-front.lien-ouvrage link='/ouvrages/{{ $publication->id }}'>{{ $publication->name }}</x-front.lien-ouvrage>
                        @if(count($publication->authors) > 0)
                            -
                            @foreach($publication->authors as $author)
                                @if (!$loop->first)
                                    ,
                                @endif
                                <x-front.lien-auteur link='/auteurs/{{ $author->id }}'>{{ $author->fullname }}</x-front.lien-auteur>
                            @endforeach
                        @endif

                        @if($publication->publisher)
                            - <x-front.lien-editeur link='/sediteurs/{{ $publication->publisher_id }}'>{{ $publication->publisher_name != "" ? $publication->publisher_name : $publication->publisher->name }}</x-front.lien-editeur>
                        @endif

                        @if(count($publication->collections) !== 0)
                            @if($publication->collections)
                                / <x-front.lien-collection link='/collections/{{ $publication->collections[0]->id }}'>{{ $publication->collections[0]->name }}</x-front.lien-collection>
                                @if($publication->collections[0]->pivot->number)
                                    n° {{ $publication->collections[0]->pivot->number }}
                                @endif
                            @endif
                        @endif
                         ({{ StrDateformat($publication->approximate_parution) }})

                        @if($publication->pivot->start_page)
                            en page {{ $publication->pivot->start_page }}
                        @endif
                    </div>
                @endforeach
                <!-- ... plus toutes les publis des variantes -->
                <!-- ... sauf la courante évodemment -->
                @if ((auth()->user() && auth()->user()->hasGuestRole()))
                    <div>
                        <span class='bg-lime-400 font-normal text-xs italic'>Cas 2.4 - ... et les publis des autres variantes du parent (sauf la courante évidemment pour ne pas dupliquer)</span>
                    </div>
                @endif
                @foreach ($results->parent->variants as $variant)
                @foreach ($variant->publications->sortBy('approximate_parution') as $publication)
                    @if ($results->id != $variant->id)
                    <?php $pubs[] = array("id" => $publication->id, "cover_front" => $publication->cover_front, "name" => $publication->name); ?>

                    <div class='ml-2 md:ml-8'>
                        <x-front.lien-ouvrage link='/ouvrages/{{ $publication->id }}'>{{ $publication->name }}</x-front.lien-ouvrage>
                        @if(count($publication->authors) > 0)
                            -
                            @foreach($publication->authors as $author)
                                @if (!$loop->first)
                                    ,
                                @endif
                                <x-front.lien-auteur link='/auteurs/{{ $author->id }}'>{{ $author->fullname }}</x-front.lien-auteur>
                            @endforeach
                        @endif

                        @if($publication->publisher)
                            - <x-front.lien-editeur link='/sediteurs/{{ $publication->publisher_id }}'>{{ $publication->publisher_name != "" ? $publication->publisher_name : $publication->publisher->name }}</x-front.lien-editeur>
                        @endif

                        @if(count($publication->collections) !== 0)
                            @if($publication->collections)
                                / <x-front.lien-collection link='/collections/{{ $publication->collections[0]->id }}'>{{ $publication->collections[0]->name }}</x-front.lien-collection>
                                @if($publication->collections[0]->pivot->number)
                                    n° {{ $publication->collections[0]->pivot->number }}
                                @endif
                            @endif
                        @endif
                         ({{ StrDateformat($publication->approximate_parution) }})

                        @if($publication->pivot->start_page)
                            en page {{ $publication->pivot->start_page }}
                        @endif
                    </div>
                @endif
                @endforeach
                @endforeach


        @endif
    </div>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    @if($pubs && (count($pubs) !== 0))
        <div class='text-base pt-4'>
            <span class='font-semibold'>Galerie :</span>
            <div class="flex flex-wrap">
            @foreach ($pubs as $pub)
                <!-- zone couverture -->
                <a class='m-auto p-1 lg:p-2' href="/ouvrages/{{ $pub['id'] }}"><img class='m-auto p-1 lg:p-2 border border-purple-800 h-40' src="https://www.bdfi.info/medium/{{ InitialeCouv($pub['cover_front']) }}/m_{{ $pub['cover_front'] }}.jpg" alt="couv" title="Couverture {{ $pub['name'] }}"></a>
            @endforeach
            </div>
        </div>
    @endif
</div>
