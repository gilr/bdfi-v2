<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;
use App\Enums\AuthorGender;
use App\Enums\QualityStatus;

class Illustrator extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use RevisionableTrait;

    protected $casts = [
        'gender' => AuthorGender::class,
        'quality' => QualityStatus::class,
    ];

    protected $revisionEnabled = true;

    //Remove old revisions (works only when used with $historyLimit)
    protected $revisionCleanup = true;
    //Stop tracking revisions after 'N' changes have been made.
    protected $historyLimit = 10000;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    protected $dontKeepRevisionOf = ['deleted_by'];

    public function author(): BelongsTo
    {
        return $this->belongsTo('App\Models\Author', 'author_id');
    }

    /*
     * Accesseurs supplémentaires
    */
    public function recordName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'illustrateur "' . $this->name . '"',
        );
    }

}
