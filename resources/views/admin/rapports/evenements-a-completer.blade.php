<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <a href="/admin">Administration BDFI</a> &rarr;
            <a href="/admin/rapports">Rapports</a> &rarr;
            Évènements à ajouter ou à clore
        </h2>
    </x-slot>

    <x-admin.discovery-notice :allowed="auth()->user()->hasMemberRole()" />

    <div class="pb-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">
                <div class="border border-yellow-800 bg-yellow-100 rounded-lg p-2">
                    Évènements toujours indiqués actifs (sans année de fin) mais dont la dernière occurrence est terminée depuis plus de
                    {{ $defaultDelay }} mois (11 mois pour un biennal. Les évènements de périodicité inconnue ou irrégulière sont traitée comme annuels - {{ $rows->count() }} évènement(s).<br />
                    « Ajouter » ouvre le formulaire d'ajout d'occurrence sur l'évènement. « Clore » renseigne l'année de fin
                    de l'évènement avec celle de sa dernière occurrence : il disparaît alors de ce rapport. Si vous devez clore l'évènement à une autre date, ajouter une occurence, indiquer la date de dernière occurence, et la date de fin.
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4">
                @if (session('status'))
                    <div class="p-2 mb-2 border border-green-800 bg-green-100 rounded">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="p-2 mb-2 border border-red-800 bg-red-100 rounded">{{ session('error') }}</div>
                @endif

                @if ($rows->isEmpty())
                    <div class="p-2 bg-green-100 border border-green-800 rounded">Aucun évènement à compléter.</div>
                @else
                    <table class="table-auto border-collapse border border-blue-800 w-full text-sm">
                        <thead>
                            <tr class="bg-blue-50">
                                <th class="p-1">Évènement</th>
                                <th class="p-1">Fin de la dernière occurrence</th>
                                <th class="p-1">Depuis</th>
                                <th class="p-1">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                @php($e = $row->event)
                                <tr class="border border-blue-500">
                                    <td class="py-1 px-2 border border-blue-500">
                                        @if (auth()->user()->hasGuestRole())
                                        <a href="/filament/events/{{ $e->id }}/edit" class="font-semibold text-blue-800 hover:underline">{{ $e->name }}</a>
                                        @else
                                        <span class="font-semibold text-blue-800">{{ $e->name }}</span>
                                        @endif
                                        <span class="text-gray-600">— {{ collect([$e->type?->getLabel(), $e->periodicity?->getLabel(), $e->place])->filter()->implode(', ') }}</span>
                                        <span class="text-xs text-gray-500">#{{ $e->id }}</span>
                                    </td>
                                    <td class="py-1 px-2 border border-blue-500" style="white-space: nowrap">
                                        @if ($row->last)
                                            {{ $row->last->end_date->format('d/m/Y') }}
                                        @else
                                            <span class="italic">aucune</span>
                                        @endif
                                    </td>
                                    <td class="py-1 px-2 border border-blue-500" style="white-space: nowrap">
                                        @if ($row->last)
                                            {{ (int) $row->last->end_date->diffInMonths(now()) }} mois
                                        @endif
                                    </td>
                                    <td class="py-1 px-2 border border-blue-500" style="white-space: nowrap">
                                        @if ($e->periodicity !== App\Enums\EventPeriodicity::UNIQUE)
                                            <x-admin.link lien='/admin/formulaires/ajout-evenement?evenement={{ $e->id }}'>ajouter</x-admin.link>
                                        @endif
                                        @if ($row->last)
                                            <form method="POST" action="/admin/rapports/evenements-a-completer/clore" class="inline"
                                                  onsubmit="return confirm({{ Js::from('Déclarer « ' . $e->name . ' » clos en ' . $row->last->start_date->format('Y') . ' ?') }})">
                                                @csrf
                                                <input type="hidden" name="event" value="{{ $e->id }}" />
                                                <button type="submit" @disabled(!auth()->user()->hasMemberRole())
                                                        class="ml-2 bg-red-100 border border-red-800 rounded px-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                                    clore en {{ $row->last->start_date->format('Y') }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
