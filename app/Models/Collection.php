<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;
use App\Enums\CollectionSupport;
use App\Enums\CollectionFormat;
use App\Enums\CollectionCible;
use App\Enums\CollectionGenre;
use App\Enums\CollectionType;
use App\Enums\QualityStatus;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Collection extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use RevisionableTrait;

    protected $casts = [
        'support' => CollectionSupport::class,
        'format' => CollectionFormat::class,
        'cible' => CollectionCible::class,
        'genre' => CollectionGenre::class,
        'type' => CollectionType::class,
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

    public function publisher()
    {
        return $this->belongsTo('App\Models\Publisher');
    }
    public function publisher2()
    {
        return $this->belongsTo('App\Models\Publisher', 'publisher2_id');
    }
    public function publisher3()
    {
        return $this->belongsTo('App\Models\Publisher', 'publisher3_id');
    }
    public function parent()
    {
        return $this->belongsTo('App\Models\Collection');
    }
    public function subcollections()
    {
        return $this->hasMany('App\Models\Collection', 'parent_id');
    }
    public function publications()
    {
        return $this->belongsToMany('App\Models\Publication')
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
    public function fullName(): Attribute
    {
        return Attribute::make(
            //get: fn($value) => Str::limit($this->name . " (" . $this->publisher->name . ")", 50),
            get: fn($value) => $this->name . " (" . $this->publisher->name . ")",
        );
    }


    public function recordName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'collection "' . $this->name . '"',
        );
    }

}
