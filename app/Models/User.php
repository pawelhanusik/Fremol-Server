<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use League\Flysystem\FileNotFoundException;

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

    public function createdConversations() {
        return $this->hasMany(Conversation::class, 'creator_id');
    }

    public function conversations() {
        return $this->belongsToMany(Conversation::class)->withTimestamps();
    }

    public function addToConversation($conversation) {
        if(is_string($conversation)) {
            $conversation = Conversation::whereName($conversation)->firstOrFail();
        }
        $this->conversations()->sync($conversation, false);
    }
    public function removeFromConversation($conversation) {
        if(is_string($conversation)) {
            $conversation = Conversation::whereName($conversation)->firstOrFail();
        }
        $this->conversations()->detach($conversation);
    }

    public function messages() {
        return $this->hasMany(Message::class);
    }

    public function totalAttachmentsCount() {
        return $this->messages()->whereNotNull('attachment_url')->count();
    }
    public function totalAttachmentsSize() {
        $ret = 0;
        $attachments = $this->messages()->whereNotNull('attachment_url')->pluck('attachment_url')->all();
        foreach ($attachments as $attachment_path) {
            try {
                $ret += Storage::size($attachment_path);
            } catch (FileNotFoundException $e) {

            }
        }
        return $ret;
    }
}
