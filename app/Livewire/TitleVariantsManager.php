<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use App\Bdfi\VariantDates;
use App\Enums\TitleVariantType;
use App\Models\Title;

/**
 * Gestion d'un ensemble de variantes de textes : après le choix d'un texte quelconque, affiche le
 * parent ("premier" ou "virtuel") et toutes ses variantes, et permet d'en ajouter, d'en retirer,
 * de changer le type de variante, ou de désigner une variante comme nouveau parent de l'ensemble.
 *
 * Règle visée : un seul parent (sans parent_id), toutes les variantes directement rattachées à lui.
 * Les ensembles qui ne la respectent pas (variante de variante, texte parent de lui-même, type
 * incohérent) sont signalés, avec un bouton de remise à plat.
 */
class TitleVariantsManager extends Component
{
    private const PARENT_TYPES = ['premier', 'virtuel'];

    // Une partie de texte ne peut pas devenir le parent de l'ensemble
    private const PART_TYPES = ['feuilleton', 'extrait'];

    public string $search = '';
    public ?int $rootId = null;
    public ?int $selectedId = null;

    public string $addSearch = '';
    public string $addType = 'titre';

    public string $notice = '';
    public string $error = '';

    /** @var array<int, int> niveau de chaque texte de l'ensemble, recalculé par group() */
    private array $depths = [];

    // Obligatoire pour interdire les bots pirateurs
    protected static $middlewares = [
        'auth.bdfiadmin',
        'admin.maintenance',
    ];

    public function mount(): void
    {
        // Accès direct à un ensemble : ?titre=<id> (lien depuis une fiche, un rapport…)
        if (filled(request('titre')) && ctype_digit((string) request('titre'))) {
            $this->select((int) request('titre'));
        }
    }

    public function select(int $titleId): void
    {
        $this->resetMessages();
        $title = Title::find($titleId);
        if (!$title) {
            $this->error = "Texte #{$titleId} introuvable.";

            return;
        }

        $this->rootId = $this->rootOf($title)->id;
        $this->selectedId = $title->id;
        $this->search = '';
        $this->addSearch = '';
    }

    public function updatedAddSearch(): void
    {
        $this->resetMessages();
    }

    public function close(): void
    {
        $this->reset(['search', 'rootId', 'selectedId', 'addSearch', 'notice', 'error']);
    }

    public function addVariant(int $titleId): void
    {
        if (!$this->begin()) {
            return;
        }
        $group = $this->group();
        $title = Title::find($titleId);

        if (!$title || $group->has($titleId)) {
            $this->error = 'Ce texte fait déjà partie de cet ensemble (ou est introuvable).';

            return;
        }
        if (!in_array($this->addType, $this->childTypes(), true)) {
            $this->error = 'Type de variante non valide.';

            return;
        }
        if ($this->belongsToAnotherGroup($title)) {
            $this->error = "« {$title->name} » (#{$title->id}) appartient déjà à un autre ensemble de variantes : "
                . 'retirez-le d\'abord de cet autre ensemble.';

            return;
        }

        $title->parent_id = $this->rootId;
        $title->variant_type = $this->addType;
        $title->save();

        $this->addSearch = '';
        $this->notice = "« {$title->name} » (#{$title->id}) ajouté comme variante.";
    }

    public function detach(int $titleId): void
    {
        if (!$this->begin()) {
            return;
        }
        $title = $this->member($titleId);
        if (!$title || $title->id === $this->rootId) {
            return;
        }
        if (Title::where('parent_id', $title->id)->where('id', '!=', $title->id)->exists()) {
            $this->error = "« {$title->name} » a lui-même des variantes : remettez d'abord l'ensemble à plat.";

            return;
        }

        $title->parent_id = null;
        $title->variant_type = TitleVariantType::PREMIER->value;
        $title->save();

        if ($this->selectedId === $title->id) {
            $this->selectedId = $this->rootId;
        }
        $this->notice = "« {$title->name} » (#{$title->id}) retiré de l'ensemble : c'est de nouveau un texte indépendant.";
    }

