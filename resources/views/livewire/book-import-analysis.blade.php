<div class="p-2">
    {{-- Paramètres --}}
    <div class="flex flex-wrap gap-4 items-center mb-2">
        <div>
            <label class="block text-sm">Source</label>
            <select wire:model.live="source" class="border rounded">
                <option value="existing">Fichier présent dans storage/app/import</option>
                <option value="upload">Téléverser un fichier</option>
            </select>
        </div>

        @if ($source === 'existing')
            <div>
                <label class="block text-sm">Fichier</label>
                <select wire:model="file" class="border rounded">
                    <option value="">— choisir —</option>
                    @foreach ($this->files as $f)
                        <option value="{{ $f }}">{{ $f }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div>
                <label class="block text-sm">Fichier (.col, .csv, .json)</label>
                <input type="file" wire:model="upload" accept=".col,.csv,.json">
                <div wire:loading wire:target="upload" class="text-sm text-gray-500">Téléversement…</div>
            </div>
        @endif

        <div>
            <label class="block text-sm">Encodage (CSV, COL)</label>
            <select wire:model="encoding" class="border rounded">
                <option value="auto">auto</option>
                <option value="cp437">CP437</option>
                <option value="utf8">UTF-8</option>
                <option value="cp1252">CP1252</option>
            </select>
        </div>

        <button wire:click="analyse" wire:loading.attr="disabled" wire:target="analyse,upload"
                class="bg-gray-300 px-4 py-2 border border-blue-800 rounded">
            Analyser
        </button>
        <span wire:loading wire:target="analyse" class="text-gray-600">Conversion et analyse en cours…</span>
    </div>

    @foreach ($errors->all() as $message)
        <div class="text-red-700">{{ $message }}</div>
    @endforeach
    @if ($error)
        <div class="text-red-700 font-semibold">{{ $error }}</div>
    @endif

    @if ($result)
        <div class="text-sm text-gray-600">
            Fichier <b>{{ $result['file'] }}</b> : {{ $result['books'] }} livre(s).
        </div>

        {{-- Erreurs de validation : l'analyse n'a pas lieu --}}
        @isset($result['errors'])
            <div class="text-red-700 font-semibold">
                {{ $result['errorsTotal'] }} livre(s) invalide(s) : corrigez le fichier avant de continuer.
            </div>
            <table class="text-sm border">
                <tr class="bg-gray-100">
                    <th class="px-2 text-left">Livre</th><th class="px-2 text-left">Titre</th><th class="px-2 text-left">Problème(s)</th>
                </tr>
                @foreach ($result['errors'] as $e)
                    <tr class="border-t">
                        <td class="px-2">#{{ $e['n'] }}</td>
                        <td class="px-2">{{ $e['title'] }}</td>
                        <td class="px-2">
                            @foreach ($e['messages'] as $m)
                                <div>{{ $m }}</div>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
            </table>
        @endisset

        @if ($result['warnings'] ?? [])
            <div>
                <div class="font-semibold text-yellow-800 mt-2">Écarts fichier / base (éditeur ou collection renommés en base ?)</div>
                <ul class="list-disc pl-6 text-sm">
                    @foreach ($result['warnings'] as $w)
                        <li>{{ $w }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @isset($result['report'])
            @php($r = $result['report'])
            <table class="text-sm border">
                <tr class="bg-gray-100">
                    <th class="px-2">Livres</th><th class="px-2">Importables</th><th class="px-2">Ignorés</th>
                    <th class="px-2">dont déjà en base</th><th class="px-2">Auteurs à créer</th>
                    <th class="px-2">Titres à créer</th><th class="px-2">Cycles à créer</th>
                </tr>
                <tr class="text-center">
                    <td>{{ $r['total'] }}</td><td>{{ $r['publications'] }}</td><td>{{ $r['skipped'] }}</td>
                    <td>{{ $r['alreadyImported'] }}</td><td>{{ $r['newAuthors']['count'] }}</td>
                    <td>{{ $r['newTitles']['count'] }}</td><td>{{ $r['newCycles']['count'] }}</td>
                </tr>
            </table>

            {{-- Introuvables (livres ignorés) --}}
            @foreach (['missingPublishers' => 'Éditeurs introuvables (livres ignorés)', 'missingCollections' => 'Collections introuvables (livres ignorés)'] as $key => $label)
                @if ($r[$key])
                    <div>
                        <div class="font-semibold text-yellow-800 mt-2">{{ $label }}</div>
                        <ul class="list-disc pl-6 text-sm">
                            @foreach ($r[$key] as $name => $nums)
                                <li>{{ $name }} : {{ count($nums) }} livre(s) (#{{ implode(', #', array_slice($nums, 0, 10)) }}{{ count($nums) > 10 ? '…' : '' }})</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach

            {{-- Information : cycles en base sans parent --}}
            @if ($r['cyclesMissingParent'])
                <div>
                    <div class="font-semibold text-yellow-800 mt-2">Cycles en base sans parent, alors que le fichier en indique un (non modifiés)</div>
                    <ul class="list-disc pl-6 text-sm">
                        @foreach ($r['cyclesMissingParent'] as $c)
                            <li>{{ $c['cycle'] }} (#{{ $c['id'] }}) → {{ $c['parent'] }} : livre(s) #{{ implode(', #', array_slice($c['books'], 0, 10)) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Ids du fichier qui ne correspondent plus : recherche par nom appliquée --}}
            @if ($r['idMismatches'] ?? [])
                <div>
                    <div class="font-semibold text-yellow-800 mt-2">Ids du fichier qui ne correspondent plus à la base (recherche par nom appliquée)</div>
                    <ul class="list-disc pl-6 text-sm">
                        @foreach ($r['idMismatches'] as $m)
                            <li>{{ $m['type'] }} #{{ $m['id'] }} : {{ $m['label'] }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Listes repliables --}}
            @foreach ([
                'newAuthors'      => 'Auteurs qui seraient créés',
                'newTitles'       => 'Titres qui seraient créés',
                'newCycles'       => 'Cycles qui seraient créés',
                'newPublications' => 'Publications qui seraient ajoutées',
            ] as $key => $label)
                @if ($r[$key]['count'])
                    <details @if ($key === 'newPublications') open @endif>
                        <summary class="cursor-pointer font-semibold">{{ $label }} ({{ $r[$key]['count'] }})</summary>
                        <ul class="list-disc pl-6 text-sm">
                            @foreach ($r[$key]['items'] as $n => $item)
                                <li>
                                    @switch($key)
                                        @case('newTitles')
                                            {{ $item['title'] }} ({{ implode(', ', $item['authors']) }})
                                            @break
                                        @case('newPublications')
                                            #{{ $n }} {{ $item['title'] }}
                                            @if ($item['authors']) ({{ implode(', ', $item['authors']) }}) @endif
                                            — {{ $item['publisher'] }}
                                            @if ($item['collection'] !== '') / {{ $item['collection'] }}@if ($item['number'] !== '') n°{{ $item['number'] }}@endif @endif
                                            @if ($item['isbn'] !== '') [{{ $item['isbn'] }}] @endif
                                            @if ($item['reprints'] > 0) (+ {{ $item['reprints'] }} retirage(s)) @endif
                                            @break
                                        @default
                                            {{ $item }}
                                    @endswitch
                                </li>
                            @endforeach
                            @if ($r[$key]['count'] > count($r[$key]['items']))
                                <li>… et {{ $r[$key]['count'] - count($r[$key]['items']) }} autre(s)</li>
                            @endif
                        </ul>
                    </details>
                @endif
            @endforeach

            {{-- Livres ignorés, avec leur raison (résumé par raison dans le titre de la liste) --}}
            @if ($result['skipped'] ?? [])
                @php($reasons = collect($result['skipped'])->countBy('reason')->sortDesc())
                <details>
                    <summary class="cursor-pointer font-semibold">
                        Livres ignorés ({{ count($result['skipped']) }} :
                        {{ $reasons->map(fn ($n, $reason) => "{$n} {$reason}")->implode(' ; ') }})
                    </summary>
                    <ul class="list-disc pl-6 text-sm">
                        @foreach ($result['skipped'] as $n => $s)
                            <li>
                                #{{ $n }} {{ $s['title'] }}
                                @if ($s['isbn'] !== '') [{{ $s['isbn'] }}] @endif
                                @if ($reasons->count() > 1) — <i>{{ $s['reason'] }}</i> @endif
                            </li>
                        @endforeach
                    </ul>
                </details>
            @endif

            @if ($result['toAdd'])
                <button wire:click="downloadToAdd" class="mt-2 bg-gray-300 px-4 py-2 border border-blue-800 rounded">
                    Télécharger {{ basename($result['toAdd']) }}
                </button>
                {{-- Suite naturelle : simuler puis importer ces livres (fichier présélectionné, mode simulation) --}}
                <a href="/admin/outils/import?file={{ urlencode($result['toAdd']) }}"
                   class="inline-block mt-2 bg-gray-300 px-4 py-2 border border-blue-800 rounded">
                    Importer ces {{ $r['newPublications']['count'] }} livre(s) →
                </a>
            @else
                <div class="text-sm">Aucun livre à ajouter : pas de fichier "to-add" généré.</div>
            @endif

            <div class="text-xs text-gray-500">Analyse indicative : la base peut changer avant l'import réel.</div>
        @endisset
    @endif
</div>
