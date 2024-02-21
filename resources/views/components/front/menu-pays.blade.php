<div class='flex flex-wrap grid-6 text-base m-1 self-center'>
    @foreach($countries as $country)
        <div class='border-b-4 bg-gray-100 {{ ($tab == "$country->name" ? "bg-yellow-100 border-yellow-500" : "border-gray-300 hover:bg-purple-100 hover:border-purple-400") }}'>
            <a class='px-1 md:px-2' href='/auteurs/pays/{{ $country->name }}'>{{ $country->code }}</a>
        </div>
    @endforeach
</div>

