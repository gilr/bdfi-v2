@if (auth()->user() && ! auth()->user()->canOpenAdminLink($lien))
{{-- Page non accessible (contributeur) : texte seul --}}
<span class="text-yellow-800">{{ $slot }}</span>
@else
<a class="text-yellow-800 border-b border-yellow-600 hover:bg-yellow-100 hover:text-purple-700 hover:border-purple-800" href="{{ $lien }}">{{ $slot }}</a>
@endif
