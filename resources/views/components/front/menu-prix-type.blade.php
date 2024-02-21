<div class='flex flex-wrap grid-6 text-base m-1 self-center'>
    <div class='mx-2'>Catégories de prix par type</div>
    @foreach($types as $type)
        <div class='border-b-4 bg-gray-100 {{ ($tab == "$type->value" ? "bg-yellow-100 border-yellow-500" : "border-gray-300 hover:bg-purple-100 hover:border-purple-400") }}'>
            <a class='px-2 md:px-4' href='/prix/type/{{ $type }}'>{{ $type->getShortLabel() }} </a>
        </div>
    @endforeach
</div>
