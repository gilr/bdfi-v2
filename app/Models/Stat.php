<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

class Stat extends Model
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

    protected $casts = [
        'date' => 'datetime:Y-m-d',
    ];

    /*
     * Accesseurs supplémentaires
    */
    public function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->date->format('Y-m-d'),
        );
    }

    public function recordName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'stat "' . $this->name . '"',
        );
    }

}
