@push('styles')
    @include('front.partials.zoom-styles')
@endpush
@push('scripts')
    @include('front.partials.zoom-scripts')
@endpush

@if ($has_new_editions === true)
<style>
input.swap ~ div .reed { display: none; }
input.swap:checked ~ div .reed { display: block; }
</style>
<input class='swap mx-4 my-1 accent-purple-200' type="checkbox" checked>Afficher les nouvelles éditions dans la collection<br />
@endif

<div class="flex flex-wrap">
    @foreach ($results->publications as $publication)
        @if ($publication->first_edition_id === NULL)
        <div class='m-auto p-2 relative image-container'>
        @else
        <div class='reed m-auto p-2 relative image-container'>
        @endif
            <a class='m-auto p-1 lg:p-2' href='/ouvrages/{{ $publication->slug }}'>
                <img class='m-auto p-0.5 md:p-1 border border-purple-800' src="https://www.bdfi.info/vignettes/{{ InitialeCouv($publication->cover_front) }}/v_{{ $publication->cover_front }}.jpg" alt="couv" title="Couverture {{ $publication->name }} - Aller vers la page ouvrage">
            </a>
            <!-- Icône loupe -->
            <div class="zoom-icon" title="Agrandir la couverture" onclick="openZoomedImage('https://www.bdfi.info/couvs/{{ InitialeCouv($publication->cover_front) }}/{{ $publication->cover_front }}.jpg')">🔍</div>
        </div>
    @endforeach
</div>
