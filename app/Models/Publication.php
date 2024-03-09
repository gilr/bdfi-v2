<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;
use App\Enums\PublicationSupport;
use App\Enums\PublicationContent;
use App\Enums\PublicationFormat;
use App\Enums\GenreAppartenance;
use App\Enums\GenreStat;
use App\Enums\AudienceTarget;
use App\Enums\PublicationStatus;

class Publication extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use RevisionableTrait;

    protected $casts = [
        'support' => PublicationSupport::class,
        'format' => PublicationFormat::class,
        'type' => PublicationContent::class,
        'is_genre' => GenreAppartenance::class,
        'genre_stat' => GenreStat::class,
        'target_audience' => AudienceTarget::class,
        'status' => PublicationStatus::class,
    ];

    protected $revisionEnabled = true;

    //Remove old revisions (works only when used with $historyLimit)
    protected $revisionCleanup = true;
    //Stop tracking revisions after 'N' changes have been made.
    protected $historyLimit = 10000;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    protected $dontKeepRevisionOf = ['deleted_by'];

    public function publisher()
    {
        return $this->belongsTo('App\Models\Publisher');
    }
    public function collections()
    {
        return $this->belongsToMany('App\Models\Collection')
                    ->withTimestamps()
                    ->withPivot('id', 'order','number');
    }
    public function reprints()
    {
        return $this->hasMany('App\Models\Reprint');
    }
    public function titles()
    {
        return $this->belongsToMany('App\Models\Title', 'table_of_content')
                    ->withTimestamps()
                    ->withPivot(['level', 'order', 'start_page', 'end_page'])
                    ->using('App\Models\TableOfContent');
//                    ->orderByPivot('start_page', 'asc');
    }
    public function authors()
    {
        return $this->belongsToMany('App\Models\Author')
                    ->withTimestamps()
                    ->withPivot(['id', 'role']) // Add extra fields to the 'pivot' object
                    ->using('App\Models\AuthorPublication');
    }

    /*
     * Accesseurs supplémentaires
    */
    public function fullName(): Attribute
    {
        if (isset($this->publisher))
        {
            return Attribute::make(
                get: fn($value) => $this->name . " (" . $this->publisher->name . ")",
            );
        }
        else
        {
            return Attribute::make(
                get: fn($value) => $this->name . " (éditeur inconnu)",
            );

        }
    }

    public function recordName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'publication "' . $this->name . '"',
        );
    }

}
