<div>
    <input type="text" wire:model.live.debounce.500ms="search" class="border inline-block m-2 bg-yellow-100" placeholder="Entrez un extrait de nom..."> ---&gt;

    @if (!empty($this->results))
        <select name="selected_id">
            <option value="">Sélectionner...</option>
            @foreach($this->results as $result)
                <option value="{{ $result->id }}">{{ $result->fullName }}</option>
            @endforeach
        </select>
    @endif
</div>
