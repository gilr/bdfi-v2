<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;
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

    public function publications(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Publication', 'table_of_content')
                    ->withTimestamps()
                    ->withPivot(['level', 'order', 'start_page', 'end_page'])
                    ->using('App\Models\TableOfContent')
                    ->orderBy('approximate_parution', 'asc');
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\Title');
    }

    public function variants()
    {
        return $this->hasMany('App\Models\Title', 'parent_id')
            ->where('variant_type', '!=', 'feuilleton')
            ->orderBy('copyright_fr', 'asc');
    }

    public function episodes()
    {
        return $this->hasMany('App\Models\Title', 'parent_id')
            ->where('variant_type', 'feuilleton')
            ->orderBy('copyright_fr', 'asc');
    }

    public function authors()
    {
        return $this->belongsToMany('App\Models\Author')
                    ->withTimestamps();
    }

    public function cycles()
    {
        return $this->belongsToMany('App\Models\Cycle')
                    ->withTimestamps()
                    ->withPivot('number', 'order');
    }

    /*
     * Accesseurs supplémentaires
    */
    public function recordName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'titre "' . $this->name . '"',
        );
    }

}
