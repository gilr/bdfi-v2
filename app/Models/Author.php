<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Venturecraft\Revisionable\RevisionableTrait;
use Wildside\Userstamps\Userstamps;
use App\Enums\AuthorGender;
use App\Enums\QualityStatus;
use App\Enums\TitleType;
use DB;
use Illuminate\Database\Eloquent\Relations\MorphOne;


class Author extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use RevisionableTrait;

    protected $casts = [
        'gender' => AuthorGender::class,
        'quality' => QualityStatus::class,
    ];

    protected $revisionEnabled = true;
     
    //Remove old revisions (works only when used with $historyLimit)
    protected $revisionCleanup = true;
    //Stop tracking revisions after 'N' changes have been made.
    protected $historyLimit = 10000;

	protected $revisionForceDeleteEnabled = true;
	protected $revisionCreationsEnabled = true;

    protected $dontKeepRevisionOf = ['deleted_by'];

    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }
    public function country2()
    {
        return $this->belongsTo('App\Models\Country', 'country2_id');
    }

    public function winners()
    {
        return $this->hasMany('App\Models\AwardWinner', 'author_id');
    }
    public function winners2()
    {
        return $this->hasMany('App\Models\AwardWinner', 'author2_id');
    }
    public function winners3()
    {
        return $this->hasMany('App\Models\AwardWinner', 'author3_id');
    }

    public function websites()
    {
        return $this->hasMany('App\Models\Website');
    }

    public function signatures()
    {
        return $this->belongsToMany('App\Models\Author', 'signatures', 'author_id', 'signature_id')
                    ->using('App\Models\Signature')
                    ->wherePivotNull('deleted_at')
                    ->withTimestamps();
    }

    public function references()
    {
        return $this->belongsToMany('App\Models\Author', 'signatures', 'signature_id', 'author_id')
                    ->using('App\Models\Signature')
                    ->wherePivotNull('deleted_at')
                    ->withTimestamps();
    }

    public function relations()
    {
        return $this->belongsToMany('App\Models\Author', 'relationships', 'author1_id', 'author2_id')
                    ->using('App\Models\Relationship')
                    ->withPivot(['relationship_type_id'])
                    ->wherePivotNull('deleted_at')
                    ->withTimestamps();
    }
    public function inverserelations()
    {
        return $this->belongsToMany('App\Models\Author', 'relationships', 'author2_id', 'author1_id')
                    ->using('App\Models\Relationship')
                    ->withPivot(['relationship_type_id'])
                    ->wherePivotNull('deleted_at')
                    ->withTimestamps();
    }

    public function publications()
    {
        return $this->belongsToMany('App\Models\Publication')
                    ->where('status', 'paru')
                    ->withPivot(['id', 'role'])
                    ->withTimestamps()
                    ->using('App\Models\AuthorPublication');
    }

    public function titles()
    {
        return $this->belongsToMany('App\Models\Title')
                    ->orderBy('copyright', 'asc')
                    ->withTimestamps();
    }

    public function writtenDocuments()
    {
        return $this->hasMany('App\Models\Document');
    }


    /**
     * Get the collection's documents.
     */
    public function aboutDocument(): MorphOne
    {
        return $this->morphOne(Document::class, 'item');
    }


/*
    public function title_novels()
    {
        return $this->belongsToMany('App\Models\Title')
                    ->where('is_serial', 0)
                    ->where(function ($query) {
                        $query
                        ->where('type', TitleType::ROMAN)
                        ->orWhere('type', TitleType::FIXUP)
                        ->orWhere('type', TitleType::NOVELLA);
                    })
                    ->orderBy('copyright', 'asc')
                    ->withTimestamps();
    }
    public function title_collections()
    {
        return $this->belongsToMany('App\Models\Title')
                    ->where(function ($query) {
                        $query
                        ->where('type', TitleType::RECUEIL)
                        ->orWhere('type', TitleType::ANTHO)
                        ->orWhere('type', TitleType::OMNIBUS)
                        ->orWhere('type', TitleType::CHRONIQUES);
                    })
                    ->orderBy('copyright', 'asc')
                    ->withTimestamps();
    }

    public function title_shorts()
    {
        return $this->belongsToMany('App\Models\Title')
                    ->where('is_serial', 0)
                    ->where(function ($query) {
                        $query
                        ->where('type', TitleType::NOUVELLE)
                        ->orWhere('type', TitleType::SHORTSHORT);
                    })
                    ->orderBy('copyright', 'asc')
                    ->withTimestamps();
    }

    public function title_others()
    {
        return $this->belongsToMany('App\Models\Title')
                    ->where(function ($query) {
                        $query
                        ->where('type', TitleType::POEME)
                        ->orWhere('type', TitleType::CHANSON)
                        ->orWhere('type', TitleType::THEATRE)
                        ->orWhere('type', TitleType::SCENARIO)
                        ->orWhere('type', TitleType::RADIO);
                    })
                    ->orderBy('copyright', 'asc')
                    ->withTimestamps();
    }

    public function title_nonfictions()
    {
        return $this->belongsToMany('App\Models\Title')
                    ->where(function ($query) {
                        $query
                        ->where('type', TitleType::LETTRE)
                        ->orWhere('type', TitleType::PREFACE)
                        ->orWhere('type', TitleType::POSTFACE)
                        ->orWhere('type', TitleType::BIBLIO)
                        ->orWhere('type', TitleType::BIO)
                        ->orWhere('type', TitleType::ESSAI)
                        ->orWhere('type', TitleType::GUIDE)
                        ->orWhere('type', TitleType::ARTICLE)
                        ->orWhere('type', TitleType::LIVREJEU)
                        ->orWhere('type', TitleType::JEU);
                    })
                    ->orderBy('copyright', 'asc')
                    ->withTimestamps();
    }
*/
    /*
     * Accesseurs supplémentaires
    */
    public function recordName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'auteur "' . $this->fullName . '"',
        );
    }

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => ($this->first_name == "" ? $this->name : sanitizeFirstName($this->first_name) . " " . $this->name),
        );
    }

    public function getWebsitesCountAttribute()
    {
        return $this->websites()->count();
    }

    public function getReferencesCountAttribute()
    {
        return $this->references()->count();
    }

    public function getSignaturesCountAttribute()
    {
        return $this->signatures()->count();
    }
    public function getRelationsCountAttribute()
    {
        return $this->relations()->count();
    }
    public function getInverseRelationsCountAttribute()
    {
        return $this->inverserelations()->count();
    }

    /*
     * autres Fonctions publices
    */
    public static function getBirthsOfDay()
    {
        $today = date("-m-d");

        $auteurs = DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death FROM authors WHERE
            SUBSTR(birth_date,5,6) = '$today'
            ORDER BY birth_date");

        return $auteurs;;
    }
    public static function getDeathsOfDay()
    {
        $today = date("-m-d");

        $auteurs = DB::select ("SELECT id, nom_bdfi, name, first_name, birth_date, date_death FROM authors WHERE
            SUBSTR(date_death,5,6) = '$today'
            ORDER BY date_death");

        return $auteurs;;
    }


}
