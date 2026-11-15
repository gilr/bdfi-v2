<div>
    <div class="flex flex-wrap items-center gap-2 mb-1">
        <label for="{{ $fieldName }}" class="w-32 font-semibold">{{ $label }}</label>
        <input type="text" id="{{ $fieldName }}" name="{{ $fieldName }}" wire:model.live.debounce.400ms="value"
               @if ($required) required @endif maxlength="{{ $maxlength }}"
               placeholder="{{ $placeholder }}" class="{{ $cssClass }}">
        @if ($help !== '')
            <span class="text-xs text-gray-500">{{ $help }}</span>
        @endif

        @if ($this->isUppercase)
            <div class="w-full text-sm text-yellow-800">
                Attention : nom entièrement en majuscules. À garder seulement si cela s'écrit ainsi (sigle, collectif) ;
                sinon, écrire par exemple « Masterton » et non « MASTERTON ».
            </div>
        @endif
    </div>

    @if ($secondFieldName)
        <div class="flex flex-wrap items-center gap-2 mb-1">
            <label for="{{ $secondFieldName }}" class="w-32 font-semibold">{{ $secondLabel }}</label>
            <input type="text" id="{{ $secondFieldName }}" name="{{ $secondFieldName }}" wire:model.live.debounce.400ms="secondValue"
                   maxlength="{{ $secondMaxlength }}" placeholder="{{ $secondPlaceholder }}" class="{{ $secondCssClass }}">
            @if ($secondHelp !== '')
                <span class="text-xs text-gray-500">{{ $secondHelp }}</span>
            @endif
        </div>
    @endif

    @if ($match)
        <div class="text-sm bg-yellow-100 border border-yellow-600 rounded p-1 mt-1 mb-1 max-w-xl">
            <span class="font-semibold">Attention :</span> {{ $noun }} « {{ $match['label'] }} » existe déjà (#{{ $match['id'] }})
            @if ($frontLink && $match['slug'])
                — <x-front.lien-standard link="{{ $frontLink }}{{ $match['slug'] }}">voir sa fiche</x-front.lien-standard>
            @endif
            . Si c'est un homonyme volontaire, vous pourrez le confirmer à l'envoi.
        </div>
    @endif
</div>
