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

            <div class="flex flex-wrap items-center gap-2 mb-2">
                <label for="edit_name" class="w-32 font-semibold">Nom *</label>
                <input id="edit_name" type="text" wire:model="name" maxlength="32" class="w-64 border rounded bg-yellow-100 p-2">
                <label for="edit_first_name" class="font-semibold ml-2">Prénom(s)</label>
                <input id="edit_first_name" type="text" wire:model="first_name" maxlength="32" class="w-64 border rounded bg-yellow-100 p-2">
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-2">
                <label for="edit_country_id" class="w-32 font-semibold">Nationalité</label>
                <select id="edit_country_id" wire:model="country_id" class="w-64 border rounded bg-yellow-100 p-2">
                    <optgroup label="Les plus fréquentes">
                        @foreach ($countries['frequent'] as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Tous les pays">
                        @foreach ($countries['others'] as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </optgroup>
                </select>
                <label for="edit_gender" class="font-semibold ml-2">H/F & co</label>
                <select id="edit_gender" wire:model="gender" class="w-48 border rounded bg-yellow-100 p-2">
                    @foreach ($genders as $g)
                        <option value="{{ $g->value }}">{{ $g->getLabel() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-2">
                <label for="edit_is_pseudonym" class="w-32 font-semibold">Pseudonyme</label>
                <input type="checkbox" id="edit_is_pseudonym" wire:model="is_pseudonym">
                <label for="edit_legal_name" class="font-semibold ml-2">Nom légal</label>
                <input id="edit_legal_name" type="text" wire:model="legal_name" maxlength="128" class="w-64 border rounded bg-yellow-100 p-2"
                       placeholder="Si pseudonyme ou nom de plume">
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-2">
                <label for="edit_birth_date" class="w-32 font-semibold">Naissance</label>
                <input id="edit_birth_date" type="text" wire:model="birth_date" maxlength="10" class="w-32 border rounded bg-yellow-100 p-2"
                       placeholder="AAAA-MM-JJ">
                <label for="edit_birthplace" class="font-semibold ml-2">Lieu</label>
                <input id="edit_birthplace" type="text" wire:model="birthplace" maxlength="64" class="w-48 border rounded bg-yellow-100 p-2">
                <label for="edit_date_death" class="font-semibold ml-2">Décès</label>
                <input id="edit_date_death" type="text" wire:model="date_death" maxlength="10" class="w-32 border rounded bg-yellow-100 p-2"
                       placeholder="AAAA-MM-JJ">
            </div>
            <div class="text-xs text-gray-500 mb-2">AAAA-MM-JJ, 00 si mois ou jour inconnu (1983-00-00), AAAA-circa ou -AAA-circa ; laisser vide une date inconnue</div>

            <div class="flex flex-wrap items-center gap-2 mb-2">
                <label for="edit_alt_names" class="w-32 font-semibold">Écritures alt.</label>
                <input id="edit_alt_names" type="text" wire:model="alt_names" maxlength="512" class="w-full border rounded bg-yellow-100 p-2 max-w-xl"
                       placeholder="Autres écritures/graphies du nom, séparées par des virgules">
            </div>

            <label>Biographie :</label>
            <textarea wire:model="bio" rows="5"  class="border p-2 w-full mb-2"></textarea>

            <div class="flex space-x-2 mt-2">
                <button wire:click="save" @disabled(! auth()->user()?->hasMemberRole()) class="bg-blue-400 mr-2 text-white p-2 disabled:opacity-50 disabled:cursor-not-allowed">Enregistrer</button>
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

