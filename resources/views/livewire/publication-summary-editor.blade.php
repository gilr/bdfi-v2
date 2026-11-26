<div>
    <!-- Choix de la publication -->
    <input type="text" wire:model.live.debounce.500ms="search" class="border p-2 w-full mb-4" placeholder="Extrait du titre ou de l'ISBN de la publication..." />

    @if($publications && count($publications) > 0)
        <div class='grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-0 lg:gap-0.5 border py-2'>
            @foreach($publications as $p)
                <div wire:key="pub-{{ $p->id }}">
                    <span wire:click="selectPublication({{ $p->id }})" class="p-2 cursor-pointer hover:bg-gray-200 {{ $publicationId === $p->id ? 'bg-blue-400 font-bold' : '' }}">
                        {{ $p->name }}
                        <span class="text-sm italic text-red-700">{{ $p->publisher?->name }} {{ substr($p->approximate_parution, 0, 4) }}</span>
                        @if ($p->status !== \App\Enums\PublicationStatus::PUBLIE)
                            <span class="text-xs bg-yellow-200 px-1">{{ $p->status->getLabel() }}</span>
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    @elseif(mb_strlen(trim($search)) >= 2)
        <p>Aucune publication trouvée.</p>
    @endif

    @if($publication)
        <div class="mt-4 p-4 border">

            <div class="flex justify-between items-start">
                <h3 class="font-bold mb-1">Sommaire de {{ $publication->name }} <span class="text-xs text-gray-500">#{{ $publication->id }}</span></h3>
                <button wire:click="resetSelection" class="bg-gray-500 text-white px-2 rounded">Fermer</button>
            </div>

            {{-- Rappel des données de la publication (non modifiables ici) --}}
            <div class="text-sm bg-blue-50 border rounded p-1 mb-2">
                {{ $publication->status->getLabel() }}
                · éditeur {{ $publication->publisher?->name ?? '?' }}
                @if ($publication->collections->isNotEmpty())
                    · collection {{ $publication->collections->map(fn ($c) => $c->name . (filled($c->pivot->number) ? ' n° ' . $c->pivot->number : ''))->implode(', ') }}
                @endif
                @if ($publication->authors->isNotEmpty())
                    · {{ $publication->authors->map(fn ($a) => trim($a->first_name . ' ' . $a->name))->implode(', ') }}
                @endif
                <br />
                {{ $publication->type->getLabel() }} · date {{ $publication->approximate_parution }} · ISBN {{ $publication->isbn }}
                · {{ $publication->support->getLabel() }}, {{ $publication->format->getLabel() }}
                @if (filled($publication->approximate_pages)) · ~{{ $publication->approximate_pages }} pages @endif
                · <a class="text-red-700" href="/ouvrages/{{ $publication->slug }}" target="_blank">page</a>
                @if (auth()->user()?->hasGuestRole())
                    · <a class="text-red-700" href="/filament/publications/{{ $publication->id }}" target="_blank">fiche</a>
                    · <a class="text-red-700" href="/admin/formulaires/modifier-publication?id={{ $publication->id }}" target="_blank">formulaire</a>
                @endif
            </div>

            @if($notice !== '')
                <div class="p-2 mb-2 text-green-800 bg-green-100 border border-green-500">{{ $notice }}</div>
            @endif
            @if($error !== '')
                <div class="p-2 mb-2 text-red-700 bg-red-100 border border-red-300">{{ $error }}</div>
            @endif
            @if($errors->isNotEmpty())
                <ul class="mb-2 text-red-700 list-disc pl-6">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            @endif

            {{-- Sommaire --}}
            @if(!$main)
                <div class="p-2 mb-2 text-red-700 bg-red-100 border border-red-300">
                    Cette publication n'a pas de contenu principal : il est obligatoire (l'équivalent du titre de l'ouvrage, à la racine du sommaire).
                    Rattachez un texte existant identique, ou créez-le ci-dessous.
                </div>
                @foreach ($mainCandidates as $c)
                    <div class="border rounded p-1 my-1" wire:key="mcand-{{ $c['id'] }}">
                        <span class="font-semibold">{{ $c['name'] }}</span> <span class="text-sm">de {{ $c['authors'] }}</span>
                        <span class="text-xs text-gray-500">(#{{ $c['id'] }}, {{ $c['type'] }})</span>
                        <a href="/textes/{{ $c['slug'] }}" target="_blank" class="text-red-700 text-sm">voir</a>
                        <div class="text-sm">
                            1ère parution fr : <span class="font-mono">{{ $c['copyright_fr'] ?: '?' }}</span>
                            @if ($c['title_vo']) · titre VO : {{ $c['title_vo'] }} @endif
                            @if ($c['translators']) · traduction : {{ $c['translators'] }} @endif
                        </div>
                        <button wire:click="attachMain({{ $c['id'] }})" @disabled(! auth()->user()?->hasMemberRole()) class="bg-green-100 text-green-800 border border-green-600 px-2 rounded disabled:opacity-50">C'est le même texte : rattacher</button>
                    </div>
                @endforeach
            @endif

            @if($toc->isNotEmpty())
                <table class="table-auto border-collapse border border-blue-800 w-full mb-2">
                    <thead>
                        <tr class="bg-blue-50">
                            <th class="p-1">Ordre</th>
                            <th class="p-1">Niveau</th>
                            <th class="p-1">Texte</th>
                            <th class="p-1">Pages (début - fin)</th>
                            <th class="p-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($toc as $t)
                            @php($isMain = $main && $main->id === $t->id)
                            <tr class="border border-blue-500 {{ $isMain ? 'bg-yellow-50' : '' }}" wire:key="toc-{{ $t->id }}">
                                <td class="py-1 px-2 border border-blue-500">
                                    @if($isMain)
                                        <span class="font-mono">1</span>
                                    @else
                                        <input type="text" wire:model="rows.{{ $t->id }}.order" maxlength="4" class="w-14 border rounded bg-yellow-100 font-mono">
                                    @endif
                                </td>
                                <td class="py-1 px-2 border border-blue-500">
                                    @if($isMain)
                                        <span class="font-mono">0</span>
                                    @else
                                        <select wire:model="rows.{{ $t->id }}.level" class="w-20 border rounded bg-yellow-100">
                                            @foreach ($levels as $l)
                                                @if ($l !== '0')
                                                    <option value="{{ $l }}">{{ $l }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    @endif
                                </td>
                                <td class="py-1 px-2 border border-blue-500">
                                    <div style="padding-left: {{ (int) round(((float) $t->level) * 24) }}px">
                                        @if($isMain) <span class="text-xs bg-yellow-200 px-1">contenu principal</span> @endif
                                        <span class="{{ $isMain ? 'font-bold' : '' }}">{{ $t->title->name }}</span>
                                        <span class="text-sm italic text-red-700">{{ $t->title->authors->map(fn ($a) => trim($a->first_name . ' ' . $a->name))->implode(', ') }}</span>
                                        <span class="text-xs text-gray-500">({{ $t->title->type?->getLabel() }}, #{{ $t->title->id }}{{ $t->title->copyright_fr ? ', ' . substr($t->title->copyright_fr, 0, 4) : '' }})</span>
                                        <a href="/textes/{{ $t->title->slug }}" target="_blank" class="text-red-700 text-sm">voir</a>
                                        @if (auth()->user()?->hasGuestRole())
                                            <a href="/admin/formulaires/modifier-oeuvre?id={{ $t->title->id }}" target="_blank" class="text-red-700 text-sm">modifier</a>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-1 px-2 border border-blue-500 whitespace-nowrap">
                                    <div class="flex items-center gap-1">
                                        <input type="text" wire:model="rows.{{ $t->id }}.start_page" maxlength="8" class="w-14 border rounded bg-yellow-100 font-mono px-1" aria-label="Page de début">
                                        <span>-</span>
                                        <input type="text" wire:model="rows.{{ $t->id }}.end_page" maxlength="8" class="w-14 border rounded bg-yellow-100 font-mono px-1" aria-label="Page de fin">
                                    </div>
                                </td>
                                <td class="py-1 px-2 border border-blue-500">
                                    @if(!$isMain)
                                        <button wire:click="removeRow({{ $t->id }})" wire:confirm="Retirer ce texte du sommaire ? Le texte reste en base." @disabled(! auth()->user()?->hasMemberRole())
                                                class="bg-gray-500 text-white px-2 rounded disabled:opacity-50">Retirer</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="text-xs text-gray-500 mb-2">
                    Niveaux : 0 contenu principal (racine) · 0.5 et 1.5 sections · 1 texte du sommaire · 2 et plus sous-niveaux. Les ordres sont renumérotés de 1 à n à l'enregistrement.
                    « Retirer » rompt seulement le rattachement à cette publication.
                </div>
                <button wire:click="saveRows" @disabled(! auth()->user()?->hasMemberRole()) class="bg-blue-400 text-white px-3 py-1 rounded disabled:opacity-50 disabled:cursor-not-allowed">Enregistrer les rattachements</button>
            @elseif($main === null)
                <p class="italic text-sm">Sommaire vide.</p>
            @endif

            {{-- Rattacher un texte existant --}}
            @if($main)
                <div class="mt-4 border-t pt-2">
                    <div class="font-semibold">Ajouter un texte existant</div>
                    <input type="text" wire:model.live.debounce.500ms="addSearch" class="border p-2 w-full max-w-xl bg-yellow-100" placeholder="Extrait du titre du texte (ou #identifiant)...">
                    @foreach ($addResults as $c)
                        <div class="border rounded p-1 my-1" wire:key="add-{{ $c['id'] }}">
                            <span class="font-semibold">{{ $c['name'] }}</span> <span class="text-sm">de {{ $c['authors'] ?: '?' }}</span>
                            <span class="text-xs text-gray-500">(#{{ $c['id'] }}, {{ $c['type'] }}{{ $c['variant'] !== 'premier' ? ', ' . $c['variant'] : '' }})</span>
                            <a href="/textes/{{ $c['slug'] }}" target="_blank" class="text-red-700 text-sm">voir</a>
                            <span class="text-sm">1ère parution fr : <span class="font-mono">{{ $c['copyright_fr'] ?: '?' }}</span>
                                @if ($c['title_vo']) · VO : {{ $c['title_vo'] }} @endif
                                @if ($c['translators']) · trad. : {{ $c['translators'] }} @endif</span>
                            <button wire:click="attach({{ $c['id'] }})" @disabled(! auth()->user()?->hasMemberRole()) class="bg-green-100 text-green-800 border border-green-600 px-2 rounded disabled:opacity-50">Ajouter</button>
                        </div>
                    @endforeach
                    @if(mb_strlen(trim($addSearch)) >= 2 && count($addResults) === 0)
                        <p class="text-sm italic">Aucun texte trouvé (ou déjà dans ce sommaire).</p>
                    @endif
                </div>
            @endif

            {{-- Créer un texte --}}
            <div class="mt-4 border-t pt-2">
                @if(!$showNew)
                    <button wire:click="openNew" @disabled(! auth()->user()?->hasMemberRole()) class="bg-blue-400 text-white px-3 py-1 rounded disabled:opacity-50">
                        {{ $main ? 'Créer un nouveau texte et l\'ajouter' : 'Créer le contenu principal' }}
                    </button>
                    <span class="text-xs text-gray-500">Un texte nouveau est un texte « premier » ; pour une variante d'un texte existant, rattachez-la ensuite dans le formulaire des variantes.</span>
                @else
                    <div class="font-semibold mb-1">{{ $main ? 'Nouveau texte' : 'Nouveau contenu principal' }}</div>

                    @if($duplicates)
                        <div class="p-2 mb-2 border border-red-300 bg-red-100 text-red-700">
                            Textes existants de même titre et de mêmes auteurs :
                            @foreach ($duplicateCards as $c)
                                <div class="border rounded p-1 my-1 bg-white text-black" wire:key="dup-{{ $c['id'] }}">
                                    <span class="font-semibold">{{ $c['name'] }}</span> <span class="text-sm">de {{ $c['authors'] }}</span>
                                    <span class="text-xs text-gray-500">(#{{ $c['id'] }}, {{ $c['type'] }})</span>
                                    <a href="/textes/{{ $c['slug'] }}" target="_blank" class="text-red-700 text-sm">voir</a>
                                    <span class="text-sm">1ère parution fr : <span class="font-mono">{{ $c['copyright_fr'] ?: '?' }}</span>
                                        @if ($c['title_vo']) · VO : {{ $c['title_vo'] }} @endif
                                        @if ($c['translators']) · trad. : {{ $c['translators'] }} @endif</span>
                                    <button wire:click="{{ $main ? 'attach' : 'attachMain' }}({{ $c['id'] }})" class="bg-green-100 text-green-800 border border-green-600 px-2 rounded">C'est le même : rattacher</button>
                                </div>
                            @endforeach
                            <button wire:click="createAnyway" class="bg-gray-500 text-white px-2 rounded">Aucun n'est identique : créer quand même</button>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <label for="n_name" class="w-32 font-semibold">Titre *</label>
                        @if($main)
                            <input id="n_name" wire:model="new.name" maxlength="256" class="w-full border rounded bg-yellow-100 max-w-xl">
                        @else
                            <span>{{ $publication->name }}</span> <span class="text-xs text-gray-500">(titre de la publication)</span>
                        @endif
                        <label for="n_type" class="font-semibold ml-2">Type *</label>
                        <select id="n_type" wire:model="new.type" class="w-48 border rounded bg-yellow-100">
                            @if($main) <option value="">— choisir —</option> @endif
                            @foreach ($newTypes as $t)
                                <option value="{{ $t->value }}">{{ $t->getLabel() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-wrap items-start gap-2 mb-1">
                        <span class="w-32 font-semibold">Auteur(s)</span>
                        <div class="flex-1">
                            @if($main)
                                @forelse ($newAuthors as $id => $label)
                                    <span class="inline-block bg-yellow-100 border rounded px-2 py-0.5 mr-1" wire:key="na-{{ $id }}">
                                        {{ $label }} <button type="button" wire:click="removeAuthor({{ $id }})" class="text-red-600 text-sm">×</button>
                                    </span>
                                @empty
                                    <span class="text-sm italic">Aucun auteur (section, texte anonyme...)</span>
                                @endforelse
                                <button type="button" wire:click="useBookAuthors" class="text-sm text-blue-700 underline">reprendre ceux de la publication</button>
                                <div class="mt-1">
                                    <input type="text" wire:model.live.debounce.400ms="authorSearch" class="border rounded bg-yellow-100 w-64" placeholder="Chercher un auteur existant...">
                                    @foreach ($authorResults as $a)
                                        <button type="button" wire:click="addAuthor({{ $a['id'] }})" class="text-sm bg-gray-200 rounded px-2 mx-0.5" wire:key="ar-{{ $a['id'] }}">{{ $a['label'] }}</button>
                                    @endforeach
                                </div>
                                <div class="mt-1 text-sm">
                                    Auteur absent de la base :
                                    <input wire:model="newAuthorFirst" maxlength="32" placeholder="Prénom" class="w-32 border rounded bg-yellow-100">
                                    <input wire:model="newAuthorLast" maxlength="32" placeholder="Nom" class="w-36 border rounded bg-yellow-100">
                                    <button type="button" wire:click="createAuthor" class="bg-gray-500 text-white px-2 rounded">Créer l'auteur</button>
                                </div>
                            @else
                                @foreach ($newAuthors as $id => $label)
                                    <span class="inline-block bg-yellow-100 border rounded px-2 py-0.5 mr-1">{{ $label }}</span>
                                @endforeach
                                <span class="text-xs text-gray-500">(auteurs de la publication)</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <label for="n_copyright" class="w-32 font-semibold">Copyright</label>
                        <input id="n_copyright" wire:model="new.copyright" maxlength="10" class="w-32 border rounded bg-yellow-100 font-mono" placeholder="AAAA-MM-JJ">
                        <label for="n_copyright_fr" class="font-semibold ml-2">1ère parution fr</label>
                        <input id="n_copyright_fr" wire:model="new.copyright_fr" maxlength="10" class="w-32 border rounded bg-yellow-100 font-mono" placeholder="AAAA-MM-JJ">
                        <span class="text-xs text-gray-500">AAAA-MM-JJ (00 si inconnu), AAAA-Tn-00 ; copyright : 1ère publication en langue d'origine</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <label for="n_title_vo" class="w-32 font-semibold">Titre original</label>
                        <input id="n_title_vo" wire:model="new.title_vo" maxlength="512" class="w-full border rounded bg-yellow-100 max-w-xl" placeholder="Si traduit">
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <label for="n_pub_vo" class="w-32 font-semibold">Publi. d'origine</label>
                        <input id="n_pub_vo" wire:model="new.pub_vo" maxlength="256" class="w-full border rounded bg-yellow-100 max-w-xl" placeholder="Revue ou recueil de première publication">
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <label for="n_translators" class="w-32 font-semibold">Traducteur(s)</label>
                        <input id="n_translators" wire:model="new.translators" maxlength="512" class="w-full border rounded bg-yellow-100 max-w-xl" placeholder="Nom Prénom, Nom Prénom">
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <label for="n_is_genre" class="w-32 font-semibold">Genres réf. *</label>
                        <select id="n_is_genre" wire:model="new.is_genre" class="w-40 border rounded bg-yellow-100">
                            @foreach (App\Enums\GenreAppartenance::cases() as $c) <option value="{{ $c->value }}">{{ $c->getLabel() }}</option> @endforeach
                        </select>
                        <label for="n_genre_stat" class="font-semibold ml-2">Genre *</label>
                        <select id="n_genre_stat" wire:model="new.genre_stat" class="w-40 border rounded bg-yellow-100">
                            @foreach (App\Enums\GenreStat::cases() as $c) <option value="{{ $c->value }}">{{ $c->getLabel() }}</option> @endforeach
                        </select>
                        <label for="n_audience" class="font-semibold ml-2">Public *</label>
                        <select id="n_audience" wire:model="new.target_audience" class="w-40 border rounded bg-yellow-100">
                            @foreach (App\Enums\AudienceTarget::cases() as $c) <option value="{{ $c->value }}">{{ $c->getLabel() }}</option> @endforeach
                        </select>
                        <label for="n_age" class="font-semibold ml-2">Age</label>
                        <input id="n_age" wire:model="new.target_age" maxlength="10" class="w-20 border rounded bg-yellow-100 font-mono" placeholder="14+">
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <label for="n_novel" class="w-32 font-semibold">Novellisation</label>
                        <select id="n_novel" wire:model="new.is_novelization" class="w-56 border rounded bg-yellow-100">
                            @foreach ($novelizations as $n) <option value="{{ $n->value }}">{{ $n->getLabel() }}</option> @endforeach
                        </select>
                        <label class="ml-2"><input type="checkbox" wire:model="new.is_visible"> visible</label>
                        <label class="ml-2"><input type="checkbox" wire:model="new.is_serial"> paru en feuilleton</label>
                        <label class="ml-2"><input type="checkbox" wire:model="new.is_fullserial"> feuilleton complet</label>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <label for="n_serial_info" class="w-32 font-semibold">Infos feuilleton</label>
                        <input id="n_serial_info" wire:model="new.serial_info" maxlength="512" class="w-full border rounded bg-yellow-100 max-w-xl">
                    </div>
                    <label for="n_synopsis">Résumé (synopsis) :</label>
                    <textarea id="n_synopsis" wire:model="new.synopsis" rows="2" class="border p-2 w-full mb-1"></textarea>
                    <label for="n_information">Informations notables à afficher :</label>
                    <textarea id="n_information" wire:model="new.information" rows="2" class="border p-2 w-full mb-1"></textarea>
                    <label for="n_private">Infos privées (non affichées) :</label>
                    <textarea id="n_private" wire:model="new.private" rows="2" class="border p-2 w-full mb-1"></textarea>

                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="w-32 font-semibold">Dans le sommaire</span>
                        @if($main)
                            <label for="n_level">Niveau</label>
                            <select id="n_level" wire:model="new.level" class="w-20 border rounded bg-yellow-100">
                                @foreach ($levels as $l) @if ($l !== '0') <option value="{{ $l }}">{{ $l }}</option> @endif @endforeach
                            </select>
                        @else
                            <span class="text-sm">contenu principal : niveau 0, en premier</span>
                        @endif
                        <label for="n_sp" class="ml-2">Pages</label>
                        <input id="n_sp" wire:model="new.start_page" maxlength="8" class="w-14 border rounded bg-yellow-100 font-mono px-1" aria-label="Page de début">
                        <span>-</span>
                        <input wire:model="new.end_page" maxlength="8" class="w-14 border rounded bg-yellow-100 font-mono px-1" aria-label="Page de fin">
                    </div>

                    <div class="flex gap-2 mt-2">
                        <button wire:click="createText" @disabled(! auth()->user()?->hasMemberRole()) class="bg-blue-400 text-white px-3 py-1 rounded disabled:opacity-50">Créer et ajouter au sommaire</button>
                        <button wire:click="closeNew" class="bg-gray-500 text-white px-3 py-1 rounded">Annuler</button>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
