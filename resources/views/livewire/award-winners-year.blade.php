<div>
    <div class="flex flex-wrap items-center gap-4 mb-2">
        <div>
            <label for="award_id" class="font-semibold text-sm block mb-1">Prix *</label>
            <select id="award_id" name="award_id" wire:model.live="awardId" class="w-64 border rounded bg-yellow-100">
                <option value="">— choisir —</option>
                @foreach ($awards as $a)
                    <option value="{{ $a->id }}" @selected($awardId == $a->id)>{{ $a->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="year" class="font-semibold text-sm block mb-1">Année *</label>
            <select id="year" name="year" wire:model.live="year" class="w-32 border rounded bg-yellow-100">
                <option value="">— choisir —</option>
                @foreach ($years as $y)
                    <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <div class="text-xs text-gray-500 self-end mb-1">Choisissez d'abord un prix et une année pour afficher ses catégories.</div>
    </div>

    @if ($awardId && $year)
        @if ($categories->isEmpty())
            <div class="text-sm text-yellow-800 bg-yellow-100 border border-yellow-600 rounded p-1">
                Ce prix n'a aucune catégorie enregistrée (à créer d'abord dans Filament).
            </div>
        @else
            <div class="text-xs text-gray-500 mb-2">
                Lauréat vide = catégorie non décernée cette année-là. Si un champ de la ligne est renseigné (auteur, titre…),
                le lauréat doit l'être aussi.
            </div>
            <div class="space-y-3">
                @foreach ($categories as $cat)
                    <div class="border rounded p-2" wire:key="row-{{ $cat->id }}">
                        <div class="font-semibold text-sm mb-1">{{ $cat->name }}</div>

                        @error("winners.{$cat->id}.name")
                            <div class="text-sm text-red-700 mb-1">{{ $message }}</div>
                        @enderror

                        <div class="flex flex-wrap items-start gap-3">
                            <div>
                                <label class="text-sm block mb-1">Attribué à</label>
                                <input type="text" name="winners[{{ $cat->id }}][name]" value="{{ $this->fieldValue($cat->id, 'name') }}"
                                       maxlength="256" class="w-48 border rounded bg-yellow-100" placeholder="Nom(s) du/des lauréat(s)">
                            </div>

                            <div class="w-48">
                                <livewire:entity-picker
                                    model="App\Models\Author"
                                    :field-name="'winners-'.$cat->id.'-author1'"
                                    :id-field-name="'winners['.$cat->id.'][author1_id]'"
                                    label="Auteur 1"
                                    placeholder="Rechercher un auteur…"
                                    :select-only="true"
                                    :old-id="$this->authorValue($cat->id, 'author_id')"
                                    :key="'picker-'.$cat->id.'-1'"
                                />
                            </div>
                            <div class="w-48">
                                <livewire:entity-picker
                                    model="App\Models\Author"
                                    :field-name="'winners-'.$cat->id.'-author2'"
                                    :id-field-name="'winners['.$cat->id.'][author2_id]'"
                                    label="Auteur 2"
                                    placeholder="Rechercher un auteur…"
                                    :select-only="true"
                                    :old-id="$this->authorValue($cat->id, 'author2_id')"
                                    :key="'picker-'.$cat->id.'-2'"
                                />
                            </div>
                            <div class="w-48">
                                <livewire:entity-picker
                                    model="App\Models\Author"
                                    :field-name="'winners-'.$cat->id.'-author3'"
                                    :id-field-name="'winners['.$cat->id.'][author3_id]'"
                                    label="Auteur 3"
                                    placeholder="Rechercher un auteur…"
                                    :select-only="true"
                                    :old-id="$this->authorValue($cat->id, 'author3_id')"
                                    :key="'picker-'.$cat->id.'-3'"
                                />
                            </div>

                            <div>
                                <label class="text-sm block mb-1">Attribué pour</label>
                                <input type="text" name="winners[{{ $cat->id }}][title]" value="{{ $this->fieldValue($cat->id, 'title') }}"
                                       maxlength="256" class="w-48 border rounded bg-yellow-100" placeholder="Titre de l'ouvrage/du texte">
                            </div>
                            <div>
                                <label class="text-sm block mb-1">Titre original</label>
                                <input type="text" name="winners[{{ $cat->id }}][vo_title]" value="{{ $this->fieldValue($cat->id, 'vo_title') }}"
                                       maxlength="256" class="w-48 border rounded bg-yellow-100" placeholder="Si traduit">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    <div class="flex flex-wrap items-center gap-2 mt-3">
        @php($blocked = !$awardId || !$year || ! auth()->user()?->hasMemberRole())
        <button type="submit" @disabled($blocked)
                class="bg-blue-400 font-semibold border border-blue-800 rounded px-4 py-2"
                @style(['opacity: 0.5; cursor: not-allowed' => $blocked])>Enregistrer</button>
        <span class="text-xs text-gray-500">* Prix et année obligatoires. Tous les autres champs sont optionnels.</span>
    </div>
</div>
