<div class="p-6 bg-white rounded shadow">
    @if ($error)
        <div class="mb-4 p-2 text-sm text-red-800 bg-red-100 border border-red-600 rounded">{{ $error }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2">
        <div class="mb-4 mr-4">
            <label for="inputFormat" class="block font-semibold">Format d'entrée</label>
            <select wire:model.live="inputFormat" id="inputFormat" class="w-full border rounded p-2">
                @foreach($formats as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4 ml-4">
            <label for="outputFormat" class="block font-semibold">Format de sortie</label>
            <select wire:model="outputFormat" id="outputFormat" class="w-full border rounded p-2">
                @foreach($this->outputFormats as $key => $label)
                    <option value="{{ $key }}" @disabled(! $this->isOutputImplemented($key))>{{ $label }}</option>
                @endforeach
            </select>
            <span class="text-xs text-gray-500">Seul JSON est disponible en sortie pour l'instant ; les autres formats sont au catalogue mais pas encore écrits.</span>
        </div>
    </div>

    <form enctype="multipart/form-data">
        <div class="mb">
            <div x-data
                x-on:change="
                if ($event.target.files.length > 0) {
                    $wire.set('wantFile', true);
                } else {
                    $wire.set('wantFile', false);
                }
                ">
            <label for="inputFile" class="block font-semibold">Données d'entrée : fichier à importer</label>
            <div class="flex items-center gap-3">
                <input type="file" wire:model="inputFile" id="inputFile" class="text-sm text-blue-600" />
                <div wire:loading wire:target="inputFile" class="mx-2 text-sm text-blue-600">
                    ⏳ Téléchargement du fichier en cours...
                </div>
                @if($inputFile)
                    <button wire:click="resetFile" class="text-sm text-blue-600 px-1 border rounded border-gray-400">
                        Forcer le reset fichier
                    </button>
                @endif
            </div>

            @if ($this->needsEncoding)
                <div wire:transition class="mt-2">
                    <label for="encoding" class="block font-semibold">Encodage du fichier</label>
                    <select wire:model="encoding" id="encoding" class="w-48 border rounded p-2 text-sm">
                        <option value="auto">Auto-détection (UTF-8 sinon CP437)</option>
                        <option value="utf8">UTF-8</option>
                        <option value="cp437">CP437 (DOS)</option>
                        <option value="cp1252">CP1252 (Windows)</option>
                    </select>
                </div>
            @endif

            @if ($this->isTextInput)
                <div wire:transition>
                    <label for="inputData" class="block font-semibold">... ou données à copier (UTF-8)</label>
                    <textarea wire:model="inputData" id="inputData" rows="6" class="text-xs w-full border rounded p-2 font-mono"></textarea>
                </div>
            @endif
        </div>
    </div>
    </form>

    <div class="mb-2">
        <button wire:click="convert" @disabled(! auth()->user()?->hasProponentRole()) wire:loading.attr="disabled" wire:target="inputFile" class="mt-4 disabled:cursor-not-allowed bg-gray-300 hover:bg-slate-300 active:bg-slate-400 active:scale-95 transition-all px-4 py-2 border border-blue-800 rounded disabled:opacity-80">
            Lancer la conversion
        </button>
    </div>

        @if($outputData)
            <div class="mt-2">
                <label class="block font-semibold">Résultat</label>

                <textarea id="outputData" readonly rows="10" class="text-xs w-full border rounded p-2 font-mono bg-gray-100">{{ $outputData }}
                    </textarea>

                <div class="mt-2 flex gap-3">
                    {{-- bouton copier --}}
                    <button
                        onclick="navigator.clipboard.writeText(document.getElementById('outputData').value)"
                        class="mx-2 bg-gray-300 hover:bg-gray-400 active:bg-gray-500 active:scale-95 transition-all px-4 py-2 border border-blue-800 rounded text-sm">
                        Copier le résultat dans le clipboard
                    </button>
                    {{-- bouton télécharger --}}
                    <a href="{{ route('outils.download') }}" target="_blank">
                        <button
                        class="mx-2 bg-gray-300 hover:bg-gray-400 active:bg-gray-500 active:scale-95 transition-all px-4 py-2 border border-blue-800 rounded text-sm">
                            Télécharger le résultat
                        </button>
                    </a>
                </div>
            </div>
        @endif

</div>
