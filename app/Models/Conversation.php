<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;
    protected $fillable = ['name'];

    public function creator() {
        return $this->belongsTo(User::class);
    }
    
    public function users() {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function messages() {
        return $this->hasMany(Message::class);
    }
}
