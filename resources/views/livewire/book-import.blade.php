<div class="p-2">
    {{-- Environnement visé (information seulement) --}}
    <div class="mb-2 p-2 rounded {{ $this->environment['env'] === 'production' ? 'bg-red-100 border border-red-700' : 'bg-yellow-100 border border-yellow-600' }}">
        Environnement : <b>{{ $this->environment['env'] }}</b> — base : <b>{{ $this->environment['database'] }}</b>
    </div>

    {{-- Paramètres --}}
    <div class="flex flex-wrap gap-4 items-center mb-2">
        <div>
            <label class="block text-sm">Fichier (storage/app/import)</label>
            <select wire:model="file" class="border rounded">
                <option value="">— choisir —</option>
                @foreach ($this->files as $f)
                    <option value="{{ $f }}">{{ $f }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm">Encodage (CSV, COL)</label>
            <select wire:model="encoding" class="border rounded">
                <option value="auto">auto</option>
                <option value="cp437">CP437</option>
                <option value="utf8">UTF-8</option>
                <option value="cp1252">CP1252</option>
            </select>
        </div>

        <div>
            <label class="block text-sm">Mode</label>
            <label class="mr-2"><input type="radio" wire:model.live="mode" value="dry-run"> Simulation (tout est annulé)</label>
            <label><input type="radio" wire:model.live="mode" value="real"> <b>Import réel</b></label>
        </div>
    </div>

    @if ($mode === 'real')
        <div class="mb-2 p-2 rounded bg-red-100 border border-red-700">
            <label>
                <input type="checkbox" wire:model.live="confirmed">
                Je confirme l'écriture en base <b>{{ $this->environment['database'] }}</b> ({{ $this->environment['env'] }}).
            </label>
        </div>
    @endif

    <button wire:click="import" wire:loading.attr="disabled" wire:target="import"
            @disabled($mode === 'real' && !$confirmed)
            class="bg-gray-300 px-4 py-2 border border-blue-800 rounded">
        {{ $mode === 'real' ? 'Importer' : 'Simuler l\'import' }}
    </button>
    <span wire:loading wire:target="import" class="text-gray-600">Import en cours…</span>

    @foreach ($errors->all() as $message)
        <div class="text-red-700 mt-2">{{ $message }}</div>
    @endforeach
    @if ($error)
        <div class="text-red-700 font-semibold mt-2">{{ $error }}</div>
    @endif

    @if ($result)
        <div class="mt-2 p-2 rounded {{ $result['dryRun'] ? 'bg-yellow-100' : 'bg-green-100' }}">
            {{ $result['dryRun'] ? 'Simulation terminée : rien n\'a été conservé en base.' : 'Import réel terminé.' }}
            @if ($result['stats']['aborted'] ?? false) <b>Import interrompu.</b> @endif
        </div>

        @if ($result['stats'])
            @php($s = $result['stats'])
            <table class="text-sm border mt-2">
                <tr class="bg-gray-100">
                    <th class="px-2">Livres</th><th class="px-2">Importés</th><th class="px-2">Ignorés</th>
                    <th class="px-2">Déjà en base</th><th class="px-2">Annulés</th>
                </tr>
                <tr class="text-center">
                    <td>{{ $result['books'] }}</td><td>{{ $s['imported'] }}</td><td>{{ $s['skipped'] }}</td>
                    <td>{{ $s['already_imported'] }}</td><td>{{ $s['rolled_back'] }}</td>
                </tr>
            </table>

            {{-- Import réel : liens vers les fiches créées, pour vérifier la navigation --}}
            @if (!$result['dryRun'] && $s['created'])
                <div class="font-semibold mt-2">Publications créées ({{ count($s['created']) }})</div>
                <ul class="list-disc pl-6 text-sm">
                    @foreach ($s['created'] as $p)
                        <li><x-front.lien-standard link="/ouvrages/{{ $p['slug'] }}">{{ $p['title'] }}</x-front.lien-standard> (#{{ $p['id'] }})</li>
                    @endforeach
                </ul>
            @endif

            @if ($s['id_mismatches'])
                <div class="font-semibold text-yellow-800 mt-2">Ids du fichier qui ne correspondent plus à la base (recherche par nom appliquée)</div>
                <ul class="list-disc pl-6 text-sm">
                    @foreach ($s['id_mismatches'] as $m)
                        <li>{{ $m['type'] }} #{{ $m['id'] }} : {{ $m['label'] }}</li>
                    @endforeach
                </ul>
            @endif

            @if ($s['cycles_missing_parent'])
                <div class="font-semibold text-yellow-800 mt-2">Cycles en base sans parent, alors que le fichier en indique un (non modifiés)</div>
                <ul class="list-disc pl-6 text-sm">
                    @foreach ($s['cycles_missing_parent'] as $c)
                        <li>{{ $c['cycle'] }} (#{{ $c['id'] }}) → {{ $c['parent'] }}</li>
                    @endforeach
                </ul>
            @endif
        @endif

        {{-- Journal, livre par livre (équivalent de la sortie console) --}}
        @if ($result['log'])
            <details class="mt-2">
                <summary class="cursor-pointer font-semibold">Journal de l'import ({{ count($result['log']) }} ligne(s))</summary>
                <div class="text-xs font-mono overflow-y-auto border rounded p-2">
                    @foreach ($result['log'] as $line)
                        <div class="{{ $line['level'] === 'warn' ? 'text-yellow-800' : ($line['level'] === 'debug' ? 'text-gray-500' : '') }}">{{ $line['message'] }}</div>
                    @endforeach
                </div>
            </details>
        @endif
    @endif
</div>
