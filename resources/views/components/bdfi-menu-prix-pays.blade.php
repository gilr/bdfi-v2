<div class='flex flex-wrap grid-6 text-base m-1 self-center'>
    <div class='mx-2'>Liste des prix par pays</div>
    @foreach($pays as $monpays)
        <div class='border-b-4 bg-gray-100 {{ ($tab == "$monpays->name" ? "bg-yellow-100 border-yellow-500" : "border-gray-300 hover:bg-purple-100 hover:border-purple-400") }}'>
            <a class='px-2 md:px-4' href='/prix/pays/{{ $monpays->name }}'>{{ $monpays->name }}</a>
        </div>
    @endforeach
</div>
