<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;
use App\Enums\AwardCategoryType;
use App\Enums\AwardCategoryGenre;

class AwardCategory extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use RevisionableTrait;

    protected $casts = [
        'type' => AwardCategoryType::class,
        'genre' => AwardCategoryGenre::class
    ];
    protected $revisionEnabled = true;
     
    //Remove old revisions (works only when used with $historyLimit)
    protected $revisionCleanup = true;
    //Stop tracking revisions after 'N' changes have been made.
    protected $historyLimit = 1000;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    protected $dontKeepRevisionOf = ['deleted_by'];

    public function award()
    {
        return $this->belongsTo('App\Models\Award');
    }

    public function award_winners()
    {
        return $this->hasMany('App\Models\AwardWinner');
    }

    /*
     * Accesseurs supplémentaires
    */

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->award->name . ', ' . $this->name,
        );
    }

    public function recordName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'catégorie prix "' . $this->name . '"',
        );
    }
}
