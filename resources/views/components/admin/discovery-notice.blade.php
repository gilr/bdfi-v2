{{--
    Bandeau "mode découverte" : affiché quand le rôle de l'utilisateur ne permet pas
    d'enregistrer / d'exécuter sur la page (ex. rôle Invité).
    Usage : <x-admin.discovery-notice :allowed="auth()->user()->hasMemberRole()" />
    Les boutons d'action de la page sont désactivés en parallèle ; le refus reste
    de toute façon appliqué côté serveur.
--}}
@props(['allowed' => false])

@unless ($allowed)
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="p-2 m-1 border border-yellow-600 bg-yellow-100 rounded text-sm">
            <span class="font-semibold">Mode découverte</span> &mdash;
            votre rôle ({{ auth()->user()?->role?->getLabel() }}) permet de parcourir cette page,
            mais pas d'enregistrer ni de lancer d'exécution.
        </div>
    </div>
@endunless
