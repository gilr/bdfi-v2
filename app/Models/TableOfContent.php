<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Voir commentaires dans AuthorPublication
//use Illuminate\Database\Eloquent\SoftDeletes;
//use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

class TableOfContent extends Pivot
{
    use HasFactory;
    use Userstamps;
//    use SoftDeletes;
//    use RevisionableTrait;

/*
    protected $revisionEnabled = true;

    //Remove old revisions (works only when used with $historyLimit)
    protected $revisionCleanup = true;
    //Stop tracking revisions after 'N' changes have been made.
    protected $historyLimit = 10000;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    protected $dontKeepRevisionOf = ['deleted_by'];
*/

    public $incrementing = true;

    public function author(): BelongsTo
    {
        return $this->belongsTo('App\Models\Author');
    }
    public function title(): BelongsTo
    {
        return $this->belongsTo('App\Models\Title');
    }

    /*
     * Accesseurs supplémentaires
    */
    public function recordName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'auteur-texte "' . $this->author->name . '-' . $this->title->name . '"',
        );
    }

}
