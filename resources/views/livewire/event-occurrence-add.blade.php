<div>
    @php
        $years = fn ($e) => $e->year_start ? ($e->year_start . '–' . ($e->year_end ?? '')) : '';
        $input = 'border p-1 w-full';
    @endphp

    @if ($error !== '')
        <div class="p-2 mb-2 border border-red-800 bg-red-100 rounded">{{ $error }}</div>
    @endif
    @if ($notice !== '')
        <div class="p-2 mb-2 border border-green-800 bg-green-100 rounded">{{ $notice }}</div>
    @endif

    @if ($mode === null)
        {{-- Étape 1 : recherche de l'évènement --}}
        <input type="text" wire:model.live.debounce.400ms="search" class="border p-2 w-full mb-2 bg-yellow-100"
               placeholder="Extrait du nom de l'évènement…" autofocus />

        @if (mb_strlen(trim($search)) >= 2)
            @if ($results->isNotEmpty())
                <div class="border rounded mb-2">
                    @foreach ($results as $e)
                        <div wire:key="ev-{{ $e->id }}" wire:click="selectEvent({{ $e->id }})" class="p-1 cursor-pointer hover:bg-gray-200">
                            <span class="font-semibold">{{ $e->name }}</span>
                            <span class="text-sm">
                                — {{ $e->type?->getLabel() }}@if ($e->place), {{ $e->place }}@endif
                                @if ($years($e)) ({{ $years($e) }})@endif
                            </span>
                            <span class="text-xs text-gray-500">
                                {{ $e->occurrences_count }} occurrence(s)@if ($e->occurrences_max_start_date), dernière en {{ substr($e->occurrences_max_start_date, 0, 4) }}@endif · #{{ $e->id }}
                            </span>
                        </div>
                    @endforeach
                </div>
                @if ($results->count() >= 30)
                    <div class="text-xs text-gray-500 mb-2">30 premiers résultats : précisez la recherche si besoin.</div>
                @endif
            @else
                <p class="mb-2">Aucun évènement trouvé.</p>
            @endif
            <button wire:click="createNew" class="bg-green-200 font-semibold border border-green-800 rounded px-4 py-1">
                Créer ce nouvel évènement
            </button>
            <span class="text-sm text-gray-600">… si l'évènement cherché n'est pas dans la liste ci-dessus.</span>
        @endif
    @else
        <form wire:submit="save">
            @if ($mode === 'new')
                {{-- Nouvel évènement --}}
                <div class="text-lg font-semibold border-b border-blue-800 mb-2">Nouvel évènement</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-4">
                    <label class="md:col-span-2">Nom *
                        <input type="text" wire:model.live.blur="name" maxlength="128" class="{{ $input }}" />
                        @error('name') <span class="text-red-700 text-sm">{{ $message }}</span> @enderror
                    </label>
                    <label>Type *
                        <select wire:model="type" class="{{ $input }}">
                            <option value="">—</option>
                            @foreach ($types as $t)
                                <option value="{{ $t->value }}">{{ $t->getLabel() }}</option>
                            @endforeach
                        </select>
                        @error('type') <span class="text-red-700 text-sm">{{ $message }}</span> @enderror
                    </label>
                    <label>Périodicité
                        <select wire:model="periodicity" class="{{ $input }}">
                            <option value="">—</option>
                            @foreach ($periodicities as $p)
                                <option value="{{ $p->value }}">{{ $p->getLabel() }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Lieu habituel <span class="text-xs text-gray-500">(si toujours le même ; peut être précisé dans chaque occurrence)</span>
                        <input type="text" wire:model="eventPlace" maxlength="64" class="{{ $input }}" />
                        @error('eventPlace') <span class="text-red-700 text-sm">{{ $message }}</span> @enderror
                    </label>
                    <label>Site web (url)
                        <input type="text" wire:model="url" maxlength="256" class="{{ $input }}" />
                    </label>
                    <label>Année de création <span class="text-xs text-gray-500">(par défaut, celle de l'occurrence saisie)</span>
                        <input type="text" wire:model="yearStart" maxlength="4" class="{{ $input }}" />
                        @error('yearStart') <span class="text-red-700 text-sm">{{ $message }}</span> @enderror
                    </label>
                    <label>Année de fin <span class="text-xs text-gray-500">(vide si toujours actif)</span>
                        <input type="text" wire:model="yearEnd" maxlength="4" class="{{ $input }}" />
                        @error('yearEnd') <span class="text-red-700 text-sm">{{ $message }}</span> @enderror
                    </label>
                    <label class="md:col-span-2 flex items-center gap-2">
                        <input type="checkbox" wire:model="isFullScope" /> Imaginaire ET littérature
                    </label>
                    <label class="md:col-span-2">Description générale <span class="text-xs text-gray-500">(les informations propres à une édition vont dans l'occurrence)</span>
                        <textarea wire:model="eventInformation" rows="3" class="{{ $input }}"></textarea>
                    </label>
                </div>
            @else
                {{-- Évènement existant --}}
                <div class="text-lg font-semibold border-b border-blue-800 mb-2">
                    {{ $event->name }}
                    <span class="text-sm font-normal">
                        — {{ collect([$event->type?->getLabel(), $event->periodicity?->getLabel(), $event->place])->filter()->implode(', ') }}
                        @if ($years($event)) ({{ $years($event) }})@endif
                    </span>
                    <a href="/filament/events/{{ $event->id }}/edit" target="_blank" class="text-sm font-normal text-blue-800 underline">fiche</a>
                </div>
                @if ($event->occurrences->isEmpty())
                    <p class="mb-4 text-sm">Aucune occurrence enregistrée pour cet évènement.</p>
                @else
                    <table class="text-sm mb-4">
                        <thead>
                            <tr class="bg-gray-200 text-left">
                                <th class="py-1 px-2 border border-blue-500">Dates</th>
                                <th class="py-1 px-2 border border-blue-500">Libellé</th>
                                <th class="py-1 px-2 border border-blue-500">Lieu</th>
                                <th class="py-1 px-2 border border-blue-500">Confirmée</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($event->occurrences as $o)
                                <tr wire:key="occ-{{ $o->id }}">
                                    <td class="py-1 px-2 border border-blue-500">
                                        {{ $o->start_date->format('d/m/Y') }}@if (!$o->end_date->eq($o->start_date)) au {{ $o->end_date->format('d/m/Y') }}@endif
                                    </td>
                                    <td class="py-1 px-2 border border-blue-500">{{ $o->label }}</td>
                                    <td class="py-1 px-2 border border-blue-500">{{ $o->place }}</td>
                                    <td class="py-1 px-2 border border-blue-500">{{ $o->is_confirmed ? 'oui' : 'non' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @endif

            {{-- Occurrence --}}
            <div class="text-lg font-semibold border-b border-blue-800 mb-2">
                {{ $mode === 'new' ? 'Première occurrence' : 'Nouvelle occurrence' }}
                @if ($mode === 'existing' && $event->occurrences->isNotEmpty())
                    <span class="text-sm font-normal text-gray-600">(pré-remplie d'après la dernière : mêmes jours de la semaine, à vérifier)</span>
                @endif
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-2">
                <label>Date de début *
                    <input type="date" wire:model.live.blur="startDate" class="{{ $input }}" />
                    @error('startDate') <span class="text-red-700 text-sm">{{ $message }}</span> @enderror
                </label>
                <label>Date de fin *
                    <input type="date" wire:model.live.blur="endDate" class="{{ $input }}" />
                    @error('endDate') <span class="text-red-700 text-sm">{{ $message }}</span> @enderror
                </label>
                <label>Libellé <span class="text-xs text-gray-500">(facultatif : « 24e édition »… ; à défaut, nom + année)</span>
                    <input type="text" wire:model="label" maxlength="128" class="{{ $input }}" />
                    @error('label') <span class="text-red-700 text-sm">{{ $message }}</span> @enderror
                </label>
                <label>Lieu <span class="text-xs text-gray-500">(si différent du lieu habituel, ou pour le préciser)</span>
                    <input type="text" wire:model="place" maxlength="128" class="{{ $input }}" />
                    @error('place') <span class="text-red-700 text-sm">{{ $message }}</span> @enderror
                </label>
                <label class="md:col-span-2 flex items-center gap-2">
                    <input type="checkbox" wire:model="isConfirmed" /> Dates confirmées
                </label>
                <label class="md:col-span-2">Informations sur cette occurrence
                    <textarea wire:model="information" rows="3" class="{{ $input }}"></textarea>
                </label>
            </div>

            @if ($warnings)
                <div class="p-2 mb-2 border border-yellow-600 bg-yellow-100 rounded text-sm">
                    @foreach ($warnings as $w)
                        <div>⚠ {{ $w }}</div>
                    @endforeach
                    <div class="text-gray-600">Simple avertissement : l'enregistrement reste possible.</div>
                </div>
            @endif

            <button type="submit" @disabled(!$canEdit)
                    class="bg-blue-400 font-semibold border border-blue-800 rounded px-4 py-1 disabled:opacity-50 disabled:cursor-not-allowed">
                Enregistrer
            </button>
            <button type="button" wire:click="back" class="bg-gray-200 border border-gray-600 rounded px-4 py-1">
                Retour à la recherche
            </button>
        </form>
    @endif
</div>
