<?php

namespace App\Models;

use App\eSweldo\Computations\Payrollable;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, Payrollable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'employee_id',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'type',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Hash the user password when setting it
     *
     * @param  string $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    /**
     * Generate a path for a user
     *
     * @return string
     */
    public function path()
    {
        return '/employees/' . $this->id;
    }

    /**
     * Get the user's profile
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Name of the user
     *
     * @return string
     */
    public function name()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Create the user with the profile
     *
     * @param array $attributes
     * @return mixed
     */
    public static function persist(array $attributes)
    {
        $attributes['company_id'] = companyId();

        return DB::transaction(function () use ($attributes) {
            /** @var User $user */
            $user = (new self)->create($attributes);

            $user->profile()->create($attributes);

            return $user;
        });
    }

    /**
     * Get the users from the same company as the signed-in user
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeSameCompany(Builder $query)
    {
        return $query->where('company_id', companyId());
    }

    /**
     * Check if the user has profile
     *
     * @return bool
     */
    public function hasProfile()
    {
        return ! ! $this->profile;
    }
}
