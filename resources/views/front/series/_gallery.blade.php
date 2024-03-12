<div class="flex flex-wrap">
    @foreach ($pubs as $publication)
        <!-- zone couverture -->
        <a class='m-auto p-1 lg:p-2' href='/ouvrages/{{ $publication['id'] }}'><img class='m-auto p-1 lg:p-2 border border-purple-800' src="https://www.bdfi.info/vignettes/{{ substr($publication['cover_front'], 0, 1) }}/v_{{ $publication['cover_front'] }}.jpg" alt="couv" title="Couverture {{ $publication['name'] }}"></a>
    @endforeach
</div>