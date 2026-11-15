<div>
    @php
        $describe = function ($t) {
            $authors = $t->authors->map(fn ($a) => $a->firstNameFirst)->implode(', ');
            $parts = array_filter([
                $authors !== '' ? $authors : 'auteur ?',
                filled($t->translators) ? 'trad. ' . $t->translators : null,
                filled($t->copyright_fr) ? 'fr ' . $t->copyright_fr : null,
                filled($t->copyright) ? 'VO ' . $t->copyright : null,
                $t->type?->getLabel(),
            ]);
            return implode(' — ', $parts);
        };
    @endphp

    @if ($error !== '')
        <div class="p-2 mb-2 border border-red-800 bg-red-100 rounded">{{ $error }}</div>
    @endif
    @if ($notice !== '')
        <div class="p-2 mb-2 border border-green-800 bg-green-100 rounded">{{ $notice }}</div>
    @endif

    @if (!$rootId)
        {{-- Étape 1 : choix d'un texte quelconque de l'ensemble --}}
        <input type="text" wire:model.live.debounce.400ms="search" class="border p-2 w-full mb-2 bg-yellow-100"
               placeholder="Extrait du titre d'un texte (ou son n° de fiche)…" />

        @if ($results->isNotEmpty())
            <div class="border rounded">
                @foreach ($results as $t)
                    <div wire:key="res-{{ $t->id }}" wire:click="select({{ $t->id }})" class="p-1 cursor-pointer hover:bg-gray-200">
                        <span class="font-semibold">{{ $t->name }}</span>
                        <span class="text-sm">— {{ $describe($t) }}</span>
                        <span class="text-xs text-gray-500">#{{ $t->id }}@if ($t->parent_id) · variante ({{ $t->variant_type }})@endif</span>
                    </div>
                @endforeach
            </div>
            @if ($results->count() >= 30)
                <div class="text-xs text-gray-500">30 premiers résultats : précisez la recherche si besoin.</div>
            @endif
        @elseif (mb_strlen(trim($search)) >= 2)
            <p>Aucun texte trouvé.</p>
        @endif
    @else
        {{-- Étape 2 : l'ensemble (parent + variantes) --}}
        @if ($anomalies)
            <div class="p-2 mb-2 border border-yellow-600 bg-yellow-100 rounded">
                <div class="font-semibold">Ensemble à remettre à plat :</div>
                <ul class="list-disc pl-6 text-sm">
                    @foreach ($anomalies as $a)
                        <li>{{ $a }}</li>
                    @endforeach
                </ul>
                <button wire:click="normalize" @disabled(!$canEdit)
                        wire:confirm="Rattacher toutes les variantes directement au parent affiché en tête ?"
                        class="mt-2 bg-blue-400 font-semibold border border-blue-800 rounded px-4 py-1 disabled:opacity-50 disabled:cursor-not-allowed">
                    Remettre à plat
                </button>
                <span class="text-xs text-gray-500">Le parent reste celui affiché en tête ; vous pourrez ensuite en désigner un autre.</span>
            </div>
        @endif

        @if ($earlier)
            <div class="p-2 mb-2 border border-red-800 bg-red-100 rounded">
                Erreur possible : {{ count($earlier) }} variante(s) ont une date fr antérieure à celle du parent.
                Le parent devrait être la première publication : vérifiez les dates, ou désignez la bonne variante avec « C'est lui le premier ».
            </div>
        @endif

        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="py-1 px-2 border border-blue-500">Rôle</th>
                    <th class="py-1 px-2 border border-blue-500">Texte</th>
                    <th class="py-1 px-2 border border-blue-500">Type</th>
                    <th class="py-1 px-2 border border-blue-500">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($group as $t)
                    @php
                        $isRoot = $t->id === $rootId;
                        $depth = $depths[$t->id] ?? 1;
                        $types = $isRoot ? $parentTypes : $childTypes;
                        $known = collect($types)->contains(fn ($c) => $c->value === $t->variant_type);
                    @endphp
                    <tr wire:key="row-{{ $t->id }}" class="{{ $isRoot ? 'bg-blue-100' : '' }}">
                        <td class="py-1 px-2 border border-blue-500" style="white-space: nowrap">
                            @if ($isRoot)
                                <span class="font-semibold">Parent</span>
                            @elseif ($depth > 1)
                                <span class="text-red-700">Variante de #{{ $t->parent_id }}</span>
                            @else
                                Variante
                            @endif
                            @if ($t->id === $selectedId)
                                <span class="text-xs text-gray-500">(choisi)</span>
                            @endif
                        </td>
                        <td class="py-1 px-2 border border-blue-500">
                            <span class="font-semibold">{{ $t->name }}</span>
                            <span class="text-xs text-gray-500">#{{ $t->id }}</span><br />
                            {{ $describe($t) }}
                            @if (in_array($t->id, $earlier, true))
                                <div class="text-red-700 font-semibold">⚠ Date fr antérieure à celle du parent : est-ce lui le premier ?</div>
                            @endif
                            <span class="text-xs">
                                · <a class="text-red-700" href="/textes/{{ $t->slug }}" target="_blank">page</a>
                                · <a class="text-red-700" href="/filament/titles/{{ $t->id }}" target="_blank">fiche</a>
                            </span>
                        </td>
                        <td class="py-1 px-2 border border-blue-500">
                            <select wire:change="setType({{ $t->id }}, $event.target.value)" @disabled(!$canEdit)
                                    class="border rounded bg-yellow-100 text-sm">
                                @unless ($known)
                                    <option value="" selected>⚠ {{ $t->variant_type }}</option>
                                @endunless
                                @foreach ($types as $c)
                                    <option value="{{ $c->value }}" @selected($t->variant_type === $c->value)>{{ $c->getLabel() }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td class="py-1 px-2 border border-blue-500" style="white-space: nowrap">
                            @unless ($isRoot)
                                @unless (in_array($t->variant_type, $partTypes, true))
                                    <button wire:click="promote({{ $t->id }})" @disabled(!$canEdit)
                                            wire:confirm="Faire de « {{ $t->name }} » (#{{ $t->id }}) le parent de tout l'ensemble ?"
                                            class="bg-blue-400 border border-blue-800 rounded px-2 py-1 disabled:opacity-50 disabled:cursor-not-allowed">
                                        C'est lui le premier
                                    </button>
                                @endunless
                                <button wire:click="detach({{ $t->id }})" @disabled(!$canEdit)
                                        wire:confirm="Retirer « {{ $t->name }} » (#{{ $t->id }}) de l'ensemble ? Il redeviendra un texte indépendant."
                                        class="bg-gray-300 border border-blue-800 rounded px-2 py-1 disabled:opacity-50 disabled:cursor-not-allowed">
                                    Retirer
                                </button>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if ($group->count() === 1)
            <div class="text-sm text-gray-500 mt-1">Ce texte n'a aucune variante pour l'instant.</div>
        @endif

        {{-- Ajout d'une variante --}}
        <div class="mt-4 p-2 border rounded">
            <div class="font-semibold mb-1">Ajouter une variante à cet ensemble</div>
            <div class="flex flex-wrap items-center gap-2">
                <label for="add_type" class="text-sm">Type</label>
                <select id="add_type" wire:model.live="addType" class="border rounded bg-yellow-100 text-sm">
                    @foreach ($childTypes as $c)
                        <option value="{{ $c->value }}">{{ $c->getLabel() }}</option>
                    @endforeach
                </select>
                <input type="text" wire:model.live.debounce.400ms="addSearch" class="border p-1 w-full max-w-xl bg-yellow-100"
                       placeholder="Extrait du titre du texte à rattacher (ou son n° de fiche)…" />
            </div>
            <div class="text-xs text-gray-500 mt-1">
                {{ \App\Enums\TitleVariantType::tryFrom($addType)?->getDescription() }}.
                Choisissez le type, puis cliquez sur le texte à rattacher.
            </div>

            @if ($candidates->isNotEmpty())
                <div class="border rounded mt-1">
                    @foreach ($candidates as $t)
                        <div wire:key="cand-{{ $t->id }}" class="p-1 flex flex-wrap items-center gap-2 hover:bg-gray-200">
                            <button wire:click="addVariant({{ $t->id }})" @disabled(!$canEdit)
                                    class="bg-blue-400 border border-blue-800 rounded px-2 disabled:opacity-50 disabled:cursor-not-allowed">Rattacher</button>
                            <span>
                                <span class="font-semibold">{{ $t->name }}</span>
                                <span class="text-sm">— {{ $describe($t) }}</span>
                                <span class="text-xs text-gray-500">#{{ $t->id }}@if ($t->parent_id) · déjà variante ({{ $t->variant_type }})@endif</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            @elseif (mb_strlen(trim($addSearch)) >= 2)
                <p class="text-sm">Aucun autre texte trouvé.</p>
            @endif
        </div>

        <div class="mt-4">
            <button wire:click="close" class="bg-gray-300 border border-blue-800 rounded px-4 py-2">Choisir un autre texte</button>
        </div>
    @endif
</div>
