
<!-- Utilisation :
<x-front.display-icon-verified-if verified='{{ $result->is_verified }}' second_verif='{{ Illuminate\Support\Str::contains($result->verified_by,";") }}' />
-->

@if($verified == true)
    Publication vérifiée
    <img src='/img/ok.png' class="inline w-5 mb-1" title="La publication est vérifiée ouvrage entre les mains"/>
    @if ($second_verif == true)
        <img src='/img/ok.png' class="inline w-5 mb-1" title="La publication est doublement vérifiée ouvrage entre les mains"/>
    @endif
    @auth
        @if (auth()->user()->hasGuestRole())
            <span class='text-blue-900 bg-sky-200 shadow-sm shadow-blue-600 rounded-sm px-1'> ({{ $results->verified_by }})</span>
        @endif
    @endauth
@else
    <img src='/img/ok.png' class="inline w-5 mb-1" title="La publication n'est pas vérifiée"/>
    Publication non vérifiée
@endif
