<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;
use Cviebrock\EloquentSluggable\Sluggable;

class Reprint extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use RevisionableTrait;
    use Sluggable;

    protected $revisionEnabled = true;

    //Remove old revisions (works only when used with $historyLimit)
    protected $revisionCleanup = true;
    //Stop tracking revisions after 'N' changes have been made.
    protected $historyLimit = 10000;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    protected $dontKeepRevisionOf = ['deleted_by'];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'fullname'
            ]
        ];
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo('App\Models\Publication');
    }

    /*
     * Accesseurs supplémentaires
    */
    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->publication->name . " - Retirage " . StrDateformat($this->approximate_parution),
        );
    }
    public function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->publication->name . " - Retirage " . StrDateformat($this->approximate_parution),
        );
    }
    public function recordName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'retirage "' . $this->name . '"',
        );
    }
    public function pubName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->publication->name,
        );
    }

}