    /** "Finalement, c'est ce texte-là le premier" : il devient le parent de tous les autres */
    public function promote(int $titleId): void
    {
        if (!$this->begin()) {
            return;
        }
        $group = $this->group();
        $new = $group->get($titleId);
        $old = $group->get($this->rootId);
        if (!$new || !$old || $new->id === $old->id) {
            return;
        }
        if (in_array($new->variant_type, self::PART_TYPES, true)) {
            $this->error = 'Un épisode ou un extrait ne peut pas devenir le parent : changez d\'abord son type.';

            return;
        }

        DB::transaction(function () use ($group, $new, $old) {
            // L'écart entre les deux textes (signature, traduction, titre) est le même vu dans l'autre sens
            $relation = in_array($new->variant_type, self::PARENT_TYPES, true)
                ? TitleVariantType::TITRE->value
                : $new->variant_type;

            $new->parent_id = null;
            $new->variant_type = TitleVariantType::PREMIER->value;
            $new->save();

            foreach ($group as $member) {
                if ($member->id === $new->id) {
                    continue;
                }
                $member->parent_id = $new->id;
                if ($member->id === $old->id) {
                    $member->variant_type = $relation;
                }
                $member->save();
            }
        });

        $this->rootId = $new->id;
        $this->notice = "« {$new->name} » (#{$new->id}) est maintenant le parent de l'ensemble. "
            . 'Vérifiez les types des autres variantes : ils décrivaient l\'écart avec l\'ancien parent.';
    }

    public function setType(int $titleId, string $type): void
    {
        if (!$this->begin()) {
            return;
        }
        $title = $this->member($titleId);
        if (!$title) {
            return;
        }
        $allowed = $title->id === $this->rootId ? self::PARENT_TYPES : $this->childTypes();
        if (!in_array($type, $allowed, true)) {
            $this->error = 'Type non valide pour ce texte.';

            return;
        }

        $title->variant_type = $type;
        $title->save();
        $this->notice = "Type de « {$title->name} » (#{$title->id}) : " . TitleVariantType::from($type)->getLabel() . '.';
    }

    /** Remise à plat : un seul parent sans parent, toutes les variantes rattachées directement à lui */
    public function normalize(): void
    {
        if (!$this->begin()) {
            return;
        }
        $group = $this->group();

        DB::transaction(function () use ($group) {
            foreach ($group as $member) {
                if ($member->id === $this->rootId) {
                    $member->parent_id = null;
                    if (!in_array($member->variant_type, self::PARENT_TYPES, true)) {
                        $member->variant_type = TitleVariantType::PREMIER->value;
                    }
                } else {
                    $member->parent_id = $this->rootId;
                }
                if ($member->isDirty()) {
                    $member->save();
                }
            }
        });

        $this->notice = 'Ensemble remis à plat : toutes les variantes sont rattachées directement au parent.';
    }

    private function begin(): bool
    {
        // Modification réservée aux membres et plus (comme les autres formulaires)
        abort_unless(auth()->user()?->hasMemberRole(), 403);
        $this->resetMessages();

        return $this->rootId !== null;
    }

    private function resetMessages(): void
    {
        $this->notice = '';
        $this->error = '';
    }

    /** @return string[] */
    private function childTypes(): array
    {
        return collect(TitleVariantType::cases())
            ->map(fn ($c) => $c->value)
            ->reject(fn ($v) => in_array($v, self::PARENT_TYPES, true))
            ->values()->all();
    }

    private function member(int $titleId): ?Title
    {
        return $this->group()->get($titleId);
    }

    /** Remonte jusqu'au parent de tête (avec garde contre les boucles et les textes parents d'eux-mêmes) */
    private function rootOf(Title $title): Title
    {
        $seen = [$title->id => true];
        while ($title->parent_id && !isset($seen[$title->parent_id])) {
            $parent = Title::find($title->parent_id);
            if (!$parent) {
                break;
            }
            $seen[$parent->id] = true;
            $title = $parent;
        }

        // Boucle (A parent de B et B parent de A) : même tête quel que soit le texte choisi, le plus petit n°
        if ($title->parent_id && $title->parent_id !== $title->id) {
            $loop = [$title->id => $title];
            $current = $title;
            while (($current = Title::find($current->parent_id)) && !isset($loop[$current->id])) {
                $loop[$current->id] = $current;
            }
            $title = $loop[min(array_keys($loop))];
        }

        return $title;
    }

