<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
   protected $fillable = [
        'canal',
        'contenu',
        'lu',
        'date_envoi',
        'user_id',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
