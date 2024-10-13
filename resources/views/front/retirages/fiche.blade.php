<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    <div class='text-base'>
        Publication d'origne : <span class='font-semibold'>
            <x-front.lien-ouvrage link='/ouvrages/{{ $results->publication->slug }}'>{{ $results->publication->name }}</x-front.lien-ouvrage>
        </span>
    </div>
    <div class='text-base'>
        Editeur : <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/editeurs/{{ $results->publication->publisher->slug }}'>{{ $results->publication->publisher->name }}</a></span>
    </div>
    <div class='text-base'>
        Collection : <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $results->publication->collections[0]->slug }}'>{{ $results->publication->collections[0]->name }}</a></span>
    </div>

    <div class='text-base'>
        Date de publication : <span class='font-semibold'>{{ StrDateformat($results->approximate_parution) }}</span>
    </div>
    <div class='text-base'>
        Date d'Achevé d'Imprimer du retirage :
        <span class='font-semibold'>
            {{ StrDateformat(StrDLAItoBDFI($results->ai, $results->approximate_parution)) }}
        </span>
    </div>
    <div class='pl-2'>
        @if ($results->is_verified)
            Retirage vérifié
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
            <img src='/img/error.png' class="inline w-5 mb-1" /> Retirage non vérifié
        @endif
    </div>

    @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->information))
        <div class='text-base my-4 p-2 border border-yellow-500 bg-yellow-50'>
            @if ($results->information)
                {!! $results->information !!}
            @else
                <span class='italic'>... pas d'informations spécifiques.</span>
            @endif
        </div>
    @endif

    <div class='text-base'>
        DL d'origine : <span class='font-semibold'>{{ StrDateformat($results->publication->dl) }}</span>
    </div>
    <div class='text-base'>
        AI d'origine : <span class='font-semibold'>{{ StrDateformat($results->publication->ai) }}</span>
    </div>
    <div class='text-base'>
        Parution d'origine : <span class='font-semibold'>{{ StrDateformat($results->publication->approximate_parution) }}</span>
    </div>

    <hr class="mx-24 my-2 border-dotted border-purple-800"/>

    @if(count($results->publication->reprints) != 0)
        <div class='text-base'>
            <span class='font-semibold'>Liste des retirages connus de la publication :</span>
            @foreach ($results->publication->reprints as $reprint)
                <div class='ml-2 md:ml-8'>
                    @if ($results->id === $reprint->id)
                        (Fiche courante) <i>Retirage {{ StrDateformat($reprint->approximate_parution) }}
                        - AI : {{ StrDateformat(StrDLAItoBDFI($reprint->ai, $reprint->approximate_parution)) }} </i>
                    @else
                        <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/retirages/{{ $reprint->slug }}'>Retirage {{ StrDateformat($reprint->approximate_parution) }}</a>
                        - AI : {{ StrDateformat(StrDLAItoBDFI($reprint->ai, $reprint->approximate_parution)) }}
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class='text-base pt-4'>
        <div class="flex flex-wrap">
            <!-- zone couverture -->
            <a class='m-auto p-1 lg:p-2' href='/ouvrages/{{ $results->publication->slug }}'><img class='m-auto p-1 lg:p-2 border border-purple-800' src="https://www.bdfi.info/vignettes/{{ InitialeCouv($results->publication->cover_front) }}/v_{{ $results->publication->cover_front }}.jpg" alt="couv" title="Couverture {{ $results->publication->name }}"></a>
        </div>
    </div>

</div>