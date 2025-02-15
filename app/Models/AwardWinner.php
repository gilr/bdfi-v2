<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

class AwardWinner extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use RevisionableTrait;

    protected $revisionEnabled = true;
     
    //Remove old revisions (works only when used with $historyLimit)
    protected $revisionCleanup = true;
    //Stop tracking revisions after 'N' changes have been made.
    protected $historyLimit = 10000;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    protected $dontKeepRevisionOf = ['deleted_by'];

    public function award_category(): BelongsTo
    {
        return $this->belongsTo('App\Models\AwardCategory');
    }
    public function author(): BelongsTo
    {
        return $this->belongsTo('App\Models\Author', 'author_id');
    }
    public function author2(): BelongsTo
    {
        return $this->belongsTo('App\Models\Author', 'author2_id');
    }
    public function author3(): BelongsTo
    {
        return $this->belongsTo('App\Models\Author', 'author3_id');
    }
    public function titleRef(): BelongsTo
    {
        return $this->belongsTo('App\Models\Title', 'title_id');
    }
    
    /*
     * Accesseurs supplémentaires
    */
    public function recordName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'gagnant de prix "' . $this->name . '"',
        );
    }
}
