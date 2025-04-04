<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Enums\PublisherType;
use App\Enums\QualityStatus;

class Publisher extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use RevisionableTrait;
    use Sluggable;

    protected $casts = [
        'type' => PublisherType::class,
        'quality' => QualityStatus::class,
    ];
    protected $revisionEnabled = true;

    //Remove old revisions (works only when used with $historyLimit)
    protected $revisionCleanup = true;
    //Stop tracking revisions after 'N' changes have been made.
    protected $historyLimit = 1000;

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
                'source' => 'name'
            ]
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo('App\Models\Country');
    }

    public function collections(): HasMany
    {
        return $this->hasMany('App\Models\Collection');
    }
    public function collections2(): HasMany
    {
        return $this->hasMany('App\Models\Collection', 'publisher2_id');
    }
    public function collections3(): HasMany
    {
        return $this->hasMany('App\Models\Collection', 'publisher3_id');
    }

    public function publications(): HasMany
    {
        return $this->hasMany('App\Models\Publication');
    }

    public function publicationsWithoutCollection(): HasMany
    {
        return $this->hasMany('App\Models\Publication')
                ->whereNotIn('id', function ($query) {
                    $query->select('publication_id')->from('collection_publication');
                });
    }

    /*
     * Accesseurs supplémentaires
    */
    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->name,
        );
    }

    public function recordName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'éditeur "' . $this->name . '"',
        );
    }

}
