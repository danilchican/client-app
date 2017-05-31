<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'passport', 'email', 'password',
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
     * Getting payments for a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'owner_id');
    }

    /**
     * Getting sent bills for a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sentBills()
    {
        return $this->hasMany(Bill::class, 'sender_id');
    }

    /**
     * Getting received bills for a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receivedBills()
    {
        return $this->hasMany(Bill::class, 'receiver_id');
    }

    /**
     * Getting history for a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function history()
    {
        return $this->hasMany(History::class, 'owner_id');
    }
}
