
<div class="collapsible-block text-base w-full">
    {{-- Titre (optionnel) visible en permanence --}}
    <h3>{!! $blockTitle !!}</h3>
    <p class="intro-text">
    {{-- Début de texte visible en permanence --}}
        {!! $blockIntro !!}
        {{-- Bouton de repli/déploiement intégré --}}
        <span class="inline-block underline underline-offset-4 decoration-2 decoration-sky-500" wire:click="toggle">{{ $isExpanded ? "⬆️ (cliquer pour refermer)" : "⬇️ (cliquer pour plus d'info)" }}</span>
    </p>

    {{-- Contenu repliable --}}
    @if ($isExpanded)
        <div class="collapsible-content">
            {!! $blockContent !!}
        </div>
    @endif
</div>
