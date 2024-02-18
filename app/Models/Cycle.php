<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;
use App\Enums\CycleType;
use App\Enums\QualityStatus;

class Cycle extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use RevisionableTrait;

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

    public function parent()
    {
        return $this->belongsTo('App\Models\Cycle');
    }

    public function subseries()
    {
        return $this->hasMany('App\Models\Cycle', 'parent_id');
    }

    public function titles()
    {
        return $this->belongsToMany('App\Models\Title')
                    ->withTimestamps()
                    ->withPivot('number', 'order')
                    ->orderByPivot('order', 'asc');
    }

    /*
     * Accesseurs supplémentaires
    */
    public function recordName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'cycle "' . $this->name . '"',
        );
    }

}
