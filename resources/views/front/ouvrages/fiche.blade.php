@auth
    @if (count($results->collections))
        @foreach ($results->collections as $collection)
            @if (auth()->user() && (auth()->user()->statusCollection($collection->id)) && (auth()->user()->statusCollection($collection->id) != 'cachee'))
                <div class='text-lg mb-8 self-center bg-green-200 shadow-sm shadow-gray-400 rounded-sm px-1'>
                    @if (auth()->user()->statusPublication($results->id))
                        <x-front.display-icon-pub-owned /> Ouvrage possédé -
                    @else
                        <x-front.display-icon-pub-missing /> Ouvrage non possédé -
                    @endif
                    Collection suivie, {{ auth()->user()->statusCollection($collection->slug) }} (<x-admin.link lien='/user/gestion-biblio'>&rarr; Gestion</x-admin.link>)
                </div>
            @endif
        @endforeach
    @endif
@endauth

{{-- 12 colonnes, 5 puis 3 puis 4 --}}
<div class='grid grid-cols-1 lg:grid-cols-12 gap-0.5 bg-gradient-to-b from-yellow-400 via-pink-500 to-purple-500 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4'>

<div class='bg-gray-100 lg:col-span-5 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
     @if ($results->status === App\Enums\PublicationStatus::ANNONCE)
        <img src='/img/warning.png' class="inline w-5 mb-1" /> <span class="font-semibold text-red-700">Publication annoncée</span>
     @endif
     @if ($results->status === App\Enums\PublicationStatus::ABANDONNE)
        <img src='/img/warning.png' class="inline w-5 mb-1" /> <span class="font-semibold text-red-700">Ouvrage annoncé, mais jamais publié</span>
     @endif
     @if ($results->status === App\Enums\PublicationStatus::PROPOSE)
        <img src='/img/warning.png' class="inline w-5 mb-1" /> <span class="font-semibold text-red-700">Sous réserve - référencement en cours de traitement</span>
     @endif

    <div class='text-base mt-2 font-semibold bg-yellow-50'>
         {!! $results->name !!}
    </div>

    <div class='text-base'>
        Type : <span>{{ $results->type->getLabel() }}</span>
    </div>

    @if (count($results->authors))
        @if (count($results->authors) == 1)
            <div class='text-base'>Auteur crédité :
        @else
            <div class='text-base'>Auteurs crédités :
        @endif

        @php
            $groupedAuthors = [];
            foreach ($results->authors as $author) {
                $role = $author->pivot->role->value;
                $groupedAuthors[$role][] = $author;
            }
        @endphp

        @foreach ($groupedAuthors as $role => $authors)
            @foreach ($authors as $index => $author)
                @if (!$loop->first)
                    ,
                @endif
                <x-front.lien-auteur link='/auteurs/{{ $author->slug }}'>{{ $author->fullname }}</x-front.lien-auteur>
            @endforeach
            @if (($role != App\Enums\AuthorPublicationRole::AUTHOR->value) || count($groupedAuthors) > 1)
                @php
                    $roleEnum = App\Enums\AuthorPublicationRole::from($role); // On récupère l'enum
                @endphp
                @if (count($groupedAuthors[$role]) > 1)
                    <span class='hidden xl:inline'>({{ $roleEnum->getLabelPlural() }})</span>
                @else
                    <span class='hidden xl:inline'>({{ $roleEnum->getLabel() }})</span>
                @endif
            @endif
        @endforeach
        </div>
    @endif

    @if ($results->publisher_id)
        <div class='text-base'>
            Editeur : <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/editeurs/{{ $results->publisher->slug }}'>{{ $results->publisher_name != "" ? $results->publisher_name : $results->publisher->name }}</a></span>
        </div>
    @endif
    @if (count($results->collections))
        @foreach ($results->collections as $collection)
            <div class='text-base'>Collection :
                <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' title='{{ $collection->fullName }}' href='/collections/{{ $collection->slug }}'>{{ $collection->name }} </a></span>
                @if ($collection->pivot->number)
                    n° {{ $collection->pivot->number }} &nbsp; &nbsp;
                @endif
                @if ($first[$loop->index])
                    <a data-nav="first" class='text-sm border bg-purple-100 px-1.5 border-purple-700 rounded shadow-md shadow-indigo-500/40 hover:text-purple-700 focus:text-purple-900' href='/ouvrages/{{ $first[$loop->index] }}'>&#10094;&#10094;</a>
                @else
                    <span class='text-sm text-slate-400 border bg-slate-100 px-2 border-slate-300 rounded shadow-md'>&#10094;&#10094;</span>
                @endif
                @if ($prev[$loop->index])
                    <a data-nav="prev" class='text-sm border bg-purple-100 px-1.5 border-purple-700 rounded shadow-md shadow-indigo-500/40 hover:text-purple-700 focus:text-purple-900' href='/ouvrages/{{ $prev[$loop->index] }}'>&#10094;</a>
                @else
                    <span class='text-sm text-slate-400 border bg-slate-100 px-2 border-slate-300 rounded shadow-md'>&#10094;</span>
                @endif
                <span class='text-gray-200 text-xs italic'>{{ $collection->pivot->order }}</span>
                @if ($next[$loop->index])
                      <a data-nav="next" class='text-sm border bg-purple-100 px-1.5 border-purple-700 rounded shadow-md shadow-indigo-500/40 hover:text-purple-700 focus:text-purple-900' href='/ouvrages/{{ $next[$loop->index] }}'>&#10095;</a>
                @else
                    <span class='text-sm text-slate-400 border bg-slate-100 px-1.5 border-slate-300 rounded shadow-md'>&#10095;</span>
                @endif
                @if ($last[$loop->index])
                    <a data-nav="last" class='text-sm border bg-purple-100 px-1.5 border-purple-700 rounded shadow-md shadow-indigo-500/40 hover:text-purple-700 focus:text-purple-900' href='/ouvrages/{{ $last[$loop->index] }}'>&#10095;&#10095;</a>
                @else
                    <span class='text-sm text-slate-400 border bg-slate-100 px-2 border-slate-300 rounded shadow-md'>&#10095;&#10095;</span>
                @endif
            </div>
        @endforeach
    @endif

    @if ($results->cycle)
        <div class='text-base'>
            Série : <span class='font-semibold'>
            <!-- TODO :
                Si $results->cycle id $results->'title's[0]->cycles[0]->name
                    Série : <lien> - n°
                Sinon
                    Série : <$results->cycle> - n° (voir <lien>)
            -->
            @if (isset($results->titles[0]))
            @if (count ($results->titles[0]->cycles) > 0)
                @if ($results->cycle == $results->titles[0]->cycles[0]->name)
                    <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/series/{{ $results->titles[0]->cycles[0]->slug }}'>{{ $results->cycle }}</a> - {{ StrConvCycleNum($results->cyclenum) }}
                @else
                     {{ $results->cycle }}  {{ $results->cyclenum }} (<a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/series/{{ $results->titles[0]->cycles[0]->slug }}'>{{ $results->titles[0]->cycles[0]->name }} </a>)
                @endif
            @endif
            @endif
        </div>
    @endif

    <div class='text-base'>
        Parution : <span class='font-semibold'>{{ StrDateformat($results->approximate_parution) }}</span>
    </div>

    <div class='text-base'>
        Support : <span class='font-semibold'>{{ $results->support->getLabel() }}</span> -

       @if ($results->support->value == 'papier')
            <span class='font-semibold'>
            @if ($results->is_hardcover)
                relié
            @else
                broché
            @endif
            @if ($results->has_dustjacket)
                avec jaquette
            @endif
            </span>
        @endif
    </div>

    <div class='text-base'>
        Illustration/copyright couverture : <span class='font-semibold'>{{ normalizeCopyright($results->cover) }}</span>
    </div>

    @if (($results->illustrators) && ($results->illustrators != "?"))
        <div class='text-base'>
            Illustrations intérieures : <span class='font-semibold'>{{ $results->illustrators }}</span>
        </div>
    @endif

    @if (isset($results->titles[0]))
        @if (($results->titles[0]->translators) && ($results->titles[0]->translators != "?"))
            <div class='text-base'>
                Traduction : <span class='font-semibold'>{{ normalizeTraducteurs($results->titles[0]->translators) }}</span>
            </div>
        @endif
    @endif

    <div class='text-base'>
        ISBN : <span class='font-semibold'>
            @if ($results->isbn == "-")
                n/a
            @else
                {{ $results->isbn }}
                <span class="font-light text-base">
                    <img src='/img/{{ isbnCheckAndConvert($results->isbn, "check") == "OK" ? "ok.png" : "error.png" }}' class="inline w-5 mb-1" />
                </span>
            @endif
        </span>
    </div>
    @if ($results->isbn != "-")
        <div class='text-xs ml-2'>
            {{ isbnCheckAndConvert($results->isbn) }}
        </div>
    @endif
    @if ($results->first_edition_id !== NULL)
        <div class='text-base'>
            Nouvelle édition dans la collection.
        </div>
    @endif

    <div class='text-base'>
        @if ($results->is_genre == App\Enums\GenreAppartenance::NON)
            <img src='/img/error.png' class="inline w-5 mb-1" /> Hors genres référencés par BDFI
        @elseif ($results->is_genre == App\Enums\GenreAppartenance::INCONNU)
            <img src='/img/error.png' class="inline w-5 mb-1" /> Genre de l'ouvrage à confirmer
        @elseif ($results->genre_stat != App\Enums\GenreStat::INCONNU)
            {{ $results->genre_stat->getLabel() }} <sup title="Genre de référencement BDFI - Cliquez pour consulter la FAQ"><a href="/site/faq"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 inline"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" /></svg></a></sup>
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

    @if ($results->information)
        <div class='text-base my-4 p-2 border border-yellow-500 bg-yellow-50'>
            {!! $results->information !!}
        </div>
    @endif
</div>

<div class='bg-gray-100 lg:col-span-3'>
    {{-- zone couverture essai pour remplacement --}}
    @php
        $covers = array();
        foreach ($images as $key => $value)
        {
            $covers[] = array('url' => "https://www.bdfi.info/couvs/" . InitialeCouv($value) . "/" . $value . ".jpg", 'name' => $key);
        }
    @endphp
    <livewire:cover-slide :covers="$covers" />
</div>

<div class='bg-gray-100 lg:col-span-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    @if ($results->is_verified)
        Ouvrage vérifié
        @if (Illuminate\Support\Str::contains($results->verified_by,";"))
            <img src='/img/ok.png' class="inline w-5 mb-1" />
        @endif
        <img src='/img/ok.png' class="inline w-5 mb-1" />
        @auth
            @if (auth()->user()->hasGuestRole())
                <span class='text-blue-900 bg-sky-200 shadow-sm shadow-blue-600 rounded-sm px-1'> ({{ $results->verified_by }})</span>
            @endif
        @endauth
    @else
        <img src='/img/error.png' class="inline w-5 mb-1" /> Ouvrage non vérifié
    @endif

    @if ($results->is_verified)
        <div class='text-base pt-2'>
            Informations vérifiées :
        </div>

        <div class='border border-purple-800 p-1'>
            @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->dl))
                <div class='text-base'>
                    Dépot légal : {{ StrDateformat($results->dl) }}
                </div>
            @endif

            @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->ai))
                <div class='text-base'>
                    Achevé d'imprimé : {{ StrDateformat($results->ai) }}
                </div>
            @endif

            @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->printer))
                <div class='text-base'>
                    Imprimeur : {{ $results->printer }}
                </div>
            @endif

            @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->dimensions))
                <div class='text-base'>
                    Dimensions : {{ $results->dimensions }} mm
                </div>
            @endif

            @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->thickness))
                <div class='text-base'>
                    Épaisseur : {{ $results->thickness }}
                </div>
            @endif

            @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->pages_dpi))
                <div class='text-base'>
                    Dernier numéro de page imprimé : {{ $results->pages_dpi }}
                </div>
            @endif

            @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->pages_dpu))
                <div class='text-base'>
                    Dernier numéro de page utile : {{ $results->pages_dpu }}
                </div>
            @endif

            @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->pagination))
                <div class='text-base'>
                    Pagination totale : {{ $results->pagination }}
                </div>
            @endif

            @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->edition))
                <div class='text-base'>
                    Édition : {{ $results->edition }}
                </div>
            @endif

            @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->printed_price))
                <div class='text-base'>
                    Information de prix : {{ $results->printed_price }}
                </div>
            @endif

            <!--
                Pas prévu aujourd'hui : Autres informations imprimées -> $results->printed_info
            -->
        </div>
    @endif

    <div class='text-base pt-2'>
    @if ($results->is_verified)
        Autres informations :
    @else
        Informations indicatives :
    @endif
    </div>
    <div class='border border-purple-800 p-1'>
        <div class='text-base'>
            @if ($results->format !== "")
                @if (($results->format == App\Enums\PublicationFormat::POCHE) || ($results->format == App\Enums\PublicationFormat::MF) || ($results->format == App\Enums\PublicationFormat::GF))
                    {{ $results->format->getLabel() }}
                @elseif ($results->format != App\Enums\PublicationFormat::NA)
                    Format : {{ $results->format->getLabel() }}
                @endif
            @else
                Format inconnu
            @endif
        </div>

        @if (!$results->is_verified)
            @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->dl))
                <div class='text-base'>
                    Dépot légal : {{ StrDateformat($results->dl) }}
                </div>
            @endif

            @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->ai))
                <div class='text-base'>
                    Achevé d'imprimé : {{ StrDateformat($results->ai) }}
                </div>
            @endif

            @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->printed_price))
                <div class='text-base'>
                    Information de prix : {{ $results->printed_price }}
                </div>
            @endif

            @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->dimensions))
                <div class='text-base'>
                    Dimensions : {{ $results->dimensions }} mm
                </div>
            @endif

            @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->approximate_pages))
                <div class='text-base'>
                    Nombre de pages : {{ $results->approximate_pages }}
                </div>
            @endif
        @endif

        @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->approximate_price))
            <div class='text-base'>
                Prix public à parution : {{ $results->approximate_price }}
            </div>
        @endif
    </div>

    @if (count($results->reprints) != 0)
        <hr class="mx-24 my-2 border-dotted border-purple-800"/>

        <div class='text-base'>
            <span class='font-semibold'>Retirages</span>
            <sup title="Nouveau tirage à l'identique - Cliquez pour consulter la FAQ"><a href="/site/faq"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 inline"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" /></svg></a></sup> :
            @foreach ($results->reprints as $reprint)
                @if (!$loop->first)
                    ,
                @endif
                @if (auth()->user() && auth()->user()->hasGuestRole())
                    <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/retirages/{{ $reprint->slug }}'>{{ StrDateformat($reprint->approximate_parution) }} </a>
                @else
                    {{ StrDateformat($reprint->approximate_parution) }}
                @endif
                @if ($reprint->is_verified)
                    <img src='/img/ok.png' class="inline w-5 mb-1" />
                @else
                    <img src='/img/error.png' class="inline w-5 mb-1" />
                @endif
            @endforeach
        </div>
    @endif
