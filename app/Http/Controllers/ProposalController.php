<?php

namespace App\Http\Controllers;

use App\Bdfi\Proposal\PublicationProposalService;
use App\Enums\ProposalStatus;
use App\Http\Requests\StoreProposalRequest;
use App\Models\Proposal;
use Illuminate\Http\RedirectResponse;

/** Propositions d'ajout d'un ouvrage par les contributeurs : formulaire, envoi, suivi et retrait */
class ProposalController extends Controller
{
    /** Formulaire de proposition + propositions de l'utilisateur */
    public function create()
    {
        $mine = Proposal::where('user_id', auth()->id())->orderByDesc('id')->limit(50)->get();

        return view('admin.formulaires.proposer-publication', [
            'mine'    => $mine,
            'sent'    => session('sent'),
        ]);
    }

    public function store(StoreProposalRequest $request, PublicationProposalService $service): RedirectResponse
    {
        $book = $service->buildBook($request->validated() + ['no_isbn' => $request->boolean('no_isbn')]);

        $problems = $service->validationMessages($book);
        if ($problems) {
            return back()->withInput()->withErrors($problems);
        }

        // Un ouvrage déjà référencé ou déjà proposé est refusé
        if ($reason = $service->duplicateReason($book)) {
            return back()->withInput()->withErrors(['doublon' => $reason]);
        }

        $proposal = $service->store($request->user(), $book);

        // POST/redirect/GET : un rafraîchissement ne renvoie pas la proposition
        return redirect('/admin/formulaires/proposer-publication')->with('sent', $proposal->title);
    }

    /** Retrait par son auteur d'une proposition encore en attente */
    public function withdraw(int $id): RedirectResponse
    {
        // Pas de liaison implicite (404 avant le contrôle des droits) : proposition d'un autre ou introuvable => 403
        $proposal = Proposal::find($id);
        abort_unless($proposal && $proposal->user_id === auth()->id() && $proposal->isPending(), 403);

        $proposal->update(['status' => ProposalStatus::WITHDRAWN->value]);

        return redirect('/admin/formulaires/proposer-publication')->with('withdrawn', $proposal->title);
    }
}
