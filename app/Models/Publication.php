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
    use Sluggable;

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

    public function first_edition(): BelongsTo
    {
        return $this->belongsTo('App\Models\Publication');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo('App\Models\Publisher');
    }
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Collection')
                    ->withTimestamps()
                    ->withPivot('id', 'order','number', 'deleted_at')
                    ->wherePivot('deleted_at', null);
    }
    public function reprints(): HasMany
    {
        return $this->hasMany('App\Models\Reprint');
    }
    public function titles(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Title', 'table_of_content')
                    ->withTimestamps()
                    ->withPivot(['level', 'order', 'start_page', 'end_page', 'deleted_at'])
                    ->using('App\Models\TableOfContent')
                    ->wherePivot('deleted_at', null);
//                    ->orderByPivot('start_page', 'asc');
    }
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Author')
                    ->withTimestamps()
                    ->withPivot(['id', 'role', 'deleted_at']) // Add extra fields to the 'pivot' object
                    ->using('App\Models\AuthorPublication')
                    ->wherePivot('deleted_at', null); // Exclude soft-deleted relationships
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
