<div class="flex flex-wrap text-sm">
    @foreach ($results->publications as $publication)
        <!-- zone couverture -->
        <div class='m-auto p-2'>
            <a class='m-auto p-1 lg:p-2' href='/ouvrages/{{ $publication->slug }}'>
                <img class='m-auto p-0.5 md:p-1 border border-purple-800' src="https://www.bdfi.info/vignettes/{{ InitialeCouv($publication->cover_front) }}/v_{{ $publication->cover_front }}.jpg" alt="couv" title='{{ $publication->name }}'>
            </a>
            <div class='text-center'>
                @if(isset($publication->publisher))
                    {{ $publication->publisher->name }}
                @else
                    @if (auth()->user()->hasGuestRole())
                        <span class='text-blue-900 bg-sky-200 shadow-sm shadow-blue-600 rounded-sm px-1'>Editeur inconnu</span>
                    @endif
                @endif
                <br />
                {{ StrDateformat($publication->approximate_parution) }}
            </div>
        </div>
    @endforeach
</div>
