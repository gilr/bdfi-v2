<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Enums\CollectionSupport;
use App\Enums\CollectionFormat;
use App\Enums\CollectionCible;
use App\Enums\CollectionGenre;
use App\Enums\CollectionType;
use App\Enums\CollectionPeriodicity;
use App\Enums\QualityStatus;

class Collection extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use RevisionableTrait;
    use Sluggable;

    protected $casts = [
        'support' => CollectionSupport::class,
        'format' => CollectionFormat::class,
        'cible' => CollectionCible::class,
        'genre' => CollectionGenre::class,
        'type' => CollectionType::class,
        'periodicity' => CollectionPeriodicity::class,
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

    public function publisher(): BelongsTo
    {
        return $this->belongsTo('App\Models\Publisher');
    }
    public function publisher2(): BelongsTo
    {
        return $this->belongsTo('App\Models\Publisher', 'publisher2_id');
    }
    public function publisher3(): BelongsTo
    {
        return $this->belongsTo('App\Models\Publisher', 'publisher3_id');
    }
    public function parent(): BelongsTo
    {
        return $this->belongsTo('App\Models\Collection');
    }
    public function subcollections(): HasMany
    {
        return $this->hasMany('App\Models\Collection', 'parent_id');
    }
    public function publications(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Publication')
                ->where('status', 'paru')
                ->withTimestamps()
                ->withPivot('id', 'order','number')
                ->orderByPivot('order', 'asc');
    }
    public function publication_announced(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Publication')
                ->where('status', 'annonce')
                ->withTimestamps()
                ->withPivot('id', 'order','number')
                ->orderByPivot('order', 'asc');
    }
    public function publication_proposals(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Publication')
                ->where('status', 'proposal')
                ->withTimestamps()
                ->withPivot('id', 'order','number')
                ->orderByPivot('order', 'asc');
    }
    public function publication_abandonned(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Publication')
                ->where('status', 'abandon')
                ->withTimestamps()
                ->withPivot('id', 'order','number')
                ->orderByPivot('order', 'asc');
    }

    /**
     * Get the collection's article.
     */
    public function article(): MorphOne
    {
        return $this->morphOne(Article::class, 'item');
    }

    /*
     * Accesseurs supplémentaires
    */
    public function qualifiedName(): Attribute
    {
        return Attribute::make(
            get: fn($value) =>
            $this->format === CollectionFormat::GF ?
                $this->name . ", grand format" :
            ($this->support === CollectionSupport::NUMERIQUE ?
                $this->name . ", numérique" :
                $this->name),
        );
    }

    public function fullName(): Attribute
    {
       //get: fn($value) => Str::limit($this->name . " (" . $this->publisher->name . ")", 50),
        return Attribute::make(
            get: fn($value) =>
            $this->publisher3 !== NULL ?
                $this->qualifiedName . " (" . $this->publisher->name . ", " . $this->publisher2->name . " et " . $this->publisher3->name .")" :
            ($this->publisher2 !== NULL ?
                $this->qualifiedName . " (" . $this->publisher->name . " et " . $this->publisher2->name .")" :
                $this->qualifiedName . " (" . $this->publisher->name . ")"),
        );
    }
    public function fullShortName(): Attribute
    {
       //get: fn($value) => Str::limit($this->name . " (" . $this->publisher->name . ")", 50),
        return Attribute::make(
            get: fn($value) =>
            $this->publisher3 !== NULL ?
                $this->shortname . " (" . $this->publisher->name . ", " . $this->publisher2->name . " et " . $this->publisher3->name .")" :
            ($this->publisher2 !== NULL ?
                $this->shortname . " (" . $this->publisher->name . " et " . $this->publisher2->name .")" :
                $this->shortname . " (" . $this->publisher->name . ")"),
        );
    }

    public function recordName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'collection "' . $this->name . '"',
        );
    }

}
