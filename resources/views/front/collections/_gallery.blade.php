<div class="flex flex-wrap">
    @foreach ($results->publications as $publication)
        <!-- zone couverture -->
        <a class='m-auto p-1 lg:p-2' href='/ouvrages/{{ $publication->slug }}'>
            <img class='m-auto p-0.5 md:p-1 border border-purple-800' src="https://www.bdfi.info/vignettes/{{ InitialeCouv($publication->cover_front) }}/v_{{ $publication->cover_front }}.jpg" alt="couv" title="Couverture {{ $publication->name }}">
        </a>
    @endforeach
</div>