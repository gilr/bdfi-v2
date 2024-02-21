
@props([
    'type' => 'info',
    'bgcolors' => [
        'info' => 'bg-blue-400',
        'warning' => 'bg-yellow-400',
        'danger' => 'bg-red-400'
    ],
    'bordercolors' => [
        'info' => 'border-blue-400',
        'warning' => 'border-yellow-400',
        'danger' => 'border-red-400'
    ],
    'titles' => [
        'info' => 'Info !',
        'warning' => 'Attention !',
        'danger' => 'Oups !'
    ]
])

<style>
    @keyframes mymove { to { height: 0px; } }
</style>

<div class="mt-1 ml-8 w-8/12 shadow-md">
    <div class="flex">
        <div class="{{ $bgcolors[$type] }} w-2/12 text-center py-2">
            <div class="flex h-full justify-center items-center italic font-bold text-white">{{ $titles[$type] }}</div>
        </div>
        <div class="bg-white w-9/12 px-4 py-2">
            <div class="flex h-full items-center text-gray-600 text-sm">{{ $slot }}</div>
        </div>
        <div class="bg-white border-r-4 {{ $bordercolors[$type] }} w-1/12 md:pl-4 py-2 cursor-pointer">
            <div class="flex h-full justify-center items-center font-bold text-2xl lg:pl-2 text-red-800" onclick="this.parentElement.parentElement.parentElement.style='animation: mymove 250ms linear 500ms 1 forwards; opacity:0; transition:opacity 500ms linear;'" title="Fermer"> &times; </div>
        </div>
    </div>
</div>
