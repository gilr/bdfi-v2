<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;
use App\Enums\PublisherType;
use App\Enums\QualityStatus;

class Publisher extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use RevisionableTrait;

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

     public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    public function collections()
    {
        return $this->hasMany('App\Models\Collection');
    }
    public function collections2()
    {
        return $this->hasMany('App\Models\Collection', 'publisher2_id');
    }
    public function collections3()
    {
        return $this->hasMany('App\Models\Collection', 'publisher3_id');
    }

    public function publications()
    {
        return $this->hasMany('App\Models\Publication');
    }

    public function publicationsWithoutCollection()
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
