<div>
    @if($notice !== '')
        <div class="p-2 mb-2 text-green-800 bg-green-100 border border-green-500">{{ $notice }}</div>
    @endif
    @if($error !== '')
        <div class="p-2 mb-2 text-red-700 bg-red-100 border border-red-300">{{ $error }}</div>
    @endif

    <div class="flex items-center gap-2 mb-2">
        <label for="filter" class="font-semibold">Afficher :</label>
        <select id="filter" wire:model.live="filter" class="border rounded bg-yellow-100">
            @foreach ($statuses as $s)
                <option value="{{ $s->value }}">{{ $s->getLabel() }}</option>
            @endforeach
            <option value="all">Toutes</option>
        </select>
    </div>

    <table class="table-auto border-collapse border border-blue-800 w-full">
        <thead>
            <tr class="bg-blue-50">
                <th class="p-1">Envoyée le</th>
                <th class="p-1">Proposant</th>
                <th class="p-1">Ouvrage</th>
                <th class="p-1">Parution</th>
                <th class="p-1">ISBN</th>
                <th class="p-1">État</th>
                <th class="p-1"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($proposals as $p)
                <tr class="border border-blue-500 {{ $selected && $selected->id === $p->id ? 'bg-blue-100' : '' }}" wire:key="pr-{{ $p->id }}">
                    <td class="py-1 px-2 border border-blue-500">{{ $p->created_at?->format('Y-m-d H:i') }}</td>
                    <td class="py-1 px-2 border border-blue-500">{{ $p->user?->name }}</td>
                    <td class="py-1 px-2 border border-blue-500">{{ $p->title }} <span class="text-sm italic text-red-700">{{ $p->publisher_name }}</span></td>
                    <td class="py-1 px-2 border border-blue-500 font-mono">{{ $p->approximate_parution }}</td>
                    <td class="py-1 px-2 border border-blue-500 font-mono">{{ $p->isbn }}</td>
                    <td class="py-1 px-2 border border-blue-500">{{ $p->status->getLabel() }}</td>
                    <td class="py-1 px-2 border border-blue-500">
                        <button wire:click="select({{ $p->id }})" class="bg-blue-400 text-white px-2 rounded">Étudier</button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-2 text-center italic">Aucune proposition dans cette liste.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($selected)
        @php($book = $selected->payload)
        <div class="mt-4 p-4 border border-blue-800 rounded">
            <div class="flex justify-between items-start">
                <h3 class="font-bold text-lg">{{ $book['titre'] }} <span class="text-xs text-gray-500">proposition #{{ $selected->id }} de {{ $selected->user?->name }}</span></h3>
                <button wire:click="close" class="bg-gray-500 text-white px-2 rounded">Fermer</button>
            </div>

            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 text-sm my-2">
                <div><span class="font-semibold">Éditeur :</span> {{ $book['editeur'] ?? '?' }}@isset($book['publisher_id']) <span class="text-gray-500">(#{{ $book['publisher_id'] }})</span>@endisset</div>
                <div><span class="font-semibold">Collection :</span> {{ $book['collection'] ?? '—' }}@if(!empty($book['no_collection'])) n° {{ $book['no_collection'] }}@endif</div>
                <div><span class="font-semibold">Auteurs :</span>
                    @forelse ($book['auteurs'] ?? [] as $a)
                        {{ trim(($a['auteur_prenom'] ?? '') . ' ' . $a['auteur_nom']) }}@isset($a['author_id']) <span class="text-gray-500">(#{{ $a['author_id'] }})</span>@else <span class="text-red-700">(non référencé)</span>@endisset{{ $loop->last ? '' : ',' }}
                    @empty — @endforelse
                </div>
                <div><span class="font-semibold">ISBN :</span> {{ $book['isbn'] ?? '?' }}</div>
                <div><span class="font-semibold">Parution :</span> {{ $book['approximate_parution'] ?? '?' }}</div>
                <div><span class="font-semibold">Type / support / format :</span> {{ \App\Bdfi\Proposal\PublicationProposalService::contentLabel($book['type_ouvrage'] ?? null) }} / {{ $book['support'] ?? '' }} / {{ $book['format'] ?? '' }}</div>
                <div><span class="font-semibold">Genre / public :</span> {{ $book['genre'] ?? '' }} / {{ $book['audience'] ?? '' }} (genres réf. : {{ $book['est_genre'] ?? '' }})</div>
                <div><span class="font-semibold">Physique :</span>
                    {{ !empty($book['reliure']) ? 'relié' : '' }} {{ !empty($book['jaquette']) ? 'jaquette' : '' }} {{ !empty($book['rabat']) ? 'rabats' : '' }}
                </div>
            </dl>
            @if(!empty($book['private']))
                <div class="text-sm bg-yellow-50 border rounded p-2 mb-2"><span class="font-semibold">Notes du proposant :</span> {!! nl2br(e($book['private'])) !!}</div>
            @endif

            @if($report)
                <div class="text-sm bg-blue-50 border rounded p-2 mb-2">
                    <div class="font-semibold">Analyse de l'import (aucune écriture) :</div>
                    @if($report->publications === 1)
                        <div class="text-green-800">L'ouvrage peut être importé.</div>
                    @else
                        <div class="text-red-700">L'ouvrage ne peut pas être importé en l'état :
                            {{ $report->skipped[1] ?? 'raison inconnue' }}
                        </div>
                        @if($report->missingPublishers)
                            <div>Créer d'abord l'éditeur « {{ array_key_first($report->missingPublishers) }} » :
                                <x-admin.link-ext lien='/admin/formulaires/ajout-editeur'>formulaire d'ajout d'éditeur</x-admin.link-ext></div>
                        @endif
                        @if($report->missingCollections)
                            <div>Créer d'abord la collection « {{ array_key_first($report->missingCollections) }} » :
                                <x-admin.link-ext lien='/admin/formulaires/ajout-collection'>formulaire d'ajout de collection</x-admin.link-ext></div>
                        @endif
                    @endif
                    @if($report->newAuthors)
                        <div>Auteur(s) qui seront créés : {{ implode(', ', $report->newAuthors) }}</div>
                    @endif
                    @if($report->newTitles)
                        <div>Texte(s) qui seront créés : {{ implode(', ', array_column($report->newTitles, 'title')) }}</div>
                    @endif
                    @if($report->newCycles)
                        <div>Série(s) qui seront créées : {{ implode(', ', $report->newCycles) }}</div>
                    @endif
                </div>
            @endif

            @if($selected->isPending())
                <div class="flex flex-wrap items-start gap-4">
                    <div>
                        <button wire:click="accept" wire:confirm="Accepter cette proposition et créer l'ouvrage ?" @disabled(! auth()->user()?->hasMemberRole() || ! $report || $report->publications !== 1)
                                class="bg-green-100 text-green-800 border border-green-600 px-3 py-1 rounded font-semibold disabled:opacity-50 disabled:cursor-not-allowed">Accepter</button>
                    </div>
                    <div class="flex-1 min-w-64">
                        <textarea wire:model="rejectNote" rows="2" class="border p-2 w-full bg-yellow-100" placeholder="Motif du refus (visible du proposant)"></textarea>
                        <button wire:click="reject" @disabled(! auth()->user()?->hasMemberRole()) class="bg-gray-500 text-white px-3 py-1 rounded disabled:opacity-50 disabled:cursor-not-allowed">Refuser</button>
                    </div>
                </div>
            @else
                <div class="text-sm">
                    {{ $selected->status->getLabel() }}
                    @if($selected->reviewer) par {{ $selected->reviewer->name }} le {{ $selected->reviewed_at?->format('Y-m-d H:i') }} @endif
                    @if($selected->review_note) — {{ $selected->review_note }} @endif
                    @if($selected->result_id)
                        — <x-admin.link-ext lien='/admin/formulaires/modifier-publication?id={{ $selected->result_id }}'>Formulaire</x-admin.link-ext>
                        <x-admin.link-ext lien='/filament/publications/{{ $selected->result_id }}'>Table</x-admin.link-ext>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>
