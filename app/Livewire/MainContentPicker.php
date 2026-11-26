<?php

namespace App\Livewire;

use App\Bdfi\MainContentFinder;
use App\Enums\IsNovelization;
use App\Enums\PublicationContent;
use App\Enums\TitleType;
use Livewire\Component;

/**
 * Choix du contenu principal d'une publication dans le formulaire d'ajout rapide (formulaire classique) :
 * rattacher un texte existant identique, ou créer le texte avec la publication.
 * Rend, dans le <form> englobant, les champs "title_choice" (id du texte, ou "new") et "new_title_*".
 * Le titre, les auteurs et le type de la publication sont lus dans le formulaire au clic sur le bouton de recherche.
 * Les textes proposés (existants ou à créer) ont un type compatible avec celui de la publication
 * (PublicationContent::mainContentTypes) ; les extraits (variante "extrait") sont admis pour une fiction courte.
 */
class MainContentPicker extends Component
{
    public string $choice = '';
    public bool $searched = false;

    /** Type de la publication à la dernière recherche (valeur de PublicationContent) */
    public string $publicationType = '';

    // Création du texte avec la publication
    public string $newType = '';
    public bool $newExtract = false;
    public string $newNovelization = 'non';
    public string $newCopyright = '';
    public string $newTitleVo = '';
    public string $newTranslators = '';

    /** @var array<int, array<string, mixed>> */
    public array $candidates = [];

    public function mount(?string $oldChoice = null, ?string $oldName = null, mixed $oldAuthor1 = null, mixed $oldAuthor2 = null,
        ?string $oldType = null, ?string $oldCopyright = null, ?string $oldTitleVo = null, ?string $oldTranslators = null,
        ?string $oldPublicationType = null, mixed $oldExtract = null, ?string $oldNovelization = null): void
    {
        // (pas de paramètre nommé comme la propriété : Livewire lui assignerait null)
        $this->publicationType = (string) $oldPublicationType;
        $this->newType = (string) $oldType;
        $this->newExtract = filter_var($oldExtract, FILTER_VALIDATE_BOOLEAN);
        $this->newNovelization = $oldNovelization ?: IsNovelization::NON->value;
        $this->newCopyright = (string) $oldCopyright;
        $this->newTitleVo = (string) $oldTitleVo;
        $this->newTranslators = (string) $oldTranslators;

        // Après une erreur de validation : le choix précédent et la liste sont restaurés
        if (filled($oldChoice)) {
            $this->search((string) $oldName, $oldAuthor1, $oldAuthor2, $oldPublicationType);
            $this->choice = $oldChoice;
        }
    }

    /** La publication admet-elle un extrait pour contenu principal ? */
    public function allowsExtract(): bool
    {
        return PublicationContent::tryFrom($this->publicationType)?->allowsExtract() ?? false;
    }

    /** Types de texte admis pour le nouveau texte : ceux du type de publication ; tous pour un extrait (de n'importe quel type) */
    public function allowedTypes(): array
    {
        if ($this->newExtract && $this->allowsExtract()) {
            return TitleType::cases();
        }

        return MainContentFinder::allowedTypes($this->publicationType) ?? TitleType::cases();
    }

    /** Appelé par le bouton, avec le titre, les ids d'auteurs et le type de publication lus dans le formulaire */
    public function search(string $name, mixed $author1 = null, mixed $author2 = null, ?string $publicationType = null): void
    {
        $this->searched = true;
        $this->choice = '';
        $this->publicationType = (string) $publicationType;
        if (!$this->allowsExtract()) {
            $this->newExtract = false;
        }

        // Type du nouveau texte : celui déjà choisi s'il reste compatible, sinon le type par défaut (le premier admis)
        $allowed = array_map(fn ($t) => $t->value, $this->allowedTypes());
        if (!in_array($this->newType, $allowed, true)) {
            $this->newType = PublicationContent::tryFrom($this->publicationType) ? $allowed[0] : '';
        }

        $this->candidates = MainContentFinder::candidates($name, [$author1, $author2], $this->publicationType)
            ->map(fn ($t) => [
                'id'           => $t->id,
                'name'         => $t->name,
                'slug'         => $t->slug,
                'type'         => $t->type?->getLabel(),
                'variant'      => (string) $t->variant_type,
                'authors'      => $t->authors->map(fn ($a) => trim($a->first_name . ' ' . $a->name))->implode(', '),
                'copyright'    => $t->copyright,
                'copyright_fr' => $t->copyright_fr,
                'title_vo'     => $t->title_vo,
                'translators'  => $t->translators,
                'publications' => $t->publications->map(fn ($p) => trim($p->name . ' — ' . ($p->publisher?->name ?? '?')
                    . ' ' . substr((string) $p->approximate_parution, 0, 4)))->all(),
            ])->all();
    }

    public function render()
    {
        return view('livewire.main-content-picker', [
            'titleTypes'    => $this->allowedTypes(),
            'novelizations' => IsNovelization::cases(),
            'allowsExtract' => $this->allowsExtract(),
        ]);
    }
}
