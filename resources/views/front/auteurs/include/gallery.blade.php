<div class="flex flex-wrap text-sm">
    @foreach ($results->publications as $publication)
        <!-- zone couverture -->
        <div class='m-auto p-2'>
            <a class='m-auto my-0 py-0' href='/ouvrages/{{ $publication->id }}'><img class='m-auto p-1 lg:p-2 border border-purple-800' src="https://www.bdfi.info/vignettes/{{ substr($publication->cover_front, 0, 1) }}/v_{{ $publication->cover_front }}.jpg" alt="couv" title='{{ $publication->name }}'></a>
            <div class='text-center'>
                {{ $publication->publisher->name }}
                <br />
                {{ StrDateformat($publication->approximate_parution) }}
            </div>
        </div>
    @endforeach
</div>
