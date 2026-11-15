{{-- Ouvrages manquants d'une collection ($missing) : titres abrégés, titre complet au survol (page destinée à l'impression) --}}
<div class="text-sm grid grid-cols-3">
    @foreach ($missing as $publication)
        {{-- Bordures intérieures fines et claires (0.5px de chaque côté = 1px entre deux cellules) --}}
        {{-- Ouvrage possédé mais abîmé (si inclus) : italique sur fond gris clair --}}
        @if ($user->ownsDamagedPublication($publication->id))
        <div class="p-px text-left italic" style="border: 0.5px solid #d1d5db; background-color: #e5e7eb; -webkit-print-color-adjust: exact; print-color-adjust: exact;" title="{{ $publication->name }} (possédé mais abîmé)">
        @else
        <div class="p-px text-left" style="border: 0.5px solid #d1d5db;" title="{{ $publication->name }}">
        @endif
            @if($publication->pivot->number)
                {{ $publication->pivot->number }}.
            @endif
            @php
                $replaced = preg_replace_array("/^L'|^Le |^Les |^La /", [''], $publication->name);
            @endphp
            {{ Str::limit($replaced, 9, "...") }}
        </div>
    @endforeach
</div>
