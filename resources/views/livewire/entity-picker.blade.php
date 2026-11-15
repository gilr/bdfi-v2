<div class="relative" wire:key="entity-picker-{{ $fieldName }}">
    <label class="text-sm block mb-1">{{ $label }}</label>

    @if ($selectedId)
        <div class="flex flex-wrap items-center gap-2">
            <span class="bg-yellow-100 border rounded px-2 py-1 text-sm">
                {{ $selectedLabel }} <span class="text-xs text-gray-500">(#{{ $selectedId }})</span>
            </span>
            <button type="button" wire:click="clear" class="text-sm text-red-600">changer</button>
        </div>
    @else
        @php
            // Le texte tapé correspond-il exactement (sans tenir compte de la casse) à l'un des résultats
            // proposés ? Si oui et qu'il n'est pas cliqué, la validation créera un doublon par ce même nom.
            $exactMatch = collect($results)->first(fn ($r) => mb_strtolower(trim($r['label'])) === mb_strtolower(trim($search)));
        @endphp

        <input type="text" wire:model.live.debounce.400ms="search" placeholder="{{ $placeholder }}" autocomplete="off"
               class="w-full border rounded bg-yellow-100 max-w-xl">

        @if ($exactMatch && !$selectOnly)
            <div class="text-sm bg-red-100 border border-red-700 rounded p-1 mt-1 max-w-xl">
                <span class="font-semibold">Attention :</span> « {{ $search }} » correspond exactement à un résultat proposé ci-dessous.
                Si vous ne le sélectionnez pas, {{ $noun }} de même nom sera créé à la validation.
            </div>
        @endif

        @if (count($results))
            <div class="bg-white border rounded shadow w-full max-w-xl" style="position:absolute; z-index:20; max-height:12rem; overflow-y:auto;">
                @foreach ($results as $r)
                    <div wire:click="select({{ $r['id'] }})" wire:key="result-{{ $fieldName }}-{{ $r['id'] }}"
                         class="p-1 border-b cursor-pointer text-sm {{ !$selectOnly && $exactMatch && $exactMatch['id'] === $r['id'] ? 'bg-yellow-200 font-semibold' : '' }}">
                        {{ $r['label'] }} <span class="text-xs text-gray-500">(#{{ $r['id'] }})</span>
                        @if (!$selectOnly && $exactMatch && $exactMatch['id'] === $r['id'])
                            <span class="text-xs text-red-600">← cliquez ici pour éviter un doublon</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @elseif (trim($search) !== '')
            <div class="text-xs text-gray-500 mt-1">
                @if ($selectOnly)
                    Aucun résultat pour « {{ $search }} ».
                @else
                    Aucun résultat pour « {{ $search }} » : {{ $noun }} sera créé avec ce nom à la validation.
                @endif
            </div>
        @endif
    @endif

    <input type="hidden" name="{{ $idFieldName }}" value="{{ $selectedId }}">
    @unless ($selectOnly)
        <input type="hidden" name="{{ $nameFieldName }}" value="{{ $selectedId ? '' : $search }}">
    @endunless

    @if (!$selectedId)
        <div class="text-xs text-gray-500 mt-1">Laisser vide pour ne rien lier{{ $selectOnly ? '' : ' pour l\'instant' }}.</div>
    @endif
</div>
