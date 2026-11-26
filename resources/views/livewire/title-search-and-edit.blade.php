<div>
    <!-- Champ de recherche -->
    <input type="text" wire:model.live.debounce.500ms="search" class="border p-2 w-full mb-4" placeholder="Extrait du titre du texte (ou #identifiant)..." />

    <!-- Affichage des résultats -->
    @if($titles && count($titles) > 0)
        <div class='grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-0 lg:gap-0.5 border py-2'>
            @foreach($titles as $title)
                <div wire:key="title-{{ $title->id }}">
                    <span wire:click="selectTitle({{ $title->id }})" class="p-2 cursor-pointer hover:bg-gray-200 {{ $selectedTitle && $selectedTitle->id === $title->id ? 'bg-blue-400 font-bold' : '' }}">
                        {{ $title->name }}
                        <span class="text-sm italic text-red-700">{{ $title->authors->map(fn ($a) => trim($a->first_name . ' ' . $a->name))->implode(', ') }} {{ substr((string) $title->copyright_fr, 0, 4) }}</span>
                        @if ($title->variant_type !== 'premier')
                            <span class="text-xs bg-yellow-200 px-1">{{ $title->variant_type }}</span>
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    @elseif(mb_strlen(trim($search)) >= 2)
        <p>Aucun texte trouvé.</p>
    @endif

    <!-- Formulaire de modification -->
    @if($selectedTitle)
        <div class="mt-4 p-4 border">

            <h3 class="font-bold mb-1">Modifier le texte {{ $selectedTitle->name }} <span class="text-xs text-gray-500">#{{ $selectedTitle->id }}</span></h3>
            <div class="text-sm text-gray-600 mb-2">
                de {{ $selectedTitle->authors->map(fn ($a) => trim($a->first_name . ' ' . $a->name))->implode(', ') ?: '?' }}
                · variante : {{ $selectedTitle->variant_type }}
                @if ($selectedTitle->parent)
                    de « {{ $selectedTitle->parent->name }} » (#{{ $selectedTitle->parent->id }})
                @endif
                @if ($selectedTitle->publications->isNotEmpty())
                    · dans : {{ $selectedTitle->publications->map(fn ($p) => $p->name)->implode(' ; ') }}
                @endif
                · <a class="text-red-700" href="/textes/{{ $selectedTitle->slug }}" target="_blank">page</a>
                @if (auth()->user()?->hasGuestRole())
                    · <a class="text-red-700" href="/filament/titles/{{ $selectedTitle->id }}" target="_blank">fiche</a>
                    · <a class="text-red-700" href="/admin/formulaires/gerer-variantes?titre={{ $selectedTitle->id }}" target="_blank">variantes</a>
                @endif
                <span class="text-xs">(auteurs et séries : à modifier dans la fiche ; variantes : formulaire dédié)</span>
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="name" class="w-32 font-semibold">Titre *</label>
                <input id="name" wire:model="name" maxlength="256" required class="w-full border rounded bg-yellow-100 max-w-xl">
                <label for="type" class="font-semibold ml-2">Type *</label>
                <select id="type" wire:model="type" required class="w-48 border rounded bg-yellow-100">
                    @foreach ($types as $t)
                        <option value="{{ $t->value }}">{{ $t->getLabel() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="text-xs text-gray-500 mb-1 ml-32">Un changement de titre ne modifie pas l'adresse de la page du texte.</div>

            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="copyright" class="w-32 font-semibold">Copyright</label>
                <input id="copyright" wire:model="copyright" maxlength="10" class="w-32 border rounded bg-yellow-100 font-mono" placeholder="AAAA-MM-JJ">
                <label for="copyright_fr" class="font-semibold ml-2">1ère parution fr</label>
                <input id="copyright_fr" wire:model="copyright_fr" maxlength="10" class="w-32 border rounded bg-yellow-100 font-mono" placeholder="AAAA-MM-JJ">
                <span class="text-xs text-gray-500">AAAA-MM-JJ, 00 si inconnu (1983-05-00, 1983-00-00), AAAA-Tn-00 (trimestre) ; copyright : 1ère publication dans la langue d'origine</span>
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="title_vo" class="w-32 font-semibold">Titre original</label>
                <input id="title_vo" wire:model="title_vo" maxlength="512" class="w-full border rounded bg-yellow-100 max-w-xl" placeholder="Si traduit (« titre 1 / autre titre » si plusieurs)">
            </div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="pub_vo" class="w-32 font-semibold">Publi. d'origine</label>
                <input id="pub_vo" wire:model="pub_vo" maxlength="256" class="w-full border rounded bg-yellow-100 max-w-xl" placeholder="Revue ou recueil de première publication">
            </div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="translators" class="w-32 font-semibold">Traducteur(s)</label>
                <input id="translators" wire:model="translators" maxlength="512" class="w-full border rounded bg-yellow-100 max-w-xl" placeholder="Nom Prénom, Nom Prénom">
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="is_genre" class="w-32 font-semibold">Genres réf. *</label>
                <select id="is_genre" wire:model="is_genre" class="w-48 border rounded bg-yellow-100">
                    @foreach (App\Enums\GenreAppartenance::cases() as $c)
                        <option value="{{ $c->value }}">{{ $c->getLabel() }}</option>
                    @endforeach
                </select>
                <label for="genre_stat" class="font-semibold ml-2">Genre *</label>
                <select id="genre_stat" wire:model="genre_stat" class="w-48 border rounded bg-yellow-100">
                    @foreach (App\Enums\GenreStat::cases() as $c)
                        <option value="{{ $c->value }}">{{ $c->getLabel() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <label for="target_audience" class="w-32 font-semibold">Public *</label>
                <select id="target_audience" wire:model="target_audience" class="w-48 border rounded bg-yellow-100">
                    @foreach (App\Enums\AudienceTarget::cases() as $c)
                        <option value="{{ $c->value }}">{{ $c->getLabel() }}</option>
                    @endforeach
                </select>
                <label for="target_age" class="font-semibold ml-2">Age cible</label>
                <input id="target_age" wire:model="target_age" maxlength="10" class="w-24 border rounded bg-yellow-100 font-mono" placeholder="14+">
                <label for="is_novelization" class="font-semibold ml-2">Novellisation</label>
                <select id="is_novelization" wire:model="is_novelization" class="w-56 border rounded bg-yellow-100">
                    @foreach ($novelizations as $n)
                        <option value="{{ $n->value }}">{{ $n->getLabel() }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="w-32 font-semibold">Options</span>
                <label><input type="checkbox" wire:model="is_visible"> visible</label>
                <label class="ml-2"><input type="checkbox" wire:model="is_serial"> paru en feuilleton</label>
                <label class="ml-2"><input type="checkbox" wire:model="is_fullserial"> feuilleton complet</label>
            </div>
            <div class="flex flex-wrap items-center gap-2 mb-2">
                <label for="serial_info" class="w-32 font-semibold">Infos feuilleton</label>
                <input id="serial_info" wire:model="serial_info" maxlength="512" class="w-full border rounded bg-yellow-100 max-w-xl">
            </div>

            <label for="synopsis">Résumé (synopsis) :</label>
            <textarea id="synopsis" wire:model="synopsis" rows="3" class="border p-2 w-full mb-2"></textarea>
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
