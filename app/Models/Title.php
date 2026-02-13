<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Enums\TitleType;
use App\Enums\GenreAppartenance;
use App\Enums\GenreStat;
use App\Enums\IsNovelization;
use App\Enums\AudienceTarget;

class Title extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use RevisionableTrait;
    use Sluggable;

    protected $casts = [
        'type' => TitleType::class,
        'is_genre' => GenreAppartenance::class,
        'is_novelization' => IsNovelization::class,
        'genre_stat' => GenreStat::class,
        'target_audience' => AudienceTarget::class,
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

    public function publications(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Publication', 'table_of_content')
                    ->withTimestamps()
                    ->withPivot(['level', 'order', 'start_page', 'end_page', 'deleted_at'])
                    ->using('App\Models\TableOfContent')
                    ->wherePivot('deleted_at', null)
                    ->orderBy('approximate_parution', 'asc');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo('App\Models\Title');
    }

    public function variants(): HasMany
    {
        return $this->hasMany('App\Models\Title', 'parent_id')
            ->where('variant_type', '!=', 'feuilleton')
            ->orderBy('copyright_fr', 'asc');
    }

    public function episodes(): HasMany
    {
        return $this->hasMany('App\Models\Title', 'parent_id')
            ->where('variant_type', 'feuilleton')
            ->orderBy('copyright_fr', 'asc');
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Author')
                    ->withTimestamps();
    }

    public function cycles(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Cycle')
                    ->withTimestamps()
                    ->withPivot('number', 'order', 'deleted_at')
                    ->wherePivot('deleted_at', null);
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
            get: fn($value) => 'titre "' . $this->name . '"',
        );
    }

}
