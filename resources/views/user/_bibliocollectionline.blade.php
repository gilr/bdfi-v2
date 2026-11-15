<div class='ml-2 md:ml-8 py-0.5 my-0.5'>
    <a class='border-b border-dotted border-purple-700 font-semibold hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->slug }}'>{{ $collection->name }} </a> - {{ $collection->publisher->name }} - {{ $user->countOwnedInCollection($collection) }} / {{ count($collection->publications) }}

    {{ html()->form('POST', '/user/modifier-collection')->class("inline-block")->open() }}
        - Changement du suivi :
        {{ html()->select("col_status")->class("px-2 p-0 border-dotted bg-gray-300 text-gray-500")->open() }}
        @foreach (App\Enums\BiblioCollectionStatus::cases() as $type)
            @if ($type->value === $collection->pivot->status)
                {{ html()->option($type->getLabel(), $type->value)->class("text-black")->selected() }}
            @else
                {{ html()->option($type->getLabel(), $type->value)->class("text-black") }}
            @endif
        @endforeach
        {{ html()->select("col_status")->close() }}
        {{ html()->hidden("col", $collection->id) }}
        {{ html()->submit("Modifier")->class("bg-blue-400 border border-blue-800 rounded px-2 m-0") }}
    {{ html()->form()->close() }}

    {{-- Retrait avec demande de confirmation (fenêtre Alpine.js, fournie avec Livewire) --}}
    <div class="inline-block" x-data="{ confirmRemove: false }" x-on:keydown.escape.window="confirmRemove = false">
        - Retrait définitif :
        <button type="button" class="bg-blue-400 border border-blue-800 rounded px-2 m-0" x-on:click="confirmRemove = true">Retirer</button>

        <template x-teleport="body">
            <div x-show="confirmRemove" x-cloak x-transition.opacity
                class="fixed inset-0 z-50 bg-gray-600 bg-opacity-50 flex justify-center items-center px-4"
                x-on:click.self="confirmRemove = false">
                <div class="bg-white p-4 rounded-lg shadow-lg shadow-blue-500/50 border-t-4 border-blue-500" style="width: 33vw; min-width: min(22rem, 100%);" role="dialog" aria-modal="true">
                    <h2 class="text-lg font-semibold mb-3">Retirer la collection "{{ $collection->name }}" ?</h2>
                    <p class="mb-2">
                        Êtes-vous certain de vouloir supprimer définitivement le suivi de cette collection ?
                    </p>
                    <p class="mb-4 text-red-700">
                        Les informations de suivi vont être perdues.
                    </p>
                    {{ html()->form('POST', '/user/retirer-collection')->class("flex justify-end gap-2")->open() }}
                        {{ html()->hidden("col", $collection->id) }}
                        <button type="button" class="bg-gray-300 hover:bg-gray-400 rounded px-4 py-1" x-on:click="confirmRemove = false">Annuler</button>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold rounded px-4 py-1">Confirmer le retrait</button>
                    {{ html()->form()->close() }}
                </div>
            </div>
        </template>
    </div>
</div>
