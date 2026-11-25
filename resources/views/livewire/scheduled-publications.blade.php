<div>
    @if($notice !== '')
        <div class="p-2 mb-2 text-green-800 bg-green-100 border border-green-500">{{ $notice }}</div>
    @endif
    @if($error !== '')
        <div class="p-2 mb-2 text-red-700 bg-red-100 border border-red-300">{{ $error }}</div>
    @endif

    <table class="table-auto border-collapse border border-blue-800 w-full">
        <thead>
            <tr class="bg-blue-50">
                <th class="p-1">Titre ouvrage et accès fiche</th>
                <th class="p-1">Type</th>
                <th class="p-1">Support</th>
                <th class="p-1">Date annoncée</th>
                <th class="p-1">Changer date ou état</th>
                <th class="p-1">Dernière <br />modification</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($results as $publication)
                <tr class="border border-blue-500" wire:key="sp-{{ $publication->id }}">
                    <td class="py-1 px-2 border border-blue-500">
                        {{ $publication->name }}
                        @if ($publication->publisher)
                            <span class="text-sm italic text-red-700">{{ $publication->publisher->name }}</span>
                        @endif
                        <x-admin.link-ext lien='/admin/formulaires/modifier-publication?id={{ $publication->id }}'>Formulaire</x-admin.link-ext>
                        <x-admin.link-ext lien='/filament/publications/{{ $publication->id }}'>Table</x-admin.link-ext>
                    </td>
                    <td class="py-1 px-2 border border-blue-500">{{ $publication->type->getLabel() }}</td>
                    <td class="py-1 px-2 border border-blue-500">{{ $publication->support->getLabel() }}</td>
                    <td class="py-1 px-2 border border-blue-500 font-mono">{{ $publication->approximate_parution }}</td>
                    <td class="py-1 px-2 border border-blue-500 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <input type="text" wire:model="dates.{{ $publication->id }}" maxlength="10" placeholder="AAAA-MM-JJ"
                                   class="w-28 border rounded bg-yellow-100 font-mono pt-1 pb-1">
                            <button wire:click="changeDate({{ $publication->id }})" class="bg-blue-400 text-white px-1 rounded">OK</button>
                        </div>
                        <div class="mt-1 flex items-center gap-2">
                            <button wire:click="markPublished({{ $publication->id }})" class="bg-green-100 text-green-800 border border-green-600 px-1 rounded font-semibold">Paru</button>
                            <button wire:click="markAbandoned({{ $publication->id }})"
                                    wire:confirm="Marquer cet ouvrage comme jamais paru ?"
                                    class="bg-gray-500 text-white px-1 rounded">Jamais paru</button>
                        </div>
                    </td>
                    <td class="py-1 px-2 border border-blue-500">{{ $publication->updated_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="p-2 text-center italic">Aucune publication dans cette liste.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
