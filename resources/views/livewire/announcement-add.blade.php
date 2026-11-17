<div>
    @php($input = 'border p-1 w-full')

    @if ($notice !== '')
        <div class="p-2 mb-2 border border-green-800 bg-green-100 rounded">{{ $notice }}</div>
    @endif

    <form wire:submit="save">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-2">
            <label>Type *
                <select wire:model.live="type" class="{{ $input }}">
                    @foreach ($types as $t)
                        <option value="{{ $t->value }}">{{ $t->getLabel() }}</option>
                    @endforeach
                </select>
                @error('type') <span class="text-red-700 text-sm">{{ $message }}</span> @enderror
            </label>
            <label>Date * <span class="text-xs text-gray-500">({{ $dateHelp }})</span>
                <input type="date" wire:model="date" class="{{ $input }}" />
                @error('date') <span class="text-red-700 text-sm">{{ $message }}</span> @enderror
            </label>
            <label>Titre / Sujet * <span class="text-xs text-gray-500">({{ $nameHelp }})</span>
                <input type="text" wire:model.live.debounce.500ms="name" maxlength="64" class="{{ $input }}" />
                @error('name') <span class="text-red-700 text-sm">{{ $message }}</span> @enderror
            </label>
            <label>URL <span class="text-xs text-gray-500">(facultatif : sujet du forum détaillant l'annonce, ou page bibliographique BDFI de l'auteur)</span>
                <input type="text" wire:model="url" maxlength="256" class="{{ $input }}" placeholder="https://…" />
                @error('url') <span class="text-red-700 text-sm">{{ $message }}</span> @enderror
            </label>
            <label class="md:col-span-2">Description *
                <textarea wire:model="information" rows="4" class="{{ $input }}"></textarea>
                @error('information') <span class="text-red-700 text-sm">{{ $message }}</span> @enderror
            </label>
        </div>

        @if ($sameName->isNotEmpty())
            <div class="p-2 mb-2 border border-yellow-600 bg-yellow-100 rounded text-sm">
                <div class="font-semibold">Déjà enregistré pour ce nom (vérifiez l'orthographe, et que ce n'est pas un doublon) :</div>
                @foreach ($sameName as $a)
                    <div>{{ $a->date->format('d/m/Y') }} — {{ $a->type->getLabel() }} — <span class="font-semibold">{{ $a->name }}</span> : {{ Str::limit($a->information, 100) }}</div>
                @endforeach
            </div>
        @endif

        <button type="submit" @disabled(!$canEdit)
                class="bg-blue-400 font-semibold border border-blue-800 rounded px-4 py-1 disabled:opacity-50 disabled:cursor-not-allowed">
            Enregistrer
        </button>
    </form>

    @if ($recent->isNotEmpty())
        <div class="mt-4 text-lg font-semibold border-b border-blue-800 mb-2">Dernières saisies de ce type</div>
        <table class="text-sm w-full">
            <tbody>
                @foreach ($recent as $a)
                    <tr wire:key="ann-{{ $a->id }}">
                        <td class="py-1 px-2 border border-blue-500" style="white-space: nowrap">{{ $a->date->format('d/m/Y') }}</td>
                        <td class="py-1 px-2 border border-blue-500 font-semibold">{{ $a->name }}</td>
                        <td class="py-1 px-2 border border-blue-500">{{ Str::limit($a->information, 120) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
