<div class="border border-blue-800 rounded p-2 my-2">
    <div class="font-semibold">Contenu principal *</div>
    <div class="text-xs text-gray-500 mb-1">
        Le texte de l'ouvrage (équivalent de son titre), rattaché à la publication : un texte déjà en base si l'ouvrage reprend à l'identique
        un texte déjà publié (même titre et mêmes auteurs), sinon un nouveau texte, créé avec la publication.
        Renseignez d'abord le titre et les auteurs ci-dessus, puis cherchez.
    </div>

    <button type="button"
            wire:click="search(document.getElementById('name').value, document.querySelector('[name=author1_id]')?.value, document.querySelector('[name=author2_id]')?.value, document.getElementById('type').value)"
            class="bg-blue-400 text-white px-2 py-1 rounded">Chercher le contenu principal</button>

    @if ($searched)
        <div class="mt-2">
            @if (count($candidates) === 0)
                <p class="text-sm italic">Aucun texte existant de même titre et de mêmes auteurs (les auteurs doivent être renseignés pour la recherche) : il sera créé.</p>
            @else
                <p class="text-sm">Textes existants de même titre et de mêmes auteurs. Choisissez celui qui est <span class="font-semibold">vraiment identique</span>, ou créez un nouveau texte :</p>
                @foreach ($candidates as $c)
                    <label class="block border rounded p-1 my-1 {{ $choice === (string) $c['id'] ? 'bg-blue-100' : '' }}" wire:key="mc-{{ $c['id'] }}">
                        <input type="radio" name="title_choice" value="{{ $c['id'] }}" wire:model.live="choice">
                        <span class="font-semibold">{{ $c['name'] }}</span>
                        <span class="text-sm">de {{ $c['authors'] }}</span>
                        <span class="text-xs text-gray-500">(#{{ $c['id'] }}, {{ $c['type'] }}{{ $c['variant'] === 'extrait' ? ', extrait' : '' }})</span>
                        <a href="/textes/{{ $c['slug'] }}" target="_blank" class="text-red-700 text-sm">voir</a>
                        <div class="text-sm pl-6">
                            1ère parution fr : <span class="font-mono">{{ $c['copyright_fr'] ?: '?' }}</span>
                            · copyright : <span class="font-mono">{{ $c['copyright'] ?: '?' }}</span>
                            @if ($c['title_vo']) · titre VO : {{ $c['title_vo'] }} @endif
                            @if ($c['translators']) · traduction : {{ $c['translators'] }} @endif
                            @if ($c['publications']) <br />Présent dans : {{ implode(' ; ', $c['publications']) }} @endif
                        </div>
                    </label>
                @endforeach
            @endif

            <label class="block mt-1">
                <input type="radio" name="title_choice" value="new" wire:model.live="choice">
                <span class="font-semibold">Créer un nouveau texte</span> (le titre est celui de la publication)
            </label>

            @if ($choice === 'new')
                <div class="pl-6 mt-1">
                    @if ($allowsExtract)
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="w-40">Extrait</span>
                            <label><input type="checkbox" name="new_title_extract" value="1" wire:model.live="newExtract"> extrait d'un texte plus long</label>
                            <span class="text-xs text-gray-500">À rattacher ensuite à son texte complet dans le formulaire des variantes.</span>
                        </div>
                    @endif
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <label for="new_title_type" class="w-40">Type de texte *</label>
                        <select id="new_title_type" name="new_title_type" wire:model="newType" class="w-64 border rounded bg-yellow-100">
                            <option value="">— choisir —</option>
                            @foreach ($titleTypes as $t)
                                <option value="{{ $t->value }}">{{ $t->getLabel() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <label for="new_title_novelization" class="w-40">Novellisation</label>
                        <select id="new_title_novelization" name="new_title_novelization" wire:model="newNovelization" class="w-64 border rounded bg-yellow-100">
                            @foreach ($novelizations as $n)
                                <option value="{{ $n->value }}">{{ $n->getLabel() }}</option>
                            @endforeach
                        </select>
                        <span class="text-xs text-gray-500">Si le texte est tiré d'un film, d'une série, d'un jeu...</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <label for="new_title_copyright" class="w-40">Date de copyright</label>
                        <input id="new_title_copyright" name="new_title_copyright" wire:model="newCopyright" maxlength="10"
                               class="w-32 border rounded bg-yellow-100 font-mono" placeholder="AAAA-MM-JJ">
                        <span class="text-xs text-gray-500">1ère publication dans la langue d'origine (AAAA-MM-JJ, 00 si inconnu, AAAA-Tn-00) ; vide si inconnue.</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <label for="new_title_vo" class="w-40">Titre original (VO)</label>
                        <input id="new_title_vo" name="new_title_vo" wire:model="newTitleVo" maxlength="512" class="w-full max-w-xl border rounded bg-yellow-100"
                               placeholder="Si traduit">
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <label for="new_title_translators" class="w-40">Traducteur(s)</label>
                        <input id="new_title_translators" name="new_title_translators" wire:model="newTranslators" maxlength="512" class="w-full max-w-xl border rounded bg-yellow-100"
                               placeholder="Nom Prénom, Nom Prénom">
                    </div>
                    <div class="text-xs text-gray-500">La date de 1ère parution française du texte est celle de la publication. Genre et public sont ceux de la publication ; auteurs : ceux de la publication.</div>
                </div>
            @endif
        </div>
    @endif
</div>
