<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    <div class='text-base'>
        Publication d'origne : <span class='font-semibold'>
            <x-front.lien-ouvrage link='/ouvrages/{{ $results->publication->id }}'>{{ $results->publication->name }}</x-front.lien-ouvrage>
        </span>
    </div>
    <div class='text-base'>
        Editeur : <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/editeurs/{{ $results->publication->publisher_id }}'>{{ $results->publication->publisher->name }}</a></span>
    </div>
    <div class='text-base'>
        Collection : <span class='font-semibold'><a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $results->publication->collections[0]->id }}'>{{ $results->publication->collections[0]->name }}</a></span>
    </div>

    <div class='text-base'>
        Achevé d'imprimer du retirage : <span class='font-semibold'>{{ StrDateformat($results->ai) }}</span>
    </div>

    @if ((auth()->user() && auth()->user()->hasGuestRole()) || ($results->information))
        <div class='text-base my-4 p-2 border border-yellow-500 bg-yellow-50'>
            @if ($results->information)
                {!! $results->information !!}
            @else
                <span class='italic'>... pas d'informations</span>
            @endif
        </div>
    @endif

    <div class='text-base'>
        DL d'origine : <span class='font-semibold'>{{ $results->publication->dl }}</span>
    </div>
    <div class='text-base'>
        AI d'origine : <span class='font-semibold'>{{ $results->publication->ai }}</span>
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
                    @if ($results->id == $reprint->id)
                        <i>{{ StrDateformat($reprint->ai) }}</i>
                    @else
                        <a class='border-b border-dotted border-purple-700 hover:text-purple-700 focus:text-purple-900' href='/retirages/{{ $reprint->id }}'>{{ StrDateformat($reprint->ai) }} </a>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class='text-base pt-4'>
        <div class="flex flex-wrap">
            <!-- zone couverture -->
            <a class='m-auto p-1 lg:p-2' href='/ouvrages/{{ $results->publication->id }}'><img class='m-auto p-1 lg:p-2 border border-purple-800' src="https://www.bdfi.info/vignettes/{{ substr($results->publication->cover_front, 0, 1) }}/v_{{ $results->publication->cover_front }}.jpg" alt="couv" title="Couverture {{ $results->publication->name }}"></a>
        </div>
    </div>

</div>