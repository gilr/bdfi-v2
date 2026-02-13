@push('styles')
    @include('front.partials.zoom-styles')
@endpush
@push('scripts')
    @include('front.partials.zoom-scripts')
@endpush

<div class='grid grid-cols-1 mx-2 sm:ml-5 sm:mr-2 md:ml-10 md:mr-4 px-2 sm:pl-5 sm:pr-2 md:pl-10 md:pr-4'>
    @if($pubs && (count($pubs) !== 0))
        <div class='text-base pt-4'>
            <div class="flex flex-wrap">
                @foreach ($pubs as $pub)
                    <!-- zone couverture -->
                    <div class='m-auto p-2 relative image-container'>
                        <a class='m-auto p-1 lg:p-2' href="/ouvrages/{{ $pub['slug'] }}">
                            <img class='m-auto p-0.5 md:p-1 border border-purple-800 h-40' src="https://www.bdfi.info/medium/{{ InitialeCouv($pub['cover_front']) }}/m_{{ $pub['cover_front'] }}.jpg" alt="couv" title="Couverture {{ $pub['name'] }} - Aller vers la page ouvrage">
                        </a>
                        <!-- Icône loupe -->
                        <div class="zoom-icon" title="Agrandir la couverture" onclick="openZoomedImage('https://www.bdfi.info/couvs/{{ InitialeCouv($pub['cover_front']) }}/{{ $pub['cover_front'] }}.jpg')">🔍</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
