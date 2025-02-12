<div>
    <!-- Champ de recherche -->
    <input type="text" wire:model.live.debounce.500ms="search" class="border p-2 w-full mb-4" placeholder="Entrez un extrait de nom..." />

    <!-- Affichage des résultats -->
    @if($authors && $authors->isNotEmpty())
        <!-- Liste des résultats -->
        <div class='grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-0 lg:gap-0.5 border py-2"'>
            @foreach($authors as $author)
                <div>
                    <span wire:click="selectAuthor({{ $author->id }})" class="p-2 cursor-pointer hover:bg-gray-200 {{ $selectedPerson && $selectedPerson->id === $author->id ? 'bg-blue-400 font-bold' : '' }}">
                        {{ $author->name }}, {{ $author->first_name }} - {{ $author->birth_date }}
                    </span>
                </div>
            @endforeach
        </div>
    @elseif($search != "")
        <p>Aucun auteur trouvé.</p>
    @endif

    <!-- Formulaire de modification -->
    @if($selectedPerson)
        <div class="mt-4 p-4 border">

            <h3 class="font-bold mb-2">Modifier l'enregistrement de {{ $first_name }} {{ $name }}</h3>

            <label>Date de naissance :</label>
            <input type="text" wire:model="birth_date" class="border p-2 mb-2">

            <label>Date de décès :</label>
            <input type="text" wire:model="date_death" class="border p-2 mb-2">
            <br />

            <label>Lieu de naissance :</label>
            <input type="text" wire:model="birthplace" class="border p-2 w-full mb-2">

            <label>Biographie :</label>
            <textarea wire:model="bio" rows="5"  class="border p-2 w-full mb-2"></textarea>

            <div class="flex space-x-2 mt-2">
                <button wire:click="save" class="bg-blue-400 mr-2 text-white p-2">Enregistrer</button>
                <button wire:click="resetSelection" class="bg-gray-500 text-white p-2">Annuler</button>
            </div>

            @if($errors->isNotEmpty())
                <div class="mt-2 text-red-700">{{ $errors }}</div>
            @endif

            @if(session()->has('success'))
                <div class="mt-2 text-green-600">{{ session('success') }}</div>
            @endif
        </div>
    @endif
</div>

