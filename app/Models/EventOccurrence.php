<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

class EventOccurrence extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use RevisionableTrait;

    protected $casts = [
        'start_date' => 'datetime:Y-m-d',
        'end_date' => 'datetime:Y-m-d',
        'publication_date' => 'datetime',
        'is_confirmed' => 'boolean',
    ];

    protected $revisionEnabled = true;

    //Remove old revisions (works only when used with $historyLimit)
    protected $revisionCleanup = true;
    //Stop tracking revisions after 'N' changes have been made.
    protected $historyLimit = 1000;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    protected $dontKeepRevisionOf = ['deleted_by'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /*
     * Accesseurs supplémentaires
    */

    // Lieu de l'occurrence s'il est renseigné, sinon lieu habituel de l'évènement
    public function effectivePlace(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->place ?: $this->event?->place,
        );
    }

    // Identifiant textuel : libellé s'il existe, sinon nom de l'évènement + année (si le nom ne la contient pas déjà)
    public function fullName(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if ($this->label) {
                    return $this->label;
                }
                $name = $this->event?->name ?? '';
                $year = $this->start_date?->format('Y') ?? '';
                return str_contains($name, $year) ? $name : trim("$name $year");
            },
        );
    }

    // Occurrences non terminées (en cours ou à venir)
    public function scopeUpcoming(Builder $query): void
    {
        $query->where('end_date', '>=', date('Y-m-d'));
    }

    public function recordName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'occurrence d\'évènement "' . $this->full_name . '"',
        );
    }
}
