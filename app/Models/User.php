<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Wildside\Userstamps\Userstamps;
use App\Enums\UserRole;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use Userstamps;
    use SoftDeletes;
    use RevisionableTrait;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => UserRole::class
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    protected $revisionEnabled = true;

    //Remove old revisions (works only when used with $historyLimit)
    protected $revisionCleanup = true;
    //Stop tracking revisions after 'N' changes have been made.
    protected $historyLimit = 100;

    protected $revisionForceDeleteEnabled = true;
    protected $revisionCreationsEnabled = true;

    protected $dontKeepRevisionOf = ['deleted_by'];

    public function revisionable_type() {
        return "Utilisateur";
    }

    public function collections()
    {
        return $this->belongsToMany('App\Models\Collection', 'user_collection', 'user_id', 'collection_id')
                    ->withPivot(['status'])
                    ->withTimestamps();
    }
    public function collections_en_cours()
    {
        return $this->belongsToMany('App\Models\Collection', 'user_collection', 'user_id', 'collection_id')
                    ->withPivot(['status'])
                    ->wherePivot('status', 'en_cours');
    }
    public function collections_quasi_ok()
    {
        return $this->belongsToMany('App\Models\Collection', 'user_collection', 'user_id', 'collection_id')
                    ->withPivot(['status'])
                    ->wherePivot('status', 'quasi_ok');
    }
    public function collections_terminees()
    {
        return $this->belongsToMany('App\Models\Collection', 'user_collection', 'user_id', 'collection_id')
                    ->withPivot(['status'])
                    ->wherePivot('status', 'terminee');
    }
    public function collections_en_pause()
    {
        return $this->belongsToMany('App\Models\Collection', 'user_collection', 'user_id', 'collection_id')
                    ->withPivot(['status'])
                    ->wherePivot('status', 'en_pause');
    }
    public function collections_cachees()
    {
        return $this->belongsToMany('App\Models\Collection', 'user_collection', 'user_id', 'collection_id')
                    ->withPivot(['status'])
                    ->wherePivot('status', 'cachee');
    }

    public function publications()
    {
        return $this->belongsToMany('App\Models\Publication', 'user_publication', 'user_id', 'publication_id')
                    ->withTimestamps();
    }

    public function hasSysAdminRole()
    {
        return $this->role->value === UserRole::SYSADMIN->value;
    }
    public function isSysAdmin()
    {
        return $this->role->value === UserRole::SYSADMIN->value;
    }

    public function hasAdminRole()
    {
        return (($this->role->value === UserRole::ADMIN->value) || ($this->role->value === UserRole::SYSADMIN->value));
    }
    public function isAdmin()
    {
        return ($this->role->value === UserRole::ADMIN->value);
    }

    public function hasMemberRole()
    {
        return (($this->role->value === UserRole::ADMIN->value) || ($this->role->value === UserRole::SYSADMIN->value) || ($this->role->value === UserRole::MEMBER->value));
    }
    public function isMember()
    {
        return ($this->role->value === UserRole::MEMBER->value);
    }

    public function hasGuestRole()
    {
        return (($this->role->value === UserRole::ADMIN->value) || ($this->role->value === UserRole::SYSADMIN->value) || ($this->role->value === UserRole::MEMBER->value) || ($this->role->value === UserRole::GUEST->value));
    }
    public function isGuest()
    {
        return ($this->role->value === UserRole::GUEST->value);
    }

    public function isUser()
    {
        return ($this->role->value === UserRole::USER->value);
    }

    /*
     * Accesseurs supplémentaires
    */
    public function recordName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => 'User "' . $this->name . '"',
        );
    }

}
