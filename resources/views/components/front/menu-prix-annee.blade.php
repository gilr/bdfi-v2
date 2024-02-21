<div class='flex flex-wrap grid-6 text-base m-1 self-center'>
    @foreach($annees as $annee)
        <div class='border-b-4 bg-gray-100 {{ ($tab == "$annee" ? "bg-yellow-100 border-yellow-500" : "border-gray-300 hover:bg-purple-100 hover:border-purple-400") }}'>
            <a class='m-0.5 px-0.5 sm:pl-1 md:px-1' href='/prix/annee/{{ $annee }}'>{{ $annee }}</a>
        </div>
    @endforeach
</div>


