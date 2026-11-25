<div>
    <!-- Champ de recherche -->
    <input type="text" wire:model.live.debounce.500ms="search" class="border p-2 w-full mb-4" placeholder="Extrait du titre ou de l'ISBN..." />

    <!-- Affichage des résultats -->
    @if($publications && $publications->isNotEmpty())
        <div class='grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-0 lg:gap-0.5 border py-2'>
            @foreach($publications as $publication)
                <div wire:key="pub-{{ $publication->id }}">
                    <span wire:click="selectPublication({{ $publication->id }})" class="p-2 cursor-pointer hover:bg-gray-200 {{ $selectedPublication && $selectedPublication->id === $publication->id ? 'bg-blue-400 font-bold' : '' }}">
                        {{ $publication->name }}
                        <span class="text-sm italic text-red-700">{{ $publication->publisher?->name }} {{ substr($publication->approximate_parution, 0, 4) }}</span>
                        @if ($publication->is_verified)
                            <span class="text-xs bg-green-100 px-1">vérifié</span>
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    @elseif(mb_strlen(trim($search)) >= 2)
        <p>Aucune publication parue trouvée.</p>
    @endif

    <!-- Formulaire de vérification -->
    @if($selectedPublication)
        <div class="mt-4 p-4 border">

            <h3 class="font-bold mb-1">Vérifier {{ $selectedPublication->name }} <span class="text-xs text-gray-500">#{{ $selectedPublication->id }}</span></h3>

            {{-- À comparer avec l'ouvrage entre les mains --}}
            <div class="text-sm bg-blue-50 border rounded p-1 mb-2">
                <span class="font-semibold">Enregistré :</span>
                éditeur {{ $selectedPublication->publisher?->name ?? '?' }}
                @if ($selectedPublication->collections->isNotEmpty())
                    · collection {{ $selectedPublication->collections->map(fn ($c) => $c->name . (filled($c->pivot->number) ? ' n° ' . $c->pivot->number : ''))->implode(', ') }}
                @endif
                @if ($selectedPublication->authors->isNotEmpty())
                    · {{ $selectedPublication->authors->map(fn ($a) => $a->firstNameFirst)->implode(', ') }}
                @endif
                <br />
                ISBN {{ $selectedPublication->isbn }}
                · date {{ $selectedPublication->approximate_parution }}
                · {{ $selectedPublication->support->getLabel() }}, {{ $selectedPublication->format->getLabel() }}
                @if (filled($selectedPublication->approximate_pages))
                    · ~{{ $selectedPublication->approximate_pages }} pages
                @endif
                @if (filled($selectedPublication->approximate_price))
                    · ~{{ $selectedPublication->approximate_price }}
                @endif
                · <a class="text-red-700" href="/ouvrages/{{ $selectedPublication->slug }}" target="_blank">page</a>
                @if (auth()->user()?->hasGuestRole())
                    · <a class="text-red-700" href="/filament/publications/{{ $selectedPublication->id }}" target="_blank">fiche</a>
                @endif
                <span class="text-xs">(titre, éditeur, collection et auteurs : à corriger dans la fiche ; autres données générales : formulaire de modification)</span>
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label class="w-32 font-semibold"><input type="checkbox" wire:model.live="is_verified"> Vérifié</label>
                <label for="verified_by" class="font-semibold ml-2">Vérifié par</label>
                <input id="verified_by" wire:model="verified_by" maxlength="256" class="w-64 border rounded bg-yellow-100">
                <span class="text-xs text-gray-500">Toujours le même identifiant de préférence ; si plusieurs vérificateurs : « user1; user2 ». Vide : vous.</span>
            </div>
            <div class="text-xs text-gray-500 mb-2">
                « Vérifié » indique que les informations ci-dessous sont validées, mais aussi le sommaire réel (titres de début de page, et non sommaire).
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="cycle" class="w-32 font-semibold">Série</label>
                <input id="cycle" wire:model="cycle" maxlength="128" class="w-64 border rounded bg-yellow-100"
                       placeholder="Telle qu'en page de titre">
                <label for="cyclenum" class="font-semibold ml-2">N° dans la série</label>
                <input id="cyclenum" wire:model="cyclenum" maxlength="10" class="w-24 border rounded bg-yellow-100 font-mono">
                <span class="text-xs text-gray-500">Les rattachements aux séries se font au niveau des textes</span>
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="ai" class="w-32 font-semibold">AI</label>
                <input id="ai" wire:model="ai" maxlength="10" class="w-32 border rounded bg-yellow-100 font-mono" placeholder="AAAA-MM-JJ">
                <label for="dl" class="font-semibold ml-2">DL</label>
                <input id="dl" wire:model="dl" maxlength="10" class="w-32 border rounded bg-yellow-100 font-mono" placeholder="AAAA-MM-JJ">
                <span class="text-xs text-gray-500">Achevé d'imprimer, dépôt légal : AAAA-MM-JJ, 00 si inconnu (1983-00-00), AAAA-Tn-00 (trimestre) ; « A parution » pour le DL si indiqué ainsi ; vide si non indiqué</span>
            </div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="printer" class="w-32 font-semibold">Imprimeur</label>
                <input id="printer" wire:model="printer" maxlength="128" class="w-64 border rounded bg-yellow-100">
                <label for="edition" class="font-semibold ml-2">Édition</label>
                <input id="edition" wire:model="edition" maxlength="64" class="w-48 border rounded bg-yellow-100"
                       placeholder="ex. 5ème tirage">
                <span class="text-xs text-gray-500">Indication d'édition ou de tirage si imprimée (surtout ouvrages anciens)</span>
            </div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="pagination" class="w-32 font-semibold">Pagination</label>
                <input id="pagination" wire:model="pagination" maxlength="32" class="w-32 border rounded bg-yellow-100 font-mono"
                       placeholder="256 ou xiv + 256">
                <label for="pages_dpi" class="font-semibold ml-2">Dernière page numérotée</label>
                <input id="pages_dpi" wire:model="pages_dpi" maxlength="5" class="w-20 border rounded bg-yellow-100 font-mono">
                <label for="pages_dpu" class="font-semibold ml-2">Dernière page utile</label>
                <input id="pages_dpu" wire:model="pages_dpu" maxlength="5" class="w-20 border rounded bg-yellow-100 font-mono">
            </div>
            <div class="text-xs text-gray-500 mb-1">
                Pagination : nombre total de pages effectif, y compris les pages de fin blanches (« xiv + 256 » si pages en chiffres romains au départ).
                Dernière page numérotée : dernier numéro visible (façon BNF) ; dernière page utile : dernier numéro de page utile.
            </div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <label for="dimensions" class="w-32 font-semibold">Dimensions</label>
                <input id="dimensions" wire:model="dimensions" maxlength="10" class="w-32 border rounded bg-yellow-100 font-mono"
                       placeholder="110 x 178">
                <label for="thickness" class="font-semibold ml-2">Épaisseur</label>
                <input id="thickness" wire:model="thickness" maxlength="4" class="w-20 border rounded bg-yellow-100 font-mono"
                       placeholder="18">
                <label for="printed_price" class="font-semibold ml-2">Prix ou code prix</label>
                <input id="printed_price" wire:model="printed_price" maxlength="32" class="w-32 border rounded bg-yellow-100"
                       placeholder="ex. LP13">
                <span class="text-xs text-gray-500">Millimètres (± 1), « largeur x hauteur », épaisseur à distance du dos sans unité ; prix tel qu'imprimé, vide si aucune indication</span>
            </div>

            <div class="flex space-x-2 mt-2">
                <button wire:click="save" @disabled(! auth()->user()?->hasMemberRole()) class="bg-blue-400 mr-2 text-white p-2 disabled:opacity-50 disabled:cursor-not-allowed">Enregistrer</button>
                <button wire:click="resetSelection" class="bg-gray-500 text-white p-2">Annuler</button>
            </div>

            @if($errors->isNotEmpty())
                <ul class="mt-2 text-red-700 list-disc pl-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            @if($success !== '')
                <div class="mt-2 text-green-600">{{ $success }}</div>
            @endif
        </div>
    @endif
</div>
