<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Enums\CycleType;
use App\Enums\QualityStatus;

class Cycle extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use RevisionableTrait;
    use Sluggable;

    protected $casts = [
        'type' => CycleType::class,
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo('App\Models\Cycle');
    }

    public function subseries(): HasMany
    {
        return $this->hasMany('App\Models\Cycle', 'parent_id');
    }

    public function titles(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Title')
                    ->withTimestamps()
                    ->withPivot('number', 'order', 'deleted_at')
                    ->wherePivot('deleted_at', null)
                    ->orderByPivot('order', 'asc');
    }


    /**
     * Get the authors of the cycle
     */
    public function getAuthors()
    {
        return Author::whereHas('titles', function ($query) {
            $query->whereHas('cycles', function ($q) {
                $q->where('cycles.id', $this->id);
            });
        })->distinct()->get();
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
            get: fn($value) => 'cycle "' . $this->name . '"',
        );
    }

}
