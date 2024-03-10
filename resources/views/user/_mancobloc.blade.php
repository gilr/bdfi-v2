<div class="text-sm grid grid-cols-3">
    @foreach ($collection->publications as $publication)
        @if (!auth()->user()->statusPublication($publication->id))
            <div class="border border-gray-500 p-px text-left">
                <x-admin.publication-nok />
                @if($publication->pivot->number)
                    {{ $publication->pivot->number }}.
                @endif
                @php
                    $replaced = preg_replace_array("/^L'|^Le |^Les |^La /", [''], $publication->name);
                @endphp
                {{ Str::limit($replaced, 9, "...") }}
            </div>
        @endif
    @endforeach
</div>