<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'email_verified_at',
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function conversations() {
        return $this->belongsToMany(Conversation::class)->withTimestamps();
    }

    public function addToConversation($conversation) {
        if(is_string($conversation)) {
            $conversation = Conversation::whereName($conversation)->firstOrFail();
        }
        $this->conversations()->attach($conversation);
    }
    public function removeFromConversation($conversation) {
        if(is_string($conversation)) {
            $conversation = Conversation::whereName($conversation)->firstOrFail();
        }
        $this->conversations()->detach($conversation);
    }
}
