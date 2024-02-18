<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\Pivot;
//use Illuminate\Database\Eloquent\SoftDeletes;
//use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;
use App\Enums\AuthorPublicationRole;

// Pbs Filament :
//  - fonctionne sans soft delete : sinon on les voit même après détachement - il faudrait toujours filtrer les tashed
//  - fonctionne sans révisionnable : sinon lors du détachement, il cherche un champ "revisionable_id" qui n'existe pas

class AuthorPublication extends Pivot
{
    use HasFactory;
    use Userstamps;
//    use SoftDeletes;
//    use RevisionableTrait;

    protected $casts = [
        'role' => AuthorPublicationRole::class
    ];
/*
    protected $revisionEnabled = true;

    //Remove old revisions (works only when used with $historyLimit)
    protected $revisionCleanup = true;
    //Stop tracking revisions after 'N' changes have been made.
    protected $historyLimit = 10000;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    protected $dontKeepRevisionOf = ['deleted_by'];
*/
    public $incrementing = true;

    public function author()
    {
        return $this->belongsTo('App\Models\Author');
    }
    public function publication()
    {
        return $this->belongsTo('App\Models\Publication');
    }

    /*
     * Accesseurs supplémentaires
    */
    public function recordName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'Auteur-Publi "' . $this->author->name . '-' . $this->publication->name . '"',
        );
    }

}
