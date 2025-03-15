
<div class='flex flex-wrap grid-6 text-sm md:text-base bg-gray-100 m-1 self-center'>
    <div class="border-b-4 {{ ($tab == 'accueil' ? 'bg-yellow-100 border-yellow-500' : 'border-gray-300 hover:bg-purple-100 hover:border-purple-400') }}">
        <a class='px-2 md:px-4' href='/stats'>BDFI</a>
    </div>
    <div class="border-b-4 {{ ($tab == 'bdfi' ? 'bg-yellow-100 border-yellow-500' : 'border-gray-300 hover:bg-purple-100 hover:border-purple-400') }}">
        <a class='px-2 md:px-4' href='/stats/bdfi'>Historique BDFI</a>
    </div>
    <div class="border-b-4 {{ ($tab == 'production' ? 'bg-yellow-100 border-yellow-500' : 'border-gray-300 hover:bg-purple-100 hover:border-purple-400') }}">
        <a class='px-2 md:px-4' href='/stats/production'>Historique production</a>
    </div>
    @if (auth()->user() && auth()->user()->hasGuestRole())
        <div class="border-b-4 {{ ($tab == 'analyse' ? 'bg-yellow-100 border-yellow-500' : 'border-gray-300 hover:bg-purple-100 hover:border-purple-400') }}">
        <a class='px-2 md:px-4' href='/stats/analyse'>Analyses</a>
        </div>
    @endif
</div>
