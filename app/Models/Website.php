<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;

class Website extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use RevisionableTrait;

    protected $revisionEnabled = true;
     
    //Remove old revisions (works only when used with $historyLimit)
    protected $revisionCleanup = true;
    //Stop tracking revisions after 'N' changes have been made.
    protected $historyLimit = 100;

	protected $revisionForceDeleteEnabled = true;
	protected $revisionCreationsEnabled = true;

    protected $dontKeepRevisionOf = ['deleted_by'];

    public function revisionable_type() {
        return "Site web";
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo('App\Models\Author');
    }
    public function website_type(): BelongsTo
    {
        return $this->belongsTo('App\Models\WebsiteType');
    }
    public function country(): BelongsTo
    {
        return $this->belongsTo('App\Models\Country');
    }

    /*
     * Accesseurs supplémentaires
    */

    public function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $this->url,
        );
    }

    public function recordName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'website "' . $this->name . '"',
        );
    }

}
