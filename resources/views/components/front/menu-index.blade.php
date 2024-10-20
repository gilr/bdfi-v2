<div class='flex flex-wrap grid-6 m-1 self-center text-base sm:text-xl md:text-2xl font-mono'>
    @for ($i = 'a'; $i != 'aa'; $i++)
        <div class='border-b-4 bg-gray-100 {{ ($tab == "$i" ? "border-yellow-500 bg-yellow-100" : "border-gray-300 hover:bg-purple-100 hover:border-purple-400") }}'>
            <a class='px-0.5 sm:pl-1 md:px-1' href='/{{ $zone }}/index/{{ $i }}'>{{ strtoupper($i) }}</a>
        </div>
    @endfor
    @if ($digit)
        <div class='border-b-4 bg-gray-100 {{ ($tab == "$i" ? "border-yellow-500 bg-yellow-100" : "border-gray-300 hover:bg-purple-100 hover:border-purple-400") }}'>
            <a class='px-0.5 sm:pl-1 md:px-1' href='/{{ $zone }}/index/0'>09</a>
        </div>
    @endif
    @if ($searcharea)
        <div class='border-b-4 bg-gray-100 border-gray-300 hover:bg-purple-100'>
            <form action="{{ route($zone . '.search') }}" method="GET">
            <input class="px-1 border border-gray-400 hover:border-purple-400 rounded text-ms sm:text-sm md:text-base w-24" type="text" name="s" placeholder='Recherche' required />
            </form>
        </div>
    @endif
</div>

