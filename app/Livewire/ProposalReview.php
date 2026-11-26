<?php

namespace App\Livewire;

use App\Bdfi\Import\AnalysisReport;
use App\Bdfi\Import\BookAnalyzer;
use App\Bdfi\Import\BookImporter;
use App\Bdfi\Import\BookLookup;
use App\Bdfi\Import\ImportOptions;
use App\Bdfi\Import\WebInteractor;
use App\Enums\ProposalStatus;
use App\Enums\ProposalType;
use App\Models\Proposal;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Component;
use Throwable;

/**
 * Étude des propositions d'ajout d'ouvrage : liste, détail avec rapport d'analyse de l'import (ce qui existe déjà,
 * ce qui serait créé, ce qui manque), puis acceptation (import réel du livre JSON) ou refus motivé.
 * Lecture : invité et plus ; acceptation et refus : membres et plus.
 */
class ProposalReview extends Component
{
    // Obligatoire pour interdire les bots pirateurs
    protected static $middlewares = ['auth.bdfiadmin', 'admin.maintenance'];

    private const LOCK = 'bdfi-book-import';

    public string $filter = 'submitted';   // valeur de ProposalStatus, ou 'all'
    public ?int $selectedId = null;
    public string $rejectNote = '';
    public string $notice = '';
    public string $error = '';

    public function updatedFilter(): void
    {
        $this->selectedId = null;
        $this->reset('rejectNote', 'notice', 'error');
    }

    public function select(int $id): void
    {
        $this->selectedId = $id;
        $this->reset('rejectNote', 'notice', 'error');
    }

    public function close(): void
    {
        $this->selectedId = null;
        $this->reset('rejectNote', 'error');
    }

    private function pending(): ?Proposal
    {
        abort_unless(auth()->user()?->hasMemberRole(), 403);
        $this->reset('notice', 'error');

        $proposal = Proposal::where('type', ProposalType::PUBLICATION_ADD->value)->find($this->selectedId);
        if (!$proposal || !$proposal->isPending()) {
            $this->error = "Cette proposition n'est plus en attente.";

            return null;
        }

        return $proposal;
    }

    public function reject(): void
    {
        if (!($proposal = $this->pending())) {
            return;
        }

        $note = trim($this->rejectNote);
        if ($note === '') {
            $this->error = 'Indiquez le motif du refus : il sera visible du proposant.';

            return;
        }

        $proposal->update([
            'status' => ProposalStatus::REJECTED->value, 'review_note' => $note,
            'reviewed_by' => auth()->id(), 'reviewed_at' => now(),
        ]);
        $this->notice = "Proposition « {$proposal->title} » refusée.";
        $this->selectedId = null;
        $this->rejectNote = '';
    }

    /** Import réel du livre (éditeur et collection doivent exister : sinon l'import l'ignore et rien n'est accepté) */
    public function accept(): void
    {
        if (!($proposal = $this->pending())) {
            return;
        }

        $lock = Cache::lock(self::LOCK, 300);
        if (!$lock->get()) {
            $this->error = 'Un import est déjà en cours : réessayez dans quelques instants.';

            return;
        }

        try {
            $book = $proposal->payload;
            $stats = (new BookImporter(new ImportOptions(dryRun: false, stepByStep: false, bookByBook: false), $ui = new WebInteractor()))
                ->run([$book]);

            $created = $stats['created'][0] ?? null;
            if (!$created) {
                $warnings = collect($ui->log())->where('level', 'warn')->pluck('message')->implode(' ');
                $this->error = "Rien n'a été importé : " . ($warnings !== '' ? $warnings : 'ouvrage déjà présent, ou éditeur / collection introuvable.');

                return;
            }

            $proposal->update([
                'status' => ProposalStatus::ACCEPTED->value, 'result_id' => $created['id'],
                'reviewed_by' => auth()->id(), 'reviewed_at' => now(),
            ]);
            $this->notice = "Proposition acceptée : l'ouvrage « {$created['title']} » a été créé (#{$created['id']}).";
            $this->selectedId = null;
        } catch (Throwable $e) {
            $this->error = "Import interrompu, rien n'a été enregistré pour ce livre : " . Str::limit($e->getMessage(), 400);
        } finally {
            $lock->release();
        }
    }

    public function render()
    {
        $proposals = Proposal::with('user')
            ->where('type', ProposalType::PUBLICATION_ADD->value)
            ->when($this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
            ->orderByDesc('id')->limit(100)->get();

        $selected = $this->selectedId ? Proposal::with(['user', 'reviewer'])->find($this->selectedId) : null;
        $report = null;
        if ($selected) {
            // Analyse sans aucune écriture : doublon, éditeur et collection trouvés ?, auteurs et titres à créer
            $report = (new BookAnalyzer(new BookLookup()))->analyse([$selected->payload]);
        }

        return view('livewire.proposal-review', [
            'proposals' => $proposals,
            'selected'  => $selected,
            'report'    => $report instanceof AnalysisReport ? $report : null,
            'statuses'  => ProposalStatus::cases(),
        ]);
    }
}
