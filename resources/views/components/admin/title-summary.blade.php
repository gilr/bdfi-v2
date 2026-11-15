@props(['title'])
@php
    $authors = $title->authors->map(fn ($a) => $a->firstNameFirst)->implode(', ');
    $parts = array_filter([
        $authors !== '' ? $authors : 'auteur ?',
        filled($title->translators) ? 'trad. ' . $title->translators : null,
        filled($title->copyright_fr) ? 'fr ' . $title->copyright_fr : null,
        filled($title->copyright) ? 'VO ' . $title->copyright : null,
        $title->type?->getLabel(),
    ]);
@endphp
<span class="font-semibold">{{ $title->name }}</span>
<span class="text-xs text-gray-500">#{{ $title->id }}</span><br />
{{ implode(' — ', $parts) }}
{{ $slot }}
<span class="text-xs">
    · <a class="text-red-700" href="/textes/{{ $title->slug }}" target="_blank">page</a>
    · <a class="text-red-700" href="/filament/titles/{{ $title->id }}" target="_blank">fiche</a>
</span>