</div>
</div>

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>

    @if (isset($results->titles))
    @if (count($results->titles) != 0)
        <hr class="mx-24 my-2 border-dotted border-purple-800"/>

        <div class='text-base'>
            <span class='font-semibold'>Contenu de la publication :</span>
            @foreach ($results->titles as $title)
                <div class="{{ ($title->pivot->level < 2 ? ($title->pivot->level < 1 ? 'ml-2 md:ml-8' : 'ml-4 md:ml-16') :'ml-6 md:ml-24') }} {{ ($title->pivot->level > 0 ?: 'italic') }}">
                    <span class='font-light'>
                        @if ($title->pivot->level == 1)
                            &#8226;
                        @elseif ($title->pivot->level == 2)
                            &#9900;
                        @elseif ($title->pivot->level == 3)
                            &#8227;
                        @endif
                    </span>

                    <x-front.lien-texte link='/textes/{{ $title->slug }}'>{!! $title->name !!}</x-front.lien-texte>

                    <span class='hidden lg:inline'>
                        @if ($title->type != App\Enums\TitleType::SECTION)
                            ({{ $title->copyright }}{{ $title->title_vo != NULL ? ", $title->title_vo)" : ")"}},
                        @else
                            ,
                        @endif
                    </span>
                    <span class='hidden md:inline text-gray-800'>{{ $title->type->getLabel() }}</span>
                    @if ((count($title->authors) > 0) && ($title->type != App\Enums\TitleType::SECTION))
                    de
                        @foreach($title->authors as $author)
                            @if (!$loop->first)
                                ,
                            @endif
                            <x-front.lien-auteur link='/auteurs/{{ $author->slug }}'>{{ $author->fullname }}</x-front.lien-auteur>
                        @endforeach
                    @endif
                    <span class='hidden lg:inline'>
                    @if ($title->pivot->start_page != '')
                        - Page {{ $title->pivot->start_page }}
                    @endif
                    @if ($title->pivot->end_page)
                        à {{ $title->pivot->end_page }}
                    @endif
                    </span>

                    <span class='hidden sm:inline'>
                    @if (count($title->cycles))
                        @if (count($title->cycles) > 1)
                            - séries :
                        @else
                            - série :
                        @endif
                        @foreach ($title->cycles as $cycle)
                            @if (!$loop->first)
                                ,
                            @endif
                            <a class='border-b border-dotted border-purple-700 hover:text-purple-700' href='/series/{{ $cycle->slug }}'>{{ $cycle->name }}</a>
                        @endforeach
                    @endif
                    </span>
                    <span class='hidden sm:inline italic text-stone-500'>
                        @if ($title->is_genre == App\Enums\GenreAppartenance::NON)
                            (Hors genres référencés)
                        @elseif ($title->is_genre == App\Enums\GenreAppartenance::INCONNU)
                            (Genre à confirmer)
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    @endif
    @endif
</div>

<script>
document.addEventListener('keydown', function (e) {

    // Premier (Home)
    if (e.key === 'Home') {
        const first = document.querySelector('a[data-nav="first"]');
        if (first) window.location = first.href;
    }

    // Dernier (End)
    if (e.key === 'End') {
        const last = document.querySelector('a[data-nav="last"]');
        if (last) window.location = last.href;
    }

    // Précédent (flèche gauche)
    if (e.key === 'ArrowLeft') {
        const prev = document.querySelector('a[data-nav="prev"]');
        if (prev) window.location = prev.href;
    }

    // Suivant (flèche droite)
    if (e.key === 'ArrowRight') {
        const next = document.querySelector('a[data-nav="next"]');
        if (next) window.location = next.href;
    }
});
</script>