    /**
     * Le parent de tête puis tous ses descendants, indexés par id. $this->depths donne leur niveau
     * (0 = parent, 1 = variante directe, 2 et plus = variante de variante, anomalie).
     */
    private function group(): Collection
    {
        $group = collect();
        $root = $this->rootId ? Title::with('authors')->find($this->rootId) : null;
        if (!$root) {
            return $group;
        }

        $this->depths = [$root->id => 0];
        $group->put($root->id, $root);
        $level = [$root->id];
        $depth = 1;

        while ($level) {
            $children = Title::with('authors')->whereIn('parent_id', $level)
                ->whereNotIn('id', $group->keys())
                ->orderBy('copyright_fr')->orderBy('name')->get();
            $level = [];
            foreach ($children as $child) {
                $this->depths[$child->id] = $depth;
                $group->put($child->id, $child);
                $level[] = $child->id;
            }
            $depth++;
        }

        return $group;
    }

    private function belongsToAnotherGroup(Title $title): bool
    {
        $hasParent = $title->parent_id && $title->parent_id !== $title->id;

        return $hasParent || Title::where('parent_id', $title->id)->where('id', '!=', $title->id)->exists();
    }

    /** @return string[] */
    private function anomalies(Collection $group): array
    {
        $root = $group->get($this->rootId);
        if (!$root) {
            return [];
        }

        $anomalies = [];
        if ($root->parent_id) {
            $anomalies[] = 'Le parent a lui-même un parent (lui-même, ou une boucle).';
        }
        if (!in_array($root->variant_type, self::PARENT_TYPES, true)) {
            $anomalies[] = "Le parent est de type « {$root->variant_type} » au lieu de « premier » ou « virtuel ».";
        }
        if (max($this->depths ?: [0]) > 1) {
            $anomalies[] = 'Des variantes sont rattachées à une autre variante au lieu du parent.';
        }

        return $anomalies;
    }

    /**
     * Variantes dont la date fr est antérieure à celle du parent (si celui-ci en a une) : le parent
     * n'est alors sans doute pas la première publication.
     * @return int[]
     */
    private function earlierThanParent(Collection $group): array
    {
        $rootDate = $group->get($this->rootId)?->copyright_fr;

        return $group
            ->filter(fn ($t) => $t->id !== $this->rootId && $t->variant_type !== TitleVariantType::EPISODE->value
                && VariantDates::isEarlier($t->copyright_fr, $rootDate))
            ->keys()->all();
    }

    private function searchTitles(string $text, ?Collection $exclude = null): Collection
    {
        $text = trim($text);
        if (mb_strlen($text) < 2) {
            return collect();
        }

        return Title::with('authors')
            ->where(fn ($q) => $q->where('name', 'like', "%{$text}%")
                ->when(ctype_digit($text), fn ($q) => $q->orWhere('id', (int) $text)))
            ->when($exclude?->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $exclude->keys()))
            ->orderBy('name')->orderBy('copyright_fr')
            ->limit(30)
            ->get();
    }

    public function render()
    {
        $group = $this->group();
        if ($this->rootId && $group->isEmpty()) {
            $this->rootId = null;
        }

        $childTypes = collect(TitleVariantType::cases())
            ->reject(fn ($c) => in_array($c->value, self::PARENT_TYPES, true))->values();

        return view('livewire.title-variants-manager', [
            'group'       => $group,
            'depths'      => $this->depths,
            'earlier'     => $this->earlierThanParent($group),
            'anomalies'   => $this->anomalies($group),
            'results'     => $this->rootId ? collect() : $this->searchTitles($this->search),
            'candidates'  => $this->rootId ? $this->searchTitles($this->addSearch, $group) : collect(),
            'parentTypes' => [TitleVariantType::PREMIER, TitleVariantType::VIRTUEL],
            'childTypes'  => $childTypes,
            'partTypes'   => self::PART_TYPES,
            'canEdit'     => (bool) auth()->user()?->hasMemberRole(),
        ]);
    }
}
