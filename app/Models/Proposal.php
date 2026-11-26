<?php

namespace App\Models;

use App\Enums\ProposalStatus;
use App\Enums\ProposalType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Proposition d'un contributeur (voir migration create_proposals_table). Le contenu proposé est dans "payload" :
 * pour un ajout d'ouvrage, un livre au format JSON canonique de l'import (BookValidator / BookImporter).
 */
class Proposal extends Model
{
    protected $fillable = [
        'user_id', 'type', 'status', 'target_type', 'target_id',
        'title', 'publisher_name', 'approximate_parution', 'isbn', 'payload',
        'review_note', 'reviewed_by', 'reviewed_at', 'result_id',
    ];

    protected $casts = [
        'type'        => ProposalType::class,
        'status'      => ProposalStatus::class,
        'payload'     => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === ProposalStatus::SUBMITTED;
    }
}
