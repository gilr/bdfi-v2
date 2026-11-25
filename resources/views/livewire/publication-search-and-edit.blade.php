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
                        @if ($publication->status !== \App\Enums\PublicationStatus::PUBLIE)
                            <span class="text-xs bg-yellow-200 px-1">{{ $publication->status->getLabel() }}</span>
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    @elseif(mb_strlen(trim($search)) >= 2)
        <p>Aucune publication trouvée.</p>
    @endif

    <!-- Formulaire de modification -->
    @if($selectedPublication)
        <div class="mt-4 p-4 border">

            <h3 class="font-bold mb-1">Modifier l'enregistrement de {{ $selectedPublication->name }} <span class="text-xs text-gray-500">#{{ $selectedPublication->id }}</span></h3>
            <div class="text-sm text-gray-600 mb-2">
                {{ $selectedPublication->status->getLabel() }}
                · éditeur : {{ $selectedPublication->publisher?->name ?? '?' }}
                @if ($selectedPublication->collections->isNotEmpty())
                    · collection : {{ $selectedPublication->collections->pluck('name')->implode(', ') }}
                @endif
                @if ($selectedPublication->authors->isNotEmpty())
                    · auteurs : {{ $selectedPublication->authors->map(fn ($a) => $a->firstNameFirst)->implode(', ') }}
                @endif
                · <a class="text-red-700" href="/ouvrages/{{ $selectedPublication->slug }}" target="_blank">page</a>
                @if (auth()->user()?->hasGuestRole())
                    · <a class="text-red-700" href="/filament/publications/{{ $selectedPublication->id }}" target="_blank">fiche</a>
                @endif
                <span class="text-xs">(éditeur, collection et auteurs : à modifier dans la fiche)</span>
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="name" class="w-32 font-semibold">Titre *</label>
                <input id="name" wire:model="name" maxlength="128" required class="w-full border rounded bg-yellow-100 max-w-xl"
                       placeholder="Titre de l'ouvrage, tel qu'imprimé en couverture">
                <label for="type" class="font-semibold ml-2">Type *</label>
                <select id="type" wire:model="type" required class="w-64 border rounded bg-yellow-100">
                    @foreach ($contents as $c)
                        <option value="{{ $c->value }}">{{ $c->getLabel() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="approximate_parution" class="w-32 font-semibold">Date publi *</label>
                <input id="approximate_parution" wire:model="approximate_parution" maxlength="10" required
                       class="w-32 border rounded bg-yellow-100 font-mono" placeholder="AAAA-MM-00">
                <span class="text-xs text-gray-500">AAAA-MM-00 (1983-05-00 pour mai 1983), AAAA-00-00 si l'année seule est connue, AAAA-MM-JJ si le jour est donné, AAAA-Tn-00 (trimestre), AAAA-circa si approximative</span>
            </div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="approximate_pages" class="w-32 font-semibold">Pages approx</label>
                <input id="approximate_pages" wire:model="approximate_pages" maxlength="5" class="w-32 border rounded bg-yellow-100 font-mono">
                <label for="approximate_price" class="font-semibold ml-2">Prix approx</label>
                <input id="approximate_price" wire:model="approximate_price" maxlength="32" class="w-32 border rounded bg-yellow-100 font-mono"
                       placeholder="ex. 20 euros">
                <span class="text-xs text-gray-500">Indicatifs (BNF, revendeur...) ; les valeurs relevées sur le livre se saisissent dans « vérifier une publication »</span>
            </div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="is_genre" class="w-32 font-semibold">Genres réf. *</label>
                <select id="is_genre" wire:model="is_genre" class="w-48 border rounded bg-yellow-100">
                    @foreach (App\Enums\GenreAppartenance::cases() as $c)
                        <option value="{{ $c->value }}">{{ $c->getLabel() }}</option>
                    @endforeach
                </select>
                <label for="genre_stat" class="font-semibold ml-2">Genre *</label>
                <select id="genre_stat" wire:model="genre_stat" required class="w-48 border rounded bg-yellow-100">
                    @foreach (App\Enums\GenreStat::cases() as $c)
                        <option value="{{ $c->value }}">{{ $c->getLabel() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="target_audience" class="w-32 font-semibold">Public</label>
                <select id="target_audience" wire:model="target_audience" class="w-48 border rounded bg-yellow-100">
                    @foreach (App\Enums\AudienceTarget::cases() as $c)
                        <option value="{{ $c->value }}">{{ $c->getLabel() }}</option>
                    @endforeach
                </select>
                <label for="target_age" class="font-semibold ml-2">Age cible</label>
                <input id="target_age" wire:model="target_age" maxlength="10" class="w-24 border rounded bg-yellow-100 font-mono" placeholder="14+">
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="isbn" class="w-32 font-semibold">ISBN</label>
                <input id="isbn" wire:model="isbn" maxlength="18" class="w-48 border rounded bg-yellow-100 font-mono"
                       placeholder="978-2-..." @disabled($no_isbn)>
                <label class="ml-2"><input type="checkbox" wire:model.live="no_isbn"> pas d'ISBN</label>
                <span class="text-xs text-gray-500">« pas d'ISBN » : ouvrage ancien, fanzine… (affiché n/a) ; laissé vide : ISBN inconnu</span>
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="support" class="w-32 font-semibold">Support *</label>
                <select id="support" wire:model="support" class="w-48 border rounded bg-yellow-100">
                    @foreach ($supports as $s)
                        <option value="{{ $s->value }}">{{ $s->getLabel() }}</option>
                    @endforeach
                </select>
                <label for="format" class="font-semibold ml-2">Format</label>
                <select id="format" wire:model="format" class="w-48 border rounded bg-yellow-100">
                    @foreach ($formats as $f)
                        <option value="{{ $f->value }}">{{ $f->getLabel() }}</option>
                    @endforeach
                </select>
                <label class="ml-2"><input type="checkbox" wire:model="is_hardcover"> relié</label>
                <label class="ml-2"><input type="checkbox" wire:model="has_dustjacket"> jaquette</label>
                <label class="ml-2"><input type="checkbox" wire:model="has_coverflaps"> couverture à rabats</label>
            </div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <label for="cover" class="w-32 font-semibold">Couverture</label>
                <input id="cover" type="text" wire:model="cover" maxlength="256" class="w-64 border rounded bg-yellow-100 p-1"
                       placeholder="Illustrateur de couverture">
                <label for="illustrators" class="font-semibold ml-2">Illustrations intérieures</label>
                <input id="illustrators" type="text" wire:model="illustrators" maxlength="512" class="w-64 border rounded bg-yellow-100 p-1">
                <span class="text-xs text-gray-500">Seulement en cas de certitude ; vide : inconnu</span>
            </div>

            <label for="information">Informations notables à afficher :</label>
            <textarea id="information" wire:model="information" rows="3" class="border p-2 w-full mb-2"></textarea>
            <label for="private">Infos privées (non affichées) :</label>
            <textarea id="private" wire:model="private" rows="2" class="border p-2 w-full mb-2"></textarea>

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
