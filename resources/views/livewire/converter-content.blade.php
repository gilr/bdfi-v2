<div class="p-6 bg-white rounded shadow">
    <h2 class="text-xl font-bold mb-4">Convertisseur de contenu</h2>

    <div class="grid grid-cols-1 lg:grid-cols-2">
    <div class="mb-4 mr-4">
        <label for="inputFormat" class="block font-semibold">Format d'entrée</label>
        <select wire:model="inputFormat" id="inputFormat" class="w-full border rounded p-2">
            <option value="CSV-TAB">Format CSV CM</option>
            <option value="TEXT-TPN">Titre, prénom, nom</option>
            <option value="TEXT-TNP">Titre, nom, prénom</option>
            <option value="TEXT-NPT">Nom, prénom, titre</option>
            <option value="TEXT-PNT">Prénom, nom, titre</option>
        </select>
    </div>

    <div class="mb-4 ml-4">
        <label for="outputFormat" class="block font-semibold">Format de sortie</label>
        <select wire:model="outputFormat" id="outputFormat" class="w-full border rounded p-2">
            <option value="COL">COL (format interne base v1)</option>
        </select>
    </div>
    </div>

    <div class="mb-4">
        <label for="inputData" class="block font-semibold">Données à convertir</label>
        <textarea wire:model="inputData" id="inputData" rows="8" class="text-xs w-full border rounded p-2 font-mono"></textarea>
    </div>

    <div class="mb-4">
        <button wire:click="convert" class="mt-4 bg-gray-300 px-4 py-2 rounded">
            Lancer la conversion
        </button>
    </div>

    <div class="mb-4">
        @if($outputData)
            <div class="mt-6">
                <label class="block font-semibold">Résultat</label>
                <textarea readonly rows="10" class="text-xs w-full border rounded p-2 font-mono bg-gray-100">{{ $outputData }}</textarea>
            </div>
            <a href="{{ route('labs.download') }}" target="_blank" class="mt-6">
                <div class="mb-4">
                    <button class="mt-4 bg-gray-300 px-4 py-2 rounded">
                        Télécharger
                    </button>
                </div>
            </a>
        @endif
    </div>
</div>
