<div class='ml-2 md:ml-8 py-0.5 my-0.5'>
    <a class='border-b border-dotted border-purple-700 font-semibold hover:text-purple-700 focus:text-purple-900' href='/collections/{{ $collection->id }}'>{{ $collection->name }} </a> - {{ $collection->publisher->name }} - xxx / {{ count($collection->publications) }}

    {{ html()->form('POST', '/user/modifier-collection')->class("inline-block")->open() }}
        - Changement du suivi :
        {{ html()->select("col_status")->class("px-2 p-0 border-dotted bg-gray-300 text-gray-500")->open() }}
        @foreach (App\Enums\biblioCollectionStatus::cases() as $type)
            @if ($type->value === $collection->pivot->status)
                {{ html()->option($type->getLabel(), $type->value)->class("text-black")->selected() }}
            @else
                {{ html()->option($type->getLabel(), $type->value)->class("text-black") }}
            @endif
        @endforeach
        {{ html()->select("col_status")->close() }}
        {{ html()->hidden("col", $collection->id) }}
        {{ html()->submit("Modifier")->class("bg-blue-400 border border-blue-800 rounded px-2 m-0") }}
    {{ html()->form()->close() }}

    {{ html()->form('POST', '/user/retirer-collection')->class("inline-block")->open() }}
        - Retrait définitif :
        {{ html()->hidden($name = "col", $collection->id) }}
        {{ html()->submit("Retirer")->class("bg-blue-400 border border-blue-800 rounded px-2 m-0") }}
    {{ html()->form()->close() }}
</div>
